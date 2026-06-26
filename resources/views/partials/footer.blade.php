<!-- Footer -->
<footer class="footer">
    <div class="container-fluid px-lg-5">
        <div class="row">
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">HK Store</h5>
                <p class="text-muted pe-md-4">
                    Thương hiệu thời trang cao cấp cung cấp những trang phục chất lượng cao, phong cách tối giản, thiết kế cho lối sống hiện đại.
                </p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Về Chúng Tôi</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/about') }}">Câu Chuyện Của Chúng Tôi</a></li>
                    <li><a href="{{ url('/sustainability') }}">Phát Triển Bền Vững</a></li>
                    <li><a href="{{ url('/careers') }}">Tuyển Dụng</a></li>
                    <li><a href="{{ url('/press') }}">Báo Chí</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Chăm Sóc Khách Hàng</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/contact') }}">Liên Hệ</a></li>
                    <li><a href="{{ url('/shipping') }}">Vận Chuyển & Giao Hàng</a></li>
                    <li><a href="{{ url('/returns') }}">Đổi Trả Hàng</a></li>
                    <li><a href="{{ url('/faq') }}">Câu Hỏi Thường Gặp</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="footer-title">Thông Tin Liên Hệ</h5>
                <ul class="footer-links">
                    <li class="text-muted"><i class="bi bi-geo-alt me-2"></i> 123 Đường Thời Trang, Quận 1, TP. Hồ Chí Minh</li>
                    <li class="text-muted"><i class="bi bi-telephone me-2"></i> +84 (28) 3555-0199</li>
                    <li class="text-muted"><i class="bi bi-envelope me-2"></i> support@hkstore.vn</li>
                </ul>
                <div class="footer-socials mt-4">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" aria-label="Pinterest"><i class="bi bi-pinterest"></i></a>
                </div>
            </div>
        </div>
        <div class="row footer-bottom">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                &copy; {{ date('Y') }} {{ config('app.name', 'HK Store') }}. Tất cả quyền được bảo lưu.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="{{ url('/privacy') }}" class="text-muted me-3">Chính Sách Bảo Mật</a>
                <a href="{{ url('/terms') }}" class="text-muted me-3">Điều Khoản Dịch Vụ</a>
                <a href="{{ url('/cookie-policy') }}" class="text-muted">Chính Sách Cookie</a>
            </div>
        </div>
    </div>
</footer>
