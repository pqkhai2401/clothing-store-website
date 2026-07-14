@extends('layouts.admin')

@section('title', 'Thống kê doanh thu')

@push('styles')
@include('admin.products.styles')
<style>
    /* Trang Thống kê doanh thu — dùng biến --hk-* của theme để tự đổi sáng/tối theo nút
       "Giao diện tối" (không dùng @media prefers-color-scheme, có thể lệch trạng thái thật). */
    /* ── Thẻ bộ lọc (gộp thành 1 card, giống Dashboard) ── */
    .rev-filter { padding:22px; margin-bottom:20px; }
    .rev-filter-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
    .rev-filter-title { font-size:18px; font-weight:700; color: var(--hk-text-1); margin:0; }
    .rev-filter-sub { font-size:13px; color: var(--hk-text-2); margin:4px 0 0; }
    .rev-fgrid { display:grid; grid-template-columns:repeat(5, 1fr); gap:14px; }
    .rev-field { display:flex; flex-direction:column; gap:6px; }
    .rev-field label { font-size:11px; font-weight:700; color: var(--hk-text-2); text-transform:uppercase; letter-spacing:.06em; }
    .rev-field .form-select, .rev-field .form-control { height:40px; border-radius:10px; font-size:13px; }
    .rev-custom-date { display:none; gap:14px; align-items:flex-end; margin-top:16px; padding-top:16px; border-top:1px solid var(--hk-border); flex-wrap:wrap; }
    .rev-custom-date.show { display:flex; }
    .rev-filter-actions { display:flex; align-items:center; gap:8px; margin-top:16px; }
    .rev-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border-radius:10px;
        font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; border:1px solid var(--hk-border);
        background: var(--hk-bg-card); color: var(--hk-text-2); transition:.15s; text-decoration:none; }
    .rev-btn:hover { border-color: var(--hk-text-3); color: var(--hk-text-1); }
    .rev-btn-primary { background:#16A34A; border-color:#16A34A; color:#fff; }
    .rev-btn-primary:hover { background:#15803D; border-color:#15803D; color:#fff; }
    .rev-note { font-size:12px; color: var(--hk-text-3); margin-top:2px; }
    @media (max-width: 1200px) { .rev-fgrid { grid-template-columns:repeat(3,1fr); } }
    @media (max-width: 640px) { .rev-fgrid { grid-template-columns:1fr; } }

    .rev-stat-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:14px; margin-bottom:18px; }
    .rev-stat { background: var(--hk-bg-card); border:1px solid var(--hk-border); border-radius:14px; padding:16px 18px; }
    .rev-stat-label { font-size:11px; font-weight:700; color: var(--hk-text-2); text-transform:uppercase; letter-spacing:.03em; }
    .rev-stat-value { font-size:21px; font-weight:850; color: var(--hk-text-1); margin-top:6px; }
    .rev-stat--cogs .rev-stat-value { color:#b45309; }
    [data-theme="dark"] .rev-stat--cogs .rev-stat-value { color:#fbbf24; }
    .rev-stat--profit.is-pos .rev-stat-value { color:#15803d; }
    .rev-stat--profit.is-neg .rev-stat-value { color:#dc2626; }
    [data-theme="dark"] .rev-stat--profit.is-pos .rev-stat-value { color:#4ade80; }
    [data-theme="dark"] .rev-stat--profit.is-neg .rev-stat-value { color:#f87171; }

    .rev-panel-grid { display:grid; grid-template-columns:1.4fr 1fr; gap:16px; margin-bottom:18px; }
    @media (max-width: 1100px) { .rev-panel-grid { grid-template-columns:1fr; } }
    .rev-panel { background: var(--hk-bg-card); border:1px solid var(--hk-border); border-radius:14px; padding:16px 18px; }
    .rev-panel-title { font-size:14px; font-weight:800; color: var(--hk-text-1); margin-bottom:10px; }

    .rev-mini-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px; }
    @media (max-width: 900px) { .rev-mini-grid { grid-template-columns:1fr; } }
    .rev-mini-table { width:100%; font-size:13px; }
    .rev-mini-table td { padding:7px 4px; border-bottom:1px solid var(--hk-border); color: var(--hk-text-1); }
    .rev-mini-table tr:last-child td { border-bottom:0; }
    .rev-mini-rank { display:inline-flex; align-items:center; justify-content:center; width:20px; height:20px; border-radius:6px; background: var(--hk-bg-th); font-size:11px; font-weight:800; margin-right:8px; }

    .rev-table { width:100%; border-collapse:collapse; font-size:13px; }
    .rev-table thead th {
        background: var(--hk-bg-th); text-align:left; padding:11px 14px; font-weight:800;
        color: var(--hk-text-2); font-size:11px; text-transform:uppercase; letter-spacing:.03em;
        border-bottom:1.5px solid var(--hk-border); white-space:nowrap;
    }
    .rev-table thead th a { color:inherit; text-decoration:none; display:flex; align-items:center; gap:4px; }
    .rev-table tbody td { padding:12px 14px; border-bottom:1px solid var(--hk-border); vertical-align:middle; color: var(--hk-text-1); }
    .rev-table tbody tr:hover { background: var(--hk-bg-th); }
    .rev-num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .rev-profit-pos { color:#15803d; font-weight:800; }
    .rev-profit-neg { color:#dc2626; font-weight:800; }
    [data-theme="dark"] .rev-profit-pos { color:#4ade80; }
    [data-theme="dark"] .rev-profit-neg { color:#f87171; }
</style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    {{-- Thẻ bộ lọc — gộp tiêu đề + toàn bộ filter vào 1 card duy nhất, đặt trên đầu trang --}}
    <div class="card rev-filter">
        <form method="GET" action="{{ route('admin.revenue.index') }}" id="revFilterForm">
            <div class="rev-filter-head">
                <div>
                    <h1 class="rev-filter-title">Thống kê Doanh thu &ndash; Lợi nhuận</h1>
                    <p class="rev-filter-sub">
                        Chỉ tính đơn đã <strong>Hoàn thành</strong> &mdash; giá vốn lấy theo lô FIFO thực tế. Đang xem: {{ $periodLabel }}.
                    </p>
                </div>
                <a href="{{ route('admin.revenue.export', request()->query()) }}" class="rev-btn">
                    <i class="fa-solid fa-file-excel"></i> Xuất Excel
                </a>
            </div>

            <div class="rev-fgrid">
                <div class="rev-field">
                    <label>Khoảng thời gian</label>
                    <select class="form-select" name="period" id="revPeriodSel">
                        @foreach(['today'=>'Hôm nay','yesterday'=>'Hôm qua','7d'=>'7 ngày qua','30d'=>'30 ngày qua','month'=>'Tháng này','quarter'=>'Quý này','year'=>'Năm nay','custom'=>'Tùy chỉnh...'] as $val => $lbl)
                            <option value="{{ $val }}" @selected($period === $val)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rev-field">
                    <label>Phương thức thanh toán</label>
                    <select class="form-select" name="payment_method_id">
                        <option value="" @selected($paymentMethodId === '')>Tất cả</option>
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm->id }}" @selected((string) $pm->id === $paymentMethodId)>{{ $pm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rev-field">
                    <label>Danh mục</label>
                    <select class="form-select" name="category_id">
                        <option value="" @selected($categoryId === '')>Tất cả</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) $c->id === $categoryId)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rev-field">
                    <label>Thương hiệu</label>
                    <select class="form-select" name="brand_id">
                        <option value="" @selected($brandId === '')>Tất cả</option>
                        @foreach($brands as $b)
                            <option value="{{ $b->id }}" @selected((string) $b->id === $brandId)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="rev-field">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Mã đơn, SĐT, khách hàng..." class="form-control">
                </div>
            </div>

            <div class="rev-custom-date {{ $period === 'custom' ? 'show' : '' }}" id="revCustomDate">
                <div class="rev-field"><label>Từ ngày</label><input type="date" name="from" value="{{ $from }}" class="form-control"></div>
                <div class="rev-field"><label>Đến ngày</label><input type="date" name="to" value="{{ $to }}" class="form-control"></div>
            </div>

            <div class="rev-filter-actions">
                <button type="submit" class="rev-btn rev-btn-primary"><i class="fa-solid fa-rotate"></i> Áp dụng</button>
                <a href="{{ route('admin.revenue.index') }}" class="rev-btn">Đặt lại</a>
            </div>
        </form>
    </div>
    <p class="rev-note mb-4">
        Lọc theo danh mục/thương hiệu áp dụng cho các đơn có chứa sản phẩm thuộc bộ lọc (doanh thu vẫn tính theo toàn đơn).
        Chưa có dữ liệu hoàn tiền/đổi trả trong hệ thống nên Doanh thu thuần hiện chưa trừ Refund.
    </p>

    {{-- Thẻ tổng hợp --}}
    <div class="rev-stat-grid">
        <div class="rev-stat">
            <div class="rev-stat-label">Tổng doanh thu</div>
            <div class="rev-stat-value">{{ number_format($summary['gross_revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rev-stat">
            <div class="rev-stat-label">Doanh thu thuần</div>
            <div class="rev-stat-value">{{ number_format($summary['net_revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rev-stat rev-stat--cogs">
            <div class="rev-stat-label">Giá vốn (FIFO)</div>
            <div class="rev-stat-value">{{ number_format($summary['cogs'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rev-stat rev-stat--profit {{ $summary['profit'] >= 0 ? 'is-pos' : 'is-neg' }}">
            <div class="rev-stat-label">Lợi nhuận gộp</div>
            <div class="rev-stat-value">{{ number_format($summary['profit'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rev-stat rev-stat--profit {{ $summary['margin'] >= 0 ? 'is-pos' : 'is-neg' }}">
            <div class="rev-stat-label">Biên lợi nhuận gộp</div>
            <div class="rev-stat-value">{{ number_format($summary['margin'], 1) }}%</div>
        </div>
        <div class="rev-stat">
            <div class="rev-stat-label">Giá trị đơn TB (AOV)</div>
            <div class="rev-stat-value">{{ number_format($summary['aov'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rev-stat">
            <div class="rev-stat-label">Đơn đặt · Hoàn thành · Hủy</div>
            <div class="rev-stat-value" style="font-size:17px;">{{ number_format($summary['total_placed']) }} · {{ number_format($summary['orders']) }} · {{ number_format($summary['cancelled']) }}</div>
        </div>
        <div class="rev-stat">
            <div class="rev-stat-label">Tỷ lệ chuyển đổi thanh toán</div>
            <div class="rev-stat-value">{{ number_format($summary['conversion_rate'], 1) }}%</div>
        </div>
    </div>

    {{-- Biểu đồ --}}
    <div class="rev-panel-grid">
        <div class="rev-panel">
            <div class="rev-panel-title">Doanh thu &middot; Giá vốn &middot; Lợi nhuận theo ngày</div>
            <div id="revTrendChart"></div>
        </div>
        <div class="rev-panel">
            <div class="rev-panel-title">Doanh thu theo danh mục</div>
            <div id="revCategoryChart"></div>
        </div>
    </div>

    <div class="rev-panel-grid" style="grid-template-columns:1fr;">
        <div class="rev-panel">
            <div class="rev-panel-title">Top 10 sản phẩm theo doanh thu</div>
            <div id="revTopProductsChart"></div>
        </div>
    </div>

    {{-- Top khách hàng / nhân viên --}}
    <div class="rev-mini-grid">
        <div class="rev-panel">
            <div class="rev-panel-title">Top khách hàng</div>
            <table class="rev-mini-table">
                <tbody>
                    @forelse($topCustomers as $i => $c)
                        <tr>
                            <td><span class="rev-mini-rank">{{ $i + 1 }}</span>{{ $c['name'] }} <span class="text-muted">({{ $c['orders'] }} đơn)</span></td>
                            <td class="rev-num fw-bold">{{ number_format($c['revenue'], 0, ',', '.') }}đ</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Không có dữ liệu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="rev-panel">
            <div class="rev-panel-title">Top nhân viên xử lý đơn</div>
            <table class="rev-mini-table">
                <tbody>
                    @forelse($topStaff as $i => $s)
                        <tr>
                            <td><span class="rev-mini-rank">{{ $i + 1 }}</span>{{ $s['name'] }}</td>
                            <td class="rev-num">{{ $s['orders'] }} đơn &middot; {{ number_format($s['revenue'], 0, ',', '.') }}đ</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="text-center text-muted py-3">Không có dữ liệu.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Bảng chi tiết --}}
    @php
        $sortLink = function (string $key) use ($sort, $dir) {
            $newDir = ($sort === $key && $dir === 'desc') ? 'asc' : 'desc';
            return request()->fullUrlWithQuery(['sort' => $key, 'dir' => $newDir]);
        };
        $sortIcon = function (string $key) use ($sort, $dir) {
            if ($sort !== $key) return '';
            return $dir === 'asc' ? '<i class="fa-solid fa-arrow-up-short-wide"></i>' : '<i class="fa-solid fa-arrow-down-wide-short"></i>';
        };
    @endphp
    <div class="card edit-card shadow-sm">
        <div class="card-header"><span class="fw-bold" style="font-size:14px;">Chi tiết theo đơn hàng đã hoàn thành</span></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="rev-table">
                    <thead>
                        <tr>
                            <th><a href="{{ $sortLink('date') }}">Ngày ghi nhận {!! $sortIcon('date') !!}</a></th>
                            <th>Mã đơn</th>
                            <th><a href="{{ $sortLink('customer') }}">Khách hàng {!! $sortIcon('customer') !!}</a></th>
                            <th class="rev-num"><a href="{{ $sortLink('revenue') }}" style="justify-content:flex-end;">Doanh thu thuần {!! $sortIcon('revenue') !!}</a></th>
                            <th class="rev-num"><a href="{{ $sortLink('cogs') }}" style="justify-content:flex-end;">Giá vốn (FIFO) {!! $sortIcon('cogs') !!}</a></th>
                            <th class="rev-num"><a href="{{ $sortLink('profit') }}" style="justify-content:flex-end;">Lợi nhuận {!! $sortIcon('profit') !!}</a></th>
                            <th class="rev-num">Biên %</th>
                            <th>Thanh toán</th>
                            <th>Nhân viên xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>{{ $row['date']?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="fw-bold">
                                    <a href="{{ route('admin.orders.detail', $row['order']->id) }}">
                                        {{ $row['order']->order_code ?? ('#' . $row['order']->id) }}
                                    </a>
                                </td>
                                <td>{{ $row['order']->user?->username ?? 'Khách vãng lai' }}</td>
                                <td class="rev-num">{{ number_format($row['revenue'], 0, ',', '.') }}đ</td>
                                <td class="rev-num">{{ number_format($row['cogs'], 0, ',', '.') }}đ</td>
                                <td class="rev-num {{ $row['profit'] >= 0 ? 'rev-profit-pos' : 'rev-profit-neg' }}">
                                    {{ number_format($row['profit'], 0, ',', '.') }}đ
                                </td>
                                <td class="rev-num">{{ number_format($row['margin'], 1) }}%</td>
                                <td>{{ $row['order']->paymentMethod?->name ?? '—' }}</td>
                                <td>{{ $row['staff'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Không có đơn hàng hoàn thành nào trong khoảng thời gian này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    // Đổi "Khoảng thời gian" sang "Tùy chỉnh..." thì hiện ô Từ ngày/Đến ngày, chờ bấm "Áp dụng"
    // (không tự submit vì còn phải nhập ngày). Các preset khác tự áp dụng ngay khi đổi lựa chọn,
    // giống hành vi bộ lọc ở trang Dashboard.
    var periodSel = document.getElementById('revPeriodSel');
    var customDate = document.getElementById('revCustomDate');
    var form = document.getElementById('revFilterForm');

    if (periodSel) {
        periodSel.addEventListener('change', function () {
            customDate.classList.toggle('show', periodSel.value === 'custom');
            if (periodSel.value !== 'custom') form.submit();
        });
    }

    if (form) {
        form.querySelectorAll('select:not(#revPeriodSel)').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });
    }

    if (typeof ApexCharts === 'undefined') return;

    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var axisColor = isDark ? '#94A3B8' : '#64748B';
    var gridColor = isDark ? '#22324D' : '#EEF3F1';

    var trendLabels = @json($daily->pluck('date')->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d/m'))->values());
    var trendRevenue = @json($daily->pluck('revenue')->values());
    var trendCogs = @json($daily->pluck('cogs')->values());
    var trendProfit = @json($daily->pluck('profit')->values());

    var trendChart = new ApexCharts(document.getElementById('revTrendChart'), {
        chart: { type: 'line', height: 300, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
        series: [
            { name: 'Doanh thu', type: 'column', data: trendRevenue },
            { name: 'Giá vốn', type: 'column', data: trendCogs },
            { name: 'Lợi nhuận', type: 'line', data: trendProfit }
        ],
        colors: ['#16A34A', '#F59E0B', '#2563EB'],
        stroke: { width: [0, 0, 3], curve: 'smooth' },
        plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: axisColor } },
        xaxis: { categories: trendLabels, labels: { style: { colors: axisColor, fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: axisColor, fontSize: '11px' }, formatter: function (v) { return Math.round(v / 1000) + 'k'; } } },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (v) { return Math.round(v).toLocaleString('vi-VN') + 'đ'; } } }
    });
    trendChart.render();

    var catNames = @json($byCategory->pluck('name')->values());
    var catRevenue = @json($byCategory->pluck('revenue')->values());
    var catColors = ['#16A34A', '#2563EB', '#F59E0B', '#DB2777', '#7C3AED', '#0891B2', '#DC2626', '#65A30D'];

    var categoryChart = new ApexCharts(document.getElementById('revCategoryChart'), {
        chart: { type: 'donut', height: 300, fontFamily: 'inherit' },
        series: catRevenue,
        labels: catNames,
        colors: catColors,
        stroke: { width: 0 },
        legend: { position: 'bottom', labels: { colors: axisColor }, fontSize: '12px' },
        dataLabels: { enabled: false },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (v) { return Math.round(v).toLocaleString('vi-VN') + 'đ'; } } }
    });
    if (catNames.length) categoryChart.render();

    var topNames = @json($topProducts->pluck('product_name')->values());
    var topRevenue = @json($topProducts->pluck('revenue')->values());

    var topProductsChart = new ApexCharts(document.getElementById('revTopProductsChart'), {
        chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'inherit' },
        series: [{ name: 'Doanh thu', data: topRevenue }],
        colors: ['#16A34A'],
        plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '55%' } },
        dataLabels: { enabled: false },
        xaxis: { categories: topNames, labels: { style: { colors: axisColor, fontSize: '11px' }, formatter: function (v) { return Math.round(v / 1000) + 'k'; } } },
        yaxis: { labels: { style: { colors: axisColor, fontSize: '12px' } } },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (v) { return Math.round(v).toLocaleString('vi-VN') + 'đ'; } } }
    });
    if (topNames.length) topProductsChart.render();
})();
</script>
@endpush
