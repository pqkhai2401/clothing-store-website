<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Dịch vụ quản lý tồn kho theo LÔ (batch/lot) — chuẩn FIFO.
 *
 * Nguồn sự thật: product_batches.quantity_remaining.
 * Sổ cái: stock_movements (mỗi biến động ghi 1 dòng, gắn product_batch_id + unit_cost).
 * Cache: product_variants.stock = SUM(remaining các lô active); cost_price = bình quân tồn (hiển thị).
 *
 * LƯU Ý: mọi hàm dưới đây PHẢI được gọi bên trong 1 DB::transaction do controller mở,
 * để đảm bảo lô + sổ cái + cache luôn nhất quán (all-or-nothing).
 */
class InventoryBatchService
{
    /**
     * NHẬP KHO: tạo 1 lô mới và ghi bút toán nhập.
     */
    public function receive(
        ProductVariant $variant,
        int $quantity,
        float $costPrice,
        string $referenceType,
        int $referenceId,
        ?int $goodsReceiptItemId = null,
        ?int $userId = null,
        ?Carbon $receivedAt = null
    ): ProductBatch {
        $receivedAt ??= now();

        $batch = ProductBatch::create([
            'batch_code'            => app(DocumentSequenceService::class)->generateBatchCode(),
            'product_variant_id'    => $variant->id,
            'goods_receipt_item_id' => $goodsReceiptItemId,
            'quantity_import'       => $quantity,
            'quantity_remaining'    => $quantity,
            'cost_price'            => $costPrice,
            'received_at'           => $receivedAt,
            'status'                => ProductBatch::STATUS_ACTIVE,
        ]);

        $before = (int) $variant->stock;
        $after  = $before + $quantity;

        StockMovement::create([
            'product_variant_id' => $variant->id,
            'product_batch_id'   => $batch->id,
            'reference_type'     => $referenceType,
            'reference_id'       => $referenceId,
            'movement_type'      => 'import',
            'quantity'           => $quantity,
            'unit_cost'          => $costPrice,
            'before_quantity'    => $before,
            'after_quantity'     => $after,
            'created_by'         => $userId,
        ]);

        $this->recalcVariantStock($variant);

        return $batch;
    }

    /**
     * XUẤT KHO theo FIFO: trừ dần qua các lô nhập trước, sinh 1 bút toán export / lô.
     *
     * @return array<int,array{batch_id:int,quantity:int,unit_cost:float}> danh sách phân bổ lô
     */
    public function consumeFifo(
        ProductVariant $variant,
        int $quantity,
        string $referenceType,
        int $referenceId,
        ?int $userId = null
    ): array {
        if ($quantity <= 0) {
            return [];
        }

        // Khoá các lô còn bán được theo FIFO để chống oversell khi 2 đơn mua cùng lúc.
        $batches = ProductBatch::query()
            ->where('product_variant_id', $variant->id)
            ->sellable()
            ->fifo()
            ->lockForUpdate()
            ->get();

        $available = (int) $batches->sum('quantity_remaining');
        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'stock' => ["Không đủ tồn kho theo lô cho SKU \"{$variant->sku}\" (còn {$available}, cần {$quantity})."],
            ]);
        }

        $remainingToTake = $quantity;
        $running = (int) $variant->stock;
        $allocations = [];

        foreach ($batches as $batch) {
            if ($remainingToTake <= 0) {
                break;
            }

            $take = min($remainingToTake, (int) $batch->quantity_remaining);
            $newRemaining = (int) $batch->quantity_remaining - $take;

            $batch->update([
                'quantity_remaining' => $newRemaining,
                'status' => $newRemaining === 0 ? ProductBatch::STATUS_DEPLETED : $batch->status,
            ]);

            $before = $running;
            $after  = $running - $take;
            $running = $after;

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'product_batch_id'   => $batch->id,
                'reference_type'     => $referenceType,
                'reference_id'       => $referenceId,
                'movement_type'      => 'export',
                'quantity'           => -$take,
                'unit_cost'          => (float) $batch->cost_price,
                'before_quantity'    => $before,
                'after_quantity'     => $after,
                'created_by'         => $userId,
            ]);

            $allocations[] = [
                'batch_id'  => $batch->id,
                'quantity'  => $take,
                'unit_cost' => (float) $batch->cost_price,
            ];

            $remainingToTake -= $take;
        }

        $this->recalcVariantStock($variant);

        return $allocations;
    }

    /**
     * HOÀN KHO: đảo ngược các bút toán export của 1 chứng từ (hủy đơn / đổi trả),
     * cộng trả về ĐÚNG lô đã trừ và giá vốn gốc → giá vốn không bao giờ méo.
     */
    public function restoreByReference(
        string $referenceType,
        int $referenceId,
        string $newReferenceType,
        int $newReferenceId,
        ?int $userId = null
    ): void {
        $exports = StockMovement::query()
            ->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('movement_type', 'export')
            ->whereNotNull('product_batch_id')
            ->get();

        $touchedVariantIds = [];

        foreach ($exports as $export) {
            $batch = ProductBatch::lockForUpdate()->find($export->product_batch_id);
            if (! $batch) {
                continue;
            }

            $qty = abs((int) $export->quantity);
            $variant = ProductVariant::lockForUpdate()->find($export->product_variant_id);
            if (! $variant) {
                continue;
            }

            $batch->update([
                'quantity_remaining' => (int) $batch->quantity_remaining + $qty,
                'status' => $batch->status === ProductBatch::STATUS_DEPLETED
                    ? ProductBatch::STATUS_ACTIVE
                    : $batch->status,
            ]);

            $before = (int) $variant->stock;
            $after  = $before + $qty;

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'product_batch_id'   => $batch->id,
                'reference_type'     => $newReferenceType,
                'reference_id'       => $newReferenceId,
                'movement_type'      => 'import',
                'quantity'           => $qty,
                'unit_cost'          => (float) $batch->cost_price,
                'before_quantity'    => $before,
                'after_quantity'     => $after,
                'created_by'         => $userId,
            ]);

            // Cập nhật cache ngay để before/after của bút toán kế tiếp (nếu trùng biến thể) đúng.
            $variant->update(['stock' => $after]);
            $touchedVariantIds[$variant->id] = true;
        }

        foreach (array_keys($touchedVariantIds) as $variantId) {
            $this->recalcVariantStock($variantId);
        }
    }

    /**
     * Đồng bộ cache trên biến thể theo các lô: stock = tổng tồn lô active,
     * cost_price = bình quân gia quyền tồn còn lại (chỉ để hiển thị).
     */
    public function recalcVariantStock(ProductVariant|int $variant): void
    {
        $variant = $variant instanceof ProductVariant
            ? $variant
            : ProductVariant::find($variant);
        if (! $variant) {
            return;
        }

        $batches = ProductBatch::where('product_variant_id', $variant->id)
            ->where('status', ProductBatch::STATUS_ACTIVE)
            ->get(['quantity_remaining', 'cost_price']);

        $stock = (int) $batches->sum('quantity_remaining');
        $value = (float) $batches->sum(fn ($b) => $b->quantity_remaining * (float) $b->cost_price);
        $avgCost = $stock > 0 ? round($value / $stock, 2) : (float) $variant->cost_price;

        $variant->update([
            'stock'      => $stock,
            'cost_price' => $avgCost,
        ]);
    }

}
