@extends('layouts.app')

@section('title', 'Chính sách bảo mật | HK Store')

@section('css')
<style>
    .privacy-wrap {
        max-width: 1000px;
    }

    .privacy-page-title {
        font-family: var(--font-serif);
        font-size: 2.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 30px;
        color: var(--primary-color);
    }

    /* ===== Accordion ===== */
    .privacy-item {
        border-bottom: 1px solid var(--border-color);
    }
    .privacy-item:first-child {
        border-top: 1px solid var(--border-color);
    }

    .privacy-header {
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

    .privacy-chevron {
        font-size: 15px;
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }

    .privacy-item.active .privacy-chevron {
        transform: rotate(90deg);
    }

    .privacy-title {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }

    .privacy-body {
        display: none;
        overflow: hidden;
    }
    .privacy-item.active .privacy-body {
        display: block;
    }

    .privacy-body-inner {
        padding: 4px 6px 28px 41px;
    }

    .privacy-body-inner p {
        font-size: 15px;
        line-height: 1.9;
        color: var(--secondary-color);
        margin-bottom: 16px;
    }
    .privacy-body-inner p:last-child {
        margin-bottom: 0;
    }

    .privacy-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }
    .privacy-list li {
        font-size: 15px;
        line-height: 1.9;
        color: var(--secondary-color);
    }

    @media (max-width: 576px) {
        .privacy-page-title { font-size: 2rem; }
        .privacy-title { font-size: 15px; }
        .privacy-body-inner { padding-left: 30px; }
    }
</style>
@endsection

@section('content')
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Chính sách bảo mật', 'active' => true],
        ]
    ])

    <div class="container privacy-wrap py-5">
        <h1 class="privacy-page-title">Chính sách bảo mật</h1>

        <div class="privacy-accordion">
            <!-- 1. Mục đích và phạm vi thu thập -->
            <div class="privacy-item">
                <button class="privacy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right privacy-chevron"></i>
                    <span class="privacy-title">1. Mục đích và phạm vi thu thập</span>
                </button>
                <div class="privacy-body">
                    <div class="privacy-body-inner">
                        <p>Việc thu thập dữ liệu trên website bao gồm: họ tên, email, điện thoại, địa chỉ khách hàng. Đây là các thông tin mà chúng tôi cần thành viên cung cấp bắt buộc khi gửi thông tin nhờ tư vấn hay muốn mua sản phẩm và để chúng tôi liên hệ xác nhận lại với khách hàng trên website nhằm đảm bảo quyền lợi cho người tiêu dùng.</p>
                        <p>Các thành viên sẽ tự chịu trách nhiệm về bảo mật và lưu giữ mọi hoạt động sử dụng dịch vụ dưới thông tin mà mình cung cấp và hộp thư điện tử của mình. Ngoài ra, thành viên có trách nhiệm thông báo kịp thời cho website chúng tôi về những hành vi sử dụng trái phép, lạm dụng, vi phạm bảo mật, lưu giữ tên đăng ký và mật khẩu của bên thứ ba để có biện pháp giải quyết phù hợp.</p>
                    </div>
                </div>
            </div>

            <!-- 2. Phạm vi sử dụng thông tin -->
            <div class="privacy-item">
                <button class="privacy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right privacy-chevron"></i>
                    <span class="privacy-title">2. Phạm vi sử dụng thông tin</span>
                </button>
                <div class="privacy-body">
                    <div class="privacy-body-inner">
                        <p>Chúng tôi sử dụng thông tin thành viên cung cấp để:</p>
                        <ul class="privacy-list">
                            <li>Liên hệ xác nhận đơn hàng và giao hàng cho thành viên khi nhận được yêu cầu từ thành viên;</li>
                            <li>Cung cấp thông tin về sản phẩm đến khách hàng nếu có yêu cầu từ khách hàng;</li>
                            <li>Gửi email tiếp thị, khuyến mại về hàng hóa do chúng tôi bán;</li>
                            <li>Gửi các thông báo về các hoạt động trên website;</li>
                            <li>Liên lạc và giải quyết với người dùng trong những trường hợp đặc biệt;</li>
                            <li>Không sử dụng thông tin cá nhân của người dùng ngoài mục đích xác nhận và liên hệ có liên quan đến giao dịch;</li>
                            <li>Khi có yêu cầu của cơ quan tư pháp bao gồm: Viện kiểm sát, tòa án, cơ quan công an điều tra liên quan đến hành vi vi phạm pháp luật nào đó của khách hàng.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 3. Thời gian lưu trữ thông tin -->
            <div class="privacy-item">
                <button class="privacy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right privacy-chevron"></i>
                    <span class="privacy-title">3. Thời gian lưu trữ thông tin</span>
                </button>
                <div class="privacy-body">
                    <div class="privacy-body-inner">
                        <p>Dữ liệu cá nhân của thành viên sẽ được lưu trữ cho đến khi có yêu cầu ban quản trị hủy bỏ. Còn lại trong mọi trường hợp thông tin cá nhân thành viên sẽ được bảo mật trên máy chủ của chúng tôi.</p>
                    </div>
                </div>
            </div>

            <!-- 4. Những người hoặc tổ chức có thể được tiếp cận với thông tin cá nhân -->
            <div class="privacy-item">
                <button class="privacy-header" type="button" aria-expanded="false">
                    <i class="bi bi-chevron-right privacy-chevron"></i>
                    <span class="privacy-title">4. Những người hoặc tổ chức có thể được tiếp cận với thông tin cá nhân</span>
                </button>
                <div class="privacy-body">
                    <div class="privacy-body-inner">
                        <p>Những thông tin cá nhân trên website được phép tiếp cận bởi:</p>
                        <ul class="privacy-list">
                            <li>Ban quản trị website;</li>
                            <li>Khách hàng sở hữu thông tin cá nhân đó;</li>
                            <li>Các cơ quan Pháp luật Việt Nam có thẩm quyền;</li>
                            <li>Các đối tác hoạt động liên quan đến giao dịch của bạn, chẳng hạn như đơn vị vận chuyển.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.privacy-accordion .privacy-header').forEach(function (header) {
            header.addEventListener('click', function () {
                var item = header.closest('.privacy-item');
                var isOpen = item.classList.toggle('active');
                header.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });
        });
    </script>
@endsection
