@extends('layouts.app')

@section('title', 'NOIR | Premium Minimalist Fashion Store')

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



    /* Promotional Section */
    .promo-section {
        height: 60vh;
        background-image: linear-gradient(rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.3)), url("{{ asset('images/promo_banner.png') }}");
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        display: flex;
        align-items: center;
        color: #ffffff;
    }

    .promo-title {
        font-size: 3rem;
        margin-bottom: 20px;
    }

    @media (max-width: 768px) {
        .promo-section {
            height: 45vh;
            background-attachment: scroll;
        }
        .promo-title {
            font-size: 2rem;
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
            <h1 class="animate__animated animate__fadeInUp">CHIC MINIMALISM</h1>
            <p class="animate__animated animate__fadeInUp animate__delay-1s">Elevate your everyday wardrobe with our carefully curated pieces</p>
            <a href="{{ url('/products') }}" class="btn btn-black animate__animated animate__fadeInUp animate__delay-2s">Shop New Arrivals</a>
        </div>
    </section>

    <!-- 2. Featured Categories -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Shop by Category</h2>
                <div class="section-subtitle">Discover our main collections</div>
            </div>
            <div class="row">
                <!-- Men -->
                <div class="col-md-4">
                    <div class="category-container">
                        <div class="category-img-wrapper">
                            <img src="{{ asset('images/category_men.png') }}" alt="Men's Collection" class="category-img">
                        </div>
                        <div class="category-overlay">
                            <h3 class="category-title">Men</h3>
                            <a href="{{ url('/men') }}" class="category-link">Explore Collection</a>
                        </div>
                    </div>
                </div>
                <!-- Women -->
                <div class="col-md-4">
                    <div class="category-container">
                        <div class="category-img-wrapper">
                            <img src="{{ asset('images/category_women.png') }}" alt="Women's Collection" class="category-img">
                        </div>
                        <div class="category-overlay">
                            <h3 class="category-title">Women</h3>
                            <a href="{{ url('/women') }}" class="category-link">Explore Collection</a>
                        </div>
                    </div>
                </div>
                <!-- Accessories -->
                <div class="col-md-4">
                    <div class="category-container">
                        <div class="category-img-wrapper">
                            <img src="{{ asset('images/category_acc.png') }}" alt="Accessories Collection" class="category-img">
                        </div>
                        <div class="category-overlay">
                            <h3 class="category-title">Accessories</h3>
                            <a href="{{ url('/products?category=accessories') }}" class="category-link">Explore Collection</a>
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
                <h2 class="section-title">New Arrivals</h2>
                <div class="section-subtitle">Just added to our collection</div>
            </div>
            
            @php
                $newArrivals = [
                    ['id' => 20, 'name' => 'Premium Oversized Trench', 'category' => 'Outerwear', 'price' => 245.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-oversized-trench', 'badge' => 'NEW'],
                    ['id' => 21, 'name' => 'Structured Cotton Shirt', 'category' => 'Shirts', 'price' => 89.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop', 'slug' => 'structured-cotton-shirt'],
                    ['id' => 22, 'name' => 'Classic Straight Jeans', 'category' => 'Denim', 'price' => 120.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-straight-jeans'],
                    ['id' => 23, 'name' => 'Tailored Wool Blazer', 'category' => 'Blazers', 'price' => 195.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1614975058789-41316d0e2e9c?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-wool-blazer'],
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
                <h2 class="section-title">Best Sellers</h2>
                <div class="section-subtitle">Our most popular designs</div>
            </div>
            
            @php
                $bestSellers = [
                    ['id' => 30, 'name' => 'Classic Crewneck Tee', 'category' => 'Basics', 'price' => 35.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-crewneck-tee'],
                    ['id' => 31, 'name' => 'Pima Cotton Polo', 'category' => 'Shirts', 'price' => 65.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1562157873-818bc0726f68?q=80&w=600&auto=format&fit=crop', 'slug' => 'pima-cotton-polo'],
                    ['id' => 32, 'name' => 'Oversized Merino Hoodie', 'category' => 'Sweats', 'price' => 135.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?q=80&w=600&auto=format&fit=crop', 'slug' => 'oversized-merino-hoodie'],
                    ['id' => 33, 'name' => 'Tailored Smart Trousers', 'category' => 'Trousers', 'price' => 110.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1554568218-0f1715e72254?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-smart-trousers'],
                ];
            @endphp
            
            @include('partials.product-grid', ['products' => $bestSellers, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

    <!-- 6. Trending Products -->
    <section class="py-5 bg-light">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">Trending Now</h2>
                <div class="section-subtitle">Highly popular styles of the season</div>
            </div>
            
            @php
                $trendingNow = [
                    ['id' => 40, 'name' => 'Cropped Knit Vest', 'category' => 'Knitwear', 'price' => 75.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=600&auto=format&fit=crop', 'slug' => 'cropped-knit-vest'],
                    ['id' => 41, 'name' => 'Suede Chelsea Boots', 'category' => 'Shoes', 'price' => 210.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=600&auto=format&fit=crop', 'slug' => 'suede-chelsea-boots'],
                    ['id' => 42, 'name' => 'Premium Silk Slip Skirt', 'category' => 'Skirts', 'price' => 125.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1620799139834-6b8f844fbe61?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-silk-slip-skirt'],
                    ['id' => 43, 'name' => 'Minimalist Leather Belt', 'category' => 'Accessories', 'price' => 45.00, 'sale_price' => null, 'image' => 'https://images.unsplash.com/photo-1509319117193-57bab727e09d?q=80&w=600&auto=format&fit=crop', 'slug' => 'minimalist-leather-belt'],
                ];
            @endphp
            
            @include('partials.product-grid', ['products' => $trendingNow, 'cols' => 'col-6 col-md-3'])
        </div>
    </section>

    <!-- 7. Promotional Banner -->
    <section class="promo-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-8 text-start animate__animated animate__fadeInLeft px-4 px-md-0">
                    <span class="text-uppercase tracking-wider font-semibold text-white fs-6 mb-3 d-block">Eco-Conscious Edition</span>
                    <h2 class="promo-title">THE SUSTAINABLE COLLECTION</h2>
                    <p class="fs-5 mb-4 text-white opacity-75">Crafted from 100% organic and recycled fabrics. Fashion designed to look elegant today and protect tomorrow.</p>
                    <a href="{{ url('/products?collection=sustainable') }}" class="btn btn-black bg-white text-dark border-white">Explore Collection</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 8. Customer Reviews -->
    <section class="py-5 my-5">
        <div class="container-fluid px-lg-5">
            <div class="section-header text-center">
                <h2 class="section-title">What Our Customers Say</h2>
                <div class="section-subtitle">Real experiences from our global community</div>
            </div>
            
            @php
                $mockReviews = [
                    [
                        'author' => 'Alexander V.',
                        'rating' => 5,
                        'comment' => 'The quality of the linen shirt is unparalleled. Minimal design, perfect sizing, and extremely breathable fabric. It has immediately become a staple in my summer wardrobe.',
                        'date' => 'May 12, 2026'
                    ],
                    [
                        'author' => 'Sophia M.',
                        'rating' => 5,
                        'comment' => 'I was skeptical about the AI recommendation at first, but it suggested a tailored wool blazer that fits me like a glove. The checkout was seamless and shipping was fast.',
                        'date' => 'Jun 01, 2026'
                    ],
                    [
                        'author' => 'Liam H.',
                        'rating' => 5,
                        'comment' => 'Outstanding customer service and beautiful packaging. You can tell this brand cares about sustainability, from their fabrics to their recyclable shipping boxes.',
                        'date' => 'Jun 10, 2026'
                    ]
                ];
            @endphp
            
            @include('partials.review-list', ['reviews' => $mockReviews])
        </div>
    </section>
@endsection
