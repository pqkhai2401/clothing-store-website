@extends('layouts.app')

@section('title', 'Sản phẩm yêu thích | HK Store')

@section('css')
<style>
    /* =============================================
       WISHLIST PAGE — Custom CSS
       Tông màu: Trắng / Đen / Xám đậm (đồng bộ ecommerce.css)
       ============================================= */

    /* ----- Page Header ----- */
    .wishlist-page-header {
        padding: 48px 0 32px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 48px;
    }

    .wishlist-page-header .page-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--muted-text);
        margin-bottom: 10px;
    }

    .wishlist-page-header h1 {
        font-family: var(--font-serif);
        font-size: clamp(26px, 4vw, 40px);
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0;
        line-height: 1.15;
    }

    .wishlist-count-chip {
        display: inline-block;
        font-family: var(--font-sans);
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 1px;
        background-color: var(--primary-color);
        color: #fff;
        padding: 3px 10px;
        margin-left: 12px;
        vertical-align: middle;
        position: relative;
        top: -4px;
    }

    /* ----- Wishlist Grid ----- */
    .wishlist-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
    }

    @media (max-width: 1200px) {
        .wishlist-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 768px) {
        .wishlist-grid { grid-template-columns: repeat(2, 1fr); gap: 16px; }
    }
    @media (max-width: 480px) {
        .wishlist-grid { grid-template-columns: 1fr; }
    }

    /* ----- Wishlist Card ----- */
    .wishlist-card {
        position: relative;
        background: #fff;
        border: 1px solid var(--border-color);
        transition: box-shadow 0.25s ease, transform 0.25s ease;
    }

    .wishlist-card:hover {
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
        transform: translateY(-3px);
    }

    /* Ảnh sản phẩm */
    .wishlist-card-img-wrap {
        position: relative;
        overflow: hidden;
        aspect-ratio: 3/4;
        background-color: var(--hover-bg);
    }

    .wishlist-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .wishlist-card:hover .wishlist-card-img-wrap img {
        transform: scale(1.04);
    }

    /* Badge sale */
    .wishlist-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        background-color: #d9534f;
        color: #fff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 3px 8px;
        z-index: 2;
    }

    /* Nút xoá khỏi wishlist */
    .btn-remove-wishlist {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 34px;
        height: 34px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 3;
        transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        color: #d9534f;
        font-size: 15px;
    }

    .btn-remove-wishlist:hover {
        background: #d9534f;
        border-color: #d9534f;
        color: #fff;
    }

    /* View link overlay khi hover */
    .wishlist-card-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 16px;
        transition: background 0.3s ease;
        z-index: 2;
    }

    .wishlist-card:hover .wishlist-card-overlay {
        background: rgba(0, 0, 0, 0.12);
    }

    .btn-quick-view {
        opacity: 0;
        transform: translateY(8px);
        transition: opacity 0.25s ease, transform 0.25s ease;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        padding: 8px 20px;
        background: #fff;
        color: var(--primary-color);
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .wishlist-card:hover .btn-quick-view {
        opacity: 1;
        transform: translateY(0);
    }

    /* Thông tin sản phẩm */
    .wishlist-card-body {
        padding: 16px;
    }

    .wishlist-card-category {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--muted-text);
        font-weight: 600;
        margin-bottom: 6px;
    }

    .wishlist-card-name {
        font-family: var(--font-serif);
        font-size: 15px;
        font-weight: 400;
        margin-bottom: 10px;
        line-height: 1.35;
    }

    .wishlist-card-name a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .wishlist-card-name a:hover {
        opacity: 0.65;
    }

    /* Giá */
    .wishlist-card-price {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }

    .wishlist-card-price .price-original {
        font-size: 13px;
        color: var(--muted-text);
        text-decoration: line-through;
        font-weight: 400;
    }

    .wishlist-card-price .price-sale {
        font-size: 15px;
        font-weight: 700;
        color: #d9534f;
    }

    .wishlist-card-price .price-normal {
        font-size: 15px;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* Bộ chọn màu + size inline */
    .variant-inline-section {
        border-top: 1px solid var(--border-color);
        padding-top: 12px;
        margin-bottom: 14px;
    }

    .variant-inline-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted-text);
        margin-bottom: 7px;
    }

    .variant-inline-options {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .variant-inline-btn {
        min-width: 36px;
        height: 30px;
        padding: 0 10px;
        border: 1px solid var(--border-color);
        background: transparent;
        font-size: 11px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.18s ease;
        line-height: 1;
        color: var(--text-color);
    }

    .variant-inline-btn:hover {
        border-color: var(--primary-color);
    }

    .variant-inline-btn.selected {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }

    /* Nút thêm vào giỏ hàng */
    .btn-wishlist-add-cart {
        width: 100%;
        height: 42px;
        background: var(--primary-color);
        color: #fff;
        border: 1px solid var(--primary-color);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: background 0.25s ease, color 0.25s ease;
    }

    .btn-wishlist-add-cart:hover {
        background: transparent;
        color: var(--primary-color);
    }

    .btn-wishlist-add-cart:disabled {
        background: var(--hover-bg);
        color: var(--muted-text);
        border-color: var(--border-color);
        cursor: not-allowed;
    }

    /* =============================================
       ACTION BAR (trên danh sách)
       ============================================= */
    .wishlist-actions-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .wishlist-sort-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-weight: 600;
        color: var(--muted-text);
    }

    .btn-clear-wishlist {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted-text);
        background: transparent;
        border: 1px solid var(--border-color);
        padding: 6px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-clear-wishlist:hover {
        border-color: #d9534f;
        color: #d9534f;
    }

    /* =============================================
       EMPTY STATE
       ============================================= */
    .wishlist-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 80px 20px;
        text-align: center;
    }

    .wishlist-empty-icon {
        font-size: 72px;
        color: var(--border-color);
        margin-bottom: 24px;
        line-height: 1;
        /* Hiệu ứng pulse nhẹ */
        animation: pulse-heart 2.4s ease-in-out infinite;
    }

    @keyframes pulse-heart {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.08); opacity: 0.75; }
    }

    .wishlist-empty h2 {
        font-family: var(--font-serif);
        font-size: 26px;
        font-weight: 400;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }

    .wishlist-empty p {
        font-size: 13px;
        color: var(--muted-text);
        max-width: 380px;
        line-height: 1.75;
        margin-bottom: 32px;
    }

    .btn-continue-shopping {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary-color);
        color: #fff;
        padding: 12px 32px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        border: none;
        text-decoration: none;
        transition: opacity 0.25s ease;
    }

    .btn-continue-shopping:hover {
        opacity: 0.75;
        color: #fff;
    }

    /* =============================================
       AI RECOMMENDATION SECTION
       ============================================= */
    .ai-rec-section {
        margin-top: 80px;
        padding-top: 56px;
        border-top: 1px solid var(--border-color);
    }

    .ai-rec-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 36px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ai-rec-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--muted-text);
        margin-bottom: 8px;
    }

    .ai-badge-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%);
        color: #fff;
        width: 22px;
        height: 22px;
        border-radius: 4px;
        font-size: 12px;
    }

    .ai-rec-title {
        font-family: var(--font-serif);
        font-size: clamp(22px, 3vw, 32px);
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 0;
        line-height: 1.2;
    }

    .ai-rec-subtitle {
        font-size: 12px;
        color: var(--muted-text);
        margin-top: 6px;
        letter-spacing: 0.3px;
    }

    .btn-view-all-ai {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--primary-color);
        background: transparent;
        border: 1px solid var(--primary-color);
        padding: 9px 22px;
        text-decoration: none;
        transition: all 0.25s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .btn-view-all-ai:hover {
        background: var(--primary-color);
        color: #fff;
    }

    /* Slider AI Gợi ý */
    .ai-rec-slider-wrap {
        position: relative;
    }

    .ai-rec-slider {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        overflow: hidden;
    }

    @media (max-width: 992px) {
        .ai-rec-slider { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .ai-rec-slider { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    }

    /* Card AI gợi ý — dùng lại thiết kế product-grid-card */
    .ai-rec-card {
        position: relative;
        cursor: pointer;
    }

    .ai-rec-card-img-wrap {
        position: relative;
        overflow: hidden;
        aspect-ratio: 3/4;
        background-color: var(--hover-bg);
        margin-bottom: 14px;
    }

    .ai-rec-card-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .ai-rec-card:hover .ai-rec-card-img-wrap img {
        transform: scale(1.05);
    }

    .ai-rec-card-badge {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #1a1a1a;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 3px 8px;
        z-index: 2;
    }

    .ai-match-chip {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: rgba(255,255,255,0.92);
        color: var(--primary-color);
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
        padding: 3px 8px;
        border: 1px solid var(--border-color);
        z-index: 2;
        text-transform: uppercase;
    }

    .ai-rec-card-actions {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding-bottom: 14px;
        opacity: 0;
        transition: opacity 0.25s ease;
        z-index: 3;
        gap: 8px;
    }

    .ai-rec-card:hover .ai-rec-card-actions {
        opacity: 1;
    }

    .ai-action-btn {
        width: 38px;
        height: 38px;
        background: #fff;
        border: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 15px;
        color: var(--primary-color);
        transition: background 0.2s, color 0.2s;
        text-decoration: none;
    }

    .ai-action-btn:hover {
        background: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }

    .ai-rec-card-category {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--muted-text);
        font-weight: 600;
        margin-bottom: 5px;
    }

    .ai-rec-card-name {
        font-family: var(--font-serif);
        font-size: 14px;
        font-weight: 400;
        margin-bottom: 8px;
        line-height: 1.3;
    }

    .ai-rec-card-name a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .ai-rec-card-name a:hover { opacity: 0.6; }

    .ai-rec-card-price {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .ai-rec-card-price .p-original {
        font-size: 12px;
        color: var(--muted-text);
        text-decoration: line-through;
    }

    .ai-rec-card-price .p-sale {
        font-size: 14px;
        font-weight: 700;
        color: #d9534f;
    }

    .ai-rec-card-price .p-normal {
        font-size: 14px;
        font-weight: 700;
        color: var(--primary-color);
    }

    /* =============================================
       PAGINATION — override Bootstrap 5
       Tông đen/trắng đồng bộ với ecommerce.css
       ============================================= */
    .wishlist-pagination-wrap {
        margin-top: 48px;
        padding-top: 32px;
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Ẩn "Previous / Next" text — dùng icon thay thế */
    .wishlist-pagination-wrap .pagination {
        gap: 4px;
        margin: 0;
    }

    .wishlist-pagination-wrap .page-item .page-link {
        min-width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        border: 1px solid var(--border-color);
        border-radius: 0 !important;        /* góc vuông theo thiết kế */
        color: var(--text-color);
        background: #fff;
        padding: 0 10px;
        transition: all 0.2s ease;
        text-decoration: none;
    }

    .wishlist-pagination-wrap .page-item .page-link:hover {
        background: var(--hover-bg);
        border-color: var(--primary-color);
        color: var(--primary-color);
        z-index: 1;
    }

    /* Trang đang active */
    .wishlist-pagination-wrap .page-item.active .page-link {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
        z-index: 2;
    }

    /* Disabled (trang đầu/cuối) */
    .wishlist-pagination-wrap .page-item.disabled .page-link {
        color: var(--border-color);
        background: var(--hover-bg);
        border-color: var(--border-color);
        cursor: not-allowed;
    }

    /* Focus ring tắt */
    .wishlist-pagination-wrap .page-link:focus {
        box-shadow: none;
        outline: none;
    }
</style>
@endsection

@section('content')

    {{-- $totalCount được truyền từ controller (tổng TẤT CẢ trang, không chỉ trang hiện tại) --}}
    @php
        $totalCount  = $totalCount  ?? 0;
        $currentFrom = $wishlistItems->firstItem() ?? 0;   // vd: 1, 11, 21...
        $currentTo   = $wishlistItems->lastItem()  ?? 0;   // vd: 10, 20, 30...
        $lastPage    = $wishlistItems->lastPage()  ?? 1;
        $currentPage = $wishlistItems->currentPage() ?? 1;
    @endphp

    {{-- ===== Breadcrumb ===== --}}
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Trang chủ', 'url' => url('/')],
            ['name' => 'Sản phẩm yêu thích', 'active' => true],
        ]
    ])

    <div class="container-fluid px-lg-5">

        {{-- =============================================
             PAGE HEADER
             ============================================= --}}
        <div class="wishlist-page-header">
            <h1>
                Sản phẩm yêu thích của bạn
                @if($totalCount > 0)
                    <span class="wishlist-count-chip" id="headerCountChip">{{ $totalCount }}</span>
                @endif
            </h1>
        </div>

        {{-- =============================================
             TRẠNG THÁI CÓ SẢN PHẨM
             ============================================= --}}
        <div id="wishlistHasItems" class="{{ $totalCount > 0 ? '' : 'd-none' }}">

            {{-- Action bar --}}
            <div class="wishlist-actions-bar">
                <p class="wishlist-sort-label mb-0">
                    <strong id="itemTotalText">{{ $totalCount }}</strong> sản phẩm yêu thích
                </p>
                <button class="btn-clear-wishlist" id="btnClearAll">
                    <i class="bi bi-trash me-1"></i> Xóa tất cả
                </button>
            </div>

            {{-- ===== WISHLIST GRID ===== --}}
            <div class="wishlist-grid" id="wishlistGrid">

                @forelse($wishlistItems ?? [] as $item)
                    @php
                        $prod = $item->product ?? $item;
                        $pid  = $prod->id;
                        $pName     = $prod->name;
                        $pSlug     = $prod->slug;
                        $pPrice    = $prod->price;
                        $pDiscount = $prod->discount ?? 0;
                        $pFinalPrice = $pDiscount > 0 ? $pPrice * (100 - $pDiscount) / 100 : null;
                        $pCategory = $prod->category->name ?? 'Thời trang';
                        $pUrl      = url('/san-pham/' . $pSlug);
                        $pImage    = $prod->thumbnail
                            ? (str_starts_with($prod->thumbnail, 'http') ? $prod->thumbnail : asset('storage/' . $prod->thumbnail))
                            : 'https://placehold.co/400x533?text=No+Image';
                        $colors    = $prod->colors ?? collect([]);
                        $sizes     = $prod->sizes ?? collect([]);
                    @endphp

                    {{-- ===== WISHLIST CARD ===== --}}
                    <div class="wishlist-card" id="wl-card-{{ $pid }}" data-product-id="{{ $pid }}">

                        {{-- Ảnh sản phẩm --}}
                        <div class="wishlist-card-img-wrap">
                            {{-- Badge giảm giá --}}
                            @if($pDiscount > 0)
                                <span class="wishlist-badge">-{{ $pDiscount }}%</span>
                            @endif

                            {{-- Nút xóa --}}
                            <button class="btn-remove-wishlist"
                                    title="Xóa khỏi yêu thích"
                                    onclick="removeFromWishlist({{ $pid }}, this)">
                                <i class="bi bi-heart-fill"></i>
                            </button>

                            <img src="{{ $pImage }}" alt="{{ $pName }}" loading="lazy">

                            {{-- Quick view overlay --}}
                            <div class="wishlist-card-overlay">
                                <a href="{{ $pUrl }}" class="btn-quick-view">
                                    <i class="bi bi-eye me-1"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>

                        {{-- Thông tin --}}
                        <div class="wishlist-card-body">
                            <div class="wishlist-card-category">{{ $pCategory }}</div>
                            <h3 class="wishlist-card-name">
                                <a href="{{ $pUrl }}">{{ $pName }}</a>
                            </h3>

                            {{-- Giá --}}
                            <div class="wishlist-card-price">
                                @if($pFinalPrice)
                                    <span class="price-original">{{ number_format($pPrice, 0, ',', '.') }}đ</span>
                                    <span class="price-sale">{{ number_format($pFinalPrice, 0, ',', '.') }}đ</span>
                                @else
                                    <span class="price-normal">{{ number_format($pPrice, 0, ',', '.') }}đ</span>
                                @endif
                            </div>

                            {{-- Chọn màu nhanh --}}
                            @if($colors->count() > 0)
                                <div class="variant-inline-section">
                                    <div class="variant-inline-label">Màu sắc</div>
                                    <div class="variant-inline-options" data-variant-type="color" data-product="{{ $pid }}">
                                        @foreach($colors as $color)
                                            <button type="button"
                                                    class="variant-inline-btn"
                                                    data-color-id="{{ $color->id }}"
                                                    onclick="selectVariant(this, 'color', {{ $pid }})">
                                                {{ $color->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Chọn size nhanh --}}
                            @if($sizes->count() > 0)
                                <div class="variant-inline-section">
                                    <div class="variant-inline-label">Kích thước</div>
                                    <div class="variant-inline-options" data-variant-type="size" data-product="{{ $pid }}">
                                        @foreach($sizes as $size)
                                            <button type="button"
                                                    class="variant-inline-btn"
                                                    data-size-id="{{ $size->id }}"
                                                    onclick="selectVariant(this, 'size', {{ $pid }})">
                                                {{ $size->name }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Nút thêm vào giỏ --}}
                            <button class="btn-wishlist-add-cart"
                                    onclick="addToCartFromWishlist({{ $pid }}, this)">
                                <i class="bi bi-bag"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </div>
                @empty
                    {{-- Fallback khi vòng lặp trống (sẽ bị JS ẩn) --}}
                @endforelse

            </div>{{-- /wishlist-grid --}}

            {{-- ===== PHÂN TRANG ===== --}}
            @if($wishlistItems->hasPages())
                <div class="wishlist-pagination-wrap" id="wishlistPagination">
                    {{ $wishlistItems->links() }}
                </div>
            @endif

        </div>{{-- /wishlistHasItems --}}

        {{-- =============================================
             TRẠNG THÁI TRỐNG (EMPTY STATE)
             ============================================= --}}
        <div id="wishlistEmpty" class="{{ $totalCount === 0 ? '' : 'd-none' }}">
            <div class="wishlist-empty">
                <div class="wishlist-empty-icon">
                    <i class="bi bi-heart" style="
                        font-size: 80px;
                        background: linear-gradient(135deg, #e5e5e5, #ccc);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                        filter: drop-shadow(0 2px 6px rgba(0,0,0,0.08));
                    "></i>
                </div>
                <h2>Danh sách yêu thích của bạn đang trống</h2>
                <p>
                    Bạn chưa thêm sản phẩm nào vào danh sách yêu thích.
                    Hãy khám phá các sản phẩm của chúng tôi và lưu lại
                    những sản phẩm bạn yêu thích!
                </p>
                <a href="{{ url('/products') }}" class="btn-continue-shopping">
                    <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>

        {{-- =============================================
             AI RECOMMENDATION SECTION
             Hiển thị khi có sản phẩm yêu thích
             ============================================= --}}
        <section class="ai-rec-section" id="aiRecSection"
                 style="{{ $totalCount === 0 ? 'display:none;' : '' }}">

            <div class="ai-rec-header">
                <div>
                    <h2 class="ai-rec-title">Sản phẩm tương tự bạn có thể thích</h2>
                    <p class="ai-rec-subtitle">
                        Dựa trên những sản phẩm bạn đã yêu thích — được phân tích và gợi ý bởi hệ thống AI của HK Store
                    </p>
                </div>
                <a href="{{ url('/products') }}" class="btn-view-all-ai">
                    Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>

            <div class="ai-rec-slider" id="aiRecGrid">

                @forelse($recommendedProducts ?? [] as $recProduct)
                    @php
                        $rId       = $recProduct->id;
                        $rName     = $recProduct->name;
                        $rSlug     = $recProduct->slug;
                        $rPrice    = $recProduct->price;
                        $rDiscount = $recProduct->discount ?? 0;
                        $rFinal    = $rDiscount > 0 ? $rPrice * (100 - $rDiscount) / 100 : null;
                        $rCategory = $recProduct->category->name ?? 'Thời trang';
                        $rUrl      = url('/san-pham/' . $rSlug);
                        $rImage    = $recProduct->thumbnail
                            ? (str_starts_with($recProduct->thumbnail, 'http') ? $recProduct->thumbnail : asset('storage/' . $recProduct->thumbnail))
                            : 'https://placehold.co/400x533?text=No+Image';
                        $matchScore = $recProduct->match_score ?? null;
                    @endphp

                    {{-- data-* chứa toàn bộ thông tin cần thiết để JS clone thẻ lên wishlist grid --}}
                    <div class="ai-rec-card"
                         id="ai-rec-card-{{ $rId }}"
                         data-product-id="{{ $rId }}"
                         data-name="{{ $rName }}"
                         data-category="{{ $rCategory }}"
                         data-url="{{ $rUrl }}"
                         data-image="{{ $rImage }}"
                         data-price="{{ number_format($rPrice, 0, ',', '.') }}đ"
                         data-final="{{ $rFinal ? number_format($rFinal, 0, ',', '.') . 'đ' : '' }}"
                         data-discount="{{ $rDiscount }}">
                        <div class="ai-rec-card-img-wrap">
                            @if($rDiscount > 0)
                                <span class="ai-rec-card-badge">-{{ $rDiscount }}%</span>
                            @endif

                            @if($matchScore)
                                <span class="ai-match-chip">
                                    <i class="bi bi-stars me-1"></i>{{ $matchScore }}% phù hợp
                                </span>
                            @endif

                            <img src="{{ $rImage }}" alt="{{ $rName }}" loading="lazy">

                            <div class="ai-rec-card-actions">
                                <a href="{{ $rUrl }}" class="ai-action-btn" title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button class="ai-action-btn ai-wishlist-btn" title="Thêm vào yêu thích"
                                        onclick="addToWishlistFromRec({{ $rId }}, this)">
                                    <i class="bi bi-heart"></i>
                                </button>
                                <button class="ai-action-btn" title="Thêm vào giỏ hàng"
                                        onclick="addToCartQuick({{ $rId }})">
                                    <i class="bi bi-bag"></i>
                                </button>
                            </div>
                        </div>

                        <div class="ai-rec-card-category">{{ $rCategory }}</div>
                        <h3 class="ai-rec-card-name">
                            <a href="{{ $rUrl }}">{{ $rName }}</a>
                        </h3>
                        <div class="ai-rec-card-price">
                            @if($rFinal)
                                <span class="p-original">{{ number_format($rPrice, 0, ',', '.') }}đ</span>
                                <span class="p-sale">{{ number_format($rFinal, 0, ',', '.') }}đ</span>
                            @else
                                <span class="p-normal">{{ number_format($rPrice, 0, ',', '.') }}đ</span>
                            @endif
                        </div>
                    </div>

                @empty
                    {{-- Placeholder cards khi chưa có gợi ý từ AI --}}
                    @for($i = 0; $i < 4; $i++)
                        <div class="ai-rec-card">
                            <div class="ai-rec-card-img-wrap" style="background: var(--hover-bg);">
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                                    <div class="text-center" style="color: var(--border-color);">
                                        <i class="bi bi-image" style="font-size: 40px;"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="ai-rec-card-category" style="background: var(--hover-bg); width: 60%; height: 10px; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div class="ai-rec-card-name" style="background: var(--hover-bg); width: 85%; height: 14px; border-radius: 4px; margin-bottom: 8px;"></div>
                            <div style="background: var(--hover-bg); width: 40%; height: 14px; border-radius: 4px;"></div>
                        </div>
                    @endfor
                @endforelse

            </div>{{-- /ai-rec-slider --}}
        </section>

        <div style="height: 80px;"></div>

    </div>{{-- /container-fluid --}}

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    /* ================================================
       CSRF Helper
       ================================================ */
    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /* ================================================
       Toast Notification — dùng chung window.showToast
       (định nghĩa trong layouts/app.blade.php, neo góc phải trên)
       ================================================ */
    const showToast = window.showToast;

    /* ================================================
       Cập nhật badge header (Wishlist & Cart)
       ================================================ */
    function updateWishlistBadge(count) {
        document.querySelectorAll('.utility-icons a[href*="wishlist"] .badge-count').forEach(el => {
            el.textContent = count;
        });
        const chip = document.getElementById('headerCountChip');
        if (chip) chip.textContent = count;

        const countText = document.getElementById('itemCountText');
        if (countText) countText.textContent = count;
    }

    function refreshWishlistCount() {
        fetch('/api/wishlist/count', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrf() }
        })
        .then(r => r.json())
        .then(data => { if (data.count !== undefined) updateWishlistBadge(data.count); })
        .catch(() => {});
    }

    function updateCartBadge(count) {
        document.querySelectorAll('.utility-icons a[href*="cart"] .badge-count').forEach(el => {
            el.textContent = count;
        });
    }

    /* ================================================
       Trạng thái hiển thị (có / không có sản phẩm)
       ================================================ */
    function toggleEmptyState() {
        const grid    = document.getElementById('wishlistGrid');
        const hasEl   = document.getElementById('wishlistHasItems');
        const emptyEl = document.getElementById('wishlistEmpty');
        const aiSec   = document.getElementById('aiRecSection');

        const cardCount = grid ? grid.querySelectorAll('.wishlist-card').length : 0;

        if (cardCount > 0) {
            hasEl.classList.remove('d-none');
            emptyEl.classList.add('d-none');
            if (aiSec) aiSec.style.display = '';
        } else {
            hasEl.classList.add('d-none');
            emptyEl.classList.remove('d-none');
            if (aiSec) aiSec.style.display = 'none';
        }
    }

    /* ================================================
       Xóa một sản phẩm khỏi Wishlist (AJAX)
       ================================================ */
    const WISHLIST_PER_PAGE = 10;

    function getCurrentPage() {
        return parseInt(new URLSearchParams(window.location.search).get('page') || '1');
    }

    function getTotalPages(total) {
        return Math.max(1, Math.ceil(total / WISHLIST_PER_PAGE));
    }

    window.removeFromWishlist = function(productId, btnEl) {
        const card = document.getElementById('wl-card-' + productId);
        if (!card) return;

        btnEl.disabled = true;

        fetch('/wishlist/remove/' + productId, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf()
            }
        })
        .then(r => r.json())
        .then(data => {
            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            card.style.opacity    = '0';
            card.style.transform  = 'scale(0.95)';

            setTimeout(() => {
                card.remove();

                const serverTotal     = data.count ?? 0;
                const currentPage     = getCurrentPage();
                const prevPages       = getTotalPages(serverTotal + 1); // số trang trước khi xóa
                const newPages        = getTotalPages(serverTotal);
                const remainingOnPage = document.querySelectorAll('.wishlist-card').length;

                // [Bug 1] Trang hiện tại đã hết sản phẩm và không phải trang 1
                // → redirect về trang trước để hiển thị sản phẩm còn lại
                if (remainingOnPage === 0 && currentPage > 1) {
                    updateWishlistBadge(serverTotal);
                    showToast('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
                    setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        params.set('page', currentPage - 1);
                        window.location.href = '/wishlist?' + params.toString();
                    }, 400);
                    return;
                }

                updateWishlistBadge(serverTotal);
                updateInPageCount(serverTotal);

                // [Bug 2] Số trang giảm (vd: 2 trang → 1 trang), pagination links trong DOM
                // đã lỗi thời vì được render server-side → reload về trang 1 để cập nhật
                if (newPages < prevPages) {
                    showToast('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
                    setTimeout(() => {
                        const params = new URLSearchParams(window.location.search);
                        params.delete('page');
                        const qs = params.toString();
                        window.location.href = '/wishlist' + (qs ? '?' + qs : '');
                    }, 400);
                    return;
                }

                toggleEmptyState();

                // Nếu sản phẩm này tồn tại trong AI rec grid, reset icon trái tim về rỗng
                const aiRecCard = document.getElementById('ai-rec-card-' + productId);
                if (aiRecCard) {
                    const aiBtn = aiRecCard.querySelector('.ai-wishlist-btn');
                    if (aiBtn) {
                        aiBtn.innerHTML = '<i class="bi bi-heart"></i>';
                        aiBtn.dataset.added = 'false';
                        aiBtn.title = 'Thêm vào yêu thích';
                    }
                }

                showToast('Đã xóa sản phẩm khỏi danh sách yêu thích', 'success');
            }, 300);
        })
        .catch(() => {
            btnEl.disabled = false;
            showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
    };

    /* ================================================
       Xóa TẤT CẢ sản phẩm khỏi Wishlist
       ================================================ */
    const btnClearAll = document.getElementById('btnClearAll');
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function () {
            if (!confirm('Bạn có chắc muốn xóa toàn bộ danh sách yêu thích không?')) return;

            fetch('/wishlist/clear', {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrf()
                }
            })
            .then(r => r.json())
            .then(() => {
                const grid = document.getElementById('wishlistGrid');
                if (grid) {
                    grid.querySelectorAll('.wishlist-card').forEach(card => {
                        card.style.transition = 'opacity 0.25s ease';
                        card.style.opacity    = '0';
                    });
                    setTimeout(() => {
                        grid.innerHTML = '';
                        toggleEmptyState();
                        updateWishlistBadge(0);
                        updateInPageCount(0);
                        // Reset tất cả icon trái tim trong AI rec grid
                        document.querySelectorAll('.ai-wishlist-btn').forEach(btn => {
                            btn.innerHTML = '<i class="bi bi-heart"></i>';
                            btn.dataset.added = 'false';
                            btn.title = 'Thêm vào yêu thích';
                        });
                        showToast('Đã xóa toàn bộ danh sách yêu thích', 'success');
                    }, 300);
                }
            })
            .catch(() => showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error'));
        });
    }

    /* ================================================
       Chọn biến thể nhanh (Màu / Size) trong card
       ================================================ */
    window.selectVariant = function(btn, type, productId) {
        const parent = btn.closest('.variant-inline-options');
        if (!parent) return;
        parent.querySelectorAll('.variant-inline-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
    };

    /* ================================================
       Thêm vào giỏ hàng từ Wishlist (AJAX)
       ================================================ */
    window.addToCartFromWishlist = function(productId, btnEl) {
        const card      = document.getElementById('wl-card-' + productId);
        const colorBtn  = card?.querySelector('[data-variant-type="color"] .variant-inline-btn.selected');
        const sizeBtn   = card?.querySelector('[data-variant-type="size"] .variant-inline-btn.selected');

        const colorId = colorBtn ? colorBtn.getAttribute('data-color-id') : null;
        const sizeId  = sizeBtn  ? sizeBtn.getAttribute('data-size-id')   : null;

        btnEl.disabled    = true;
        btnEl.textContent = 'Đang thêm...';

        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf()
            },
            body: JSON.stringify({
                product_id: productId,
                color_id:   colorId,
                size_id:    sizeId,
                quantity:   1
            })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showToast(data.message || 'Đã thêm vào giỏ hàng!', 'success');
                if (data.cart_count !== undefined) updateCartBadge(data.cart_count);
            } else {
                showToast(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.', 'error');
            }
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-bag"></i> Thêm vào giỏ hàng';
        })
        .catch(() => {
            showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
            btnEl.disabled = false;
            btnEl.innerHTML = '<i class="bi bi-bag"></i> Thêm vào giỏ hàng';
        });
    };

    /* ================================================
       Thêm vào wishlist từ section AI gợi ý
       — Sau khi thêm thành công: append card lên grid,
         cập nhật đếm nội trang, đổi icon trái tim sang trạng thái đã thích
       ================================================ */
    window.addToWishlistFromRec = function(productId, btnEl) {
        const recCard = document.getElementById('ai-rec-card-' + productId);
        const alreadyAdded = btnEl.dataset.added === 'true';

        // Nếu đã thêm rồi thì toggle xóa (giống nút xóa trong grid)
        if (alreadyAdded) {
            removeFromWishlist(productId, btnEl);
            btnEl.dataset.added = 'false';
            btnEl.innerHTML = '<i class="bi bi-heart"></i>';
            btnEl.title = 'Thêm vào yêu thích';
            return;
        }

        btnEl.disabled = true;

        fetch('/wishlist/toggle/' + productId, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCsrf() }
        })
        .then(r => r.json())
        .then(data => {
            btnEl.disabled = false;

            if (!data.added) {
                // Trường hợp hiếm: server xóa thay vì thêm (race condition)
                showToast('Đã xóa khỏi danh sách yêu thích', 'default');
                return;
            }

            // 1. Đổi icon trái tim sang trạng thái "đã thích" (đỏ fill)
            btnEl.innerHTML = '<i class="bi bi-heart-fill" style="color:#d9534f;"></i>';
            btnEl.title = 'Đã yêu thích';
            btnEl.dataset.added = 'true';

            // 2. Cập nhật badge header
            if (data.count !== undefined) updateWishlistBadge(data.count);

            // 3. Xây dựng wishlist card mới từ data-* của AI rec card
            const currentOnPage = document.querySelectorAll('.wishlist-card').length;

            if (currentOnPage >= WISHLIST_PER_PAGE) {
                // Trang hiện tại đã đầy → reload để server render đúng trang + pagination
                showToast('Đã thêm vào danh sách yêu thích!', 'success');
                setTimeout(() => {
                    const params = new URLSearchParams(window.location.search);
                    const currentPage = getCurrentPage();
                    params.set('page', currentPage + 1);
                    window.location.href = '/wishlist?' + params.toString();
                }, 600);
            } else if (recCard) {
                const d = recCard.dataset;
                appendWishlistCard({
                    id:          productId,
                    name:        d.name,
                    category:    d.category,
                    url:         d.url,
                    image:       d.image,
                    price:       d.price,
                    final:       d.final,
                    discount:    parseInt(d.discount) || 0,
                    serverCount: data.count,
                });
                showToast('Đã thêm vào danh sách yêu thích!', 'success');
            } else {
                showToast('Đã thêm vào danh sách yêu thích!', 'success');
            }
        })
        .catch(() => {
            btnEl.disabled = false;
            showToast('Có lỗi xảy ra. Vui lòng thử lại.', 'error');
        });
    };

    /* ================================================
       Tạo và append wishlist card lên grid
       ================================================ */
    function appendWishlistCard(p) {
        const grid = document.getElementById('wishlistGrid');
        if (!grid) return;

        // HTML price block
        const priceHtml = p.final
            ? `<span class="price-original">${p.price}</span>
               <span class="price-sale">${p.final}</span>`
            : `<span class="price-normal">${p.price}</span>`;

        // HTML badge giảm giá
        const badgeHtml = p.discount > 0
            ? `<span class="wishlist-badge">-${p.discount}%</span>`
            : '';

        const card = document.createElement('div');
        card.className = 'wishlist-card';
        card.id = 'wl-card-' + p.id;
        card.dataset.productId = p.id;

        // Bắt đầu với opacity 0 để animate vào
        card.style.opacity = '0';
        card.style.transform = 'translateY(16px)';
        card.style.transition = 'opacity 0.35s ease, transform 0.35s ease';

        card.innerHTML = `
            <div class="wishlist-card-img-wrap">
                ${badgeHtml}
                <button class="btn-remove-wishlist"
                        title="Xóa khỏi yêu thích"
                        onclick="removeFromWishlist(${p.id}, this)">
                    <i class="bi bi-heart-fill"></i>
                </button>
                <img src="${p.image}" alt="${p.name}" loading="lazy">
                <div class="wishlist-card-overlay">
                    <a href="${p.url}" class="btn-quick-view">
                        <i class="bi bi-eye me-1"></i> Xem chi tiết
                    </a>
                </div>
            </div>
            <div class="wishlist-card-body">
                <div class="wishlist-card-category">${p.category}</div>
                <h3 class="wishlist-card-name">
                    <a href="${p.url}">${p.name}</a>
                </h3>
                <div class="wishlist-card-price">${priceHtml}</div>
                <button class="btn-wishlist-add-cart"
                        onclick="addToCartFromWishlist(${p.id}, this)">
                    <i class="bi bi-bag"></i> Thêm vào giỏ hàng
                </button>
            </div>`;

        grid.appendChild(card);

        // Trigger reflow rồi animate xuất hiện
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            });
        });

        // Cập nhật bộ đếm nội trang bằng tổng từ server (truyền vào từ caller)
        if (p.serverCount !== undefined) {
            updateInPageCount(p.serverCount);
        }

        // Đảm bảo khu vực "có sản phẩm" hiển thị (ẩn empty state nếu đang hiện)
        toggleEmptyState();
    }

    /* ================================================
       Cập nhật text đếm nội trang sau thao tác AJAX
       — Chỉ cập nhật tổng số (itemTotalText) và chip tiêu đề.
         Từ/Đến (itemFromText/itemToText) không đổi vì không reload trang.
       ================================================ */
    function updateInPageCount(newTotal) {
        // Chip đen cạnh tiêu đề
        const chip = document.getElementById('headerCountChip');
        if (chip) {
            chip.textContent   = newTotal;
            chip.style.display = newTotal > 0 ? '' : 'none';
        }

        // Tổng số trong thanh action bar
        const totalTxt = document.getElementById('itemTotalText');
        if (totalTxt) totalTxt.textContent = newTotal;

        // Cập nhật To nếu xóa sản phẩm làm To giảm xuống
        const toTxt = document.getElementById('itemToText');
        if (toTxt) {
            const currentTo = parseInt(toTxt.textContent) || 0;
            if (currentTo > newTotal) toTxt.textContent = newTotal;
        }
    }

    /* ================================================
       Thêm vào giỏ nhanh từ AI Rec (không cần biến thể)
       ================================================ */
    window.addToCartQuick = function(productId) {
        fetch('{{ route('cart.add') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrf()
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            if (ok) {
                showToast(data.message || 'Đã thêm vào giỏ hàng!', 'success');
                if (data.cart_count !== undefined) updateCartBadge(data.cart_count);
            } else {
                showToast(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.', 'error');
            }
        })
        .catch(() => showToast('Có lỗi xảy ra.', 'error'));
    };

    /* ================================================
       Khởi tạo khi DOM sẵn sàng
       ================================================ */
    document.addEventListener('DOMContentLoaded', function () {
        toggleEmptyState();
        // Không gọi refreshWishlistCount() ở đây —
        // PHP/View Composer đã render đúng số lượng ban đầu.
        // refreshWishlistCount() dùng API guard không đọc được session web,
        // sẽ trả về count=0 và ghi đè số đúng của PHP.
    });

})();
</script>
@endpush
