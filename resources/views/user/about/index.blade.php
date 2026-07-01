@extends('layouts.app')

@section('title', 'Về Chúng Tôi | HK Store')

@section('css')
<style>
    /* ===== Shared kickers / titles ===== */
    .about-kicker {
        display: block;
        font-size: 12px;
        letter-spacing: 2px;
        text-transform: uppercase;
        color: var(--muted-text);
        margin-bottom: 14px;
    }
    .text-light-60 { color: rgba(255, 255, 255, 0.6) !important; }

    .about-section-title {
        font-family: var(--font-serif);
        font-size: 2.8rem;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 18px;
    }

    /* ===== 1. Hero ===== */
    .about-hero {
        height: 90vh;
        min-height: 560px;
        display: flex;
        align-items: center;
        background: url("{{ asset('images/promo_banner.png') }}");
        background-size: cover;
        background-position: center 20%;
        text-align: center;
    }
    .about-hero-box {
        display: inline-block;
        max-width: 780px;
        margin: 0 auto;
        background: rgba(255, 255, 255, 0.78);
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 70px 50px;
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .about-eyebrow {
        display: inline-block;
        position: relative;
        font-size: 12px;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: #333333;
        margin-bottom: 20px;
        padding-bottom: 14px;
    }
    .about-eyebrow::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 1px;
        background: #333333;
    }
    .about-hero-title {
        font-family: var(--font-serif);
        font-size: 3.8rem;
        font-weight: 400;
        line-height: 1.35;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 24px;
        color: #111111;
    }
    .about-hero-text {
        font-size: 15px;
        line-height: 1.9;
        color: #444444;
        max-width: 640px;
        margin: 0 auto;
    }

    @media (max-width: 768px) {
        .about-hero { height: auto; min-height: 70vh; padding: 40px 0; }
        .about-hero-box { padding: 40px 24px; }
        .about-hero-title { font-size: 2.4rem; }
    }

    /* ===== 2. Brand Story ===== */
    .about-story-title {
        font-family: var(--font-serif);
        font-size: 2rem;
        font-weight: 400;
        line-height: 1.4;
        margin-bottom: 22px;
    }
    .about-story-text {
        font-size: 14.5px;
        line-height: 1.9;
        color: var(--secondary-color);
        margin-bottom: 18px;
    }
    .about-story-text:last-child { margin-bottom: 0; }
    .about-story-img {
        overflow: hidden;
        aspect-ratio: 4 / 5;
    }
    .about-story-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .about-story-img:hover img { transform: scale(1.05); }

    /* ===== 3. AI Section ===== */
    .about-ai {
        background-color: var(--primary-color);
        color: #ffffff;
    }
    .about-ai-subtitle {
        font-size: 16.5px;
        line-height: 1.9;
        color: #cfcfcf;
        max-width: 700px;
        margin: 0 auto;
    }
    .ai-card {
        height: 100%;
        padding: 40px 28px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.15);
        transition: border-color 0.3s ease, background-color 0.3s ease, transform 0.3s ease;
    }
    .ai-card:hover {
        border-color: rgba(255, 255, 255, 0.5);
        background-color: rgba(255, 255, 255, 0.04);
        transform: translateY(-6px);
    }
    .ai-card-icon {
        width: 76px;
        height: 76px;
        margin: 0 auto 22px;
        border-radius: 50%;
        border: 1px solid rgba(255, 255, 255, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
    }
    .ai-card h5 {
        font-size: 20px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #ffffff;
        margin-bottom: 14px;
    }
    .ai-card p {
        font-size: 15px;
        line-height: 1.8;
        color: #b8b8b8;
        margin-bottom: 0;
    }

    /* ===== 4. Core Values ===== */
    .about-values { background-color: var(--hover-bg); }
    .value-item { padding: 10px 16px; }
    .value-icon {
        width: 72px;
        height: 72px;
        margin: 0 auto 24px;
        border-radius: 50%;
        background: #ffffff;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: var(--primary-color);
    }
    .value-item h5 {
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 14px;
    }
    .value-item p {
        font-size: 13.5px;
        line-height: 1.8;
        color: var(--muted-text);
        max-width: 320px;
        margin: 0 auto;
    }

    /* ===== 5. CTA ===== */
    .about-cta {
        padding: 100px 0;
        text-align: center;
        border-top: 1px solid var(--border-color);
    }
    .about-cta-title {
        font-family: var(--font-serif);
        font-size: 2rem;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 16px;
    }
    .about-cta-text {
        font-size: 14px;
        color: var(--muted-text);
        margin-bottom: 32px;
    }
</style>
@endsection

@section('content')

    @include('partials.breadcrumb', ['items' => [
        ['name' => 'Trang chủ', 'url' => url('/')],
        ['name' => 'Về chúng tôi', 'active' => true],
    ]])

    <!-- 1. Hero Section -->
    <section class="about-hero">
        <div class="container-fluid px-lg-5">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="about-hero-box">
                        <span class="about-eyebrow">Về chúng tôi</span>
                        <h1 class="about-hero-title">HK Store - Đơn giản chính là đỉnh cao của sự tinh tế</h1>
                        <p class="about-hero-text">
                            Chúng tôi tin rằng phong cách thực sự không nằm ở số lượng, mà ở sự chọn lọc.
                            HK Store theo đuổi triết lý thời trang tối giản và bền vững, nơi mỗi thiết kế
                            đều được cân nhắc kỹ lưỡng để đồng hành lâu dài cùng bạn.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Brand Story -->
    <section class="about-story py-5">
        <div class="container-fluid px-lg-5 py-5">

            <div class="row align-items-center gy-5 mb-5">
                <div class="col-lg-6">
                    <span class="about-kicker">Câu chuyện thương hiệu</span>
                    <h2 class="about-story-title">Bắt đầu từ một chiếc tủ đồ tinh gọn</h2>
                    <p class="about-story-text">
                        HK Store ra đời từ một câu hỏi đơn giản: liệu chúng ta có thực sự cần quá nhiều
                        quần áo để luôn thời thượng? Chúng tôi bắt đầu hành trình với triết lý
                        <strong>Capsule Wardrobe</strong> — xây dựng một tủ đồ tinh gọn gồm những item nền
                        tảng, dễ phối và có chất lượng cao.
                    </p>
                    <p class="about-story-text">
                        Mỗi thiết kế được chọn lọc kỹ càng để có thể phối cùng nhiều trang phục khác nhau,
                        giúp khách hàng giảm số lượng quần áo sở hữu mà vẫn luôn tự tin, thời thượng trong
                        mọi hoàn cảnh.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="about-story-img">
                        <img src="{{ asset('images/category_men.png') }}" alt="Hành trình HK Store" loading="lazy">
                    </div>
                </div>
            </div>

            <div class="row align-items-center gy-5">
                <div class="col-lg-6 order-lg-2">
                    <span class="about-kicker">Hành trình phát triển</span>
                    <h2 class="about-story-title">Thời thượng bền vững, mỗi ngày</h2>
                    <p class="about-story-text">
                        Từ một bộ sưu tập nhỏ, HK Store dần trở thành thương hiệu thời trang tối giản
                        được nhiều khách hàng tin tưởng lựa chọn cho phong cách sống hiện đại.
                    </p>
                    <p class="about-story-text">
                        Chúng tôi không chạy theo xu hướng ngắn hạn, mà tập trung vào những giá trị lâu
                        dài: chất liệu tốt, đường may chỉn chu và thiết kế vượt thời gian.
                    </p>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="about-story-img">
                        <img src="{{ asset('images/category_women.png') }}" alt="Thời trang bền vững HK Store" loading="lazy">
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 3. AI Section -->
    <section class="about-ai py-5">
        <div class="container-fluid px-lg-5 py-5">
            <div class="text-center mb-5">
                <span class="about-kicker text-light-60">Điểm khác biệt</span>
                <h2 class="about-section-title">Trải nghiệm mua sắm thông minh cùng AI</h2>
                <p class="about-ai-subtitle">
                    HK Store không chỉ bán quần áo — chúng tôi tích hợp Trí tuệ nhân tạo
                    (AI&nbsp;Recommendation) để phân tích hành vi, lịch sử xem và sở thích cá nhân,
                    đóng vai trò như một "Stylist riêng" đưa ra gợi ý trang phục hoàn hảo nhất cho bạn.
                </p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="ai-card">
                        <div class="ai-card-icon"><i class="bi bi-cpu"></i></div>
                        <h5>Phân tích hành vi</h5>
                        <p>Ghi nhận lịch sử xem, tìm kiếm và tương tác để hiểu rõ gu thẩm mỹ của từng khách hàng.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ai-card">
                        <div class="ai-card-icon"><i class="bi bi-magic"></i></div>
                        <h5>Gợi ý cá nhân hóa</h5>
                        <p>Đề xuất trang phục và cách phối đồ phù hợp nhất với sở thích và phong cách riêng.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ai-card">
                        <div class="ai-card-icon"><i class="bi bi-graph-up-arrow"></i></div>
                        <h5>Cập nhật liên tục</h5>
                        <p>Hệ thống học hỏi theo thời gian thực, gợi ý ngày càng chính xác qua mỗi lượt mua sắm.</p>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="ai-card">
                        <div class="ai-card-icon"><i class="bi bi-person-heart"></i></div>
                        <h5>Stylist riêng 24/7</h5>
                        <p>Luôn đồng hành, đưa ra gợi ý trang phục hoàn hảo cho mọi dịp mà không cần chờ đợi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 4. Core Values -->
    <section class="about-values py-5">
        <div class="container-fluid px-lg-5 py-5">
            <div class="text-center mb-5">
                <span class="about-kicker">Cam kết của chúng tôi</span>
                <h2 class="about-section-title">Chất lượng & giá trị cốt lõi</h2>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon"><i class="bi bi-flower1"></i></div>
                        <h5>Chất liệu tuyển chọn</h5>
                        <p>Organic Cotton, Merino Wool và các chất liệu bền vững được chọn lọc kỹ càng cho từng sản phẩm.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon"><i class="bi bi-recycle"></i></div>
                        <h5>Tối ưu sản xuất</h5>
                        <p>Quy trình sản xuất được tính toán kỹ lưỡng để giảm thiểu rác thải và tác động đến môi trường.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="value-item">
                        <div class="value-icon"><i class="bi bi-person-check"></i></div>
                        <h5>Cá nhân hóa trải nghiệm</h5>
                        <p>Mỗi khách hàng nhận được trải nghiệm mua sắm và gợi ý riêng biệt, không rập khuôn.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 5. Call To Action -->
    <section class="about-cta">
        <div class="container-fluid px-lg-5">
            <h2 class="about-cta-title">Sẵn sàng làm mới tủ đồ của bạn?</h2>
            <p class="about-cta-text">Khám phá những thiết kế tối giản, tinh tế được chọn lọc dành riêng cho bạn.</p>
            <a href="{{ url('/products') }}" class="btn btn-black">Khám Phá Bộ Sưu Tập</a>
        </div>
    </section>

@endsection
