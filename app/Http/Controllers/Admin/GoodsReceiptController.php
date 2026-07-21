<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\GoodsReceiptLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\StockMovement;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
                'html' => view('admin.inventory.stocktakes.partials.table', compact('stocktakes'))->render(),
            ]);
        }

        $stocktakeVariants = $this->stockIssueVariants();
        $stocktakeCodePreview = $this->stocktakeCodePreview();

        return view('admin.inventory.index', compact('stocktakes', 'keyword', 'status', 'perPage', 'stocktakeVariants', 'stocktakeCodePreview') + ['tab' => 'stocktake']);
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

        if (in_array($status, [StockIssue::STATUS_DRAFT, StockIssue::STATUS_COMPLETED, StockIssue::STATUS_CANCELLED], true)) {
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
                'html' => view('admin.inventory.stock-issues.partials.outbound-table', compact('stockIssues'))->render(),
            ]);
        }

        $stockIssueVariants = $this->stockIssueVariants();
        $warehouses = Warehouse::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
        $orders = Order::orderByDesc('id')->get(['id', 'order_code']);

        return view('admin.inventory.index', compact('stockIssues', 'keyword', 'status', 'perPage', 'stockIssueVariants', 'warehouses', 'orders') + ['tab' => 'outbound']);
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
                'html' => view('admin.inventory.goods-receipts.partials.overview-table', compact('variants'))->render(),
            ]);
        }

        $categories = Category::whereNull('parent_id')->with('childrenCategories')->orderBy('name')->get();

        $activeVariants = ProductVariant::whereHas('product', fn ($q) => $q->whereNull('deleted_at'));

        return view('admin.inventory.index', [
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

        if (in_array($status, [GoodsReceipt::STATUS_DRAFT, GoodsReceipt::STATUS_COMPLETED, GoodsReceipt::STATUS_ADJUSTED, 'cancelled'], true)) {
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
                'html' => view('admin.inventory.goods-receipts.partials.table', compact('goodsReceipts'))->render(),
            ]);
        }

        $suppliers = $this->activeSuppliers();
        $goodsReceiptVariants = $this->goodsReceiptVariants();
        $warehouses = Warehouse::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();
        $quickCreateData = $this->quickCreateFormData();

        return view('admin.inventory.index', compact(
            'goodsReceipts',
            'keyword',
            'perPage',
            'status',
            'suppliers',
            'goodsReceiptVariants',
            'warehouses'
        ) + $quickCreateData + ['tab' => 'inbound']);
    }

    public function create()
    {
        $suppliers  = $this->activeSuppliers();
        $variants   = $this->goodsReceiptVariants();
        $warehouses = Warehouse::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();

        return view('admin.inventory.goods-receipts.create', compact('suppliers', 'variants', 'warehouses')
            + $this->quickCreateFormData());
    }

    /**
     * Dữ liệu cho modal "Thêm nhanh sản phẩm" (Quick Create) — dùng chung cho màn tạo phiếu
     * nhập kho (offcanvas ở trang danh sách) và trang tạo phiếu dạng toàn màn hình.
     */
    private function quickCreateFormData(): array
    {
        return [
            'quickCreateCategories' => Category::whereNull('parent_id')->with('childrenCategories')->orderBy('name')->get(),
            'quickCreateBrands'     => Brand::orderBy('name')->get(),
            'quickCreateColors'     => Color::orderBy('name')->get(),
            'quickCreateSizes'      => Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get(),
        ];
    }

    public function stockCard(ProductVariant $variant)
    {
        $variant->load(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name']);

        // Đọc THẲNG từ sổ cái stock_movements — nguồn sự thật duy nhất của mọi biến động kho, có
        // sẵn before_quantity/after_quantity chính xác cho TỪNG dòng (kể cả hold của đơn đang giao
        // dở). Cách cũ gom lại từ 4 bảng chứng từ rồi TÍNH LÙI từ tồn hiện tại: sai khi có đơn
        // in-flight (đã trừ kho nhưng chưa completed/cancelled nên không có dòng chứng từ tương ứng)
        // → toàn bộ cột "tồn cuối" lệch đúng bằng lượng đang giữ. Đọc sổ cái loại bỏ hẳn lỗi này,
        // đồng thời không cần loại trừ thủ công chứng từ đối ứng tự sinh từ kiểm kê nữa (những
        // chứng từ đó chỉ là giấy tờ, bút toán kho thật đã ghi dưới reference_type='stocktake').
        $movements = StockMovement::query()
            ->where('product_variant_id', $variant->id)
            ->with('creator')
            ->orderBy('id') // id auto-increment = đúng thứ tự thời gian tuyệt đối của các bút toán
            ->get();

        // 1 chứng từ có thể sinh NHIỀU dòng ledger (mỗi lô bị trừ FIFO = 1 dòng). Gom theo
        // (reference_type, reference_id, movement_type) để hiện 1 dòng/sự-kiện. Phải tách theo
        // movement_type vì đơn hàng hold (export) và hoàn kho khi hủy (import) DÙNG CHUNG
        // reference (order + order_id) — nếu gom chung sẽ triệt tiêu thành 0, mất cả 2 sự kiện.
        $transactions = $movements
            ->groupBy(fn (StockMovement $m) => $m->reference_type.'#'.$m->reference_id.'#'.$m->movement_type)
            ->map(function ($group) {
                $last = $group->last(); // đã sort id tăng dần → last = bút toán mới nhất của nhóm
                $totalChange = (int) $group->sum('quantity'); // export lưu số âm, import số dương
                $meta = $this->stockCardDocumentMeta($last->reference_type, (int) $last->reference_id, $last->movement_type);

                return [
                    'at'              => $last->created_at,
                    'sort_id'         => (int) $last->id,
                    'type'            => $meta['type'],
                    'type_label'      => $meta['type_label'],
                    'document_code'   => $meta['document_code'],
                    'document_url'    => $meta['document_url'],
                    'quantity_change' => $totalChange,
                    // Tồn cuối lấy SẴN từ after_quantity của bút toán cuối nhóm — KHÔNG tính lùi.
                    'ending_stock'    => (int) $last->after_quantity,
                    'user'            => $last->creator?->username ?? $meta['fallback_user'],
                ];
            })
            ->sortByDesc('sort_id') // mới nhất lên đầu, đúng thứ tự tuyệt đối theo id
            ->values();

        // Tồn theo LÔ (batch) — nguồn sự thật: lô còn hàng lên trước (FIFO), lô đã hết xuống dưới.
        $batches = \App\Models\ProductBatch::where('product_variant_id', $variant->id)
            ->orderByRaw("CASE WHEN status = 'active' AND quantity_remaining > 0 THEN 0 ELSE 1 END")
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        return response()->json([
            'html' => view('admin.inventory.goods-receipts.partials.stock-card', compact('variant', 'transactions', 'batches'))->render(),
        ]);
    }

    /**
     * Suy ra nhãn/mã/link hiển thị của 1 nhóm bút toán kho trên thẻ kho từ (reference_type,
     * reference_id, movement_type). Chỉ dùng cho hiển thị — số liệu tồn luôn lấy từ sổ cái.
     *
     * @return array{type:string,type_label:string,document_code:?string,document_url:?string,fallback_user:string}
     */
    private function stockCardDocumentMeta(string $referenceType, int $referenceId, string $movementType): array
    {
        return match ($referenceType) {
            'goods_receipt' => (function () use ($referenceId) {
                $doc = GoodsReceipt::find($referenceId);
                return [
                    'type' => 'nhap_kho',
                    'type_label' => 'Nhập kho',
                    'document_code' => $doc?->code,
                    'document_url' => $doc ? route('admin.goods-receipts.show', $doc->id) : null,
                    'fallback_user' => 'Hệ thống',
                ];
            })(),
            'stock_issue' => (function () use ($referenceId) {
                $doc = StockIssue::find($referenceId);
                return [
                    'type' => 'xuat_kho',
                    'type_label' => 'Xuất kho',
                    'document_code' => $doc?->code,
                    'document_url' => $doc ? route('admin.stock-issues.show', $doc->id) : null,
                    'fallback_user' => 'Hệ thống',
                ];
            })(),
            'stocktake' => (function () use ($referenceId) {
                $doc = Stocktake::find($referenceId);
                return [
                    'type' => 'dieu_chinh_kho',
                    'type_label' => 'Điều chỉnh kho',
                    'document_code' => $doc?->code,
                    'document_url' => null,
                    'fallback_user' => 'Hệ thống',
                ];
            })(),
            'order' => (function () use ($referenceId, $movementType) {
                $doc = Order::find($referenceId);
                // export = giữ hàng lúc đặt; import = hoàn kho khi hủy/đổi trả.
                $isReturn = $movementType === 'import';
                return [
                    'type' => $isReturn ? 'huy_don' : 'xuat_kho',
                    'type_label' => $isReturn ? 'Hủy đơn / hoàn kho' : 'Xuất kho (đơn hàng)',
                    'document_code' => $doc?->order_code,
                    'document_url' => $doc ? route('admin.orders.detail', $doc->id) : null,
                    'fallback_user' => 'Hệ thống',
                ];
            })(),
            default => [
                // auto_backfill và mọi reference_type khác: bút toán điều chỉnh hệ thống, không có chứng từ.
                'type' => 'dieu_chinh_kho',
                'type_label' => 'Điều chỉnh hệ thống',
                'document_code' => '—',
                'document_url' => null,
                'fallback_user' => 'Hệ thống',
            ],
        };
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
                'cost_price'   => (float) $v->cost_price,
                'sale_price'   => (float) $v->price,
            ])
            ->values();
    }

    private function stocktakeCodePreview(): string
    {
        return app(\App\Services\DocumentSequenceService::class)
            ->peekNextCode(\App\Services\DocumentSequenceService::TYPE_STOCKTAKE);
    }

    public function store(Request $request)
    {
        $action = $request->input('action', 'draft');

        $rules = [
            'receipt_type'             => ['required', Rule::in([
                GoodsReceipt::RECEIPT_TYPE_PURCHASE,
                GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT,
                GoodsReceipt::RECEIPT_TYPE_RETURN,
                GoodsReceipt::RECEIPT_TYPE_INITIAL,
            ])],
            'source_type'              => ['required', Rule::in([
                GoodsReceipt::SOURCE_TYPE_SUPPLIER,
                GoodsReceipt::SOURCE_TYPE_INTERNAL,
            ])],
            'supplier_id'              => [
                'nullable', 'integer', Rule::exists('suppliers', 'id'),
                Rule::requiredIf(fn () => $request->input('source_type') === GoodsReceipt::SOURCE_TYPE_SUPPLIER)
            ],
            'warehouse_id'             => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'received_at'              => ['required', 'date'],
            'receipt_reason'           => [
                'nullable', 'string', 'max:500',
                Rule::requiredIf(fn () => $request->input('source_type') === GoodsReceipt::SOURCE_TYPE_INTERNAL && $request->input('receipt_type') === GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT)
            ],
            'note'                     => ['nullable', 'string', 'max:1000'],
            'action'                   => ['required', Rule::in(['draft', 'complete'])],
        ];

        if ($action === 'complete') {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_variant_id'] = ['required', 'integer', Rule::exists('product_variants', 'id')];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.cost_price'] = ['required', 'numeric', 'gt:0'];
        } else {
            $rules['items'] = ['nullable', 'array'];
            $rules['items.*.product_variant_id'] = ['required', 'integer', Rule::exists('product_variants', 'id')];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.cost_price'] = ['required', 'numeric', 'gt:0'];
        }

        $validated = $request->validate($rules, [
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
            'warehouse_id.required' => 'Vui lòng chọn kho nhận.',
            'received_at.required'  => 'Vui lòng chọn ngày nhập kho.',
            'receipt_reason.required' => 'Vui lòng nhập lý do nhập kho.',
            'items.required'       => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
            'items.min'            => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
            'items.*.cost_price.gt' => 'Giá nhập kho phải lớn hơn 0 (không được để trống hoặc bằng 0).',
        ]);

        if (!empty($validated['items'])) {
            $variantIds = collect($validated['items'])->pluck('product_variant_id');
            if ($variantIds->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Không được trùng cùng một biến thể sản phẩm trong một phiếu.'
                ]);
            }
        }

        $goodsReceipt = DB::transaction(function () use ($validated) {
            $validated['source_type'] ??= GoodsReceipt::SOURCE_TYPE_SUPPLIER;
            $validated['receipt_type'] ??= GoodsReceipt::RECEIPT_TYPE_PURCHASE;
            $validated['supplier_id'] = $validated['source_type'] === GoodsReceipt::SOURCE_TYPE_INTERNAL
                ? null
                : $validated['supplier_id'];

            $groupedItems = $this->groupReceiptItems($validated['items'] ?? []);
            $totalAmount = collect($groupedItems)->sum(fn ($item) => $item['quantity'] * $item['cost_price']);
            $totalQuantity = collect($groupedItems)->sum(fn ($item) => $item['quantity']);

            $goodsReceipt = GoodsReceipt::create([
                'code'           => app(\App\Services\DocumentSequenceService::class)->generateGoodsReceiptCode(),
                'receipt_type'   => $validated['receipt_type'],
                'source_type'    => $validated['source_type'],
                'receipt_reason' => $validated['receipt_reason'] ?? null,
                'supplier_id'    => $validated['supplier_id'],
                'warehouse_id'   => $validated['warehouse_id'] ?? Warehouse::getDefault()?->id,
                'note'           => $validated['note'] ?? null,
                'status'         => GoodsReceipt::STATUS_DRAFT,
                'total_amount'   => $totalAmount,
                'total_quantity' => $totalQuantity,
                'received_at'    => $validated['received_at'] ? \Carbon\Carbon::parse($validated['received_at']) : null,
                'created_by'     => Auth::id(),
            ]);

            foreach ($groupedItems as $item) {
                $goodsReceipt->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'cost_price'         => $item['cost_price'],
                ]);
            }

            $this->logGoodsReceipt($goodsReceipt, 'created', 'Tạo phiếu nhập mới');

            if ($validated['action'] === 'complete') {
                $this->completeReceipt($goodsReceipt);
            } else {
                $this->logGoodsReceipt($goodsReceipt, 'draft_saved', 'Lưu nháp phiếu nhập');
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
            'supplier', 'creator', 'adjuster', 'adjustmentStockIssue', 'logs.user',
            'confirmer', 'canceller', 'warehouse',
            'items.productVariant.product:id,name,thumbnail',
            'items.productVariant.color:id,name,hex_code',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('admin.inventory.goods-receipts.partials.show-content', compact('goodsReceipt'))->render(),
            ]);
        }

        return view('admin.inventory.goods-receipts.show', compact('goodsReceipt'));
    }

    /**
     * Trang in phiếu nhập kho (khổ A4, HTML + window.print()). Số hiệu = mã phiếu.
     * Trang in độc lập (không layout admin), nhúng vào modal xem trước qua iframe.
     */
    public function print(string $id)
    {
        $goodsReceipt = GoodsReceipt::with([
            'supplier', 'creator', 'warehouse',
            'items.productVariant.product:id,name',
            'items.productVariant.color:id,name',
            'items.productVariant.size:id,name',
        ])->findOrFail($id);

        return view('admin.inventory.goods-receipts.print', compact('goodsReceipt'));
    }

    public function complete(string $id)
    {
        $code = null;
        try {
            DB::transaction(function () use ($id, &$code) {
                $goodsReceipt = GoodsReceipt::whereKey($id)->lockForUpdate()->firstOrFail();
                $goodsReceipt->load('items');
                $this->completeReceipt($goodsReceipt);
                $code = $goodsReceipt->code;
            });
        } catch (ValidationException $e) {
            return back()->with('error', collect($e->errors())->flatten()->first());
        }

        return back()->with('success', "Đã hoàn tất phiếu nhập kho \"{$code}\" và cập nhật tồn kho.");
    }

    public function adjust(Request $request, string $id)
    {
        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
            ->with('error', 'Không thể hủy hoặc điều chỉnh phiếu đã hoàn tất. Chỉ phiếu nháp mới được hủy.');
    }

    public function edit(Request $request, string $id)
    {
        $goodsReceipt = GoodsReceipt::with('items.productVariant.product')->findOrFail($id);

        if (! $goodsReceipt->isDraft()) {
            $message = $goodsReceipt->isCompleted()
                ? 'Không thể chỉnh sửa phiếu đã hoàn tất'
                : 'Không thể chỉnh sửa phiếu đã hủy';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
                ->with('error', $message);
        }

        $suppliers  = $this->activeSuppliers();
        $variants   = $this->goodsReceiptVariants();
        $warehouses = Warehouse::where('status', true)->orderBy('is_default', 'desc')->orderBy('name')->get();

        // Chuyển đổi chi tiết sản phẩm sang cấu trúc JSON phục vụ JS table
        $selectedItems = [];
        foreach ($goodsReceipt->items as $item) {
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
                    ],
                    'quantity' => $item->quantity,
                    'cost_price' => (float)$item->cost_price,
                ];
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.goods-receipts.partials.edit-content', compact('goodsReceipt', 'suppliers', 'variants', 'warehouses', 'selectedItems'))->render(),
            ]);
        }

        return view('admin.inventory.goods-receipts.edit', compact('goodsReceipt', 'suppliers', 'variants', 'warehouses', 'selectedItems'));
    }

    public function update(Request $request, string $id)
    {
        $goodsReceipt = GoodsReceipt::with('items')->findOrFail($id);

        if (! $goodsReceipt->isDraft()) {
            $message = $goodsReceipt->isCompleted()
                ? 'Không thể chỉnh sửa phiếu đã hoàn tất'
                : 'Không thể chỉnh sửa phiếu đã hủy';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }
            return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
                ->with('error', $message);
        }

        $action = $request->input('action', 'draft');

        $rules = [
            'receipt_type'             => ['required', Rule::in([
                GoodsReceipt::RECEIPT_TYPE_PURCHASE,
                GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT,
                GoodsReceipt::RECEIPT_TYPE_RETURN,
                GoodsReceipt::RECEIPT_TYPE_INITIAL,
            ])],
            'source_type'              => ['required', Rule::in([
                GoodsReceipt::SOURCE_TYPE_SUPPLIER,
                GoodsReceipt::SOURCE_TYPE_INTERNAL,
            ])],
            'supplier_id'              => [
                'nullable', 'integer', Rule::exists('suppliers', 'id'),
                Rule::requiredIf(fn () => $request->input('source_type') === GoodsReceipt::SOURCE_TYPE_SUPPLIER)
            ],
            'warehouse_id'             => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'received_at'              => ['required', 'date'],
            'receipt_reason'           => [
                'nullable', 'string', 'max:500',
                Rule::requiredIf(fn () => $request->input('source_type') === GoodsReceipt::SOURCE_TYPE_INTERNAL && $request->input('receipt_type') === GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT)
            ],
            'note'                     => ['nullable', 'string', 'max:1000'],
            'action'                   => ['required', Rule::in(['draft', 'complete'])],
        ];

        if ($action === 'complete') {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_variant_id'] = ['required', 'integer', Rule::exists('product_variants', 'id')];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.cost_price'] = ['required', 'numeric', 'gt:0'];
        } else {
            $rules['items'] = ['nullable', 'array'];
            $rules['items.*.product_variant_id'] = ['required', 'integer', Rule::exists('product_variants', 'id')];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.cost_price'] = ['required', 'numeric', 'gt:0'];
        }

        $validated = $request->validate($rules, [
            'supplier_id.required' => 'Vui lòng chọn nhà cung cấp.',
            'warehouse_id.required' => 'Vui lòng chọn kho nhận.',
            'received_at.required'  => 'Vui lòng chọn ngày nhập kho.',
            'receipt_reason.required' => 'Vui lòng nhập lý do nhập kho.',
            'items.required'       => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
            'items.min'            => 'Vui lòng chọn ít nhất một sản phẩm để nhập kho.',
            'items.*.cost_price.gt' => 'Giá nhập kho phải lớn hơn 0 (không được để trống hoặc bằng 0).',
        ]);

        if (!empty($validated['items'])) {
            $variantIds = collect($validated['items'])->pluck('product_variant_id');
            if ($variantIds->duplicates()->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Không được trùng cùng một biến thể sản phẩm trong một phiếu.'
                ]);
            }
        }

        DB::transaction(function () use ($goodsReceipt, $validated) {
            $validated['source_type'] ??= GoodsReceipt::SOURCE_TYPE_SUPPLIER;
            $validated['receipt_type'] ??= GoodsReceipt::RECEIPT_TYPE_PURCHASE;
            $validated['supplier_id'] = $validated['source_type'] === GoodsReceipt::SOURCE_TYPE_INTERNAL
                ? null
                : $validated['supplier_id'];

            $goodsReceipt->items()->delete();

            $groupedItems = $this->groupReceiptItems($validated['items'] ?? []);

            $totalAmount = 0;
            $totalQuantity = 0;
            foreach ($groupedItems as $item) {
                $goodsReceipt->items()->create([
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'cost_price'         => $item['cost_price'],
                ]);
                $totalAmount += $item['quantity'] * $item['cost_price'];
                $totalQuantity += $item['quantity'];
            }

            $goodsReceipt->update([
                'receipt_type'   => $validated['receipt_type'],
                'source_type'    => $validated['source_type'],
                'receipt_reason' => $validated['receipt_reason'] ?? null,
                'supplier_id'    => $validated['supplier_id'],
                'warehouse_id'   => $validated['warehouse_id'] ?? Warehouse::getDefault()?->id,
                'received_at'    => $validated['received_at'] ? \Carbon\Carbon::parse($validated['received_at']) : null,
                'note'           => $validated['note'],
                'total_amount'   => $totalAmount,
                'total_quantity' => $totalQuantity,
            ]);

            $this->logGoodsReceipt($goodsReceipt, 'updated', 'Cập nhật phiếu nhập kho');

            if ($validated['action'] === 'complete') {
                $goodsReceipt->load('items');
                $this->completeReceipt($goodsReceipt);
            } else {
                $this->logGoodsReceipt($goodsReceipt, 'draft_saved', 'Lưu nháp phiếu nhập');
            }
        });

        $message = $validated['action'] === 'complete' 
            ? 'Cập nhật và hoàn tất phiếu nhập kho thành công.'
            : 'Cập nhật phiếu nháp thành công.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'code' => $goodsReceipt->code,
                'show_url' => route('admin.goods-receipts.show', $goodsReceipt->id),
                'table_url' => route('admin.goods-receipts.list', ['tab' => 'inbound']),
            ]);
        }

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])->with('success', $message);
    }

    public function destroy(string $id)
    {
        $goodsReceipt = GoodsReceipt::findOrFail($id);

        if (!$goodsReceipt->isDraft()) {
            return back()->with('error', 'Chỉ có thể hủy phiếu nhập kho đang ở trạng thái Nháp.');
        }

        $goodsReceipt->update([
            'status' => GoodsReceipt::STATUS_CANCELLED,
            'cancelled_by' => Auth::id(),
            'cancelled_at' => now(),
        ]);
        $this->logGoodsReceipt($goodsReceipt, 'cancelled', "Hủy phiếu nhập kho \"{$goodsReceipt->code}\".");

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
            ->with('success', "Đã hủy phiếu nhập kho \"{$goodsReceipt->code}\" thành công.");
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu nhập kho để hủy.');
        }

        // Chỉ cho phép hủy phiếu Nháp.
        $receipts = GoodsReceipt::whereIn('id', $ids)->where('status', GoodsReceipt::STATUS_DRAFT)->get();
        foreach ($receipts as $receipt) {
            $receipt->update([
                'status' => GoodsReceipt::STATUS_CANCELLED,
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);
            $this->logGoodsReceipt($receipt, 'cancelled', "Hủy phiếu nhập kho \"{$receipt->code}\".");
        }
        $count = $receipts->count();

        return back()->with('success', "Đã hủy {$count} phiếu nhập kho thành công.");
    }

    /**
     * Chuyển phiếu nhập kho đã hủy vào thùng rác (soft-delete) — an toàn, có thể khôi phục.
     * Chỉ cho phép với phiếu ở trạng thái "Đã hủy" (đã chốt không còn thao tác gì khác).
     */
    public function trashDelete(string $id)
    {
        $goodsReceipt = GoodsReceipt::where('status', GoodsReceipt::STATUS_CANCELLED)->findOrFail($id);
        $goodsReceipt->update(['deleted_by' => Auth::id()]);
        $goodsReceipt->delete();

        return redirect()->route('admin.goods-receipts.list', ['tab' => 'inbound'])
            ->with('success', "Đã chuyển phiếu nhập kho \"{$goodsReceipt->code}\" vào thùng rác.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = GoodsReceipt::onlyTrashed()
            ->with(['supplier', 'creator', 'deleter'])
            ->withCount('items')
            ->orderByDesc('deleted_at');

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('code', 'like', "%{$keyword}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('name', 'like', "%{$keyword}%"));
            });
        }

        $goodsReceipts = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.inventory.goods-receipts.partials.trash-table', compact('goodsReceipts'))->render(),
            ]);
        }

        return view('admin.inventory.goods-receipts.trash', compact('goodsReceipts', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        $goodsReceipt = GoodsReceipt::onlyTrashed()->findOrFail($id);
        GoodsReceipt::onlyTrashed()->where('id', $goodsReceipt->id)->update(['deleted_by' => null]);
        $goodsReceipt->restore();

        return redirect()->route('admin.goods-receipts.trash')->with('success', 'Khôi phục phiếu nhập kho thành công');
    }

    public function forceDelete(string $id)
    {
        GoodsReceipt::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.goods-receipts.trash')->with('success', 'Đã xóa vĩnh viễn phiếu nhập kho');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu nhập kho.');
        }

        GoodsReceipt::onlyTrashed()->whereIn('id', $ids)->update(['deleted_by' => null]);
        $restored = GoodsReceipt::onlyTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', "Đã khôi phục {$restored} phiếu nhập kho thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một phiếu nhập kho.');
        }

        $count = GoodsReceipt::onlyTrashed()->whereIn('id', $ids)->count();
        GoodsReceipt::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return back()->with('success', "Đã xóa vĩnh viễn {$count} phiếu nhập kho.");
    }

    private function completeReceipt(GoodsReceipt $goodsReceipt): void
    {
        if (! $goodsReceipt->isDraft()) {
            throw ValidationException::withMessages([
                'status' => 'Phiếu nhập kho này đã được xử lý, không thể cộng tồn kho lần nữa.',
            ]);
        }

        $this->assertValidReceiptItems($goodsReceipt);

        // Mỗi dòng phiếu nhập => tạo 1 LÔ mới (giữ giá vốn riêng), ghi sổ cái + đồng bộ cache tồn.
        $batchService = app(\App\Services\InventoryBatchService::class);
        foreach ($goodsReceipt->items as $item) {
            $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);
            if (!$variant) continue;

            $batchService->receive(
                $variant,
                (int) $item->quantity,
                (float) $item->cost_price,
                'goods_receipt',
                $goodsReceipt->id,
                $item->id,
                Auth::id(),
                $goodsReceipt->received_at ?? now()
            );
        }

        $now = now();
        $goodsReceipt->update([
            'status'       => GoodsReceipt::STATUS_COMPLETED,
            'confirmed_by' => Auth::id(),
            'confirmed_at' => $now,
            'completed_at' => $now,
        ]);

        $this->logGoodsReceipt($goodsReceipt, 'confirmed', "Xác nhận nhập kho \"{$goodsReceipt->code}\" và cộng tồn kho.");
    }

    private function assertValidReceiptItems(GoodsReceipt $goodsReceipt): void
    {
        if ($goodsReceipt->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Phiếu nhập kho phải có ít nhất một mặt hàng.',
            ]);
        }

        foreach ($goodsReceipt->items as $item) {
            if ((int) $item->quantity <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Số lượng nhập từng dòng phải lớn hơn 0.',
                ]);
            }

            if ((float) $item->cost_price < 0) {
                throw ValidationException::withMessages([
                    'items' => 'Đơn giá nhập từng dòng không được âm.',
                ]);
            }
        }
    }

    private function groupReceiptItems(array $items): array
    {
        $groupedItems = [];
        foreach ($items as $item) {
            $vid = $item['product_variant_id'];
            if (isset($groupedItems[$vid])) {
                $groupedItems[$vid]['quantity'] += (int) $item['quantity'];
                $groupedItems[$vid]['cost_price'] = max((float) $groupedItems[$vid]['cost_price'], (float) $item['cost_price']);
            } else {
                $groupedItems[$vid] = [
                    'product_variant_id' => (int) $item['product_variant_id'],
                    'quantity' => (int) $item['quantity'],
                    'cost_price' => (float) $item['cost_price'],
                ];
            }
        }

        return $groupedItems;
    }

    private function logGoodsReceipt(GoodsReceipt $goodsReceipt, string $action, string $message): void
    {
        GoodsReceiptLog::create([
            'goods_receipt_id' => $goodsReceipt->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'message' => $message,
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

}

