@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $statusTotal = collect($statusRatio)->sum('value');
@endphp

@push('styles')
<style>
    .dash-page { padding: 20px 24px 40px; }

    /* ── Header ── */
    .dash-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .dash-title { font-size:24px; font-weight:800; letter-spacing:-.01em; margin:0; color:var(--hk-text-1); }
    .dash-subtitle { font-size:14px; color:var(--hk-text-2); margin:4px 0 0; }

    /* ── Buttons ── */
    .dash-btn { display:inline-flex; align-items:center; gap:8px; height:40px; padding:0 16px; border-radius:10px;
        font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; border:1px solid var(--hk-border);
        background:var(--hk-bg-card); color:var(--hk-text-2); transition:.15s; text-decoration:none; }
    .dash-btn:hover { border-color:var(--hk-text-3); color:var(--hk-text-1); }
    .dash-btn-ghost { border-color:#16A34A; color:#16A34A; background:transparent; }
    .dash-btn-ghost:hover { background:rgba(22,163,74,.08); color:#15803D; }
    [data-theme="dark"] .dash-btn-ghost { border-color:#4ADE80; color:#4ADE80; }
    [data-theme="dark"] .dash-btn-ghost:hover { background:rgba(74,222,128,.12); color:#86EFAC; }
    .dash-btn-primary { background:#16A34A; border-color:#16A34A; color:#fff; }
    .dash-btn-primary:hover { background:#15803D; border-color:#15803D; color:#fff; }

    /* ── Filter card ── */
    .dash-filter { padding:22px; margin-bottom:20px; }
    .dash-filter-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; margin-bottom:18px; flex-wrap:wrap; }
    .dash-sec-title { font-size:18px; font-weight:700; color:var(--hk-text-1); margin:0; }
    .dash-fgrid { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
    .dash-field { display:flex; flex-direction:column; gap:6px; }
    .dash-field label { font-size:11px; font-weight:700; color:var(--hk-text-2); text-transform:uppercase; letter-spacing:.06em; }
    .dash-field .form-select, .dash-field .form-control { height:40px; border-radius:10px; font-size:13px; }
    .dash-field-actions { display:flex; align-items:flex-end; gap:8px; }
    .dash-field-actions .dash-btn { flex:1; justify-content:center; }
    .dash-custom-date { display:none; gap:14px; align-items:flex-end; margin-top:16px; padding-top:16px; border-top:1px solid var(--hk-border); flex-wrap:wrap; }
    .dash-custom-date.show { display:flex; }

    /* ── Primary KPI (6 thẻ, 3/hàng) ── */
    .dash-kpi { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:14px; }
    .dash-kpi-card { padding:14px 16px; display:block; text-decoration:none; color:inherit; cursor:pointer;
        transition: box-shadow .15s ease, transform .15s ease; }
    .dash-kpi-card:hover { box-shadow:0 10px 24px rgba(15,23,42,.1); transform:translateY(-2px); color:inherit; }
    [data-theme="dark"] .dash-kpi-card:hover { box-shadow:0 10px 24px rgba(0,0,0,.35); }
    .dash-kpi-card.is-warn { border-left:3px solid #D97706 !important; }
    .dash-kpi-card.is-danger { border-left:3px solid #DC2626 !important; }
    [data-theme="dark"] .dash-kpi-card.is-warn { border-left-color:#FBBF24 !important; }
    [data-theme="dark"] .dash-kpi-card.is-danger { border-left-color:#F87171 !important; }
    .dash-kpi-main { display:flex; align-items:center; gap:11px; }
    .dash-kpi-body { flex:1; min-width:0; }
    .dash-chip { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:15px; flex:none; }
    .dash-chip.g { background:#ECFDF5; color:#16A34A; }
    .dash-chip.b { background:#EFF6FF; color:#2563EB; }
    .dash-chip.a { background:#FEF3C7; color:#B45309; }
    .dash-chip.p { background:#F5F3FF; color:#7C3AED; }
    .dash-chip.r { background:#FEF2F2; color:#DC2626; }
    [data-theme="dark"] .dash-chip.g { background:rgba(74,222,128,.12); color:#4ADE80; }
    [data-theme="dark"] .dash-chip.b { background:rgba(96,165,250,.14); color:#60A5FA; }
    [data-theme="dark"] .dash-chip.a { background:rgba(251,191,36,.14); color:#FBBF24; }
    [data-theme="dark"] .dash-chip.p { background:rgba(167,139,250,.16); color:#A78BFA; }
    [data-theme="dark"] .dash-chip.r { background:rgba(248,113,113,.12); color:#F87171; }
    .dash-delta { display:flex; flex-direction:column; align-items:flex-end; gap:2px; flex:none; }
    .dash-pct { font-size:11.5px; font-weight:600; padding:2px 8px; border-radius:999px; }
    .dash-delta small { font-size:10px; color:var(--hk-text-3); font-style:italic; }
    .dash-pct.up { color:#16A34A; background:#ECFDF5; }
    .dash-pct.down { color:#DC2626; background:#FEF2F2; }
    .dash-pct.info { color:#2563EB; background:#EFF6FF; }
    [data-theme="dark"] .dash-pct.up { color:#4ADE80; background:rgba(74,222,128,.12); }
    [data-theme="dark"] .dash-pct.down { color:#F87171; background:rgba(248,113,113,.12); }
    [data-theme="dark"] .dash-pct.info { color:#60A5FA; background:rgba(96,165,250,.14); }
    .dash-kpi-label { font-size:11px; font-weight:600; color:var(--hk-text-2); text-transform:uppercase; letter-spacing:.04em; margin:0 0 3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .dash-kpi-value { font-size:21px; font-weight:700; color:var(--hk-text-1); margin:0; letter-spacing:-.01em; line-height:1.1; font-variant-numeric:tabular-nums; }
    .dash-kpi-value.warnv { color:#B45309; }
    .dash-kpi-value.dangerv { color:#DC2626; }
    [data-theme="dark"] .dash-kpi-value.warnv { color:#FBBF24; }
    [data-theme="dark"] .dash-kpi-value.dangerv { color:#F87171; }
    .dash-kpi-sub { font-size:11.5px; color:var(--hk-text-3); margin:9px 0 0; }
    .dash-kpi-sub.warns { color:#B45309; font-weight:500; }
    .dash-kpi-sub.dangers { color:#DC2626; font-weight:500; }
    [data-theme="dark"] .dash-kpi-sub.warns { color:#FBBF24; }
    [data-theme="dark"] .dash-kpi-sub.dangers { color:#F87171; }

    /* ── Filter: hàng nút + bộ lọc nâng cao ── */
    .dash-filter-actions { display:flex; align-items:center; gap:8px; margin-top:14px; }
    .dash-adv-toggle { margin-left:auto; }
    .dash-adv-toggle .fa-chevron-down { transition:transform .2s; font-size:11px; }
    .dash-adv-toggle.open .fa-chevron-down { transform:rotate(180deg); }
    .dash-advanced { display:none; grid-template-columns:repeat(3,1fr); gap:14px; margin-top:16px; padding-top:16px; border-top:1px solid var(--hk-border); }
    .dash-advanced.show { display:grid; }

    /* ── Thống kê phụ (thu gọn) ── */
    .dash-extra { margin-bottom:14px; }
    .dash-extra-head { display:flex; align-items:center; justify-content:space-between; padding:13px 18px; cursor:pointer; user-select:none; }
    .dash-extra-head h3 { font-size:13px; font-weight:600; color:var(--hk-text-1); margin:0; display:flex; align-items:center; gap:9px; }
    .dash-extra-head h3 .fa-layer-group { color:var(--hk-text-3); }
    .dash-extra-head .caret { transition:transform .2s; color:var(--hk-text-3); font-size:12px; }
    .dash-extra.open .dash-extra-head .caret { transform:rotate(180deg); }
    .dash-extra-body { display:none; grid-template-columns:repeat(3,1fr); gap:0 26px; padding:0 18px 14px; }
    .dash-extra.open .dash-extra-body { display:grid; }
    .dash-extra-item { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:9px 0; border-top:1px solid var(--hk-border); font-size:13px; }
    .dash-extra-item .k { color:var(--hk-text-2); }
    .dash-extra-item .v { font-weight:600; color:var(--hk-text-1); font-variant-numeric:tabular-nums; }
    .dash-extra-item .v.r { color:#DC2626; }
    [data-theme="dark"] .dash-extra-item .v.r { color:#F87171; }

    /* ── Charts ── */
    .dash-charts { display:grid; grid-template-columns:2fr 1fr; gap:18px; margin-bottom:18px; }
    .dash-panel { padding:20px; }
    .dash-panel-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; gap:12px; flex-wrap:wrap; }
    .dash-legend { display:flex; gap:14px; font-size:12px; color:var(--hk-text-2); font-weight:500; }
    .dash-legend span { display:inline-flex; align-items:center; gap:6px; }
    .dash-sw { width:11px; height:11px; border-radius:3px; display:inline-block; }
    .dash-sw.cur { background:#16A34A; } .dash-sw.prev { background:var(--hk-text-3); }
    .dash-donut-legend { display:flex; flex-direction:column; gap:10px; margin-top:8px; }
    .dash-dl-row { display:flex; align-items:center; justify-content:space-between; font-size:13px; }
    .dash-dl-row .l { display:flex; align-items:center; gap:8px; color:var(--hk-text-2); }
    .dash-dl-row .d { width:9px; height:9px; border-radius:50%; }
    .dash-dl-row b { color:var(--hk-text-1); font-weight:700; font-variant-numeric:tabular-nums; }

    /* ── Tables ── */
    .dash-tbl-card { padding:0; overflow:hidden; }
    .dash-tbl-head { padding:16px 22px; border-bottom:1px solid var(--hk-border); display:flex; justify-content:space-between; align-items:center; background:var(--hk-bg-th); }
    .dash-link { color:#16A34A; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; background:none; border:none; cursor:pointer; font-family:inherit; text-decoration:none; }
    .dash-link:hover { text-decoration:underline; color:#15803D; }
    [data-theme="dark"] .dash-link { color:#4ADE80; }
    .dash-tbl-scroll { overflow-x:auto; }
    .dash-table { width:100%; border-collapse:collapse; font-size:13.5px; }
    .dash-table thead th { text-align:left; font-size:11px; font-weight:700; color:var(--hk-text-2); text-transform:uppercase; letter-spacing:.05em; padding:12px 20px; background:var(--hk-bg-th); border-bottom:1px solid var(--hk-border); white-space:nowrap; }
    .dash-table tbody td { padding:13px 20px; border-bottom:1px solid var(--hk-border); color:var(--hk-text-1); white-space:nowrap; vertical-align:middle; }
    .dash-table tbody tr:last-child td { border-bottom:none; }
    .dash-table tbody tr:hover td { background:var(--hk-bg-th); }
    .dash-oid { font-weight:700; color:#16A34A; }
    [data-theme="dark"] .dash-oid { color:#4ADE80; }
    .dash-money { font-weight:700; font-variant-numeric:tabular-nums; }
    .dash-muted { color:var(--hk-text-2); font-variant-numeric:tabular-nums; }
    .dash-pill { display:inline-block; padding:4px 11px; border-radius:999px; font-size:12px; font-weight:600; white-space:nowrap; }
    .dash-pill.pending { background:#FEF3C7; color:#B45309; }
    .dash-pill.ship { background:#EFF6FF; color:#2563EB; }
    .dash-pill.done { background:#ECFDF5; color:#16A34A; }
    .dash-pill.cancel { background:#FEF2F2; color:#DC2626; }
    .dash-pill.neutral { background:var(--hk-bg-th); color:var(--hk-text-2); border:1px solid var(--hk-border); }
    [data-theme="dark"] .dash-pill.pending { background:rgba(251,191,36,.14); color:#FBBF24; }
    [data-theme="dark"] .dash-pill.ship { background:rgba(96,165,250,.14); color:#60A5FA; }
    [data-theme="dark"] .dash-pill.done { background:rgba(74,222,128,.12); color:#4ADE80; }
    [data-theme="dark"] .dash-pill.cancel { background:rgba(248,113,113,.12); color:#F87171; }
    .dash-prodcell { display:flex; align-items:center; gap:12px; }
    .dash-thumb { width:38px; height:38px; border-radius:9px; display:flex; align-items:center; justify-content:center; flex:none; font-size:15px; }
    .dash-thumb.green { background:#ECFDF5; color:#16A34A; }
    .dash-thumb.blue { background:#EFF6FF; color:#2563EB; }
    .dash-thumb.purple { background:#F5F3FF; color:#7C3AED; }
    .dash-thumb.amber { background:#FEF3C7; color:#B45309; }
    .dash-thumb.red { background:#FEF2F2; color:#DC2626; }
    [data-theme="dark"] .dash-thumb.green { background:rgba(74,222,128,.12); color:#4ADE80; }
    [data-theme="dark"] .dash-thumb.blue { background:rgba(96,165,250,.14); color:#60A5FA; }
    [data-theme="dark"] .dash-thumb.purple { background:rgba(167,139,250,.16); color:#A78BFA; }
    [data-theme="dark"] .dash-thumb.amber { background:rgba(251,191,36,.14); color:#FBBF24; }
    [data-theme="dark"] .dash-thumb.red { background:rgba(248,113,113,.12); color:#F87171; }
    .dash-prodcell .pname { font-weight:600; color:var(--hk-text-1); }
    .dash-prodcell .pcat { display:block; color:var(--hk-text-3); font-size:11.5px; font-weight:400; }
    .dash-rank { width:22px; height:22px; border-radius:7px; background:#ECFDF5; color:#16A34A; font-size:12px; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex:none; }
    [data-theme="dark"] .dash-rank { background:rgba(74,222,128,.12); color:#4ADE80; }
    .dash-stock-low { color:#DC2626; font-weight:700; font-variant-numeric:tabular-nums; }
    [data-theme="dark"] .dash-stock-low { color:#F87171; }

    /* ── Bottom grid ── */
    .dash-bottom { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }

    /* ── Reviews (cuối trang, 2 cột) ── */
    .dash-rev-list { display:grid; grid-template-columns:1fr 1fr; column-gap:24px; }
    .dash-rev { padding:14px 20px; border-bottom:1px solid var(--hk-border); display:flex; flex-direction:column; gap:5px; }
    .dash-rev-list .dash-rev:nth-last-child(-n+2) { border-bottom:none; }
    .dash-rev-top { display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .dash-rev-who { font-size:13px; font-weight:600; color:var(--hk-text-1); }
    .dash-rev-who span { font-weight:400; color:var(--hk-text-3); }
    .dash-stars { color:#F59E0B; font-size:12px; letter-spacing:1px; white-space:nowrap; }
    .dash-stars .off { color:var(--hk-border); }
    .dash-rev-txt { font-size:12.5px; color:var(--hk-text-2); line-height:1.5; }
    .dash-rev-time { font-size:11px; color:var(--hk-text-3); }

    @media (min-width:1600px) {
        .dash-kpi { grid-template-columns:repeat(6,1fr); }
    }
    @media (max-width:1200px) {
        .dash-kpi { grid-template-columns:repeat(2,1fr); }
        .dash-charts, .dash-bottom { grid-template-columns:1fr; }
        .dash-fgrid, .dash-advanced, .dash-extra-body { grid-template-columns:repeat(2,1fr); }
    }
    @media (max-width:640px) {
        .dash-kpi, .dash-fgrid, .dash-advanced, .dash-extra-body, .dash-rev-list { grid-template-columns:1fr; }
        .dash-page { padding:16px; }
    }
</style>
@endpush

@section('content')
<div class="dash-page">

    {{-- FILTER --}}
    <div class="card dash-filter">
        <form method="GET" action="{{ route('admin.dashboard') }}">
        <div class="dash-filter-head">
            <div>
                <h2 class="dash-sec-title">Xin chào, <span class="fw-bold">{{ auth()->user()->username }}</span></h2>
                <p class="dash-subtitle" style="margin-top:2px;">Đang xem: {{ $periodLabel }}</p>
            </div>
            <a href="#" class="dash-btn dash-btn-ghost"><i class="fa-solid fa-download"></i> Xuất báo cáo</a>
        </div>
        <div class="dash-fgrid">
            <div class="dash-field"><label>Chỉ số</label>
                <select class="form-select" name="metric">
                    @foreach($metricLabels as $value => $label)
                        <option value="{{ $value }}" @selected($metric === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div class="dash-field"><label>Thời gian</label>
                <select class="form-select" id="dashTimeSel" name="range">
                    @foreach($rangeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($range === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div class="dash-field"><label>Hiển thị theo</label>
                <select class="form-select" name="group_by">
                    @foreach($groupByLabels as $value => $label)
                        <option value="{{ $value }}" @selected($groupBy === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div class="dash-field"><label>Loại biểu đồ</label>
                <select class="form-select" name="chart_type">
                    @foreach($chartTypeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($chartType === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
        </div>

        <div class="dash-advanced @if($statusFilter || $paymentFilter || $methodFilter) show @endif" id="dashAdvanced">
            <div class="dash-field"><label>Trạng thái đơn</label>
                <select class="form-select" name="status">
                    <option value="" @selected($statusFilter === '')>Tất cả</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($statusFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div class="dash-field"><label>Thanh toán</label>
                <select class="form-select" name="payment_status">
                    <option value="" @selected($paymentFilter === '')>Tất cả</option>
                    @foreach($paymentStatusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($paymentFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select></div>
            <div class="dash-field"><label>Phương thức</label>
                <select class="form-select" name="payment_method_id">
                    <option value="" @selected($methodFilter === '')>Tất cả</option>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method->id }}" @selected($methodFilter === (string) $method->id)>{{ $method->name }}</option>
                    @endforeach
                </select></div>
        </div>

        <div class="dash-custom-date @if($range === 'custom') show @endif" id="dashCustomDate">
            <div class="dash-field"><label>Từ ngày</label><input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}"></div>
            <div class="dash-field"><label>Đến ngày</label><input type="date" name="date_to" class="form-control" value="{{ $dateTo }}"></div>
        </div>

        <div class="dash-filter-actions">
            <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-rotate"></i> Áp dụng</button>
            <a href="{{ route('admin.dashboard') }}" class="dash-btn">Đặt lại</a>
            <button type="button" class="dash-btn dash-adv-toggle @if($statusFilter || $paymentFilter || $methodFilter) open @endif" id="dashAdvToggle" aria-expanded="{{ ($statusFilter || $paymentFilter || $methodFilter) ? 'true' : 'false' }}">
                <i class="fa-solid fa-sliders"></i> Bộ lọc nâng cao <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>
        </form>
    </div>

    {{-- PRIMARY KPI — 6 thẻ quan trọng nhất --}}
    <div class="dash-kpi">
        <a href="{{ route('admin.revenue.index') }}" class="card dash-kpi-card">
            <div class="dash-kpi-main">
                <div class="dash-chip g"><i class="fa-solid fa-money-bill-wave"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Doanh thu thực thu</p>
                    <p class="dash-kpi-value">{{ number_format($stats['revenue'], 0, ',', '.') }} ₫</p>
                </div>
                <div class="dash-delta"><span class="dash-pct {{ $stats['revenueDelta'] >= 0 ? 'up' : 'down' }}">{{ $stats['revenueDelta'] >= 0 ? '+' : '' }}{{ $stats['revenueDelta'] }}%</span><small>vs kỳ trước</small></div>
            </div>
            <p class="dash-kpi-sub">Chỉ tính đơn đã hoàn thành, không gồm đơn đã hủy.</p>
        </a>
        <a href="{{ route('admin.orders.list') }}" class="card dash-kpi-card">
            <div class="dash-kpi-main">
                <div class="dash-chip b"><i class="fa-solid fa-cart-shopping"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Tổng đơn hàng</p>
                    <p class="dash-kpi-value">{{ number_format($stats['orders'], 0, ',', '.') }}</p>
                </div>
                <div class="dash-delta"><span class="dash-pct {{ $stats['ordersDelta'] >= 0 ? 'info' : 'down' }}">{{ $stats['ordersDelta'] >= 0 ? '+' : '' }}{{ $stats['ordersDelta'] }}%</span><small>vs kỳ trước</small></div>
            </div>
            <p class="dash-kpi-sub">Tất cả đơn hàng trong kỳ đang chọn</p>
        </a>
        <a href="{{ route('admin.orders.list', ['status' => 'pending']) }}" class="card dash-kpi-card is-warn">
            <div class="dash-kpi-main">
                <div class="dash-chip a"><i class="fa-solid fa-clock"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Đơn chờ xử lý</p>
                    <p class="dash-kpi-value warnv">{{ number_format($stats['pending'], 0, ',', '.') }}</p>
                </div>
                <div class="dash-delta"><span class="dash-pct {{ $stats['pendingDelta'] >= 0 ? 'up' : 'down' }}">{{ $stats['pendingDelta'] >= 0 ? '+' : '' }}{{ $stats['pendingDelta'] }}%</span><small>vs kỳ trước</small></div>
            </div>
            <p class="dash-kpi-sub warns">Cần xử lý ngay</p>
        </a>
        <a href="{{ route('admin.orders.list', ['status' => 'shipping']) }}" class="card dash-kpi-card">
            <div class="dash-kpi-main">
                <div class="dash-chip p"><i class="fa-solid fa-truck"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Đơn đang giao</p>
                    <p class="dash-kpi-value">{{ number_format($stats['shipping'], 0, ',', '.') }}</p>
                </div>
            </div>
            <p class="dash-kpi-sub">Đơn đang được vận chuyển</p>
        </a>
        <a href="{{ route('admin.goods-receipts.list', ['stock_status' => 'low_stock']) }}" class="card dash-kpi-card is-danger">
            <div class="dash-kpi-main">
                <div class="dash-chip r"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Sản phẩm sắp hết hàng</p>
                    <p class="dash-kpi-value dangerv">{{ number_format($stats['lowStock'], 0, ',', '.') }}</p>
                </div>
            </div>
            <p class="dash-kpi-sub dangers">Tồn kho thấp, cần nhập thêm</p>
        </a>
        <a href="{{ route('admin.customers.list') }}" class="card dash-kpi-card">
            <div class="dash-kpi-main">
                <div class="dash-chip g"><i class="fa-solid fa-user-plus"></i></div>
                <div class="dash-kpi-body">
                    <p class="dash-kpi-label">Khách hàng mới</p>
                    <p class="dash-kpi-value">{{ number_format($stats['newCustomers'], 0, ',', '.') }}</p>
                </div>
            </div>
            <p class="dash-kpi-sub">Khách mới trong kỳ đang chọn</p>
        </a>
    </div>

    {{-- Thống kê phụ — thu gọn, mặc định đóng --}}
    <div class="card dash-extra" id="dashExtra">
        <div class="dash-extra-head" id="dashExtraToggle" role="button" tabindex="0" aria-expanded="false">
            <h3><i class="fa-solid fa-layer-group"></i> Thống kê phụ</h3>
            <i class="fa-solid fa-chevron-down caret"></i>
        </div>
        <div class="dash-extra-body">
            <div class="dash-extra-item"><span class="k">Tổng sản phẩm</span><span class="v">{{ number_format($stats['totalProducts'], 0, ',', '.') }}</span></div>
            <div class="dash-extra-item"><span class="k">Sản phẩm đang bán</span><span class="v">{{ number_format($stats['sellingProducts'], 0, ',', '.') }}</span></div>
            <div class="dash-extra-item"><span class="k">Sản phẩm đang ẩn</span><span class="v">{{ number_format($stats['hiddenProducts'], 0, ',', '.') }}</span></div>
            <div class="dash-extra-item"><span class="k">Tổng khách hàng</span><span class="v">{{ number_format($stats['totalCustomers'], 0, ',', '.') }}</span></div>
            <div class="dash-extra-item"><span class="k">Đơn đã hoàn thành</span><span class="v">{{ number_format($stats['completed'], 0, ',', '.') }}</span></div>
            <div class="dash-extra-item"><span class="k">Đơn đã hủy</span><span class="v r">{{ number_format($stats['cancelled'], 0, ',', '.') }}</span></div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="dash-charts">
        <div class="card dash-panel">
            <div class="dash-panel-head">
                <h2 class="dash-sec-title">{{ $revenueChart['title'] }}</h2>
                <div class="dash-legend"><span><i class="dash-sw cur"></i> Kỳ này</span><span><i class="dash-sw prev"></i> Kỳ trước</span></div>
            </div>
            <div id="dashRevenueChart"></div>
        </div>
        <div class="card dash-panel">
            <div class="dash-panel-head"><h2 class="dash-sec-title">Tỷ lệ trạng thái đơn</h2></div>
            <div id="dashStatusChart"></div>
            <div class="dash-donut-legend">
                @foreach($statusRatio as $s)
                    <div class="dash-dl-row">
                        <span class="l"><i class="d" style="background:{{ $s['color'] }}"></i> {{ $s['label'] }}</span>
                        <b>{{ number_format($s['value']) }} · {{ $statusTotal ? round($s['value'] / $statusTotal * 100) : 0 }}%</b>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- LATEST ORDERS --}}
    <div class="card dash-tbl-card" style="margin-bottom:18px;">
        <div class="dash-tbl-head"><h2 class="dash-sec-title">Đơn hàng mới nhất</h2><a href="{{ route('admin.orders.list') }}" class="dash-link">Xem tất cả</a></div>
        <div class="dash-tbl-scroll"><table class="dash-table">
            <thead><tr><th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thanh toán</th><th>Ngày đặt</th></tr></thead>
            <tbody>
                @foreach($recentOrders as $o)
                    <tr>
                        <td class="dash-oid">{{ $o['code'] }}</td>
                        <td>{{ $o['name'] }}</td>
                        <td class="dash-muted">{{ $o['phone'] }}</td>
                        <td class="dash-money">{{ number_format($o['total'], 0, ',', '.') }} ₫</td>
                        <td><span class="dash-pill {{ $o['status'] }}">{{ $o['status_label'] }}</span></td>
                        <td><span class="dash-pill {{ $o['payment_class'] }}">{{ $o['payment'] }}</span></td>
                        <td class="dash-muted">{{ $o['date'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table></div>
    </div>

    {{-- BOTTOM GRID --}}
    <div class="dash-bottom">
        {{-- Top selling --}}
        <div class="card dash-tbl-card">
            <div class="dash-tbl-head"><h2 class="dash-sec-title">Top sản phẩm bán chạy</h2></div>
            <div class="dash-tbl-scroll"><table class="dash-table">
                <thead><tr><th>Sản phẩm</th><th style="text-align:right">Đã bán</th><th style="text-align:right">Doanh thu</th></tr></thead>
                <tbody>
                    @foreach($topProducts as $i => $p)
                        <tr>
                            <td><div class="dash-prodcell"><span class="dash-rank">{{ $i + 1 }}</span>
                                <div class="dash-thumb {{ $p['tone'] }}"><i class="fa-solid fa-shirt"></i></div>
                                <span class="pname">{{ $p['name'] }}<span class="pcat">{{ $p['cat'] }}</span></span></div></td>
                            <td style="text-align:right">{{ number_format($p['sold'], 0, ',', '.') }}</td>
                            <td style="text-align:right" class="dash-money">{{ $p['revenue'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>

        {{-- Low stock --}}
        <div class="card dash-tbl-card">
            <div class="dash-tbl-head"><h2 class="dash-sec-title">Sản phẩm sắp hết hàng</h2><a href="{{ route('admin.goods-receipts.list') }}" class="dash-link">Nhập kho</a></div>
            <div class="dash-tbl-scroll"><table class="dash-table">
                <thead><tr><th>Sản phẩm</th><th>Phân loại</th><th style="text-align:center">Tồn</th></tr></thead>
                <tbody>
                    @foreach($lowStockProducts as $p)
                        <tr>
                            <td><div class="dash-prodcell"><div class="dash-thumb red"><i class="fa-solid fa-shirt"></i></div>
                                <span class="pname">{{ $p['name'] }}</span></div></td>
                            <td class="dash-muted">{{ $p['variant'] }}</td>
                            <td style="text-align:center" class="dash-stock-low">{{ $p['stock'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>

    </div>

    {{-- Đánh giá mới nhất — đưa xuống cuối, gọn 2 cột --}}
    <div class="card dash-tbl-card">
        <div class="dash-tbl-head"><h2 class="dash-sec-title">Đánh giá mới nhất</h2><a href="{{ route('admin.reviews.list') }}" class="dash-link">Xem tất cả</a></div>
        <div class="dash-rev-list">
            @foreach($latestReviews as $r)
                <div class="dash-rev">
                    <div class="dash-rev-top">
                        <span class="dash-rev-who">{{ $r['user'] }} <span>· {{ $r['product'] }}</span></span>
                        <span class="dash-stars">@for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star {{ $i <= $r['stars'] ? '' : 'off' }}"></i>@endfor</span>
                    </div>
                    <p class="dash-rev-txt" style="margin:0;">{{ $r['text'] }}</p>
                    <span class="dash-rev-time">{{ $r['time'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Toggle ô ngày tùy chỉnh ──
    var timeSel = document.getElementById('dashTimeSel');
    var customDate = document.getElementById('dashCustomDate');
    if (timeSel) {
        timeSel.addEventListener('change', function () {
            customDate.classList.toggle('show', timeSel.value === 'custom');
        });
    }

    // ── Tự động áp dụng bộ lọc ngay khi đổi lựa chọn (không cần bấm "Áp dụng"),
    //    trừ khi chọn "Tùy chỉnh…" — lúc đó chờ người dùng nhập ngày rồi tự bấm Áp dụng ──
    var filterForm = document.querySelector('.dash-filter form');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () {
                if (sel === timeSel && sel.value === 'custom') return;
                filterForm.submit();
            });
        });
    }

    // ── Toggle bộ lọc nâng cao ──
    var advToggle = document.getElementById('dashAdvToggle');
    var advPanel = document.getElementById('dashAdvanced');
    if (advToggle && advPanel) {
        advToggle.addEventListener('click', function () {
            var open = advPanel.classList.toggle('show');
            advToggle.classList.toggle('open', open);
            advToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // ── Toggle thống kê phụ ──
    var extra = document.getElementById('dashExtra');
    var extraToggle = document.getElementById('dashExtraToggle');
    if (extra && extraToggle) {
        var toggleExtra = function () {
            var open = extra.classList.toggle('open');
            extraToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        extraToggle.addEventListener('click', toggleExtra);
        extraToggle.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleExtra(); }
        });
    }

    if (typeof ApexCharts === 'undefined') return;

    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var axisColor = isDark ? '#94A3B8' : '#64748B';
    var gridColor = isDark ? '#22324D' : '#EEF3F1';

    // ── Biểu đồ chỉ số đã chọn (đường/cột/vùng, kỳ này vs kỳ trước) ──
    var isMoney   = @json($revenueChart['isMoney']);
    var chartType = @json($chartType); // 'line' | 'bar' | 'area'
    var isArea    = chartType === 'area';
    var isBar     = chartType === 'bar';

    var revenueChart = new ApexCharts(document.getElementById('dashRevenueChart'), {
        chart: { type: isBar ? 'bar' : (isArea ? 'area' : 'line'), height: 300, toolbar: { show: false }, fontFamily: 'inherit', zoom: { enabled: false } },
        series: [
            { name: 'Kỳ này',  data: @json($revenueChart['current']) },
            { name: 'Kỳ trước', data: @json($revenueChart['previous']) }
        ],
        colors: ['#16A34A', '#94A3B8'],
        stroke: isBar ? { width: 0 } : { curve: 'smooth', width: [3, 2], dashArray: [0, 5] },
        fill: (isBar || !isArea)
            ? { type: 'solid', opacity: 1 }
            : { type: ['gradient', 'solid'], opacity: [0.18, 0], gradient: { opacityFrom: 0.28, opacityTo: 0.02 } },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: {
            categories: @json($revenueChart['labels']),
            labels: { style: { colors: axisColor, fontSize: '12px' } },
            axisBorder: { show: false }, axisTicks: { show: false }
        },
        yaxis: { labels: { style: { colors: axisColor, fontSize: '12px' }, formatter: function (v) { return isMoney ? (Math.round(v * 10) / 10) + ' tr' : Math.round(v); } } },
        grid: { borderColor: gridColor, strokeDashArray: 4, padding: { left: 8, right: 8 } },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (v) { return isMoney ? (Math.round(v * 10) / 10) + ' triệu ₫' : (Math.round(v) + ''); } } },
        markers: { size: 0, hover: { size: 5 } }
    });
    revenueChart.render();

    // ── Donut trạng thái đơn ──
    var statusChart = new ApexCharts(document.getElementById('dashStatusChart'), {
        chart: { type: 'donut', height: 260, fontFamily: 'inherit' },
        series: @json(collect($statusRatio)->pluck('value')),
        labels: @json(collect($statusRatio)->pluck('label')),
        colors: @json(collect($statusRatio)->pluck('color')),
        stroke: { width: 0 },
        legend: { show: false },
        dataLabels: { enabled: false },
        plotOptions: { pie: { donut: {
            size: '72%',
            labels: {
                show: true,
                total: { show: true, label: 'Tổng đơn', color: axisColor, fontSize: '12px',
                    formatter: function (w) { return w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0).toLocaleString('vi-VN'); } },
                value: { color: isDark ? '#F8FAFC' : '#0F172A', fontSize: '24px', fontWeight: 800 }
            }
        } } },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (v) { return v + ' đơn'; } } }
    });
    statusChart.render();
})();
</script>
@endpush
