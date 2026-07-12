@extends('layouts.app')

@section('title', 'Hoàn trả & Đổi trả | HK Store')

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
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
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

    .policy-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .policy-list li {
        font-size: 15px;
        line-height: 1.9;
        color: var(--secondary-color);
    }
    .policy-list.numbered {
        counter-reset: step;
    }
    .policy-list.numbered li {
        counter-increment: step;
    }
    .policy-list.numbered li::before {
        content: "Bước " counter(step) ": ";
        font-weight: 600;
        color: var(--primary-color);
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
            ['name' => 'Hoàn trả & Đổi trả', 'active' => true],
        ]
    ])

    <div class="container policy-wrap py-5">
        <h1 class="policy-page-title">Hoàn trả &amp; Đổi trả</h1>

        <div class="policy-accordion">
            <!-- 1 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">1. Điều kiện đổi trả</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Để được đổi hoặc trả sản phẩm, quý khách vui lòng đảm bảo các điều kiện sau:</p>
                        <ul class="policy-list">
                            <li>Yêu cầu đổi trả trong vòng <strong>7 ngày</strong> kể từ ngày nhận hàng.</li>
                            <li>Sản phẩm còn nguyên tem, nhãn mác, chưa qua sử dụng, giặt là hay chỉnh sửa.</li>
                            <li>Sản phẩm còn đầy đủ phụ kiện, quà tặng kèm theo (nếu có).</li>
                            <li>Có hóa đơn mua hàng hoặc mã đơn hàng để đối chiếu.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 2 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">2. Các trường hợp được đổi trả</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>HK Store hỗ trợ đổi trả trong các trường hợp:</p>
                        <ul class="policy-list">
                            <li>Sản phẩm bị lỗi kỹ thuật từ nhà sản xuất (đường may, phai màu, hỏng khóa kéo…).</li>
                            <li>Giao sai sản phẩm, sai màu sắc hoặc sai kích cỡ so với đơn đặt hàng.</li>
                            <li>Sản phẩm không vừa size, quý khách có nhu cầu đổi sang size khác (tùy tình trạng tồn kho).</li>
                            <li>Sản phẩm bị hư hỏng trong quá trình vận chuyển.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 3 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">3. Quy trình đổi trả</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <ul class="policy-list numbered">
                            <li>Liên hệ với chúng tôi qua hotline hoặc email chăm sóc khách hàng kèm mã đơn hàng và lý do đổi trả.</li>
                            <li>Nhân viên xác nhận yêu cầu và hướng dẫn quý khách cách gửi trả sản phẩm.</li>
                            <li>Gửi sản phẩm về địa chỉ của HK Store (đóng gói cẩn thận, kèm hóa đơn nếu có).</li>
                            <li>Chúng tôi kiểm tra sản phẩm và tiến hành đổi hàng mới hoặc hoàn tiền cho quý khách.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 4 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">4. Chính sách hoàn tiền</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <p>Trường hợp không thể đổi sản phẩm phù hợp, quý khách sẽ được hoàn tiền theo quy định:</p>
                        <ul class="policy-list">
                            <li>Hoàn tiền qua đúng phương thức thanh toán ban đầu (chuyển khoản, thẻ hoặc tiền mặt khi nhận hàng).</li>
                            <li>Thời gian xử lý hoàn tiền từ <strong>3 – 7 ngày làm việc</strong> kể từ khi nhận và kiểm tra sản phẩm hoàn trả.</li>
                            <li>Số tiền hoàn lại tương ứng với giá trị sản phẩm; phí vận chuyển ban đầu có thể không được hoàn tùy trường hợp.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 5 -->
            <div class="policy-item">
                <button class="policy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right policy-chevron"></i>
                    <span class="policy-title">5. Chi phí đổi trả</span>
                </button>
                <div class="policy-body">
                    <div class="policy-body-inner">
                        <ul class="policy-list">
                            <li>Đối với lỗi từ phía HK Store (giao sai, sản phẩm lỗi): chúng tôi chịu toàn bộ chi phí vận chuyển đổi trả.</li>
                            <li>Đối với nhu cầu đổi size, đổi mẫu theo ý khách: quý khách vui lòng chịu phí vận chuyển hai chiều.</li>
                        </ul>
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
