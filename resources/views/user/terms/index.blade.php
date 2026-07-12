@extends('layouts.app')

@section('title', 'Điều khoản dịch vụ | HK Store')

@section('css')
<style>
    .terms-wrap {
        max-width: 1000px;
    }

    .terms-page-title {
        font-family: var(--font-serif);
        font-size: 2.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 30px;
        color: var(--primary-color);
    }

    /* ===== Accordion ===== */
    .terms-item {
        border-bottom: 1px solid var(--border-color);
    }
    .terms-item:first-child {
        border-top: 1px solid var(--border-color);
    }

    .terms-header {
        display: flex;
        align-items: center;
        gap: 18px;
        width: 100%;
        background: none;
        border: none;
        padding: 22px 6px;
        text-align: left;
        cursor: pointer;
        color: var(--primary-color);
    }

    .terms-chevron {
        font-size: 15px;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .terms-item.active .terms-chevron {
        transform: rotate(90deg);
    }

    .terms-title {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .terms-body {
        display: none;
        overflow: hidden;
    }
    .terms-item.active .terms-body {
        display: block;
    }

    .terms-body-inner {
        padding: 4px 6px 28px 41px;
    }

    .terms-body-inner p {
        font-size: 15px;
        line-height: 1.9;
        color: var(--secondary-color);
        margin-bottom: 16px;
    }
    .terms-body-inner p:last-child {
        margin-bottom: 0;
    }

    .terms-pay-method {
        margin-bottom: 6px;
    }
    .terms-pay-method strong {
        color: var(--primary-color);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    @media (max-width: 576px) {
        .terms-page-title { font-size: 2rem; }
        .terms-title { font-size: 16px; }
        .terms-body-inner { padding-left: 30px; }
    }
</style>
@endsection

@section('content')
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Điều khoản dịch vụ', 'active' => true],
        ]
    ])

    <div class="container terms-wrap py-5">
        <h1 class="terms-page-title">Điều khoản dịch vụ</h1>

        <div class="terms-accordion">
            <!-- 1. Giới thiệu -->
            <div class="terms-item">
                <button class="terms-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right terms-chevron"></i>
                    <span class="terms-title">1. Giới thiệu</span>
                </button>
                <div class="terms-body">
                    <div class="terms-body-inner">
                        <p>Chào mừng quý khách hàng đến với website chúng tôi.</p>
                        <p>Khi quý khách hàng truy cập vào trang website của chúng tôi có nghĩa là quý khách đồng ý với các điều khoản này. Trang web có quyền thay đổi, chỉnh sửa, thêm hoặc lược bỏ bất kỳ phần nào trong Điều khoản mua bán hàng hóa này, vào bất cứ lúc nào. Các thay đổi có hiệu lực ngay khi được đăng trên trang web mà không cần thông báo trước. Và khi quý khách tiếp tục sử dụng trang web, sau khi các thay đổi về Điều khoản này được đăng tải, có nghĩa là quý khách chấp nhận với những thay đổi đó.</p>
                        <p>Quý khách hàng vui lòng kiểm tra thường xuyên để cập nhật những thay đổi của chúng tôi.</p>
                    </div>
                </div>
            </div>

            <!-- 2. Hướng dẫn sử dụng website -->
            <div class="terms-item">
                <button class="terms-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right terms-chevron"></i>
                    <span class="terms-title">2. Hướng dẫn sử dụng website</span>
                </button>
                <div class="terms-body">
                    <div class="terms-body-inner">
                        <p>Khi vào web của chúng tôi, khách hàng phải đảm bảo đủ 18 tuổi, hoặc truy cập dưới sự giám sát của cha mẹ hay người giám hộ hợp pháp. Khách hàng đảm bảo có đầy đủ hành vi dân sự để thực hiện các giao dịch mua bán hàng hóa theo quy định hiện hành của pháp luật Việt Nam.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Thanh toán an toàn và tiện lợi -->
            <div class="terms-item">
                <button class="terms-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right terms-chevron"></i>
                    <span class="terms-title">3. Thanh toán an toàn và tiện lợi</span>
                </button>
                <div class="terms-body">
                    <div class="terms-body-inner">
                        <p>Người mua có thể tham khảo các phương thức thanh toán sau đây và lựa chọn áp dụng phương thức phù hợp:</p>
                        <p class="terms-pay-method"><strong>Cách 1</strong>: Thanh toán trực tiếp (người mua nhận hàng tại địa chỉ người bán)</p>
                        <p class="terms-pay-method"><strong>Cách 2:</strong> Thanh toán sau (COD – giao hàng và thu tiền tận nơi)</p>
                        <p class="terms-pay-method"><strong>Cách 3:</strong> Thanh toán online qua thẻ tín dụng, chuyển khoản</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.terms-accordion .terms-header').forEach(function (header) {
            header.addEventListener('click', function () {
                var item = header.closest('.terms-item');
                var isOpen = item.classList.toggle('active');
                header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    </script>
@endsection
