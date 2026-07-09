<div class="row g-3 product-stat-row">
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Tổng đơn hàng {{ $periodLabel }}</div>
            <div class="product-stat-value">{{ number_format($periodOrders) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Doanh thu {{ $periodLabel }}</div>
            <div class="product-stat-value">{{ number_format($periodRevenue, 0, ',', '.') }}đ</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Đơn chờ duyệt</div>
            <div class="product-stat-value product-stat-value--success">{{ number_format($pendingOrders) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Đơn bị hủy {{ $periodLabel }}</div>
            <div class="product-stat-value product-stat-value--danger">{{ number_format($cancelledOrders) }}</div>
        </div>
    </div>
</div>
