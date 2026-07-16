<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\StockIssue;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueController extends Controller
{
    /**
     * Thống kê Doanh thu - Lợi nhuận.
     *
     * Nguyên tắc ghi nhận doanh thu: chỉ đơn đã "completed" mới được tính (xem review nghiệp vụ
     * — Pending/Processing/Shipping chưa chuyển giao rủi ro/lợi ích, Cancelled đã hoàn kho).
     * Mốc thời gian: orders.completed_at (fallback updated_at cho dữ liệu cũ trước khi có cột này).
     *
     * Doanh thu thuần (Net Revenue) = orders.total_money — cột này vốn đã được tính bằng
     * (tổng giá bán - discount_amount + shipping_fee) ngay từ lúc đặt hàng (CheckoutController),
     * nên dùng lại trực tiếp, không cộng trừ lại để tránh lệch số với đơn hàng gốc.
     * Giá vốn (COGS) = tổng unit_cost x quantity của các bút toán "export" trong stock_movements
     * ứng với phiếu xuất bán đã hoàn tất của đơn — đúng giá theo LÔ đã trừ FIFO tại thời điểm bán
     * (cùng cách tính đã dùng ở Báo cáo lãi gộp FIFO, để không tồn tại 2 định nghĩa khác nhau).
     */
    public function index(Request $request)
    {
        [$from, $to, $periodLabel, $period] = $this->resolveRange($request);

        $paymentMethodId = (string) $request->input('payment_method_id', '');
        $categoryId      = (string) $request->input('category_id', '');
        $brandId         = (string) $request->input('brand_id', '');
        $search          = trim((string) $request->input('search', ''));
        $sort            = (string) $request->input('sort', 'date');
        $dir             = $request->input('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $completedQuery = Order::query()
            ->where('status', 'completed')
            ->whereBetween(DB::raw('COALESCE(completed_at, updated_at)'), [$from, $to])
            ->when($paymentMethodId !== '', fn ($q) => $q->where('payment_method_id', $paymentMethodId))
            ->when($categoryId !== '', fn ($q) => $q->whereHas('orderItems', function ($qi) use ($categoryId) {
                $qi->whereHas('productVariant.product', fn ($qp) => $qp->where('category_id', $categoryId));
            }))
            ->when($brandId !== '', fn ($q) => $q->whereHas('orderItems', function ($qi) use ($brandId) {
                $qi->whereHas('productVariant.product', fn ($qp) => $qp->where('brand_id', $brandId));
            }))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('order_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('username', 'like', "%{$search}%"));
                });
            });

        $orders = (clone $completedQuery)
            ->with(['user:id,username', 'paymentMethod:id,name'])
            ->get();

        $orderIds = $orders->pluck('id');

        // Phiếu xuất BÁN đã hoàn tất gắn với các đơn trên — nguồn giá vốn FIFO + người xử lý.
        $issues = StockIssue::query()
            ->where('issue_type', StockIssue::ISSUE_TYPE_SALE)
            ->where('status', StockIssue::STATUS_COMPLETED)
            ->whereIn('order_id', $orderIds)
            ->get(['id', 'order_id', 'total_cost_amount', 'created_by']);

        $issueIds = $issues->pluck('id');

        $cogsByIssueId = StockMovement::query()
            ->where('reference_type', 'stock_issue')
            ->whereIn('reference_id', $issueIds)
            ->where('movement_type', 'export')
            ->selectRaw('reference_id, SUM(ABS(quantity) * unit_cost) as cogs')
            ->groupBy('reference_id')
            ->pluck('cogs', 'reference_id');

        $cogsByOrderId = [];
        $staffIdByOrderId = [];
        foreach ($issues as $issue) {
            $cogs = (float) ($cogsByIssueId[$issue->id] ?? 0);
            if ($cogs <= 0) {
                // Phiếu cũ trước khi quản lý theo lô → fallback giá vốn đã chốt lúc tạo phiếu.
                $cogs = (float) $issue->total_cost_amount;
            }
            $cogsByOrderId[$issue->order_id] = ($cogsByOrderId[$issue->order_id] ?? 0) + $cogs;
            $staffIdByOrderId[$issue->order_id] = $issue->created_by;
        }

        $staffIds = array_values(array_unique(array_filter($staffIdByOrderId)));
        $staffNames = User::whereIn('id', $staffIds)->pluck('username', 'id');

        $rows = $orders->map(function (Order $order) use ($cogsByOrderId, $staffIdByOrderId, $staffNames) {
            $revenue = (float) $order->total_money;
            $cogs = (float) ($cogsByOrderId[$order->id] ?? 0);
            $profit = $revenue - $cogs;
            $staffId = $staffIdByOrderId[$order->id] ?? null;

            return [
                'order'   => $order,
                'date'    => $order->completed_at ?? $order->updated_at,
                'revenue' => $revenue,
                'cogs'    => $cogs,
                'profit'  => $profit,
                'margin'  => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
                'staff'   => $staffId ? ($staffNames[$staffId] ?? '—') : '—',
            ];
        });

        // Sắp xếp bảng chi tiết (không phân trang — cùng cách tiếp cận với Báo cáo lãi gộp FIFO,
        // phù hợp quy mô dữ liệu của một cửa hàng, tránh phải đồng bộ 2 nguồn tính khác nhau).
        $sortMap = [
            'date'     => fn ($r) => $r['date'],
            'revenue'  => fn ($r) => $r['revenue'],
            'cogs'     => fn ($r) => $r['cogs'],
            'profit'   => fn ($r) => $r['profit'],
            'customer' => fn ($r) => mb_strtolower($r['order']->user?->username ?? ''),
        ];
        $sorter = $sortMap[$sort] ?? $sortMap['date'];
        $rows = $dir === 'asc' ? $rows->sortBy($sorter)->values() : $rows->sortByDesc($sorter)->values();

        // Thẻ tổng hợp.
        $summary = [
            'gross_revenue' => (float) $rows->sum(fn ($r) => $r['revenue'] + (float) $r['order']->discount_amount - (float) $r['order']->shipping_fee),
            'net_revenue'   => (float) $rows->sum('revenue'),
            'cogs'          => (float) $rows->sum('cogs'),
            'profit'        => (float) $rows->sum('profit'),
            'orders'        => $rows->count(),
        ];
        $summary['margin'] = $summary['net_revenue'] > 0 ? ($summary['profit'] / $summary['net_revenue']) * 100 : 0;
        $summary['aov'] = $summary['orders'] > 0 ? $summary['net_revenue'] / $summary['orders'] : 0;

        // Đơn đặt trong kỳ (theo created_at) — bức tranh vận hành, tách bạch khỏi doanh thu đã ghi nhận.
        $placedQuery = Order::query()->whereBetween('created_at', [$from, $to]);
        $totalPlaced = (clone $placedQuery)->count();
        $cancelledCount = (clone $placedQuery)->where('status', 'cancelled')->count();
        $paidCount = (clone $placedQuery)->where('payment_status', 'paid')->count();
        $summary['total_placed'] = $totalPlaced;
        $summary['cancelled'] = $cancelledCount;
        $summary['conversion_rate'] = $totalPlaced > 0 ? ($paidCount / $totalPlaced) * 100 : 0;

        // Biểu đồ theo ngày: gộp lại từ chính $rows đã tính ở trên (không tính lại công thức khác
        // ở nơi khác) để đảm bảo mọi con số trên trang luôn khớp nhau.
        $daily = $rows->groupBy(fn ($r) => Carbon::parse($r['date'])->format('Y-m-d'))
            ->map(fn ($group, $day) => [
                'date'    => $day,
                'revenue' => (float) $group->sum('revenue'),
                'cogs'    => (float) $group->sum('cogs'),
                'profit'  => (float) $group->sum('profit'),
            ])
            ->sortKeys()
            ->values();

        // Doanh thu theo danh mục / top sản phẩm — truy vấn cấp order_item, giới hạn đúng theo
        // $orderIds đã lọc ở trên (đảm bảo status/thời gian/phương thức/tìm kiếm luôn khớp với
        // thẻ tổng hợp + bảng chi tiết), đồng thời áp lại category/brand ở cấp item để loại các
        // sản phẩm khác danh mục/thương hiệu lẫn trong cùng đơn hàng.
        $itemRows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereIn('orders.id', $orderIds)
            ->when($categoryId !== '', fn ($q) => $q->where('products.category_id', $categoryId))
            ->when($brandId !== '', fn ($q) => $q->where('products.brand_id', $brandId))
            ->select(
                'products.id as product_id',
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.unit_price * order_items.quantity) as revenue'),
                DB::raw('SUM(order_items.quantity) as qty')
            )
            ->groupBy('products.id', 'products.name', 'categories.name')
            ->get();

        $topProducts = $itemRows->sortByDesc('revenue')->take(10)->values();

        $byCategory = $itemRows
            ->groupBy(fn ($r) => $r->category_name ?? 'Chưa phân loại')
            ->map(fn ($g, $name) => ['name' => $name, 'revenue' => (float) $g->sum('revenue')])
            ->sortByDesc('revenue')
            ->values();

        $topCustomers = $rows->groupBy(fn ($r) => $r['order']->user_id ?? 0)
            ->map(fn ($g) => [
                'name'    => $g->first()['order']->user?->username ?? 'Khách vãng lai',
                'revenue' => (float) $g->sum('revenue'),
                'orders'  => $g->count(),
            ])
            ->sortByDesc('revenue')
            ->take(5)
            ->values();

        $topStaff = $rows->groupBy('staff')
            ->map(fn ($g, $name) => ['name' => $name, 'orders' => $g->count(), 'revenue' => (float) $g->sum('revenue')])
            ->sortByDesc('orders')
            ->take(5)
            ->values();

        return view('admin.revenue.index', [
            'rows'            => $rows,
            'summary'         => $summary,
            'daily'           => $daily,
            'topProducts'     => $topProducts,
            'byCategory'      => $byCategory,
            'topCustomers'    => $topCustomers,
            'topStaff'        => $topStaff,
            'from'            => $from->format('Y-m-d'),
            'to'              => $to->format('Y-m-d'),
            'period'          => $period,
            'periodLabel'     => $periodLabel,
            'paymentMethods'  => PaymentMethod::orderBy('name')->get(['id', 'name']),
            'categories'      => Category::where('status', true)->orderBy('name')->get(['id', 'name']),
            'brands'          => Brand::where('status', true)->orderBy('name')->get(['id', 'name']),
            'paymentMethodId' => $paymentMethodId,
            'categoryId'      => $categoryId,
            'brandId'         => $brandId,
            'search'          => $search,
            'sort'            => $sort,
            'dir'             => $dir,
        ]);
    }

    public function export(Request $request)
    {
        $data = $this->index($request)->getData();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\RevenueExport($data['rows']),
            'thong-ke-doanh-thu-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    /**
     * @return array{0: Carbon, 1: Carbon, 2: string, 3: string}
     */
    private function resolveRange(Request $request): array
    {
        $period = (string) $request->input('period', 'month');
        $now = now();

        return match ($period) {
            'today'     => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Hôm nay', $period],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
                'Hôm qua',
                $period,
            ],
            '7d'  => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), '7 ngày qua', $period],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), '30 ngày qua', $period],
            'quarter' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
                'Quý ' . $now->quarter . '/' . $now->year,
                $period,
            ],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'Năm ' . $now->year, $period],
            'custom' => (function () use ($request, $now) {
                $from = $request->filled('from')
                    ? Carbon::parse($request->input('from'))->startOfDay()
                    : $now->copy()->startOfMonth();
                $to = $request->filled('to')
                    ? Carbon::parse($request->input('to'))->endOfDay()
                    : $now->copy()->endOfDay();

                return [$from, $to, 'Từ ' . $from->format('d/m/Y') . ' đến ' . $to->format('d/m/Y'), 'custom'];
            })(),
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'Tháng ' . $now->month . '/' . $now->year, 'month'],
        };
    }
}
