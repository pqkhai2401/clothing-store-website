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
    <section class="hero-section">
        <div class="container hero-content">
            <h1 class="animate__animated animate__fadeInUp">PHONG CÁCH TỐI GIẢN</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">Nâng tầm phong cách hàng ngày với bộ sưu tập được chọn lọc kỹ càng</p>
            <a href="{{ url('/products') }}" class="btn btn-black animate__animated animate__fadeInUp animate__delay-2s">Khám Phá Hàng Mới</a>
        </div>
    </section>

    <!-- 2. Featured Categories -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Mua Theo Danh Mục</h2>
                <div class="section-subtitle">Khám phá các bộ sưu tập chính của chúng tôi</div>
            </div>
            <div class="row">
                <!-- Men -->
                <div class="col-md-6">
                    <div class="category-container">
                        <div class="category-img-wrapper">
                            <img src="{{ asset('images/category_men.png') }}" alt="Men's Collection" class="category-img">
                        </div>
                        <div class="category-overlay">
                            <h3 class="category-title">Nam</h3>
                            <a href="{{ url('/men') }}" class="category-link">Xem Bộ Sưu Tập</a>
                        </div>
                    </div>
                </div>
                <!-- Women -->
                <div class="col-md-6">
                    <div class="category-container">
                        <div class="category-img-wrapper">
                            <img src="{{ asset('images/category_women.png') }}" alt="Bộ Sưu Tập Nữ" class="category-img">
                        </div>
                        <div class="category-overlay">
                            <h3 class="category-title">Nữ</h3>
                            <a href="{{ url('/women') }}" class="category-link">Xem Bộ Sưu Tập</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. New Arrivals -->
    <section class="py-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Hàng Mới Về</h2>
                <div class="section-subtitle">Vừa được thêm vào bộ sưu tập</div>
            </div>
            
            @php
                $newArrivals = [
                    ['id' => 20, 'name' => 'Áo Khoác Trench Oversized Cao Cấp', 'category' => 'Áo khoác', 'price' => 2450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-oversized-trench', 'badge' => 'MỚI'],
                    ['id' => 21, 'name' => 'Áo Sơ Mi Cotton Dáng Suông', 'category' => 'Áo sơ mi', 'price' => 890000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop', 'slug' => 'structured-cotton-shirt'],
                    ['id' => 22, 'name' => 'Quần Jean Thẳng Cổ Điển', 'category' => 'Denim', 'price' => 1200000, 'discount' => 20, 'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-straight-jeans'],
                    ['id' => 23, 'name' => 'Áo Blazer Len May Đo', 'category' => 'Blazer', 'price' => 1950000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1614975058789-41316d0e2e9c?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-wool-blazer'],
                ];
            @endphp
            
            @include('partials.product-grid', ['products' => $newArrivals, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

    <!-- 4. Recommended Products (AI Integrated) -->
    @include('partials.recommended-products')

    <!-- 5. Best Sellers -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Bán Chạy Nhất</h2>
                <div class="section-subtitle">Những thiết kế được yêu thích nhất của chúng tôi</div>
            </div>
            
            @php
                $bestSellers = [
                    ['id' => 30, 'name' => 'Áo Thun Cổ Tròn Cổ Điển', 'category' => 'Cơ bản', 'price' => 350000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-crewneck-tee'],
                    ['id' => 31, 'name' => 'Áo Polo Cotton Pima', 'category' => 'Áo sơ mi', 'price' => 650000, 'discount' => 15, 'image' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?q=80&w=600&auto=format&fit=crop', 'slug' => 'pima-cotton-polo'],
                    ['id' => 32, 'name' => 'Áo Hoodie Len Merino Oversized', 'category' => 'Áo nỉ', 'price' => 1350000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?q=80&w=600&auto=format&fit=crop', 'slug' => 'oversized-merino-hoodie'],
                    ['id' => 33, 'name' => 'Quần Âu Dáng Suông', 'category' => 'Quần dài', 'price' => 1100000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-smart-trousers'],
                ];
            @endphp
            
            @include('partials.product-grid', ['products' => $bestSellers, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

    <!-- 6. products with the most views -->
    <section class="py-5 bg-light">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">ĐƯỢC QUAN TÂM NHIỀU NHẤT</h2>
                <div class="section-subtitle">Khám phá những sản phẩm được xem nhiều nhất trên cửa hàng.</div>
            </div>
            
            @php
                $trendingNow = [
                    ['id' => 40, 'name' => 'Áo Gile Len Croptop', 'category' => 'Áo len', 'price' => 750000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=600&auto=format&fit=crop', 'slug' => 'cropped-knit-vest'],
                    ['id' => 41, 'name' => 'Giày Chelsea Boots Da Lộn', 'category' => 'Giày', 'price' => 2100000, 'discount' => 10, 'image' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=600&auto=format&fit=crop', 'slug' => 'suede-chelsea-boots'],
                    ['id' => 42, 'name' => 'Chân Váy Lụa Cao Cấp', 'category' => 'Chân váy', 'price' => 1250000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1620799139834-6b8f844fbe61?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-silk-slip-skirt'],
                    ['id' => 43, 'name' => 'Thắt Lưng Da Tối Giản', 'category' => 'Phụ kiện', 'price' => 450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1509319117193-57bab727e09d?q=80&w=600&auto=format&fit=crop', 'slug' => 'minimalist-leather-belt'],
                ];
            @endphp
            
            @include('partials.product-grid', ['products' => $trendingNow, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

@endsection
