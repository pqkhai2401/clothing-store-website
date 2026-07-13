@extends('layouts.app')

@section('title', 'Câu hỏi thường gặp | HK Store')

@section('css')
<style>
    .policy-wrap {
        max-width: 1000px;
    }

    .policy-page-title {
        font-family: var(--font-serif);
        font-size: 2.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 30px;
        color: var(--primary-color);
    }

    /* ===== Accordion ===== */
    .policy-item {
        border-bottom: 1px solid var(--border-color);
    }
    .policy-item:first-child {
        border-top: 1px solid var(--border-color);
    }

    .policy-header {
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

    .policy-chevron {
        font-size: 15px;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .policy-item.active .policy-chevron {
        transform: rotate(90deg);
    }

    .policy-title {
        font-size: 17px;
        font-weight: 700;
        letter-spacing: 0.3px;
    }

    .policy-body {
        display: none;
        overflow: hidden;
    }
    .policy-item.active .policy-body {
        display: block;
    }

    .policy-body-inner {
        padding: 4px 6px 28px 41px;
    }

    .policy-body-inner p {
        font-size: 15px;
        line-height: 1.9;
        color: var(--secondary-color);
        margin-bottom: 16px;
    }
    .policy-body-inner p:last-child {
        margin-bottom: 0;
    }

    .policy-body-inner a {
        color: var(--primary-color);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    @media (max-width: 576px) {
        .policy-page-title { font-size: 2rem; }
        .policy-title { font-size: 15px; }
        .policy-body-inner { padding-left: 30px; }
    }
</style>
@endsection

@section('content')
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Câu hỏi thường gặp', 'active' => true],
        ]
    ])

    <div class="container policy-wrap py-5">
        <h1 class="policy-page-title">Câu hỏi thường gặp</h1>

        <div class="policy-accordion">
            <!-- 1 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Làm thế nào để đặt hàng trên HK Store?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Quý khách chọn sản phẩm yêu thích, chọn màu sắc và kích cỡ phù hợp rồi bấm "Thêm vào giỏ hàng". Sau đó vào giỏ hàng, kiểm tra lại đơn và bấm "Thanh toán" để điền thông tin nhận hàng và hoàn tất đặt hàng.</p>
                    </div>
                </div>
            </div>

            <!-- 2 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Tôi có cần tạo tài khoản để mua hàng không?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Quý khách cần đăng nhập tài khoản để thêm sản phẩm vào giỏ hàng và thanh toán. Việc này giúp quý khách dễ dàng theo dõi đơn hàng, lưu sản phẩm yêu thích và nhận các ưu đãi dành riêng cho thành viên. Quý khách có thể đăng ký nhanh bằng email hoặc tài khoản Google.</p>
                    </div>
                </div>
            </div>

            <!-- 3 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">HK Store hỗ trợ những hình thức thanh toán nào?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Hiện tại chúng tôi hỗ trợ các hình thức thanh toán:</p>
                        <p>
                            &bull; Thanh toán khi nhận hàng (COD) – kiểm tra hàng và trả tiền tận nơi.<br>
                            &bull; Thanh toán online qua thẻ tín dụng/ghi nợ và chuyển khoản ngân hàng (quét mã QR qua cổng PayOS).
                        </p>
                    </div>
                </div>
            </div>

            <!-- 4 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Thời gian giao hàng mất bao lâu?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Thời gian giao hàng dự kiến từ <strong>2 – 5 ngày làm việc</strong> tùy khu vực. Khu vực nội thành thường nhận hàng nhanh hơn so với các tỉnh xa. Đơn hàng sẽ được xử lý và bàn giao cho đơn vị vận chuyển trong vòng 24 giờ sau khi đặt thành công.</p>
                    </div>
                </div>
            </div>

            <!-- 5 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Làm sao để theo dõi đơn hàng của tôi?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Sau khi đăng nhập, quý khách vào mục <a href="{{ url('/orders') }}">Đơn hàng của tôi</a> để xem trạng thái và lịch sử các đơn đã đặt. Mỗi đơn hàng đều hiển thị tình trạng xử lý, vận chuyển và giao hàng.</p>
                    </div>
                </div>
            </div>

            <!-- 6 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Làm thế nào để chọn đúng kích cỡ (size)?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Mỗi sản phẩm đều có các tùy chọn size (S, M, L, XL…) và màu sắc ngay trên trang chi tiết. Quý khách nên tham khảo mô tả sản phẩm và bảng gợi ý kích cỡ để chọn size phù hợp nhất. Nếu chưa chắc chắn, quý khách có thể liên hệ nhân viên tư vấn trước khi đặt hàng.</p>
                    </div>
                </div>
            </div>

            <!-- 7 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">Chính sách đổi trả của HK Store như thế nào?</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Quý khách được đổi hoặc trả sản phẩm trong vòng 7 ngày kể từ ngày nhận hàng nếu sản phẩm còn nguyên tem nhãn và chưa qua sử dụng. Xem chi tiết tại trang <a href="{{ route('returns') }}">Hoàn trả &amp; Đổi trả</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.policy-accordion .policy-header').forEach(function (header) {
            header.addEventListener('click', function () {
                var item = header.closest('.policy-item');
                var isOpen = item.classList.toggle('active');
                header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    </script>
@endsection
