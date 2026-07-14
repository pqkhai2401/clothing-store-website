{{-- Khung offcanvas trượt phải dùng chung cho "Sửa đơn hàng" — nội dung nạp qua AJAX,
     include ở cả orders/index.blade.php (danh sách) và orders/detail.blade.php (chi tiết). --}}
<style>
    /* Rộng như panel sửa phiếu nhập kho — mặc định Bootstrap quá hẹp (400px) khiến bảng sản phẩm
       (tên/màu/size/giá/số lượng/thành tiền) bị bóp chữ xuống dòng liên tục, rất khó nhìn. */
    .order-edit-offcanvas { width: min(820px, 92vw) !important; }
</style>
<div class="offcanvas offcanvas-end order-edit-offcanvas" tabindex="-1" id="orderEditOffcanvas">
    <div data-order-edit-body class="h-100">
        <div class="offcanvas-body text-center py-5">
            <div class="spinner-border text-secondary" role="status"></div>
        </div>
    </div>
</div>
