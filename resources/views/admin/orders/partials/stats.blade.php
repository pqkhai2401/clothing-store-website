@if(($cancelRequests ?? 0) > 0)
    {{-- Khách xin hủy đơn đã xác nhận/đang giao — cần admin duyệt, không để khách chờ. --}}
    <a href="{{ route('admin.orders.list', ['cancel_request' => 1]) }}"
       class="ord-refund-alert ord-cancelreq-alert" title="Lọc đúng các đơn khách đang xin hủy">
        <i class="fa-regular fa-hand"></i>
        <span><b>{{ number_format($cancelRequests) }} yêu cầu hủy đơn</b> từ khách đang chờ bạn duyệt.</span>
        <span class="ord-refund-alert-cta">Xem danh sách <i class="fa-solid fa-arrow-right"></i></span>
    </a>
@endif

@if(($refundPending ?? 0) > 0)
    {{-- Cảnh báo tiền: đơn ĐÃ THU TIỀN nhưng bị hủy → còn nợ khách. Bấm vào để lọc đúng các đơn đó.
         Hệ thống không tự hoàn tiền (không tích hợp API cổng) nên phải nhắc admin ngay tại đây. --}}
    <a href="{{ route('admin.orders.list', ['status' => 'cancelled', 'payment_status' => 'paid']) }}"
       class="ord-refund-alert" title="Xem danh sách đơn cần hoàn tiền">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>
            <b>{{ number_format($refundPending) }} đơn đã thanh toán nhưng bị hủy</b> —
            cần hoàn lại khách <b>{{ number_format($refundPendingAmount ?? 0, 0, ',', '.') }}đ</b>.
        </span>
        <span class="ord-refund-alert-cta">Xem danh sách <i class="fa-solid fa-arrow-right"></i></span>
    </a>
@endif

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
