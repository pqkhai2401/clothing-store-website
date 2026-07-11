<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\ProductVariant;
use App\Models\Stocktake;
use App\Models\StockIssue;
use App\Models\Warehouse;
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

    public function destroy(string $id)
    {
        $stocktake = Stocktake::findOrFail($id);

        if (! $stocktake->isPending()) {
            return back()->with('error', 'Chỉ có thể xóa phiếu kiểm kê đang chờ xử lý.');
        }

        $stocktake->update(['deleted_by' => Auth::id()]);
        $stocktake->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'stocktake'])
            ->with('success', 'Xóa phiếu kiểm kê thành công');
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Stocktake::onlyTrashed()
            ->with(['creator', 'deleter'])
            ->withCount('items')
            ->orderByDesc('deleted_at');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('note', 'like', "%{$keyword}%");
            });
        }

        $stocktakes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.stocktake-trash-table', compact('stocktakes'))->render(),
            ]);
        }

        return view('admin.goods-receipts.stocktake-trash', compact('stocktakes', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        $stocktake = Stocktake::onlyTrashed()->findOrFail($id);
        Stocktake::onlyTrashed()->where('id', $stocktake->id)->update(['deleted_by' => null]);
        $stocktake->restore();

        return redirect()->route('admin.stocktakes.trash')->with('success', 'Khôi phục phiếu kiểm kê thành công');
    }

    public function forceDelete(string $id)
    {
        Stocktake::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.stocktakes.trash')->with('success', 'Đã xóa vĩnh viễn phiếu kiểm kê');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu kiểm kê.');
        }

        Stocktake::onlyTrashed()->whereIn('id', $ids)->update(['deleted_by' => null]);
        $restored = Stocktake::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Đã khôi phục {$restored} phiếu kiểm kê thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu kiểm kê.');
        }

        $count = Stocktake::onlyTrashed()->whereIn('id', $ids)->count();
        Stocktake::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', "Đã xóa vĩnh viễn {$count} phiếu kiểm kê.");
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

            $batchService = app(\App\Services\InventoryBatchService::class);

            if ($diff < 0) {
                $quantity = abs($diff);
                $costPrice = (float) $item->unit_cost;
                $salePrice = (float) $variant->price;

                // Cân bằng GIẢM: trừ tồn theo FIFO qua các lô.
                $batchService->consumeFifo($variant, $quantity, 'stocktake', $stocktake->id, Auth::id());

                $negativeItems[] = [
                    'product_id'         => $variant->product_id,
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $quantity,
                    'cost_price'         => $costPrice,
                    'sale_price'         => $salePrice,
                    'total_cost'         => $quantity * $costPrice,
                    'total_sale'         => $quantity * $salePrice,
                ];
            } else {
                // Cân bằng TĂNG: tạo lô điều chỉnh mới với giá vốn kiểm kê.
                $batchService->receive($variant, $diff, (float) $item->unit_cost, 'stocktake', $stocktake->id, null, Auth::id());

                $positiveItems[] = [
                    'product_variant_id' => $item->product_variant_id,
                    'quantity'           => $diff,
                    'cost_price'         => (float) $item->unit_cost,
                ];
            }
        }

        $stockIssue = null;
        $goodsReceipt = null;
        $warehouseId = Warehouse::getDefault()?->id;

        if (! empty($negativeItems)) {
            $stockIssue = StockIssue::create([
                'code'              => app(\App\Services\DocumentSequenceService::class)->generateStockIssueCode(),
                'issue_type'        => StockIssue::ISSUE_TYPE_ADJUSTMENT,
                'warehouse_id'      => $warehouseId,
                'reason'            => "Cân bằng giảm tồn theo phiếu kiểm kê {$stocktake->code}",
                'note'              => "Tự động sinh từ phiếu kiểm kê {$stocktake->code}",
                'status'            => StockIssue::STATUS_COMPLETED,
                'total_quantity'    => collect($negativeItems)->sum('quantity'),
                'total_cost_amount' => collect($negativeItems)->sum('total_cost'),
                'total_sale_amount' => collect($negativeItems)->sum('total_sale'),
                'total_amount'      => collect($negativeItems)->sum('total_sale'),
                'created_by'        => Auth::id(),
                'confirmed_by'      => Auth::id(),
                'confirmed_at'      => now(),
                'issued_at'         => now(),
            ]);
            foreach ($negativeItems as $i) {
                $stockIssue->items()->create($i);
            }
        }

        if (! empty($positiveItems)) {
            $goodsReceipt = GoodsReceipt::create([
                'code'           => app(\App\Services\DocumentSequenceService::class)->generateGoodsReceiptCode(),
                'receipt_type'   => GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT,
                'source_type'    => GoodsReceipt::SOURCE_TYPE_INTERNAL,
                'receipt_reason' => "Cân bằng tăng tồn theo phiếu kiểm kê {$stocktake->code}",
                'supplier_id'    => null,
                'warehouse_id'   => $warehouseId,
                'note'           => "Tự động sinh từ phiếu kiểm kê {$stocktake->code}",
                'status'         => GoodsReceipt::STATUS_COMPLETED,
                'total_amount'   => collect($positiveItems)->sum(fn ($i) => $i['quantity'] * $i['cost_price']),
                'total_quantity' => collect($positiveItems)->sum('quantity'),
                'received_at'    => now(),
                'completed_at'   => now(),
                'created_by'     => Auth::id(),
                'confirmed_by'   => Auth::id(),
                'confirmed_at'   => now(),
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
        $lastToday = Stocktake::withTrashed()->where('code', 'like', "{$prefix}%")->orderByDesc('code')->first();
        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

}

