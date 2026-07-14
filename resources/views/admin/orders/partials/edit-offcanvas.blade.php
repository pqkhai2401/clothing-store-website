{{-- Khung offcanvas trượt phải dùng chung cho "Sửa đơn hàng" — nội dung nạp qua AJAX,
     include ở cả orders/index.blade.php (danh sách) và orders/detail.blade.php (chi tiết). --}}
<div class="offcanvas offcanvas-end order-edit-offcanvas" tabindex="-1" id="orderEditOffcanvas">
    <div data-order-edit-body class="h-100">
        <div class="offcanvas-body text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
        </div>
    </div>
</div>
