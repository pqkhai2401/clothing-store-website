<!-- Footer -->
<footer class="footer">
    <div class="container-fluid px-lg-5">
        <div class="row">
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">HK Store</h5>
                <p class="text-muted pe-md-4">
                Thương hiệu thời trang cao cấp chuyên cung cấp các sản phẩm may mặc chất lượng cao, theo phong cách tối giản, được thiết kế phù hợp với lối sống hiện đại.                </p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Về chúng tôi</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/about') }}">Về chúng tôi</a></li>
                    {{-- <li><a href="{{ url('/sustainability') }}">Bền vững</a></li>
                    <li><a href="{{ url('/careers') }}">Cơ hội nghề nghiệp</a></li>
                    <li><a href="{{ url('/press') }}">Phóng viên</a></li> --}}
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Chăm sóc khách hàng</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/contact') }}">Liên hệ</a></li>
                    <li><a href="{{ url('/shipping') }}">Mua sắm</a></li>
                    <li><a href="{{ url('/returns') }}">Hoàn trả & Đổi trả</a></li>
                    <li><a href="{{ url('/faq') }}">Câu hỏi thường gặp</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="footer-title">Thông tin liên hệ</h5>
                <ul class="footer-links">
                    <li class="text-muted"><i class="bi bi-geo-alt me-2"></i> 65 Huỳnh Thúc Kháng, Sài Gòn, Hồ Chí Minh 50000, Việt Nam</li>
                    <li class="text-muted"><i class="bi bi-telephone me-2"></i> 02838212360</li>
                    <li class="text-muted"><i class="bi bi-envelope me-2"></i> #### </li>
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
                &copy; HK Store. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="{{ url('/privacy') }}" class="text-muted me-3">Chính sách bảo mật</a>
                <a href="{{ url('/terms') }}" class="text-muted me-3">Điều khoản dịch vụ</a>
                <a href="{{ url('/cookie-policy') }}" class="text-muted">Chính sách cookie</a>
            </div>
        </div>
    </div>
</footer>
