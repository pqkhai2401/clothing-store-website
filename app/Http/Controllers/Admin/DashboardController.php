<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private const LOW_STOCK_THRESHOLD = 10;

    public const METRIC_LABELS = [
        'revenue'       => 'Doanh thu thực thu',
        'orders'        => 'Số đơn hàng',
        'sold'          => 'Số sản phẩm bán ra',
        'new_customers' => 'Khách hàng mới',
        'cancelled'     => 'Đơn bị hủy',
    ];

    public const RANGE_LABELS = [
        'today'      => 'Hôm nay',
        '7d'         => '7 ngày gần nhất',
        '30d'        => '30 ngày gần nhất',
        'this_month' => 'Tháng này',
        'last_month' => 'Tháng trước',
        'this_year'  => 'Năm nay',
        'last_year'  => 'Năm trước',
        'custom'     => 'Tùy chỉnh…',
    ];

    public const GROUP_BY_LABELS = [
        'day'   => 'Ngày',
        'month' => 'Tháng',
        'year'  => 'Năm',
    ];

    public const CHART_TYPE_LABELS = [
        'line' => 'Biểu đồ đường',
        'bar'  => 'Biểu đồ cột',
        'area' => 'Biểu đồ vùng',
    ];

    public function index(Request $request)
    {
        $metric    = array_key_exists($request->input('metric'), self::METRIC_LABELS) ? $request->input('metric') : 'revenue';
        $range     = array_key_exists($request->input('range'), self::RANGE_LABELS) ? $request->input('range') : '30d';
        $groupBy   = array_key_exists($request->input('group_by'), self::GROUP_BY_LABELS) ? $request->input('group_by') : 'day';
        $chartType = array_key_exists($request->input('chart_type'), self::CHART_TYPE_LABELS) ? $request->input('chart_type') : 'line';
        $dateFrom  = (string) $request->input('date_from', '');
        $dateTo    = (string) $request->input('date_to', '');

        $statusFilter  = (string) $request->input('status', '');
        $paymentFilter = (string) $request->input('payment_status', '');
        $methodFilter  = (string) $request->input('payment_method_id', '');

        $filters = [
            'status'            => $statusFilter !== '' ? $statusFilter : null,
            'payment_status'    => $paymentFilter !== '' ? $paymentFilter : null,
            'payment_method_id' => $methodFilter !== '' ? $methodFilter : null,
        ];

        [$from, $to] = $this->resolveRange($range, $dateFrom, $dateTo);
        $prevTo   = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subSeconds($from->diffInSeconds($to));

        // KPI theo kỳ đang chọn (áp dụng bộ lọc nâng cao)
        $periodRevenue      = $this->metricValue('revenue', $from, $to, $filters);
        $prevPeriodRevenue  = $this->metricValue('revenue', $prevFrom, $prevTo, $filters);
        $periodOrders       = $this->metricValue('orders', $from, $to, $filters);
        $prevPeriodOrders   = $this->metricValue('orders', $prevFrom, $prevTo, $filters);
        $periodPending      = $this->orderFilterQuery($from, $to, $filters)->where('status', 'pending')->count();
        $prevPeriodPending  = $this->orderFilterQuery($prevFrom, $prevTo, $filters)->where('status', 'pending')->count();
        $newCustomersPeriod = (int) $this->metricValue('new_customers', $from, $to, $filters);

        // Số liệu tức thời (không phụ thuộc kỳ/bộ lọc) — snapshot hiện tại của hệ thống
        $pending   = Order::where('status', 'pending')->count();
        $shipping  = Order::where('status', 'shipping')->count();
        $completed = Order::where('status', 'completed')->count();
        $cancelled = Order::where('status', 'cancelled')->count();

        $totalProducts   = Product::count();
        $sellingProducts = Product::where('status', true)->count();
        $hiddenProducts  = $totalProducts - $sellingProducts;
        $totalCustomers  = User::role(UserRole::CUSTOMER->value)->count();

        $lowStock = ProductVariant::where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->whereHas('product', fn ($q) => $q->where('status', true))
            ->count();

        $stats = [
            'revenue'         => $periodRevenue,
            'revenueDelta'    => $this->percentChange($periodRevenue, $prevPeriodRevenue),
            'orders'          => $periodOrders,
            'ordersDelta'     => $this->percentChange($periodOrders, $prevPeriodOrders),
            'pending'         => $pending,
            'pendingDelta'    => $this->percentChange($periodPending, $prevPeriodPending),
            'sellingProducts' => $sellingProducts,
            'totalProducts'   => $totalProducts,
            'totalCustomers'  => $totalCustomers,
            'newCustomers'    => $newCustomersPeriod,
            'shipping'        => $shipping,
            'completed'       => $completed,
            'cancelled'       => $cancelled,
            'hiddenProducts'  => $hiddenProducts,
            'lowStock'        => $lowStock,
        ];

        $revenueChart = $this->buildMetricChart($metric, $from, $to, $prevFrom, $prevTo, $groupBy, $filters);

        $statusRatio = [
            ['label' => 'Hoàn thành', 'value' => $completed, 'color' => '#16A34A'],
            ['label' => 'Đang giao',  'value' => $shipping,  'color' => '#2563EB'],
            ['label' => 'Chờ xử lý',  'value' => $pending,   'color' => '#D97706'],
            ['label' => 'Đã hủy',     'value' => $cancelled, 'color' => '#DC2626'],
        ];

        return view('admin.dashboard', [
            'stats'            => $stats,
            'revenueChart'     => $revenueChart,
            'statusRatio'      => $statusRatio,
            'recentOrders'     => $this->buildRecentOrders(),
            'topProducts'      => $this->buildTopProducts(),
            'lowStockProducts' => $this->buildLowStockProducts(),
            'latestReviews'    => $this->buildLatestReviews(),
            'paymentMethods'   => PaymentMethod::where('status', true)->orderBy('id')->get(),

            'metric'    => $metric,
            'range'     => $range,
            'groupBy'   => $groupBy,
            'chartType' => $chartType,
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,

            'statusFilter'  => $statusFilter,
            'paymentFilter' => $paymentFilter,
            'methodFilter'  => $methodFilter,

            'metricLabels'    => self::METRIC_LABELS,
            'rangeLabels'     => self::RANGE_LABELS,
            'groupByLabels'   => self::GROUP_BY_LABELS,
            'chartTypeLabels' => self::CHART_TYPE_LABELS,
            'statusLabels'        => OrderController::STATUS_LABELS,
            'paymentStatusLabels' => OrderController::PAYMENT_STATUS_LABELS,

            'periodLabel' => $from->format('d/m/Y') . ' – ' . $to->format('d/m/Y'),
        ]);
    }

    /**
     * Suy ra khoảng [from, to] thật từ preset "range" (hoặc date_from/date_to khi range=custom).
     */
    private function resolveRange(string $range, string $dateFrom, string $dateTo): array
    {
        $now = now();

        return match ($range) {
            'today'      => [$now->copy()->startOfDay(), $now->copy()],
            '7d'         => [$now->copy()->subDays(6)->startOfDay(), $now->copy()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year'  => [$now->copy()->startOfYear(), $now->copy()],
            'last_year'  => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'custom'     => $this->resolveCustomRange($dateFrom, $dateTo, $now),
            default      => [$now->copy()->subDays(29)->startOfDay(), $now->copy()], // '30d'
        };
    }

    private function resolveCustomRange(string $dateFrom, string $dateTo, Carbon $now): array
    {
        try {
            $from = $dateFrom !== '' ? Carbon::parse($dateFrom)->startOfDay() : $now->copy()->subDays(29)->startOfDay();
            $to   = $dateTo !== '' ? Carbon::parse($dateTo)->endOfDay() : $now->copy();
        } catch (\Throwable) {
            return [$now->copy()->subDays(29)->startOfDay(), $now->copy()];
        }

        return $from->lte($to) ? [$from, $to] : [$to, $from];
    }

    /**
     * % thay đổi so với kỳ trước. Tránh chia cho 0 khi kỳ trước không có dữ liệu.
     */
    private function percentChange(float $current, float $previous): float
    {
        if ($previous == 0.0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Query đơn hàng trong khoảng [from, to] có áp dụng bộ lọc nâng cao (trạng thái/thanh toán/phương thức).
     */
    private function orderFilterQuery(Carbon $from, Carbon $to, array $filters)
    {
        return Order::whereBetween('created_at', [$from, $to])
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('payment_status', $v))
            ->when($filters['payment_method_id'] ?? null, fn ($q, $v) => $q->where('payment_method_id', $v));
    }

    /**
     * Giá trị 1 chỉ số trong khoảng [from, to], áp dụng bộ lọc nâng cao.
     */
    private function metricValue(string $metric, Carbon $from, Carbon $to, array $filters): float
    {
        if ($metric === 'sold') {
            return (float) OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$from, $to])
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('orders.status', $v))
                ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('orders.payment_status', $v))
                ->when($filters['payment_method_id'] ?? null, fn ($q, $v) => $q->where('orders.payment_method_id', $v))
                ->sum('order_items.quantity');
        }

        if ($metric === 'new_customers') {
            return (float) User::role(UserRole::CUSTOMER->value)
                ->whereBetween('created_at', [$from, $to])
                ->count();
        }

        if ($metric === 'cancelled') {
            return (float) $this->orderFilterQuery($from, $to, $filters)->where('status', 'cancelled')->count();
        }

        if ($metric === 'orders') {
            return (float) $this->orderFilterQuery($from, $to, $filters)->count();
        }

        // revenue (mặc định) — loại đơn đã hủy, chỉ tính đơn đã hoàn thành hoặc đã thanh toán
        return (float) $this->orderFilterQuery($from, $to, $filters)
            ->where('status', '!=', 'cancelled')
            ->where(fn ($q) => $q->where('status', 'completed')->orWhere('payment_status', 'paid'))
            ->sum('total_money');
    }

    /**
     * Danh sách [period_key, label] cho từng điểm trên trục thời gian, theo group_by (ngày/tháng/năm).
     */
    private function periodKeys(Carbon $from, Carbon $to, string $groupBy): array
    {
        $keys = [];

        if ($groupBy === 'month') {
            $cursor = $from->copy()->startOfMonth();
            while ($cursor->lte($to)) {
                $keys[] = [$cursor->format('Y-m'), 'Th ' . $cursor->format('n/Y')];
                $cursor->addMonthNoOverflow();
            }
        } elseif ($groupBy === 'year') {
            $cursor = $from->copy()->startOfYear();
            while ($cursor->lte($to)) {
                $keys[] = [$cursor->format('Y'), $cursor->format('Y')];
                $cursor->addYear();
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            while ($cursor->lte($to)) {
                $keys[] = [$cursor->format('Y-m-d'), $cursor->format('d/m')];
                $cursor->addDay();
            }
        }

        return $keys;
    }

    /**
     * Tổng hợp giá trị 1 chỉ số theo từng mốc thời gian (ngày/tháng/năm) trong khoảng [from, to],
     * bằng 1 câu SQL GROUP BY duy nhất (tránh N truy vấn khi khoảng thời gian dài).
     * Trả về mảng ['period_key' => value].
     */
    private function bucketedMetricSeries(string $metric, Carbon $from, Carbon $to, string $groupBy, array $filters): array
    {
        $dateFormat = match ($groupBy) {
            'month' => '%Y-%m',
            'year'  => '%Y',
            default => '%Y-%m-%d',
        };

        if ($metric === 'sold') {
            return OrderItem::query()
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$from, $to])
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('orders.status', $v))
                ->when($filters['payment_status'] ?? null, fn ($q, $v) => $q->where('orders.payment_status', $v))
                ->when($filters['payment_method_id'] ?? null, fn ($q, $v) => $q->where('orders.payment_method_id', $v))
                ->selectRaw("DATE_FORMAT(orders.created_at, '{$dateFormat}') as period_key, SUM(order_items.quantity) as val")
                ->groupBy('period_key')
                ->pluck('val', 'period_key')
                ->toArray();
        }

        if ($metric === 'new_customers') {
            return User::role(UserRole::CUSTOMER->value)
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period_key, COUNT(*) as val")
                ->groupBy('period_key')
                ->pluck('val', 'period_key')
                ->toArray();
        }

        $query = $this->orderFilterQuery($from, $to, $filters);

        if ($metric === 'cancelled') {
            $query->where('status', 'cancelled');
        } elseif ($metric === 'revenue') {
            $query->where('status', '!=', 'cancelled')
                ->where(fn ($q) => $q->where('status', 'completed')->orWhere('payment_status', 'paid'));
        }

        $valueExpr = $metric === 'revenue' ? 'SUM(total_money)' : 'COUNT(*)';

        return $query
            ->selectRaw("DATE_FORMAT(created_at, '{$dateFormat}') as period_key, {$valueExpr} as val")
            ->groupBy('period_key')
            ->pluck('val', 'period_key')
            ->toArray();
    }

    private function buildMetricChart(string $metric, Carbon $from, Carbon $to, Carbon $prevFrom, Carbon $prevTo, string $groupBy, array $filters): array
    {
        $keys     = $this->periodKeys($from, $to, $groupBy);
        $prevKeys = $this->periodKeys($prevFrom, $prevTo, $groupBy);

        $currentMap  = $this->bucketedMetricSeries($metric, $from, $to, $groupBy, $filters);
        $previousMap = $this->bucketedMetricSeries($metric, $prevFrom, $prevTo, $groupBy, $filters);

        $isMoney = $metric === 'revenue';

        $labels = [];
        $current = [];
        $previous = [];

        foreach ($keys as $i => [$key, $label]) {
            $labels[] = $label;
            $value    = (float) ($currentMap[$key] ?? 0);
            $current[] = $isMoney ? round($value / 1_000_000, 2) : (int) $value;

            $prevKey   = $prevKeys[$i][0] ?? null;
            $prevValue = $prevKey !== null ? (float) ($previousMap[$prevKey] ?? 0) : 0.0;
            $previous[] = $isMoney ? round($prevValue / 1_000_000, 2) : (int) $prevValue;
        }

        return [
            'labels'   => $labels,
            'current'  => $current,
            'previous' => $previous,
            'title'    => self::METRIC_LABELS[$metric] . ' theo ' . mb_strtolower(self::GROUP_BY_LABELS[$groupBy]),
            'isMoney'  => $isMoney,
        ];
    }

    private function buildRecentOrders()
    {
        $statusPillClass = [
            'pending'    => 'pending',
            'processing' => 'neutral',
            'shipping'   => 'ship',
            'completed'  => 'done',
            'cancelled'  => 'cancel',
        ];

        return Order::with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function (Order $order) use ($statusPillClass) {
                return [
                    'code'          => $order->order_code,
                    'name'          => $order->user->username ?? '—',
                    'phone'         => $order->phone,
                    'total'         => (float) $order->total_money,
                    'status'        => $statusPillClass[$order->status] ?? 'neutral',
                    'status_label'  => OrderController::STATUS_LABELS[$order->status] ?? $order->status,
                    'payment'       => OrderController::PAYMENT_STATUS_LABELS[$order->payment_status] ?? $order->payment_status,
                    'payment_class' => $order->payment_status === 'paid' ? 'done' : 'neutral',
                    'date'          => $order->created_at->format('d/m/Y'),
                ];
            });
    }

    private function buildTopProducts()
    {
        $tones = ['green', 'blue', 'purple', 'amber', 'red'];
        $genderLabels = Gender::labels();

        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('order_items.product_variant_id, SUM(order_items.quantity) as sold_qty, SUM(order_items.quantity * order_items.unit_price) as revenue_sum')
            ->groupBy('order_items.product_variant_id')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->with('productVariant.product.category')
            ->get()
            ->values()
            ->map(function ($row, int $index) use ($tones, $genderLabels) {
                $product = $row->productVariant?->product;

                return [
                    'name'    => $product->name ?? '—',
                    'cat'     => trim(($product->category->name ?? '') . ' · ' . ($genderLabels[$product->gender ?? ''] ?? ''), ' ·'),
                    'sold'    => (int) $row->sold_qty,
                    'revenue' => number_format((float) $row->revenue_sum, 0, ',', '.') . ' ₫',
                    'tone'    => $tones[$index % count($tones)],
                ];
            });
    }

    private function buildLowStockProducts()
    {
        return ProductVariant::with(['product', 'color', 'size'])
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->whereHas('product', fn ($q) => $q->where('status', true))
            ->orderBy('stock')
            ->limit(5)
            ->get()
            ->map(fn (ProductVariant $variant) => [
                'name'    => $variant->product->name ?? '—',
                'variant' => trim(($variant->color->name ?? '') . ' / ' . ($variant->size->name ?? ''), ' /'),
                'stock'   => $variant->stock,
            ]);
    }

    private function buildLatestReviews()
    {
        return Review::approved()
            ->with(['user', 'product'])
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Review $review) => [
                'user'    => $review->user->username ?? 'Ẩn danh',
                'product' => $review->product->name ?? '—',
                'stars'   => $review->rating,
                'text'    => $review->comment,
                'time'    => $review->created_at->locale('vi')->diffForHumans(),
            ]);
    }
}
