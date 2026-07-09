<div class="row g-3 product-stat-row">
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Tổng số log</div>
            <div class="product-stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Hôm nay</div>
            <div class="product-stat-value product-stat-value--success">{{ number_format($stats['today'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Trong tuần</div>
            <div class="product-stat-value">{{ number_format($stats['this_week'] ?? 0) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Người dùng hoạt động</div>
            <div class="product-stat-value">{{ number_format($stats['active_users'] ?? 0) }}</div>
        </div>
    </div>
</div>
