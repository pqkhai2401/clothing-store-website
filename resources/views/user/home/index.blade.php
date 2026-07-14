@extends('layouts.app')

@section('title', 'HK Store | Thời Trang Cao Cấp')

@section('css')
<style>
    /* Hero Section */
    .hero-section {
        height: 75vh;
        background-image: linear-gradient(rgba(0, 0, 0, 0.2), rgba(0, 0, 0, 0.2)), url("{{ asset('images/hero_banner.png') }}");
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        text-align: center;
    }

    #heroCarousel, #heroCarousel .carousel-inner, #heroCarousel .carousel-item {
        height: 75vh;
    }

    #heroCarousel .carousel-item {
        background-size: cover;
        background-position: center;
        color: #ffffff;
        text-align: center;
    }

    #heroCarousel .carousel-item::before {
        content: "";
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.2);
    }

    #heroCarousel .hero-content {
        position: relative;
        z-index: 1;
    }

    @media (max-width: 768px) {
        #heroCarousel, #heroCarousel .carousel-inner, #heroCarousel .carousel-item {
            height: 60vh;
        }
    }

    .hero-content h1 {
        font-family: var(--font-serif);
        font-size: 5rem;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 4px;
        margin-bottom: 20px;
        color: #ffffff;
    }

    .hero-content p {
        font-size: 16px;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 35px;
    }

    @media (max-width: 768px) {
        .hero-section {
            height: 60vh;
        }
        .hero-content h1 {
            font-size: 2.8rem;
        }
        .hero-content p {
            font-size: 12px;
        }
    }

    /* Category Section */
    .category-container {
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
    }

    .category-img-wrapper {
        overflow: hidden;
        height: 480px;
        position: relative;
    }

    .category-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .category-container:hover .category-img {
        transform: scale(1.05);
    }

    .category-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 40px;
        background: linear-gradient(to top, rgba(0,0,0,0.4), transparent);
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
    }

    .category-title {
        font-family: var(--font-serif);
        font-size: 28px;
        margin-bottom: 10px;
        color: #ffffff;
    }

    .category-link {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #ffffff;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    @media (max-width: 768px) {
        .category-img-wrapper {
            height: 350px;
        }
    }
    /* Section Title */
    .section-header {
        margin-bottom: 50px;
    }

    .section-title {
        font-size: 32px;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 10px;
    }

    .section-subtitle {
        font-size: 14px;
        color: var(--muted-text);
        letter-spacing: 1px;
    }

    /* Reviews section */
    .review-card {
        border: 1px solid var(--border-color);
        background-color: #ffffff;
        padding: 40px;
        border-radius: 0;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .review-stars {
        color: #ffb400;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .review-text {
        font-style: italic;
        font-size: 15px;
        line-height: 1.8;
        color: var(--secondary-color);
        margin-bottom: 25px;
    }

    .review-author {
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }
</style>
@endsection

@section('content')

    <!-- 1. Hero Banner -->
    @if($heroBanners->isNotEmpty())
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                @foreach($heroBanners as $index => $banner)
                    <div class="carousel-item @if($index === 0) active @endif"
                         style="background-image: url('{{ asset($banner->image_path) }}');">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="container hero-content">
                                @if($banner->title)
                                    <h1 class="animate__animated animate__fadeInUp">{{ $banner->title }}</h1>
                                @endif
                                @if($banner->subtitle)
                                    <p class="animate__animated animate__fadeInUp animate__delay-1s">{{ $banner->subtitle }}</p>
                                @endif
                                @if($banner->button_text && $banner->button_link)
                                    <a href="{{ $banner->button_link }}" class="btn btn-black animate__animated animate__fadeInUp animate__delay-2s">{{ $banner->button_text }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if($heroBanners->count() > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Trước</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Sau</span>
                </button>
                <div class="carousel-indicators">
                    @foreach($heroBanners as $index => $banner)
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="{{ $index }}"
                                class="@if($index === 0) active @endif" aria-current="{{ $index === 0 ? 'true' : 'false' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <section class="hero-section">
            <div class="container hero-content">
                <h1 class="animate__animated animate__fadeInUp">PHONG CÁCH TỐI GIẢN</h1>
                <p class="animate__animated animate__fadeInUp animate__delay-1s">Nâng tầm phong cách hàng ngày với bộ sưu tập được chọn lọc kỹ càng</p>
                <a href="{{ url('/san-pham') }}" class="btn btn-black animate__animated animate__fadeInUp animate__delay-2s">Khám Phá Hàng Mới</a>
            </div>
        </section>
    @endif

    <!-- 2. Featured Categories -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Mua Theo Bộ Sưu Tập</h2>
                <div class="section-subtitle">Khám phá phong cách qua các mùa trong năm</div>
            </div>
            <div class="row">
                @foreach($collections as $collection)
                    <div class="col-6 col-md-3">
                        <div class="category-container">
                            <div class="category-img-wrapper" style="height: 420px;">
                                <img src="{{ $collection->banner ? asset($collection->banner) : 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=600&auto=format&fit=crop' }}" alt="{{ $collection->name }}" class="category-img">
                            </div>
                            <div class="category-overlay">
                                <h3 class="category-title" style="font-size: 22px;">{{ $collection->name }}</h3>
                                <a href="{{ route('collections.show', $collection->slug) }}" class="category-link">Xem Bộ Sưu Tập</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 3. New Arrivals -->
    <section class="py-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Hàng Mới Về</h2>
            </div>
            
            @include('partials.product-grid', ['products' => $newArrivals, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

    <!-- 4. Recommended Products (AI Integrated) -->
    @include('partials.recommended-products', ['products' => $recommendedProducts->all()])

    <!-- 5. Best Sellers -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Bán Chạy Nhất</h2>
                <div class="section-subtitle">Những thiết kế được yêu thích nhất của chúng tôi</div>
            </div>
            
            @include('partials.product-grid', ['products' => $bestSellers, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

{{--
    <!-- 7. Promotional Banner -->
    <section class="promo-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-8 text-start animate__animated animate__fadeInLeft px-4 px-md-0">
                    <span class="text-uppercase tracking-wider font-semibold text-white fs-6 mb-3 d-block">Bộ Sưu Tập Thân Thiện Môi Trường</span>
                    <h2 class="promo-title">BỘ SƯU TẬP BỀN VỮNG</h2>
                    <p class="fs-5 mb-4 text-white opacity-75">Được làm từ 100% vải hữu cơ và tái chế. Thời trang được thiết kế để vừa thanh lịch hôm nay, vừa bảo vệ ngày mai.</p>
                    <a href="{{ route('collections.show', 'bst-ha') }}" class="btn btn-black bg-white text-dark border-white">Khám Phá Bộ Sưu Tập</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Customer Reviews -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Khách Hàng Nói Gì</h2>
                <div class="section-subtitle">Những trải nghiệm thực tế từ cộng đồng khách hàng của chúng tôi</div>
            </div>
            
            @php
                $mockReviews = [
                    [
                        'author' => 'Minh Tuấn',
                        'rating' => 5,
                        'comment' => 'Chất lượng áo linen thực sự xuất sắc. Thiết kế tối giản, vừa vặn hoàn hảo và vải cực kỳ thoáng mát. Ngay lập tức trở thành item không thể thiếu trong tủ đồ hè của tôi.',
                        'date' => '12 tháng 5, 2026'
                    ],
                    [
                        'author' => 'Thu Hà',
                        'rating' => 5,
                        'comment' => 'Lúc đầu tôi còn nghi ngờ về gợi ý từ AI, nhưng nó đề xuất chiếc blazer len may đo vừa như in với vóc dáng của tôi. Thanh toán trơn tru và giao hàng nhanh chóng.',
                        'date' => '01 tháng 6, 2026'
                    ],
                    [
                        'author' => 'Hoàng Long',
                        'rating' => 5,
                        'comment' => 'Dịch vụ khách hàng xuất sắc và bao bì rất đẹp. Rõ ràng thương hiệu này quan tâm đến môi trường, từ chất liệu vải cho đến hộp giao hàng có thể tái chế.',
                        'date' => '10 tháng 6, 2026'
                    ]
                ];
            @endphp
            
            @include('partials.review-list', ['reviews' => $mockReviews])
        </div>
    </section>
--}}
@endsection
