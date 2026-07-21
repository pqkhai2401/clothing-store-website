<?php

namespace App\Services;

use App\Models\Order;
use App\Models\StockIssue;
use App\Models\StockIssueLog;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;

/**
 * =============================================================================
 *  SINH PHIẾU XUẤT KHO CHO ĐƠN HÀNG (chỉ CHỨNG TỪ — KHÔNG trừ kho)
 * =============================================================================
 *
 * Từ khi áp dụng mô hình "Hold & Release", kho đã được trừ FIFO NGAY LÚC CHECKOUT
 * (xem CheckoutController::store → InventoryBatchService::consumeFifo với
 * reference_type = 'order'). Vì vậy phiếu xuất kho ở đây chỉ đóng vai trò CHỨNG TỪ
 * ghi nhận việc hàng đã thực sự xuất cho đơn — tuyệt đối KHÔNG gọi consumeFifo nữa,
 * nếu không hàng sẽ bị trừ HAI LẦN.
 *
 * Giá vốn từng dòng lấy ngược từ SỔ CÁI (stock_movements của chính đơn) → phản ánh
 * đúng giá nhập của các LÔ đã bị trừ theo FIFO, chính xác hơn giá bình quân trên
 * product_variants.cost_price.
 *
 * LƯU Ý: phải được gọi bên trong 1 DB::transaction do phía gọi mở.
 */
class OrderFulfillmentService
{
    /**
     * Tạo phiếu xuất kho (đã hoàn tất) cho đơn. Idempotent: đã có phiếu COMPLETED thì bỏ qua.
     */
    public function generateSaleStockIssue(Order $order): ?StockIssue
    {
        $exists = StockIssue::where('order_id', $order->id)
            ->where('status', StockIssue::STATUS_COMPLETED)
            ->exists();
        if ($exists) {
            return null;
        }

        $orderItems = $order->orderItems()->with('productVariant')->get();
        if ($orderItems->isEmpty()) {
            return null;
        }

        $warehouse = Warehouse::where('is_default', true)->where('status', true)->first()
            ?? Warehouse::where('status', true)->first();
        if (! $warehouse) {
            throw new \RuntimeException('Không tìm thấy kho hàng hoạt động nào trong hệ thống.');
        }

        $fifoCost = $this->fifoCostByVariant($order);

        $userId = Auth::id() ?? $order->created_by ?? 1; // webhook/CLI không có Auth

        $totalQty  = (int) $orderItems->sum('quantity');
        $totalSale = (float) $orderItems->sum(fn ($item) => $item->quantity * $item->unit_price);
        $totalCost = 0.0;

        $stockIssue = StockIssue::create([
            'code'              => app(DocumentSequenceService::class)->generateStockIssueCode(),
            'issue_type'        => StockIssue::ISSUE_TYPE_SALE,
            'warehouse_id'      => $warehouse->id,
            'order_id'          => $order->id,
            'reason'            => 'Xuất kho cho đơn hàng #' . ($order->order_code ?? $order->id),
            'note'              => $order->note,
            'status'            => StockIssue::STATUS_DRAFT,
            'total_quantity'    => $totalQty,
            'total_cost_amount' => 0,
            'total_sale_amount' => $totalSale,
            'total_amount'      => $totalSale,
            'created_by'        => $userId,
        ]);

        foreach ($orderItems as $item) {
            $variant = $item->productVariant;
            if (! $variant) {
                continue;
            }

            // Giá vốn thật theo các lô FIFO đã trừ lúc checkout; nếu không có bút toán
            // (đơn cũ trước khi áp dụng Hold) thì lùi về giá bình quân đang cache.
            $agg      = $fifoCost[$variant->id] ?? null;
            $lineCost = $agg && $agg['qty'] > 0
                ? (float) $agg['cost'] * ($item->quantity / $agg['qty'])
                : (float) $variant->cost_price * $item->quantity;
            $unitCost = $item->quantity > 0 ? $lineCost / $item->quantity : 0.0;

            $stockIssue->items()->create([
                'product_id'         => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity'           => $item->quantity,
                'cost_price'         => round($unitCost, 2),
                'sale_price'         => $item->unit_price,
                'total_cost'         => round($lineCost, 2),
                'total_sale'         => $item->unit_price * $item->quantity,
            ]);

            $totalCost += $lineCost;
        }

        $stockIssue->update([
            'status'            => StockIssue::STATUS_COMPLETED,
            'total_cost_amount' => round($totalCost, 2),
            'confirmed_by'      => $userId,
            'confirmed_at'      => now(),
            'issued_at'         => now(),
        ]);

        StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id'        => $userId,
            'action'         => 'created',
            'message'        => 'Tạo phiếu xuất kho tự động từ đơn hàng #' . ($order->order_code ?? $order->id),
        ]);

        StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id'        => $userId,
            'action'         => 'confirmed',
            'message'        => 'Hoàn tất xuất kho (hàng đã được giữ từ lúc đặt đơn)',
        ]);

        return $stockIssue;
    }

    /**
     * Tổng giá vốn FIFO + số lượng đã trừ của đơn, gom theo biến thể.
     *
     * @return array<int,array{cost:float,qty:int}>
     */
    private function fifoCostByVariant(Order $order): array
    {
        return StockMovement::query()
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('movement_type', 'export')
            ->selectRaw('product_variant_id, SUM(ABS(quantity) * unit_cost) as cost, SUM(ABS(quantity)) as qty')
            ->groupBy('product_variant_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->product_variant_id => ['cost' => (float) $row->cost, 'qty' => (int) $row->qty],
            ])
            ->all();
    }
}
