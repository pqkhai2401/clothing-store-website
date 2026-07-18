@extends('layouts.app')

@section('title', $product->name . ' | HK Store')

@section('css')
<style>
    /* ===== Gallery Khu vực ảnh sản phẩm ===== */
    .product-gallery {
        position: sticky;
        top: 20px;
    }

    .gallery-main-img {
        width: 100%;
        height: auto;
        object-fit: cover;
        background-color: var(--hover-bg);
    }

    .gallery-thumbs {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 600px;
        overflow-y: auto;
    }

    .gallery-thumb {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
        opacity: 0.6;
        transition: all 0.2s ease;
        background-color: var(--hover-bg);
    }

    .gallery-thumb:hover,
    .gallery-thumb.active {
        opacity: 1;
        border-color: var(--primary-color);
    }

    /* ===== Thông tin sản phẩm bên phải ===== */
    .product-detail-info {
        padding-left: 30px;
    }

    @media (max-width: 992px) {
        .product-detail-info {
            padding-left: 0;
            margin-top: 30px;
        }
    }

    .product-detail-category {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--muted-text);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .product-detail-title {
        font-family: var(--font-serif);
        font-size: 32px;
        font-weight: 400;
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .product-sku {
        font-size: 12px;
        color: var(--muted-text);
        margin-bottom: 15px;
        letter-spacing: 0.5px;
    }

    .product-detail-price {
        font-size: 22px;
        font-weight: 600;
        margin-bottom: 25px;
    }

    .product-detail-price .original-price {
        text-decoration: line-through;
        color: var(--muted-text);
        font-weight: 400;
        margin-right: 10px;
        font-size: 18px;
    }

    .product-detail-price .sale-price {
        color: #d9534f;
    }

    .product-detail-price .discount-badge {
        font-size: 12px;
        background-color: #d9534f;
        color: #fff;
        padding: 2px 8px;
        margin-left: 10px;
        font-weight: 600;
    }

    /* ===== Chọn biến thể: Màu sắc ===== */
    .variant-selector-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 10px;
        color: var(--primary-color);
    }

    .color-picker {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }

    .color-option {
        min-width: 70px;
        height: 38px;
        padding: 0 14px;
        border: 1px solid var(--border-color);
        background-color: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        text-transform: capitalize;
    }

    .color-option:hover {
        border-color: var(--primary-color);
    }

    .color-option.active {
        background-color: var(--primary-color);
        color: #ffffff;
        border-color: var(--primary-color);
    }

    /* ===== Chọn biến thể: Kích thước ===== */
    .size-picker {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 25px;
    }

    .size-option {
        min-width: 48px;
        height: 44px;
        padding: 0 14px;
        border: 1px solid var(--border-color);
        background-color: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .size-option:hover {
        border-color: var(--primary-color);
    }

    .size-option.active {
        background-color: var(--primary-color);
        color: #ffffff;
        border-color: var(--primary-color);
    }

    /* ===== Thông tin tồn kho ===== */
    .stock-info {
        font-size: 13px;
        margin-bottom: 20px;
        min-height: 20px;
    }

    .stock-info .in-stock {
        color: #28a745;
        font-weight: 500;
    }

    .stock-info .out-of-stock {
        color: #d9534f;
        font-weight: 600;
    }

    /* ===== Số lượng & Nút hành động ===== */
    .purchase-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 15px;
    }

    .quantity-selector {
        display: flex;
        border: 1px solid var(--border-color);
        height: 50px;
        align-items: center;
    }

    .quantity-btn {
        background: none;
        border: none;
        width: 40px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 16px;
    }

    .quantity-input {
        border: none;
        width: 40px;
        text-align: center;
        font-size: 14px;
        font-weight: 500;
        outline: none;
    }

    .btn-wishlist-action {
        width: 50px;
        height: 50px;
        border: 1px solid var(--border-color);
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }

    .btn-wishlist-action:hover {
        border-color: var(--primary-color);
        color: #d9534f;
    }

    .action-buttons {
        display: flex;
        gap: 12px;
        margin-bottom: 30px;
    }

    .btn-add-cart {
        flex: 0 0 auto;
        height: 46px;
        padding: 0 30px;
    }

    .btn-buy-now {
        flex: 0 0 auto;
        height: 46px;
        padding: 0 30px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    /* ===== Accordion Mô tả & Bảng size ===== */
    .product-info-accordion {
        border-top: 1px solid var(--border-color);
    }

    .product-info-accordion .accordion-item {
        border: none;
        border-bottom: 1px solid var(--border-color);
        border-radius: 0;
        background: transparent;
    }

    .product-info-accordion .accordion-button {
        font-family: var(--font-sans);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--primary-color);
        padding: 18px 0;
        background: transparent;
        box-shadow: none;
        border-radius: 0;
    }

    .product-info-accordion .accordion-button:not(.collapsed) {
        background: transparent;
        color: var(--primary-color);
        box-shadow: none;
    }

    .product-info-accordion .accordion-body {
        padding: 0 0 18px 0;
        font-size: 13px;
        line-height: 1.8;
        color: var(--muted-text);
    }

    /* ===== Bảng size (partials.size-chart) ===== */
    .size-chart-tabs {
        border-bottom: 1px solid var(--border-color);
        gap: 4px;
    }

    .size-chart-tabs .nav-link {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted-text);
        border: none;
        border-bottom: 2px solid transparent;
        border-radius: 0;
        padding: 8px 14px;
    }

    .size-chart-tabs .nav-link.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        background: transparent;
    }

    .size-chart-table {
        font-size: 12px;
    }

    .size-chart-table th {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--primary-color);
        border-bottom: 1px solid var(--border-color);
        white-space: nowrap;
    }

    .size-chart-table td {
        vertical-align: middle;
        color: var(--secondary-color);
    }

    /* ===== Sản phẩm liên quan ===== */
    .related-section-title {
        font-family: var(--font-serif);
        font-size: 28px;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-align: center;
        margin-bottom: 10px;
    }

    .related-section-subtitle {
        font-size: 13px;
        color: var(--muted-text);
        text-align: center;
        margin-bottom: 40px;
    }

    /* ===== Khu vực Đánh giá sản phẩm ===== */
    .reviews-section {
        border-top: 1px solid var(--border-color, #e5e5e5);
        padding-top: 40px;
        margin-top: 20px;
    }

    .reviews-section-title {
        font-family: var(--font-serif);
        font-size: 26px;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    /* Bộ chấm sao trong FORM (cho phép click chọn) — dùng radio ẩn + label icon.
       Kỹ thuật: đảo chiều flex (row-reverse) để hiệu ứng hover/checked tô sáng
       từ sao đang trỏ trở về sao 1 bằng CSS thuần, không cần JS. */
    .star-rating-input {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 6px;
        font-size: 30px;
        line-height: 1;
    }

    .star-rating-input input[type="radio"] {
        display: none;
    }

    .star-rating-input label {
        color: #d9d9d9;
        cursor: pointer;
        transition: color 0.15s ease;
    }

    .star-rating-input label:hover,
    .star-rating-input label:hover ~ label,
    .star-rating-input input[type="radio"]:checked ~ label {
        color: #f5b301; /* Vàng hổ phách sang trọng */
    }

    /* Nút gửi đánh giá: vuông vức, sang trọng theo phong cách HK Store */
    .btn-submit-review {
        border-radius: 0;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 13px;
        font-weight: 600;
        padding: 14px 40px;
        background-color: var(--primary-color, #111);
        color: #fff;
        border: none;
        transition: opacity 0.2s ease;
    }

    .btn-submit-review:hover {
        opacity: 0.85;
        color: #fff;
    }

    /* Card hiển thị từng đánh giá đã duyệt */
    .review-item {
        border-bottom: 1px solid var(--border-color, #eee);
        padding: 20px 0;
    }

    .review-item:last-child {
        border-bottom: none;
    }

    .review-stars-static {
        color: #f5b301;
        font-size: 15px;
        letter-spacing: 2px;
    }

    .review-author {
        font-weight: 600;
        font-size: 14px;
    }

    /* Ảnh đại diện người đánh giá */
    .review-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid var(--border-color, #e5e5e5);
        background-color: var(--hover-bg, #f3f3f3);
    }

    .review-date {
        font-size: 12px;
        color: var(--muted-text);
    }

    .review-comment {
        font-size: 14px;
        line-height: 1.6;
        margin-top: 8px;
        color: var(--body-text, #333);
    }

    /* Ô nhắc điều kiện đánh giá khi user chưa đủ điều kiện */
    .review-notice {
        border: 1px dashed var(--border-color, #ccc);
        padding: 20px;
        text-align: center;
        font-size: 14px;
        color: var(--muted-text);
    }
</style>
@endsection

@php
    // Hàm xử lý đường dẫn ảnh thông minh:
    // - Nếu null/rỗng -> trả về ảnh placeholder mặc định
    // - Nếu là URL tuyệt đối (http/https) -> dùng trực tiếp
    // - Nếu là đường dẫn tương đối -> bọc qua asset()
    function productImage($path) {
        $placeholder = 'https://placehold.co/800x1000?text=No+Image';
        if (empty($path)) {
            return $placeholder;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (str_starts_with($path, 'storage/')) {
            return asset($path);
        }
        return asset('storage/' . $path);
    }
@endphp

@section('content')

    {{-- ===== Breadcrumb điều hướng ===== --}}
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => $product->category->name ?? 'Sản phẩm', 'url' => url('/danh-muc/' . ($product->category->slug ?? ''))],
            ['name' => $product->name, 'active' => true],
        ]
    ])

    <div class="container-fluid px-lg-5 mt-4">
        {{-- Nút "Quay lại" đặt ngay dưới breadcrumb, trên cùng khối nội dung --}}
        <x-back-button />

        {{-- ===== Khu vực chính: Ảnh bên trái + Thông tin bên phải ===== --}}
        <div class="row mb-5">

            {{-- ===== CỘT TRÁI: Gallery ảnh sản phẩm ===== --}}
            <div class="col-lg-7">
                <div class="product-gallery">
                    <div class="row">
                        {{-- Cột thumbnail dọc --}}
                        <div class="col-2">
                            <div class="gallery-thumbs">
                                {{-- Thumbnail ảnh chính (thumbnail của sản phẩm) --}}
                                {{-- Thumbnail chính của sản phẩm --}}
                                <img
                                    src="{{ productImage($product->thumbnail) }}"
                                    alt="{{ $product->name }}"
                                    class="gallery-thumb active"
                                    onclick="changeMainImage(this)"
                                >

                                {{-- Các ảnh phụ từ bảng product_images --}}
                                @foreach($product->productImages as $img)
                                    <img
                                        src="{{ productImage($img->image) }}"
                                        alt="{{ $product->name }}"
                                        class="gallery-thumb"
                                        onclick="changeMainImage(this)"
                                    >
                                @endforeach
                            </div>
                        </div>

                        {{-- Ảnh lớn chính --}}
                        <div class="col-10">
                            <img
                                id="mainProductImage"
                                src="{{ productImage($product->thumbnail) }}"
                                alt="{{ $product->name }}"
                                class="gallery-main-img"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== CỘT PHẢI: Thông tin sản phẩm ===== --}}
            <div class="col-lg-5">
                <div class="product-detail-info">

                    {{-- Danh mục --}}
                    <div class="product-detail-category">
                        {{ $product->category->name ?? '' }}
                    </div>

                    {{-- Tên sản phẩm --}}
                    <h1 class="product-detail-title">{{ $product->name }}</h1>

                    {{-- Mã SKU (hiển thị SKU của biến thể mặc định, sẽ cập nhật bằng JS) --}}
                    <div class="product-sku">
                        SKU: <span id="productSku">{{ $defaultVariant->sku ?? 'N/A' }}</span>
                    </div>

                    {{-- Giá sản phẩm --}}
                    <div class="product-detail-price" id="productPriceBlock">
                        @php
                            $minPrice = $product->min_variant_price;
                            $maxPrice = $product->max_variant_price;
                            $minFinal = $minPrice !== null ? $product->discountedPrice($minPrice) : null;
                            $maxFinal = $maxPrice !== null ? $product->discountedPrice($maxPrice) : null;
                        @endphp
                        @if($minPrice === null)
                            <span>Liên hệ</span>
                        @elseif($product->hasActiveDiscount())
                            <span class="original-price">{{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ</span>
                            <span class="sale-price">{{ number_format($minFinal, 0, ',', '.') }}đ - {{ number_format($maxFinal, 0, ',', '.') }}đ</span>
                        @else
                            @if($minPrice == $maxPrice)
                                <span>{{ number_format($minPrice, 0, ',', '.') }}đ</span>
                            @else
                                <span>{{ number_format($minPrice, 0, ',', '.') }}đ - {{ number_format($maxPrice, 0, ',', '.') }}đ</span>
                            @endif
                        @endif
                    </div>

                    {{-- ===== NHÓM 1: Chọn Màu sắc (dạng nút bấm) ===== --}}
                    <div class="mb-3">
                        <div class="variant-selector-title">Màu sắc</div>
                        <div class="color-picker" id="colorPicker">
                            @foreach($colors as $color)
                                <button
                                    type="button"
                                    class="color-option"
                                    data-color-id="{{ $color->id }}"
                                >
                                    {{ $color->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- ===== NHÓM 2: Chọn Kích thước (dạng nút bấm) ===== --}}
                    <div class="mb-3">
                        <div class="variant-selector-title">Kích thước</div>
                        <div class="size-picker" id="sizePicker">
                            @foreach($sizes as $size)
                                <button
                                    type="button"
                                    class="size-option"
                                    data-size-id="{{ $size->id }}"
                                >
                                    {{ $size->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Thông tin tồn kho (cập nhật động qua JS) --}}
                    <div class="stock-info" id="stockInfo"></div>

                    {{-- Số lượng + Nút yêu thích --}}
                    <div class="purchase-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn" type="button" id="btnDecrease">−</button>
                            <input type="text" value="1" id="qtyInput" class="quantity-input" readonly>
                            <button class="quantity-btn" type="button" id="btnIncrease">+</button>
                        </div>

                        <button class="btn-wishlist-action" type="button"
                                id="btnToggleWishlist"
                                title="Thêm vào yêu thích"
                                data-product-id="{{ $product->id }}"
                                data-in-wishlist="{{ $isInWishlist ?? false ? 'true' : 'false' }}">
                            <i class="bi {{ ($isInWishlist ?? false) ? 'bi-heart-fill' : 'bi-heart' }}"
                               style="{{ ($isInWishlist ?? false) ? 'color:#d9534f;' : '' }}"></i>
                        </button>
                    </div>

                    {{-- Nút Thêm giỏ hàng + Mua ngay --}}
                    <div class="action-buttons">
                        <button class="btn btn-outline-dark btn-add-cart" type="button" id="btnAddCart">
                            <i class="bi bi-bag"></i> Thêm vào giỏ hàng
                        </button>
                        <button class="btn btn-dark btn-buy-now" type="button" id="btnBuyNow">
                            Mua ngay
                        </button>
                    </div>

                    {{-- ===== Accordion: Thông tin chi tiết & Bảng size ===== --}}
                    <div class="accordion product-info-accordion" id="productAccordion">
                        {{-- Mô tả sản phẩm --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDesc">
                                    Thông tin chi tiết
                                </button>
                            </h2>
                            <div id="collapseDesc" class="accordion-collapse collapse" data-bs-parent="#productAccordion">
                                <div class="accordion-body">
                                    {!! $product->description !!}
                                </div>
                            </div>
                        </div>

                        {{-- Bảng size --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSize">
                                    Bảng size
                                </button>
                            </h2>
                            <div id="collapseSize" class="accordion-collapse collapse" data-bs-parent="#productAccordion">
                                <div class="accordion-body">
                                    <p class="mb-3">Vui lòng tham khảo bảng size chuẩn của HK Store để chọn size phù hợp nhất với bạn.</p>
                                    @include('partials.size-chart', ['gender' => $product->gender])
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ============================================================= --}}
        {{-- ===== KHU VỰC ĐÁNH GIÁ SẢN PHẨM (REVIEWS) ================== --}}
        {{-- ============================================================= --}}
        <section class="reviews-section">
            <div class="row">
                <div class="col-lg-10 mx-auto">

                    {{-- Tiêu đề + điểm trung bình --}}
                    <h2 class="reviews-section-title">Đánh giá sản phẩm</h2>
                    <div class="mb-4">
                        @if($reviews->count() > 0)
                            <span class="review-stars-static">
                                {{-- Vẽ 5 sao theo điểm trung bình (làm tròn) --}}
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= round($averageRating) ? 'bi-star-fill' : 'bi-star' }}"></i>
                                @endfor
                            </span>
                            <span class="ms-2 fw-semibold">{{ number_format($averageRating, 1) }}/5</span>
                            <span class="review-date">({{ $reviews->count() }} đánh giá)</span>
                        @else
                            <span class="review-date">Chưa có đánh giá nào cho sản phẩm này.</span>
                        @endif
                    </div>

                    {{-- ----- Thông báo flash sau khi gửi đánh giá ----- --}}
                    @if(session('success'))
                        <div class="alert alert-success rounded-0">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger rounded-0">{{ session('error') }}</div>
                    @endif
                    {{-- Hiển thị lỗi validate (nếu có) --}}
                    @if($errors->any())
                        <div class="alert alert-danger rounded-0">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- ----- FORM VIẾT ĐÁNH GIÁ ----- --}}
                    {{--
                        Logic hiển thị (điều kiện lấy từ Controller):
                        1. Chưa đăng nhập  -> mời đăng nhập.
                        2. Đã đánh giá rồi -> báo đã đánh giá.
                        3. $canReview = true (đã mua + hoàn thành + chưa đánh giá) -> hiện form.
                        4. Còn lại -> nhắc "cần mua sản phẩm này để đánh giá".
                    --}}
                    @auth
                        @if($canReview)
                            <div class="mb-5">
                                <h5 class="mb-3">Viết đánh giá của bạn</h5>
                                <form action="{{ route('reviews.store', $product->id) }}" method="POST">
                                    @csrf

                                    {{-- Bộ chấm sao 1-5: dùng radio ẩn, click label để chọn --}}
                                    <div class="mb-3">
                                        <label class="form-label d-block mb-2">Chất lượng sản phẩm</label>
                                        <div class="star-rating-input">
                                            {{-- Đặt từ 5 -> 1 để kết hợp với CSS row-reverse tô sáng đúng chiều --}}
                                            @for($star = 5; $star >= 1; $star--)
                                                <input type="radio"
                                                       id="star{{ $star }}"
                                                       name="rating"
                                                       value="{{ $star }}"
                                                       {{ old('rating') == $star ? 'checked' : '' }}>
                                                <label for="star{{ $star }}" title="{{ $star }} sao">
                                                    <i class="bi bi-star-fill"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>

                                    {{-- Ô nhập nội dung bình luận --}}
                                    <div class="mb-3">
                                        <label for="comment" class="form-label">Nội dung đánh giá</label>
                                        <textarea name="comment"
                                                  id="comment"
                                                  rows="4"
                                                  class="form-control rounded-0"
                                                  maxlength="1000"
                                                  placeholder="Chia sẻ cảm nhận của bạn về chất liệu, kích cỡ, dịch vụ...">{{ old('comment') }}</textarea>
                                    </div>

                                    <button type="submit" class="btn btn-submit-review">Gửi đánh giá</button>
                                </form>
                            </div>
                        @elseif($hasReviewed)
                            <div class="review-notice mb-5">
                                Bạn đã đánh giá sản phẩm này. Cảm ơn bạn đã đóng góp ý kiến!
                            </div>
                        @else
                            <div class="review-notice mb-5">
                                Bạn cần mua sản phẩm này để có thể đánh giá.
                            </div>
                        @endif
                    @else
                        <div class="review-notice mb-5">
                            Vui lòng <a href="{{ route('auth.loginpage') }}">đăng nhập</a> để viết đánh giá.
                            Bạn cần mua sản phẩm này để có thể đánh giá.
                        </div>
                    @endauth

                    {{-- ----- DANH SÁCH ĐÁNH GIÁ ĐÃ DUYỆT (approved) ----- --}}
                    <div class="reviews-list">
                        @forelse($reviews as $review)
                            @php
                                // Tên hiển thị + ảnh đại diện của người đánh giá.
                                // Nếu user có avatar_url -> lấy từ storage hoặc Google; nếu không -> ảnh chữ cái tự sinh.
                                $rvAuthor = $review->user->username ?? $review->user->name ?? 'Khách hàng';
                                $rvAvatar = $review->user->avatar_display_url
                                    ?: 'https://ui-avatars.com/api/?name=' . urlencode($rvAuthor) . '&background=random';
                            @endphp
                            <div class="review-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="d-flex align-items-center gap-2">
                                        {{-- Ảnh đại diện người dùng --}}
                                        <img src="{{ $rvAvatar }}" alt="{{ $rvAuthor }}" class="review-avatar">
                                        <div>
                                            <span class="review-author">{{ $rvAuthor }}</span>
                                            <span class="badge bg-success ms-1" style="border-radius:0;font-weight:400;">
                                                <i class="bi bi-patch-check-fill"></i> Đã mua hàng
                                            </span>
                                        </div>
                                    </div>
                                    <span class="review-date">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>

                                {{-- Số sao của đánh giá --}}
                                <div class="review-stars-static mt-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>

                                <p class="review-comment">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <p class="review-date">Hãy là người đầu tiên đánh giá sản phẩm này!</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </section>

        {{-- ===== GỢI Ý PHỐI CÙNG (MIX & MATCH — AI STYLIST) ===== --}}
        @if($mixAndMatchProducts->count() > 0)
            <section class="py-5 my-3">
                <h2 class="related-section-title">Phối cùng phong cách</h2>
                <div class="related-section-subtitle">AI Stylist gợi ý các món ghép thành bộ hoàn chỉnh</div>

                @include('partials.product-grid', [
                    'products' => $mixAndMatchProducts,
                    'cols' => 'col-6 col-md-3'
                ])
            </section>
        @endif

        {{-- ===== SẢN PHẨM LIÊN QUAN ===== --}}
        @if($relatedProducts->count() > 0)
            <section class="py-5 my-3">
                <h2 class="related-section-title">Sản phẩm liên quan</h2>
                <div class="related-section-subtitle">Các sản phẩm cùng danh mục bạn có thể thích</div>

                @include('partials.product-grid', [
                    'products' => $relatedProducts,
                    'cols' => 'col-6 col-md-3'
                ])
            </section>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    (function () {
        // ===== Biến lưu trạng thái lựa chọn hiện tại =====
        const productId = {{ $product->id }};
        const originalThumbnail = "{{ productImage($product->thumbnail) }}";
        let selectedColorId = null;
        let selectedSizeId = null;
        let currentStock = 0;

        // ===== Tham chiếu đến các phần tử DOM =====
        const colorBtns = document.querySelectorAll('#colorPicker .color-option');
        const sizeBtns = document.querySelectorAll('#sizePicker .size-option');
        const skuEl = document.getElementById('productSku');
        const priceBlock = document.getElementById('productPriceBlock');
        const stockInfo = document.getElementById('stockInfo');
        const qtyInput = document.getElementById('qtyInput');
        const btnAddCart = document.getElementById('btnAddCart');
        const btnBuyNow = document.getElementById('btnBuyNow');
        const mainImage = document.getElementById('mainProductImage');

        // ===== Xử lý click chọn Màu sắc =====
        colorBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Bỏ active tất cả, thêm active cho nút được chọn
                colorBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                selectedColorId = parseInt(btn.getAttribute('data-color-id'));

                // Cập nhật giá real-time ngay khi chọn màu (dù chưa chọn size)
                checkVariant();
            });
        });

        // ===== Xử lý click chọn Kích thước =====
        sizeBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Bỏ active tất cả, thêm active cho nút được chọn
                sizeBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                selectedSizeId = parseInt(btn.getAttribute('data-size-id'));

                // Cập nhật giá real-time ngay khi chọn size (dù chưa chọn màu)
                checkVariant();
            });
        });

        // ===== Gọi API kiểm tra biến thể (Fetch API) =====
        // Được gọi ngay khi chọn Màu HOẶC Size (không cần chọn đủ cả 2):
        // - Chỉ chọn 1 trong 2 -> API trả về khoảng giá (min-max) của các biến thể khớp
        // - Chọn đủ cả 2 -> API trả về đúng 1 biến thể (giá, tồn kho, SKU, ảnh)
        function checkVariant() {
            fetch('/api/products/check-variant', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    product_id: productId,
                    color_id: selectedColorId,
                    size_id: selectedSizeId
                })
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.found) {
                    // Không có biến thể nào khớp với lựa chọn hiện tại
                    skuEl.textContent = 'N/A';
                    currentStock = 0;
                    stockInfo.innerHTML = '<span class="out-of-stock">Biến thể không có sẵn</span>';
                    disableButtons();
                    return;
                }

                if (data.mode === 'exact') {
                    // Đã chọn đủ Màu + Size -> hiển thị giá, tồn kho, SKU, ảnh của đúng biến thể đó
                    skuEl.textContent = data.sku || 'N/A';
                    updatePriceDisplay(data.price, data.price, data.has_discount, data.final_price, data.final_price);

                    currentStock = data.stock;
                    updateStockDisplay(data.stock);

                    if (data.image) {
                        mainImage.src = resolveImageUrl(data.image);
                    } else {
                        mainImage.src = originalThumbnail;
                    }
                } else {
                    // Mới chọn Màu hoặc Size (chưa đủ cả 2) -> chỉ cập nhật khoảng giá, chưa xác định tồn kho/SKU
                    skuEl.textContent = 'N/A';
                    updatePriceDisplay(data.min_price, data.max_price, data.has_discount, data.min_final, data.max_final);

                    currentStock = 0;
                    stockInfo.innerHTML = '';
                }
            })
            .catch(function () {
                stockInfo.innerHTML = '<span class="out-of-stock">Lỗi khi kiểm tra. Vui lòng thử lại.</span>';
            });
        }

        // ===== Cập nhật hiển thị giá (hỗ trợ cả giá đơn và khoảng giá min-max) =====
        function updatePriceDisplay(minPrice, maxPrice, hasDiscount, minFinal, maxFinal) {
            var minNum = parseFloat(minPrice) || 0;
            var maxNum = parseFloat(maxPrice) || 0;
            var minFinalNum = parseFloat(minFinal) || 0;
            var maxFinalNum = parseFloat(maxFinal) || 0;
            var isRange = minNum !== maxNum;

            function fmtRange(a, b) {
                return isRange ? (formatCurrency(a) + 'đ - ' + formatCurrency(b) + 'đ') : (formatCurrency(a) + 'đ');
            }

            if (hasDiscount) {
                priceBlock.innerHTML =
                    '<span class="original-price">' + fmtRange(minNum, maxNum) + '</span>' +
                    '<span class="sale-price">' + fmtRange(minFinalNum, maxFinalNum) + '</span>';
            } else {
                priceBlock.innerHTML = '<span>' + fmtRange(minNum, maxNum) + '</span>';
            }
        }

        // ===== Cập nhật hiển thị tồn kho =====
        function updateStockDisplay(stock) {
            if (stock > 0) {
                stockInfo.innerHTML = '<span class="in-stock">Còn ' + stock + ' sản phẩm</span>';
                enableButtons();
            } else {
                stockInfo.innerHTML = '<span class="out-of-stock">Hết hàng</span>';
                disableButtons();
            }
        }

        // ===== Vô hiệu hóa nút khi hết hàng =====
        function disableButtons() {
            btnAddCart.disabled = true;
            btnAddCart.classList.add('disabled');
            btnBuyNow.disabled = true;
            btnBuyNow.classList.add('disabled');
            btnBuyNow.textContent = 'Hết hàng';
        }

        // ===== Kích hoạt lại nút khi còn hàng =====
        function enableButtons() {
            btnAddCart.disabled = false;
            btnAddCart.classList.remove('disabled');
            btnBuyNow.disabled = false;
            btnBuyNow.classList.remove('disabled');
            btnBuyNow.textContent = 'Mua ngay';
        }

        // ===== Xử lý đường dẫn ảnh (URL tuyệt đối hoặc tương đối) =====
        function resolveImageUrl(path) {
            if (!path) return 'https://placehold.co/800x1000?text=No+Image';
            if (path.startsWith('http://') || path.startsWith('https://')) return path;
            return '/storage/' + path;
        }

        // ===== Định dạng tiền tệ VNĐ =====
        function formatCurrency(num) {
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // ===== Xử lý nút tăng/giảm số lượng =====
        document.getElementById('btnIncrease').addEventListener('click', function () {
            var val = parseInt(qtyInput.value);
            var max = currentStock > 0 ? currentStock : 99;
            if (!isNaN(val) && val < max) {
                qtyInput.value = val + 1;
            }
        });

        document.getElementById('btnDecrease').addEventListener('click', function () {
            var val = parseInt(qtyInput.value);
            if (!isNaN(val) && val > 1) {
                qtyInput.value = val - 1;
            }
        });

        function selectedQuantity() {
            const quantity = parseInt(qtyInput.value, 10);
            return Number.isInteger(quantity) && quantity > 0 ? quantity : 1;
        }

        function validateCartSelection() {
            if (!selectedColorId) {
                window.showToast('Vui lòng chọn màu sắc.', 'error');
                return false;
            }

            if (!selectedSizeId) {
                window.showToast('Vui lòng chọn kích thước.', 'error');
                return false;
            }

            if (currentStock <= 0) {
                window.showToast('Biến thể đã chọn hiện đã hết hàng.', 'error');
                return false;
            }

            if (selectedQuantity() > currentStock) {
                window.showToast('Số lượng vượt quá tồn kho hiện có.', 'error');
                return false;
            }

            return true;
        }

        async function addSelectedVariantToCart(redirectToCheckout) {
            if (!validateCartSelection()) return;

            const targetButton = redirectToCheckout ? btnBuyNow : btnAddCart;
            const originalHtml = targetButton.innerHTML;
            targetButton.disabled = true;
            targetButton.innerHTML = redirectToCheckout ? 'Đang xử lý...' : 'Đang thêm...';

            try {
                const response = await fetch('{{ route('cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        color_id: selectedColorId,
                        size_id: selectedSizeId,
                        quantity: selectedQuantity()
                    })
                });

                if (response.status === 401) {
                    window.location.href = '{{ route('auth.loginpage') }}';
                    return;
                }

                const data = await response.json();

                if (!response.ok) {
                    const message = data.message || Object.values(data.errors || {})?.[0]?.[0] || 'Không thể thêm sản phẩm vào giỏ hàng.';
                    window.showToast(message, 'error');
                    return;
                }

                if (data.cart_count !== undefined) {
                    window.updateCartBadge(data.cart_count);
                }

                if (redirectToCheckout) {
                    // "Mua ngay" -> chuyển thẳng sang trang thanh toán
                    window.location.href = data.checkout_url || '{{ route('checkout.index') }}';
                } else {
                    // "Thêm vào giỏ hàng" -> ở lại trang chi tiết sản phẩm, chỉ hiển thị toast
                    window.showToast(data.message || 'Đã thêm sản phẩm vào giỏ hàng.', 'success');
                }
            } catch (error) {
                window.showToast('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.', 'error');
            } finally {
                targetButton.disabled = false;
                targetButton.innerHTML = originalHtml;
            }
        }

        btnAddCart.addEventListener('click', function () {
            addSelectedVariantToCart(false);
        });

        btnBuyNow.addEventListener('click', function () {
            addSelectedVariantToCart(true);
        });
    })();

    // ===== Chuyển ảnh chính khi click vào thumbnail =====
    function changeMainImage(thumbEl) {
        document.querySelectorAll('.gallery-thumb').forEach(function (t) {
            t.classList.remove('active');
        });
        thumbEl.classList.add('active');
        document.getElementById('mainProductImage').src = thumbEl.src;
    }

    // ===== AJAX: Toggle Wishlist (real-time badge update) =====
    (function () {
        const btn = document.getElementById('btnToggleWishlist');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const productId  = btn.getAttribute('data-product-id');
            const inWishlist = btn.getAttribute('data-in-wishlist') === 'true';
            const icon       = btn.querySelector('i');

            btn.disabled = true;

            fetch('/yeu-thich/bat-tat/' + productId, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.disabled = false;

                if (data.added) {
                    // Đã thêm vào wishlist
                    icon.className = 'bi bi-heart-fill';
                    icon.style.color = '#d9534f';
                    btn.setAttribute('data-in-wishlist', 'true');
                    btn.setAttribute('title', 'Xóa khỏi yêu thích');
                } else {
                    // Đã xóa khỏi wishlist
                    icon.className = 'bi bi-heart';
                    icon.style.color = '';
                    btn.setAttribute('data-in-wishlist', 'false');
                    btn.setAttribute('title', 'Thêm vào yêu thích');
                }

                // Cập nhật badge số lượng trên header (real-time, không reload)
                if (data.count !== undefined) {
                    document.querySelectorAll('.utility-icons a[href*="wishlist"] .badge-count').forEach(function (el) {
                        el.textContent = data.count;
                    });
                }
            })
            .catch(function () {
                btn.disabled = false;
            });
        });
    })();
</script>
@endpush
