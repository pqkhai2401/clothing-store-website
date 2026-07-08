<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GoodsReceiptController extends Controller
{
    public const LOW_STOCK_THRESHOLD = 15;

    public function index(Request $request)
    {
        $tab = in_array($request->input('tab'), ['overview', 'inbound', 'outbound', 'stocktake'], true)
            ? $request->input('tab')
            : 'overview';

        return match ($tab) {
            'inbound'   => $this->inboundIndex($request),
            'outbound'  => $this->outboundIndex($request),
            'stocktake' => $this->stocktakeIndex($request),
            default     => $this->overviewIndex($request),
        };
    }

    private function stocktakeIndex(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $status  = $request->input('status');
        $sort = $request->input('sort', 'id');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Stocktake::with(['creator', 'processor'])->withCount('items');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('note', 'like', "%{$keyword}%");
            });
        }

        if (in_array($status, [Stocktake::STATUS_PENDING, Stocktake::STATUS_APPROVED, Stocktake::STATUS_REJECTED], true)) {
            $query->where('status', $status);
        }

        match ($sort) {
            'created_at' => $query->orderBy('created_at', $direction),
            default      => $query->orderBy('id', $direction),
        };

        $stocktakes = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.stocktake-table', compact('stocktakes'))->render(),
            ]);
        }

        $stocktakeVariants = $this->stockIssueVariants();
        $stocktakeCodePreview = $this->stocktakeCodePreview();

        return view('admin.goods-receipts.index', compact('stocktakes', 'keyword', 'status', 'perPage', 'stocktakeVariants', 'stocktakeCodePreview') + ['tab' => 'stocktake']);
    }

    private function outboundIndex(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $status  = $request->input('status');
        $sort = $request->input('sort', 'id');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = StockIssue::with('creator')->withCount('items')
            ->withSum('items as items_quantity_sum', 'quantity');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhere('reason', 'like', "%{$keyword}%");
            });
        }

        if (in_array($status, [StockIssue::STATUS_DRAFT, StockIssue::STATUS_ISSUED], true)) {
            $query->where('status', $status);
        }

        match ($sort) {
            'total_amount' => $query->orderBy('total_amount', $direction),
            'created_at'   => $query->orderBy('created_at', $direction),
            default        => $query->orderBy('id', $direction),
        };

        $stockIssues = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.outbound-table', compact('stockIssues'))->render(),
            ]);
        }

        $stockIssueVariants = $this->stockIssueVariants();

        return view('admin.goods-receipts.index', compact('stockIssues', 'keyword', 'status', 'perPage', 'stockIssueVariants') + ['tab' => 'outbound']);
    }

    private function overviewIndex(Request $request)
    {
        $keyword     = trim((string) $request->input('search', $request->input('keyword')));
        $categoryId  = $request->input('category_id');
        $stockStatus = in_array($request->input('stock_status'), ['in_stock', 'low_stock', 'out_of_stock'], true)
            ? $request->input('stock_status')
            : '';
        $sort       = $request->input('sort', 'id');
        $direction  = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'asc';
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = ProductVariant::query()
            ->with(['product:id,name,thumbnail,category_id', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'));

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('sku', 'like', "%{$keyword}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('color', fn ($cq) => $cq->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('size', fn ($sq) => $sq->where('name', 'like', "%{$keyword}%"));
            });
        }

        if ($categoryId) {
            $childIds = Category::where('parent_id', $categoryId)->pluck('id');
            $allIds   = $childIds->push((int) $categoryId)->unique()->values();
            $query->whereHas('product', fn ($pq) => $pq->whereIn('category_id', $allIds));
        }

        match ($stockStatus) {
            'in_stock'     => $query->where('stock', '>', self::LOW_STOCK_THRESHOLD),
            'low_stock'    => $query->where('stock', '>', 0)->where('stock', '<=', self::LOW_STOCK_THRESHOLD),
            'out_of_stock' => $query->where('stock', '<=', 0),
            default        => null,
        };

        match ($sort) {
            'stock'      => $query->orderBy('stock', $direction),
            'cost_price' => $query->orderBy('cost_price', $direction),
            default      => $query->orderBy('id', $direction),
        };

        $variants = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.overview-table', compact('variants'))->render(),
            ]);
        }

        $categories = Category::whereNull('parent_id')->with('childrenCategories')->orderBy('name')->get();

        $activeVariants = ProductVariant::whereHas('product', fn ($q) => $q->whereNull('deleted_at'));

        return view('admin.goods-receipts.index', [
            'tab'               => 'overview',
            'variants'          => $variants,
            'keyword'           => $keyword,
            'categoryId'        => $categoryId,
            'stockStatus'       => $stockStatus,
            'perPage'           => $perPage,
            'categories'        => $categories,
            'totalStock'        => (int) $activeVariants->clone()->sum('stock'),
            'totalValue'        => (float) ($activeVariants->clone()->selectRaw('SUM(stock * cost_price) as v')->value('v') ?? 0),
            'lowStockCount'     => $activeVariants->clone()->where('stock', '>', 0)->where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count(),
        ]);
    }

    private function inboundIndex(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $status  = $request->input('status');
        $sort = $request->input('sort', 'id');
        $direction = in_array($request->input('direction'), ['asc', 'desc'], true) ? $request->input('direction') : 'desc';
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = GoodsReceipt::with(['supplier', 'creator'])->withCount('items');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$keyword}%"));
            });
        }

        if (in_array($status, [GoodsReceipt::STATUS_DRAFT, GoodsReceipt::STATUS_COMPLETED, GoodsReceipt::STATUS_ADJUSTED], true)) {
            $query->where('status', $status);
        }

        match ($sort) {
            'total_amount' => $query->orderBy('total_amount', $direction),
            'created_at'   => $query->orderBy('created_at', $direction),
            default        => $query->orderBy('id', $direction),
        };

        $goodsReceipts = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.table', compact('goodsReceipts'))->render(),
            ]);
        }

        $suppliers = $this->activeSuppliers();
        $goodsReceiptVariants = $this->goodsReceiptVariants();

        return view('admin.goods-receipts.index', compact(
            'goodsReceipts',
            'keyword',
            'perPage',
            'status',
            'suppliers',
            'goodsReceiptVariants'
        ) + ['tab' => 'inbound']);
    }

    public function create()
    {
        $suppliers = $this->activeSuppliers();
        $variants = $this->goodsReceiptVariants();

        return view('admin.goods-receipts.create', compact('suppliers', 'variants'));
    }

    public function stockCard(ProductVariant $variant)
    {
        $variant->load(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name']);

        $transactions = collect();

        GoodsReceiptItem::query()
            ->with(['goodsReceipt.creator'])
            ->where('product_variant_id', $variant->id)
            ->whereHas('goodsReceipt', fn ($query) => $query->whereIn('status', [
                GoodsReceipt::STATUS_COMPLETED,
                GoodsReceipt::STATUS_ADJUSTED,
            ]))
            ->get()
            ->each(function (GoodsReceiptItem $item) use ($transactions) {
                $receipt = $item->goodsReceipt;
                $transactions->push([
                    'at' => $receipt?->completed_at ?? $receipt?->created_at,
                    'type' => 'nhap_kho',
                    'type_label' => 'Nhập kho',
                    'document_code' => $receipt?->code,
                    'document_url' => $receipt ? route('admin.goods-receipts.show', $receipt->id) : null,
                    'quantity_change' => (int) $item->quantity,
                    'user' => $receipt?->creator?->username ?? 'N/A',
                ]);
            });

        StockIssueItem::query()
            ->with(['stockIssue.creator'])
            ->where('product_variant_id', $variant->id)
            ->whereHas('stockIssue', fn ($query) => $query->whereIn('status', [
                StockIssue::STATUS_ISSUED,
                StockIssue::STATUS_ADJUSTED,
            ]))
            ->get()
            ->each(function (StockIssueItem $item) use ($transactions) {
                $issue = $item->stockIssue;
                $transactions->push([
                    'at' => $issue?->issued_at ?? $issue?->created_at,
                    'type' => 'xuat_kho',
                    'type_label' => 'Xuất kho',
                    'document_code' => $issue?->code,
                    'document_url' => $issue ? route('admin.stock-issues.show', $issue->id) : null,
                    'quantity_change' => -1 * (int) $item->quantity,
                    'user' => $issue?->creator?->username ?? 'N/A',
                ]);
            });

        StocktakeItem::query()
            ->with(['stocktake.processor', 'stocktake.creator'])
            ->where('product_variant_id', $variant->id)
            ->whereHas('stocktake', fn ($query) => $query->where('status', Stocktake::STATUS_APPROVED))
            ->get()
            ->each(function (StocktakeItem $item) use ($transactions) {
                $diff = $item->diff();
                if ($diff === 0) {
                    return;
                }

                $stocktake = $item->stocktake;
                $transactions->push([
                    'at' => $stocktake?->processed_at ?? $stocktake?->created_at,
                    'type' => 'dieu_chinh_kho',
                    'type_label' => 'Điều chỉnh kho',
                    'document_code' => $stocktake?->code,
                    'document_url' => null,
                    'quantity_change' => $diff,
                    'user' => $stocktake?->processor?->username ?? $stocktake?->creator?->username ?? 'N/A',
                ]);
            });

        OrderItem::query()
            ->with(['order.user'])
            ->where('product_variant_id', $variant->id)
            ->whereHas('order', fn ($query) => $query->whereIn('status', ['completed', 'cancelled']))
            ->get()
            ->each(function (OrderItem $item) use ($transactions) {
                $order = $item->order;
                $isCancelled = $order?->status === 'cancelled';
                $transactions->push([
                    'at' => $order?->updated_at ?? $order?->created_at,
                    'type' => $isCancelled ? 'order_cancelled' : 'xuat_kho',
                    'type_label' => $isCancelled ? 'Order Cancelled' : 'Xuất kho',
                    'document_code' => $order?->order_code,
                    'document_url' => $order ? route('admin.orders.detail', $order->id) : null,
                    'quantity_change' => ($isCancelled ? 1 : -1) * (int) $item->quantity,
                    'user' => $order?->user?->username ?? 'Hệ thống',
                ]);
            });

        $runningStock = (int) $variant->stock;
        $transactions = $transactions
            ->filter(fn ($transaction) => ! empty($transaction['at']))
            ->sortByDesc('at')
            ->values()
            ->map(function (array $transaction) use (&$runningStock) {
                $transaction['ending_stock'] = $runningStock;
                $runningStock -= (int) $transaction['quantity_change'];

                return $transaction;
            });

        return response()->json([
            'html' => view('admin.goods-receipts.partials.stock-card', compact('variant', 'transactions'))->render(),
        ]);
    }

    private function activeSuppliers()
    {
        return Supplier::where('status', true)->orderBy('name')->get();
    }

    private function goodsReceiptVariants()
    {
        return ProductVariant::query()
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'))
            ->get()
            ->map(fn (ProductVariant $v) => [
                'id'           => $v->id,
                'sku'          => $v->sku,
                'product_name' => $v->product?->name,
                'thumbnail'    => $v->product?->thumbnail,
                'color_name'   => $v->color?->name,
                'color_hex'    => $v->color?->display_hex_code,
                'size_name'    => $v->size?->name,
                'stock'        => $v->stock,
                'cost_price'   => (float) $v->cost_price,
            ])
            ->values();
    }

    private function stockIssueVariants()
    {
        return ProductVariant::query()
            ->with(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name'])
            ->whereHas('product', fn ($q) => $q->whereNull('deleted_at'))
            ->where('stock', '>', 0)
            ->get()
            ->map(fn (ProductVariant $v) => [
                'id'           => $v->id,
                'sku'          => $v->sku,
                'product_name' => $v->product?->name,
                'thumbnail'    => $v->product?->thumbnail,
                'color_name'   => $v->color?->name,
                'color_hex'    => $v->color?->display_hex_code,
                'size_name'    => $v->size?->name,
                'stock'        => $v->stock,
                'unit_price'   => (float) $v->sale_price,
            ])
            ->values();
    }

    private function stocktakeCodePreview(): string
    {
        $prefix = 'PKK' . now()->format('Ymd');
        $lastToday = Stocktake::where('code', 'like', "{$prefix}%")->orderByDesc('code')->first();
        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'              => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'note'                     => ['nullable', 'string', 'max:1000'],
            'action'                   => ['required', Rule::in(['draft', 'complete'])],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'integer', Rule::exists('product_variants', 'id')],
            'items.*.quantity'         => ['required', 'integer', 'min:1'],
            'items.*.cost_price'       => ['required', 'numeric', 'min:0'],
        ], [
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
            'items.required'       => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
            'items.min'            => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
        ]);

        $goodsReceipt = DB::transaction(function () use ($validated) {
            $totalAmount = collect($validated['items'])
                ->sum(fn ($item) => $item['quantity'] * $item['cost_price']);

            $goodsReceipt = GoodsReceipt::create([
                'code'         => $this->generateCode(),
                'supplier_id'  => $validated['supplier_id'],
                'note'         => $validated['note'] ?? null,
                'status'       => GoodsReceipt::STATUS_DRAFT,
                'total_amount' => $totalAmount,
                'created_by'   => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $goodsReceipt->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'cost_price'         => $item['cost_price'],
                ]);
            }

            if ($validated['action'] === 'complete') {
                $this->completeReceipt($goodsReceipt);
            }

            return $goodsReceipt;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "Tạo phiếu nhập kho \"{$goodsReceipt->code}\" thành công.",
                'code' => $goodsReceipt->code,
                'show_url' => route('admin.goods-receipts.show', $goodsReceipt->id),
                'table_url' => route('admin.goods-receipts.list', ['tab' => 'inbound']),
            ]);
        }

        return redirect()->route('admin.goods-receipts.show', $goodsReceipt->id)
            ->with('success', "Tạo phiếu nhập kho \"{$goodsReceipt->code}\" thành công.");
    }

    public function show(Request $request, string $id)
    {
        $goodsReceipt = GoodsReceipt::with([
            'supplier', 'creator', 'adjuster',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.goods-receipts.partials.show-content', compact('goodsReceipt'))->render(),
            ]);
        }

        return view('admin.goods-receipts.show', compact('goodsReceipt'));
    }

    public function complete(string $id)
    {
        $goodsReceipt = GoodsReceipt::with('items')->findOrFail($id);

        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Phiếu nhập kho này đã được hoàn tất trước đó.');
        }

        DB::transaction(fn () => $this->completeReceipt($goodsReceipt));

        return back()->with('success', "Đã hoàn tất phiếu nhập kho \"{$goodsReceipt->code}\" và cập nhật tồn kho.");
    }

    public function adjust(Request $request, string $id)
    {
        $validated = $request->validate([
            'adjustment_reason' => ['required', 'string', 'max:2000'],
        ], [
            'adjustment_reason.required' => 'Vui lòng nhập lý do điều chỉnh phiếu nhập kho.',
        ]);

        $goodsReceipt = GoodsReceipt::with('items')->findOrFail($id);

        if (! $goodsReceipt->isCompleted()) {
            return back()->with('error', 'Chỉ có thể điều chỉnh phiếu nhập kho đã hoàn tất và chưa từng điều chỉnh.');
        }

        try {
            $this->assertEnoughStockForReceiptAdjustment($goodsReceipt);
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        $stockIssue = DB::transaction(function () use ($goodsReceipt, $validated) {
            $stockIssue = StockIssue::create([
                'code' => $this->generateStockIssueCode(),
                'reason' => 'Điều chỉnh phiếu nhập sai',
                'reason_type' => StockIssue::REASON_TYPE_ADJUSTMENT,
                'note' => "Tự động sinh để điều chỉnh phiếu nhập {$goodsReceipt->code}. Lý do: {$validated['adjustment_reason']}",
                'status' => StockIssue::STATUS_ISSUED,
                'total_amount' => $goodsReceipt->items->sum(fn ($item) => $item->quantity * $item->cost_price),
                'created_by' => Auth::id(),
                'issued_at' => now(),
            ]);

            foreach ($goodsReceipt->items as $item) {
                $stockIssue->items()->create([
                    'product_variant_id' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->cost_price,
                ]);

                $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
                if ($variant) {
                    $variant->update(['stock' => $variant->stock - $item->quantity]);
                }
            }

            $goodsReceipt->update([
                'status' => GoodsReceipt::STATUS_ADJUSTED,
                'adjusted_by' => Auth::id(),
                'adjusted_at' => now(),
                'adjustment_reason' => $validated['adjustment_reason'],
                'adjustment_stock_issue_id' => $stockIssue->id,
            ]);

            return $stockIssue;
        });

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
            ->with('success', "Đã điều chỉnh phiếu nhập \"{$goodsReceipt->code}\" và tự động sinh phiếu xuất \"{$stockIssue->code}\".");
    }

    public function destroy(string $id)
    {
        $goodsReceipt = GoodsReceipt::findOrFail($id);

        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Không thể xóa phiếu nhập kho đã hoàn tất.');
        }

        $goodsReceipt->deleted_by = Auth::id();
        $goodsReceipt->save();
        $goodsReceipt->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
            ->with('success', 'Xóa phiếu nhập kho thành công');
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu nhập kho để xóa.');
        }

        // Chỉ cho xóa mềm phiếu Nháp. Phiếu đã hoàn tất đã làm thay đổi tồn kho nên phải giữ lại.
        $query = GoodsReceipt::whereIn('id', $ids)->where('status', GoodsReceipt::STATUS_DRAFT);
        $query->update(['deleted_by' => Auth::id()]);
        $deleted = $query->delete();

        return back()->with('success', "Đã xóa {$deleted} phiếu nhập kho thành công.");
    }

    private function completeReceipt(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
            if (!$variant) continue;

            $variant->update([
                'stock'      => $variant->stock + $item->quantity,
                'cost_price' => $item->cost_price,
            ]);
        }

        $goodsReceipt->update([
            'status'       => GoodsReceipt::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    private function assertEnoughStockForReceiptAdjustment(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->items as $item) {
            $variant = ProductVariant::find($item->product_variant_id);

            if (! $variant || $variant->stock < $item->quantity) {
                throw ValidationException::withMessages([
                    'items' => "Không đủ tồn kho để điều chỉnh SKU \"{$variant?->sku}\" (còn {$variant?->stock}, cần trừ {$item->quantity}).",
                ]);
            }
        }
    }

    private function generateStockIssueCode(): string
    {
        $prefix = 'PXK' . now()->format('Ymd');
        $lastToday = StockIssue::withTrashed()->where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    private function generateCode(): string
    {
        $prefix = 'PN' . now()->format('Ymd');
        $lastToday = GoodsReceipt::withTrashed()->where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}