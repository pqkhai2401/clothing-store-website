<div class="row g-3 product-stat-row">
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Tổng số mã</div>
            <div class="product-stat-value">{{ number_format($totalVouchers) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Đang hoạt động</div>
            <div class="product-stat-value product-stat-value--success">{{ number_format($activeVouchers) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Đã hết hạn</div>
            <div class="product-stat-value">{{ number_format($expiredVouchers) }}</div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="product-stat-card">
            <div class="product-stat-label">Đã dùng hết lượt</div>
            <div class="product-stat-value product-stat-value--danger">{{ number_format($depletedVouchers) }}</div>
        </div>
    </div>
</div>
