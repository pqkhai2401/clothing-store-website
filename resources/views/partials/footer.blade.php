<!-- Footer -->
<footer class="footer">
    <div class="container-fluid px-lg-5">
        <div class="row">
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">HK Store</h5>
                <p class="text-muted pe-md-4">
                    Premium fashion brand offering high quality, minimalist clothing designed for modern lifestyles.
                </p>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">About Us</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/about') }}">Our Story</a></li>
                    <li><a href="{{ url('/sustainability') }}">Sustainability</a></li>
                    <li><a href="{{ url('/careers') }}">Careers</a></li>
                    <li><a href="{{ url('/press') }}">Press</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4 mb-md-0">
                <h5 class="footer-title">Customer Service</h5>
                <ul class="footer-links">
                    <li><a href="{{ url('/contact') }}">Contact Us</a></li>
                    <li><a href="{{ url('/shipping') }}">Shipping & Delivery</a></li>
                    <li><a href="{{ url('/returns') }}">Returns & Exchanges</a></li>
                    <li><a href="{{ url('/faq') }}">FAQs</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5 class="footer-title">Contact Information</h5>
                <ul class="footer-links">
                    <li class="text-muted"><i class="bi bi-geo-alt me-2"></i> 123 Fashion Street, New York, NY 10001</li>
                    <li class="text-muted"><i class="bi bi-telephone me-2"></i> +1 (212) 555-0199</li>
                    <li class="text-muted"><i class="bi bi-envelope me-2"></i> support@noirfashion.com</li>
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
                &copy; {{ date('Y') }} {{ config('app.name', 'NOIR') }}. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                <a href="{{ url('/privacy') }}" class="text-muted me-3">Privacy Policy</a>
                <a href="{{ url('/terms') }}" class="text-muted me-3">Terms of Service</a>
                <a href="{{ url('/cookie-policy') }}" class="text-muted">Cookie Policy</a>
            </div>
        </div>
    </div>
</footer>
