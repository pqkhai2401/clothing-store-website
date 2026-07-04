<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GoodsReceipt;
use App\Models\ProductVariant;
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
        $tab = in_array($request->input('tab'), ['overview', 'inbound', 'outbound'], true)
            ? $request->input('tab')
            : 'overview';

        return match ($tab) {
            'inbound'  => $this->inboundIndex($request),
            'outbound' => view('admin.goods-receipts.index', ['tab' => 'outbound']),
            default    => $this->overviewIndex($request),
        };
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

        return view('admin.goods-receipts.index', [
            'tab'           => 'overview',
            'variants'      => $variants,
            'keyword'       => $keyword,
            'categoryId'    => $categoryId,
            'stockStatus'   => $stockStatus,
            'perPage'       => $perPage,
            'categories'    => $categories,
            'totalStock'    => (int) ProductVariant::sum('stock'),
            'totalValue'    => (float) (ProductVariant::selectRaw('SUM(stock * cost_price) as v')->value('v') ?? 0),
            'lowStockCount' => ProductVariant::where('stock', '>', 0)->where('stock', '<=', self::LOW_STOCK_THRESHOLD)->count(),
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

        if (in_array($status, [GoodsReceipt::STATUS_DRAFT, GoodsReceipt::STATUS_COMPLETED], true)) {
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

        return view('admin.goods-receipts.index', compact('goodsReceipts', 'keyword', 'perPage', 'status') + ['tab' => 'inbound']);
    }

    public function create()
    {
        $suppliers = Supplier::where('status', true)->orderBy('name')->get();

        $variants = ProductVariant::query()
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

        return view('admin.goods-receipts.create', compact('suppliers', 'variants'));
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

        return redirect()->route('admin.goods-receipts.show', $goodsReceipt->id)
            ->with('success', "Tạo phiếu nhập kho \"{$goodsReceipt->code}\" thành công.");
    }

    public function show(string $id)
    {
        $goodsReceipt = GoodsReceipt::with([
            'supplier', 'creator',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

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

    public function destroy(string $id)
    {
        $goodsReceipt = GoodsReceipt::findOrFail($id);

        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Không thể xóa phiếu nhập kho đã hoàn tất.');
        }

        $goodsReceipt->delete();

        return redirect()->route('admin.goods-receipts.list')->with('success', 'Xóa phiếu nhập kho thành công');
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

    private function generateCode(): string
    {
        $prefix = 'PN' . now()->format('Ymd');
        $lastToday = GoodsReceipt::where('code', 'like', "{$prefix}%")
            ->orderByDesc('code')
            ->first();

        $sequence = $lastToday ? ((int) substr($lastToday->code, -3)) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }
}
