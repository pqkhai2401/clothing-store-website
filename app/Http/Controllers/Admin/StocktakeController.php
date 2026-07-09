<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\ProductVariant;
use App\Models\Stocktake;
use App\Models\StockIssue;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StocktakeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string', 'max:2000'],
            'submit_action' => ['required', Rule::in(['pending', 'approve'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.actual_stock' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Vui lòng thêm ít nhất một sản phẩm để kiểm kê.',
            'items.min' => 'Vui lòng thêm ít nhất một sản phẩm để kiểm kê.',
        ]);

        $stocktake = DB::transaction(function () use ($validated) {
            $stocktake = Stocktake::create([
                'code' => $this->generateCode(),
                'note' => $validated['note'] ?? null,
                'status' => Stocktake::STATUS_PENDING,
                'created_by' => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);
                $stocktake->items()->create([
                    'product_variant_id' => $variant->id,
                    'system_stock' => $variant->stock,
                    'actual_stock' => (int) $item['actual_stock'],
                    'unit_cost' => $variant->cost_price,
                ]);
            }

            if ($validated['submit_action'] === 'approve') {
                $this->approveStocktake($stocktake);
            }

            return $stocktake;
        });

        return response()->json([
            'message' => $validated['submit_action'] === 'approve'
                ? "Đã cân bằng kho theo phiếu kiểm kê \"{$stocktake->code}\" thành công."
                : "Đã lưu phiếu kiểm kê \"{$stocktake->code}\" (chờ xử lý).",
            'code' => $stocktake->code,
        ]);
    }

    public function show(Request $request, string $id)
    {
        $stocktake = Stocktake::with([
            'creator',
            'processor',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        return response()->json([
            'html' => view('admin.goods-receipts.partials.stocktake-detail-content', compact('stocktake'))->render(),
        ]);
    }

    public function approve(string $id)
    {
        $stocktake = Stocktake::with('items')->findOrFail($id);

        if (! $stocktake->isPending()) {
            return back()->with('error', 'Phiếu kiểm kê này đã được xử lý trước đó.');
        }

        DB::transaction(fn () => $this->approveStocktake($stocktake));

        return back()->with('success', "Đã duyệt cân bằng kho cho phiếu \"{$stocktake->code}\".");
    }

    public function reject(string $id)
    {
        $stocktake = Stocktake::findOrFail($id);

        if (! $stocktake->isPending()) {
            return back()->with('error', 'Phiếu kiểm kê này đã được xử lý trước đó.');
        }

        $stocktake->update([
            'status' => Stocktake::STATUS_REJECTED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', "Đã hủy bỏ phiếu kiểm kê \"{$stocktake->code}\".");
    }

    private function approveStocktake(Stocktake $stocktake): void
    {
        $negativeItems = [];
        $positiveItems = [];

        foreach ($stocktake->items as $item) {
            $diff = $item->diff();

            if ($diff === 0) {
                continue;
            }

            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
            if (! $variant) {
                continue;
            }

            $variant->update(['stock' => $item->actual_stock]);

            if ($diff < 0) {
                $negativeItems[] = ['product_variant_id' => $item->product_variant_id, 'quantity' => abs($diff), 'unit_price' => $item->unit_cost];
            } else {
                $positiveItems[] = ['product_variant_id' => $item->product_variant_id, 'quantity' => $diff, 'cost_price' => $item->unit_cost];
            }
        }

        $stockIssue = null;
        $goodsReceipt = null;

        if (! empty($negativeItems)) {
            $stockIssue = StockIssue::create([
                'code' => $this->generateStockIssueCode(),
                'reason' => StockIssue::REASON_TYPE_LABELS[StockIssue::REASON_TYPE_STOCKTAKE],
                'reason_type' => StockIssue::REASON_TYPE_STOCKTAKE,
                'note' => "Tự động sinh từ phiếu kiểm kê {$stocktake->code}",
                'status' => StockIssue::STATUS_ISSUED,
                'total_amount' => collect($negativeItems)->sum(fn ($i) => $i['quantity'] * $i['unit_price']),
                'created_by' => Auth::id(),
                'issued_at' => now(),
            ]);
            foreach ($negativeItems as $i) {
                $stockIssue->items()->create($i);
            }
        }

        if (! empty($positiveItems)) {
            $supplier = Supplier::firstOrCreate(
                ['name' => 'Điều chỉnh kiểm kê kho (nội bộ)'],
                ['status' => true, 'note' => 'Nhà cung cấp hệ thống dùng cho các phiếu nhập kho tự động sinh từ kiểm kê.']
            );

            $goodsReceipt = GoodsReceipt::create([
                'code' => $this->generateGoodsReceiptCode(),
                'supplier_id' => $supplier->id,
                'note' => "Tự động sinh từ phiếu kiểm kê {$stocktake->code}",
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => collect($positiveItems)->sum(fn ($i) => $i['quantity'] * $i['cost_price']),
                'created_by' => Auth::id(),
                'completed_at' => now(),
            ]);
            foreach ($positiveItems as $i) {
                $goodsReceipt->items()->create($i);
            }
        }

        $stocktake->update([
            'status' => Stocktake::STATUS_APPROVED,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
            'stock_issue_id' => $stockIssue?->id,
            'goods_receipt_id' => $goodsReceipt?->id,
        ]);
    }

    private function generateCode(): string
    {
        $prefix = 'PKK' . now()->format('Ymd');
        $lastToday = Stocktake::where('code', 'like', "{$prefix}%")->orderByDesc('code')->first();
        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function generateStockIssueCode(): string
    {
        $prefix = 'PXK' . now()->format('Ymd');
        $lastToday = StockIssue::withTrashed()->where('code', 'like', "{$prefix}%")->orderByDesc('code')->first();
        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function generateGoodsReceiptCode(): string
    {
        $prefix = 'PN' . now()->format('Ymd');
        $lastToday = GoodsReceipt::where('code', 'like', "{$prefix}%")->orderByDesc('code')->first();
        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
