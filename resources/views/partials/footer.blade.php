<!-- Footer -->
<footer class="footer">
    <div class="container-fluid px-lg-5">
        <div class="row">
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">{{ $siteSettings->site_name }}</h5>
                <p class="text-muted pe-md-4">
                Thương hiệu thời trang cao cấp chuyên cung cấp các sản phẩm may mặc chất lượng cao, theo phong cách tối giản, được thiết kế phù hợp với lối sống hiện đại.                </p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Về chúng tôi</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/gioi-thieu') }}">Về chúng tôi</a></li>
                    {{-- <li><a href="{{ url('/sustainability') }}">Bền vững</a></li>
                    <li><a href="{{ url('/careers') }}">Cơ hội nghề nghiệp</a></li>
                    <li><a href="{{ url('/press') }}">Phóng viên</a></li> --}}
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Chăm sóc khách hàng</h5>
                <ul class="footer-links">
                    <li><a href="{{ route('returns') }}">Hoàn trả & Đổi trả</a></li>
                    <li><a href="{{ route('faq') }}">Câu hỏi thường gặp</a></li>
                    <li><a href="{{ route('privacy') }}">Chính sách bảo mật</a></li>
                    <li><a href="{{ route('terms') }}">Điều khoản dịch vụ</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="footer-title">Thông tin liên hệ</h5>
                <ul class="footer-links">
                    <li class="text-muted"><i class="bi bi-geo-alt me-2"></i> {{ $siteSettings->address ?: '—' }}</li>
                    <li class="text-muted"><i class="bi bi-telephone me-2"></i> {{ $siteSettings->hotline ?: '—' }}</li>
                    <li class="text-muted"><i class="bi bi-envelope me-2"></i> {{ $siteSettings->email ?: '—' }}</li>
                </ul>
                <div class="footer-socials mt-4">
                    @if($siteSettings->facebook_enabled && $siteSettings->facebook_url)
                        <a href="{{ $siteSettings->facebook_url }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                            <img src="{{ asset('images/Facebook.png') }}" alt="Facebook" loading="lazy">
                        </a>
                    @endif
                    @if($siteSettings->instagram_enabled && $siteSettings->instagram_url)
                        <a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                            <img src="{{ asset('images/Instagram.png') }}" alt="Instagram" loading="lazy">
                        </a>
                    @endif
                    @if($siteSettings->zalo_enabled && $siteSettings->zalo_url)
                        <a href="{{ $siteSettings->zalo_url }}" target="_blank" rel="noopener noreferrer" aria-label="Zalo">
                            <img src="{{ asset('images/Zalo.png') }}" alt="Zalo" loading="lazy">
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="row footer-bottom">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                &copy; {{ $siteSettings->site_name }}. All rights reserved.
            </div>
        </div>
    </div>
</footer>
