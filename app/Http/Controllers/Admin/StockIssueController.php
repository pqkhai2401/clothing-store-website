<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\StockIssueLog;
use App\Models\Warehouse;
use App\Models\Order;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockIssueController extends Controller
{
    public function create()
    {
        return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'issue_type' => ['required', Rule::in(array_keys(StockIssue::ISSUE_TYPE_LABELS))],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')],
            'reason' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:2000'],
            'submit_action' => ['required', Rule::in(['draft', 'complete'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.sale_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'issue_type.required' => 'Vui lòng chọn loại xuất kho.',
            'warehouse_id.required' => 'Vui lòng chọn kho xuất.',
            'items.required' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'items.min' => 'Vui lòng chọn ít nhất một sản phẩm.',
        ]);

        if (in_array($validated['issue_type'], ['adjustment', 'damaged'], true) && empty($validated['reason'])) {
            throw ValidationException::withMessages([
                'reason' => ['Vui lòng nhập lý do xuất kho cho loại xuất này.']
            ]);
        }

        $variantIds = collect($validated['items'])->pluck('product_variant_id');
        if ($variantIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Không được chọn trùng lặp sản phẩm/biến thể trong cùng một phiếu xuất kho.']
            ]);
        }

        if ($validated['submit_action'] === 'complete') {
            $this->assertSufficientStock($validated['items']);
        }

        $allowsPriceEdit = ($validated['issue_type'] === StockIssue::ISSUE_TYPE_RETURN_SUPPLIER);

        $normalizedItems = collect($validated['items'])->map(function (array $item) use ($allowsPriceEdit) {
            $variant = ProductVariant::findOrFail($item['product_variant_id']);
            $costPrice = (float) $variant->cost_price;
            $salePrice = (float) $variant->price;

            if ($allowsPriceEdit && isset($item['sale_price'])) {
                $salePrice = (float) $item['sale_price'];
            }

            return [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => (int) $item['quantity'],
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'total_cost' => $costPrice * $item['quantity'],
                'total_sale' => $salePrice * $item['quantity'],
            ];
        })->all();

        $stockIssue = DB::transaction(function () use ($validated, $normalizedItems) {
            $totalQty = collect($normalizedItems)->sum('quantity');
            $totalCost = collect($normalizedItems)->sum('total_cost');
            $totalSale = collect($normalizedItems)->sum('total_sale');

            $stockIssue = StockIssue::create([
                'code' => $this->generateCode(),
                'issue_type' => $validated['issue_type'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_id' => $validated['order_id'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'note' => $validated['note'] ?? null,
                'status' => StockIssue::STATUS_DRAFT,
                'total_quantity' => $totalQty,
                'total_cost_amount' => $totalCost,
                'total_sale_amount' => $totalSale,
                'total_amount' => $totalSale, // keep total_amount equal to total_sale_amount
                'created_by' => Auth::id(),
            ]);

            foreach ($normalizedItems as $item) {
                $stockIssue->items()->create($item);
            }

            // Ghi log created
            StockIssueLog::create([
                'stock_issue_id' => $stockIssue->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'message' => 'Tạo phiếu xuất kho',
            ]);

            if ($validated['submit_action'] === 'complete') {
                $this->completeIssue($stockIssue);
            } else {
                StockIssueLog::create([
                    'stock_issue_id' => $stockIssue->id,
                    'user_id' => Auth::id(),
                    'action' => 'draft_saved',
                    'message' => 'Lưu nháp phiếu xuất kho',
                ]);
            }

            return $stockIssue;
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "Tạo phiếu xuất kho \"{$stockIssue->code}\" thành công.",
                'code' => $stockIssue->code,
                'show_url' => route('admin.stock-issues.show', $stockIssue->id),
                'table_url' => route('admin.goods-receipts.list', ['tab' => 'outbound']),
            ]);
        }

        return redirect()->route('admin.stock-issues.show', $stockIssue->id)
            ->with('success', "Tạo phiếu xuất kho \"{$stockIssue->code}\" thành công.");
    }

    public function show(Request $request, string $id)
    {
        $stockIssue = StockIssue::with([
            'creator',
            'confirmer',
            'canceller',
            'warehouse',
            'order',
            'logs.user',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'html' => view('admin.stock-issues.partials.show-content', compact('stockIssue'))->render(),
            ]);
        }

        return view('admin.stock-issues.show', compact('stockIssue'));
    }

    public function edit(Request $request, string $id)
    {
        $stockIssue = StockIssue::with('items.productVariant.product')->findOrFail($id);

        if (!$stockIssue->isDraft()) {
            $message = $stockIssue->isCompleted()
                ? 'Không thể chỉnh sửa phiếu đã hoàn tất'
                : 'Không thể chỉnh sửa phiếu đã hủy';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound'])->with('error', $message);
        }

        $warehouses = Warehouse::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
        $variants = $this->stockIssueVariants();

        $selectedItems = [];
        foreach ($stockIssue->items as $item) {
            $variant = $item->productVariant;
            if ($variant) {
                $selectedItems[$variant->id] = [
                    'variant' => [
                        'id' => $variant->id,
                        'product_name' => $variant->product->name ?? '',
                        'sku' => $variant->sku,
                        'color_name' => $variant->color->name ?? '',
                        'color_hex' => $variant->color->hex_code ?? '#ccc',
                        'size_name' => $variant->size->name ?? '',
                        'thumbnail' => $variant->product->thumbnail ?? '',
                        'stock' => $variant->stock,
                        'cost_price' => (float)$item->cost_price,
                        'sale_price' => (float)$item->sale_price,
                    ],
                    'quantity' => $item->quantity,
                    'cost_price' => (float)$item->cost_price,
                    'sale_price' => (float)$item->sale_price,
                ];
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'html' => view('admin.stock-issues.partials.edit-content', compact('stockIssue', 'variants', 'warehouses', 'selectedItems'))->render(),
            ]);
        }

        return view('admin.stock-issues.edit', compact('stockIssue', 'variants', 'warehouses', 'selectedItems'));
    }

    public function update(Request $request, string $id)
    {
        $stockIssue = StockIssue::findOrFail($id);

        if (!$stockIssue->isDraft()) {
            throw ValidationException::withMessages([
                'items' => ['Chỉ cho phép chỉnh sửa phiếu ở trạng thái nháp.']
            ]);
        }

        $validated = $request->validate([
            'issue_type' => ['required', Rule::in(array_keys(StockIssue::ISSUE_TYPE_LABELS))],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'order_id' => ['nullable', 'integer', Rule::exists('orders', 'id')],
            'reason' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:2000'],
            'submit_action' => ['required', Rule::in(['draft', 'complete'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.sale_price' => ['nullable', 'numeric', 'min:0'],
        ], [
            'issue_type.required' => 'Vui lòng chọn loại xuất kho.',
            'warehouse_id.required' => 'Vui lòng chọn kho xuất.',
            'items.required' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'items.min' => 'Vui lòng chọn ít nhất một sản phẩm.',
        ]);

        if (in_array($validated['issue_type'], ['adjustment', 'damaged'], true) && empty($validated['reason'])) {
            throw ValidationException::withMessages([
                'reason' => ['Vui lòng nhập lý do xuất kho cho loại xuất này.']
            ]);
        }

        $variantIds = collect($validated['items'])->pluck('product_variant_id');
        if ($variantIds->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'items' => ['Không được chọn trùng lặp sản phẩm/biến thể trong cùng một phiếu xuất kho.']
            ]);
        }

        if ($validated['submit_action'] === 'complete') {
            $this->assertSufficientStock($validated['items']);
        }

        $allowsPriceEdit = ($validated['issue_type'] === StockIssue::ISSUE_TYPE_RETURN_SUPPLIER);

        $normalizedItems = collect($validated['items'])->map(function (array $item) use ($allowsPriceEdit) {
            $variant = ProductVariant::findOrFail($item['product_variant_id']);
            $costPrice = (float) $variant->cost_price;
            $salePrice = (float) $variant->price;

            if ($allowsPriceEdit && isset($item['sale_price'])) {
                $salePrice = (float) $item['sale_price'];
            }

            return [
                'product_id' => $variant->product_id,
                'product_variant_id' => $variant->id,
                'quantity' => (int) $item['quantity'],
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'total_cost' => $costPrice * $item['quantity'],
                'total_sale' => $salePrice * $item['quantity'],
            ];
        })->all();

        DB::transaction(function () use ($stockIssue, $validated, $normalizedItems) {
            $totalQty = collect($normalizedItems)->sum('quantity');
            $totalCost = collect($normalizedItems)->sum('total_cost');
            $totalSale = collect($normalizedItems)->sum('total_sale');

            $stockIssue->update([
                'issue_type' => $validated['issue_type'],
                'warehouse_id' => $validated['warehouse_id'],
                'order_id' => $validated['order_id'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'note' => $validated['note'] ?? null,
                'total_quantity' => $totalQty,
                'total_cost_amount' => $totalCost,
                'total_sale_amount' => $totalSale,
                'total_amount' => $totalSale,
            ]);

            // Recreate items
            $stockIssue->items()->delete();
            foreach ($normalizedItems as $item) {
                $stockIssue->items()->create($item);
            }

            StockIssueLog::create([
                'stock_issue_id' => $stockIssue->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'message' => 'Cập nhật thông tin phiếu xuất kho',
            ]);

            if ($validated['submit_action'] === 'complete') {
                $this->completeIssue($stockIssue);
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => "Cập nhật phiếu xuất kho \"{$stockIssue->code}\" thành công.",
                'code' => $stockIssue->code,
                'show_url' => route('admin.stock-issues.show', $stockIssue->id),
                'table_url' => route('admin.goods-receipts.list', ['tab' => 'outbound']),
            ]);
        }

        return redirect()->route('admin.stock-issues.show', $stockIssue->id)
            ->with('success', "Cập nhật phiếu xuất kho \"{$stockIssue->code}\" thành công.");
    }

    public function confirm(string $id)
    {
        $stockIssue = StockIssue::with('items')->findOrFail($id);

        if (!$stockIssue->isDraft()) {
            return back()->with('error', 'Phiếu xuất kho này đã được hoàn tất hoặc hủy trước đó.');
        }

        try {
            $this->assertSufficientStock($stockIssue->items->map(fn ($item) => [
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ])->all());
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        DB::transaction(fn () => $this->completeIssue($stockIssue));

        return back()->with('success', "Đã xuất kho phiếu \"{$stockIssue->code}\" và cập nhật tồn kho.");
    }

    public function cancel(string $id)
    {
        $stockIssue = StockIssue::findOrFail($id);

        if (!$stockIssue->isDraft()) {
            return back()->with('error', 'Phiếu đã hoàn tất hoặc đã hủy, không thể hủy tiếp.');
        }

        DB::transaction(function () use ($stockIssue) {
            $stockIssue->update([
                'status' => StockIssue::STATUS_CANCELLED,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            StockIssueLog::create([
                'stock_issue_id' => $stockIssue->id,
                'user_id' => Auth::id(),
                'action' => 'cancelled',
                'message' => 'Hủy phiếu xuất kho',
            ]);
        });

        return back()->with('success', 'Hủy phiếu xuất kho thành công');
    }

    public function destroy(string $id)
    {
        $stockIssue = StockIssue::findOrFail($id);

        if (!$stockIssue->isDraft() && !$stockIssue->isCancelled()) {
            return back()->with('error', 'Không thể xóa phiếu xuất kho đã hoàn tất.');
        }

        $stockIssue->deleted_by = Auth::id();
        $stockIssue->save();
        $stockIssue->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'outbound'])
            ->with('success', 'Xóa phiếu xuất kho thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu xuất kho để xóa.');
        }

        $query = StockIssue::whereIn('id', $ids)->whereIn('status', [StockIssue::STATUS_DRAFT, StockIssue::STATUS_CANCELLED]);
        $query->update(['deleted_by' => Auth::id()]);
        $deleted = $query->delete();

        return back()->with('success', "Đã xóa {$deleted} phiếu xuất kho thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = StockIssue::onlyTrashed()->with(['creator', 'deleter'])->orderByDesc('deleted_at');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('reason', 'like', "%{$keyword}%");
            });
        }

        $stockIssues = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.stock-issues.partials.trash-table', compact('stockIssues'))->render(),
            ]);
        }

        return view('admin.stock-issues.trash', compact('stockIssues', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        $stockIssue = StockIssue::onlyTrashed()->findOrFail($id);
        StockIssue::onlyTrashed()->where('id', $stockIssue->id)->update(['deleted_by' => null]);
        $stockIssue->restore();

        return redirect()->route('admin.stock-issues.trash')->with('success', 'Khôi phục phiếu xuất kho thành công');
    }

    public function forceDelete(string $id)
    {
        StockIssue::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.stock-issues.trash')->with('success', 'Đã xóa vĩnh viễn phiếu xuất kho');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu xuất kho.');
        }

        StockIssue::onlyTrashed()->whereIn('id', $ids)->update(['deleted_by' => null]);
        $restored = StockIssue::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Đã khôi phục {$restored} phiếu xuất kho thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu xuất kho.');
        }

        $count = StockIssue::onlyTrashed()->whereIn('id', $ids)->count();
        StockIssue::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', "Đã xóa vĩnh viễn {$count} phiếu xuất kho.");
    }

    private function stockIssueVariants()
    {
        return ProductVariant::query()
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'))
            ->where('stock', '>', 0)
            ->get()
            ->map(fn (ProductVariant $v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'product_name' => $v->product?->name,
                'thumbnail' => $v->product?->thumbnail,
                'color_name' => $v->color?->name,
                'color_hex' => $v->color?->display_hex_code,
                'size_name' => $v->size?->name,
                'stock' => $v->stock,
                'cost_price' => (float) $v->cost_price,
                'sale_price' => (float) $v->price,
            ])
            ->values();
    }

    private function assertSufficientStock(array $items): void
    {
        foreach ($items as $item) {
            $variant = ProductVariant::with(['product', 'color', 'size'])->find($item['product_variant_id']);
            if (!$variant || $variant->stock < $item['quantity']) {
                $variantName = ($variant->product->name ?? 'Sản phẩm') 
                    . ' - ' . ($variant->color->name ?? 'Không màu') 
                    . ' - Size ' . ($variant->size->name ?? 'Không size') 
                    . ' - SKU ' . ($variant->sku ?? 'N/A');
                throw ValidationException::withMessages([
                    'items' => ["Không đủ tồn kho để xuất. Sản phẩm [{$variantName}] hiện chỉ còn [{$variant->stock}] sản phẩm."]
                ]);
            }
        }
    }

    private function completeIssue(StockIssue $stockIssue): void
    {
        if (!$stockIssue->isDraft()) {
            return;
        }

        foreach ($stockIssue->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
            if (!$variant) continue;

            if ($variant->stock < $item->quantity) {
                $variantName = ($variant->product->name ?? 'Sản phẩm') 
                    . ' - ' . ($variant->color->name ?? 'Không màu') 
                    . ' - Size ' . ($variant->size->name ?? 'Không size') 
                    . ' - SKU ' . ($variant->sku ?? 'N/A');
                throw ValidationException::withMessages([
                    'items' => ["Không đủ tồn kho để xuất. Sản phẩm [{$variantName}] hiện chỉ còn [{$variant->stock}] sản phẩm."]
                ]);
            }

            $beforeQty = $variant->stock;
            $afterQty = max(0, $beforeQty - $item->quantity);

            $variant->update(['stock' => $afterQty]);

            StockMovement::create([
                'product_variant_id' => $variant->id,
                'reference_type' => 'stock_issue',
                'reference_id' => $stockIssue->id,
                'movement_type' => 'export',
                'quantity' => $item->quantity,
                'before_quantity' => $beforeQty,
                'after_quantity' => $afterQty,
                'created_by' => Auth::id(),
            ]);
        }

        $stockIssue->update([
            'status' => StockIssue::STATUS_COMPLETED,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now(),
            'issued_at' => now(),
        ]);

        StockIssueLog::create([
            'stock_issue_id' => $stockIssue->id,
            'user_id' => Auth::id(),
            'action' => 'confirmed',
            'message' => 'Hoàn tất xuất kho',
        ]);
    }

    private function generateCode(): string
    {
        $prefix = 'PXK' . now()->format('Ymd');
        $lastToday = StockIssue::withTrashed()->where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}

