@extends('layouts.app')

@section('title', 'Premium Oversized Trench | NOIR')

@section('css')
<style>
    /* Product Detail Main Section */
    .product-detail-image-wrapper {
        background-color: var(--hover-bg);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .product-detail-image {
        width: 100%;
        height: auto;
        object-fit: cover;
    }

    .product-detail-info {
        padding-left: 20px;
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
        margin-bottom: 10px;
        font-weight: 600;
    }

    .product-detail-title {
        font-family: var(--font-serif);
        font-size: 36px;
        font-weight: 400;
        margin-bottom: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .product-detail-price {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 25px;
    }

    .product-detail-price .original-price {
        text-decoration: line-through;
        color: var(--muted-text);
        font-weight: 400;
        margin-right: 10px;
    }

    .product-detail-price .sale-price {
        color: #d9534f;
    }

    .product-detail-rating {
        margin-bottom: 25px;
        font-size: 12px;
    }

    .product-detail-rating .bi-star-fill {
        color: #ffb400;
    }

    .product-detail-rating .rating-count {
        color: var(--muted-text);
        margin-left: 8px;
        text-decoration: underline;
    }

    .product-detail-description {
        font-size: 14px;
        line-height: 1.8;
        color: var(--secondary-color);
        margin-bottom: 30px;
    }

    /* Variant Selectors */
    .variant-selector-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 10px;
        color: var(--primary-color);
    }

    /* Color Selector */
    .color-picker {
        display: flex;
        gap: 12px;
        margin-bottom: 25px;
    }

    .color-option {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid var(--border-color);
        cursor: pointer;
        position: relative;
        transition: transform 0.2s ease;
    }

    .color-option:hover {
        transform: scale(1.1);
    }

    .color-option.active::after {
        content: '';
        position: absolute;
        top: -4px;
        left: -4px;
        right: -4px;
        bottom: -4px;
        border-radius: 50%;
        border: 1px solid var(--primary-color);
    }

    /* Size Selector */
    .size-picker {
        display: flex;
        gap: 8px;
        margin-bottom: 35px;
    }

    .size-option {
        min-width: 44px;
        height: 44px;
        border: 1px solid var(--border-color);
        background-color: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
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

    /* Add to Cart Area */
    .purchase-actions {
        display: flex;
        gap: 15px;
        margin-bottom: 40px;
    }

    .quantity-selector {
        display: flex;
        border: 1px solid var(--border-color);
        height: 52px;
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

    .btn-add-cart {
        flex: 1;
        height: 52px;
        background-color: var(--primary-color);
        color: #ffffff;
        border: 1px solid var(--primary-color);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .btn-add-cart:hover {
        background-color: transparent;
        color: var(--primary-color);
    }

    .btn-wishlist-action {
        width: 52px;
        height: 52px;
        border: 1px solid var(--border-color);
        background: transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        transition: all 0.3s ease;
    }

    .btn-wishlist-action:hover {
        border-color: var(--primary-color);
        color: #d9534f;
    }

    /* Accordion Details */
    .product-info-accordion {
        border-top: 1px solid var(--border-color);
    }

    .accordion-item {
        border: none;
        border-bottom: 1px solid var(--border-color);
        border-radius: 0;
        background: transparent;
    }

    .accordion-button {
        font-family: var(--font-sans);
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--primary-color);
        padding: 20px 0;
        background: transparent;
        box-shadow: none;
        border-radius: 0;
    }

    .accordion-button:not(.collapsed) {
        background: transparent;
        color: var(--primary-color);
        box-shadow: none;
    }

    .accordion-body {
        padding: 0 0 20px 0;
        font-size: 13px;
        line-height: 1.8;
        color: var(--muted-text);
    }
</style>
@endsection

@section('content')

    <!-- Breadcrumbs -->
    @include('partials.breadcrumb', [
        'items' => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'url' => url('/products')],
            ['name' => 'Premium Oversized Trench', 'active' => true]
        ]
    ])

    <div class="container-fluid px-lg-5 mt-4">
        <!-- Product Details Grid -->
        <div class="row mb-5">
            <!-- Product Images Column -->
            <div class="col-md-7">
                <div class="row">
                    <div class="col-12 mb-3">
                        <div class="product-detail-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=1200&auto=format&fit=crop" alt="Premium Oversized Trench" class="product-detail-image w-100">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="product-detail-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1591047139257-24b54e78229b?q=80&w=600&auto=format&fit=crop" alt="Premium Oversized Trench Detail 1" class="product-detail-image w-100">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="product-detail-image-wrapper">
                            <img src="https://images.unsplash.com/photo-1548624149-f9b1859aa7d0?q=80&w=600&auto=format&fit=crop" alt="Premium Oversized Trench Detail 2" class="product-detail-image w-100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Info Column -->
            <div class="col-md-5">
                <div class="product-detail-info">
                    <div class="product-detail-category">Outerwear</div>
                    <h1 class="product-detail-title">Premium Oversized Trench</h1>
                    
                    <div class="product-detail-rating">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <span class="rating-count">(24 reviews)</span>
                    </div>

                    <div class="product-detail-price">$245.00</div>

                    <div class="product-detail-description">
                        An elegant interpretation of the classic trench coat. Tailored from premium water-repellent organic cotton gabardine, it features an oversized silhouette, raglan sleeves, structured epaulettes, and a waist-defining self-tie belt. A timeless, versatile outerwear staple designed to elevate casual and formal outfits.
                    </div>

                    <!-- Color Selector -->
                    <div class="mb-4">
                        <div class="variant-selector-title">Color: Sand</div>
                        <div class="color-picker">
                            <div class="color-option active" style="background-color: #d7c49e;" title="Sand"></div>
                            <div class="color-option" style="background-color: #1a1a1a;" title="Black"></div>
                            <div class="color-option" style="background-color: #4a5448;" title="Olive"></div>
                        </div>
                    </div>

                    <!-- Size Selector -->
                    <div class="mb-4">
                        <div class="variant-selector-title">Size: XS / S</div>
                        <div class="size-picker">
                            <button class="size-option active">XS/S</button>
                            <button class="size-option">M/L</button>
                            <button class="size-option">XL</button>
                        </div>
                    </div>

                    <!-- Quantity & Add to Cart -->
                    <div class="purchase-actions">
                        <div class="quantity-selector">
                            <button class="quantity-btn" onclick="decreaseQty()">-</button>
                            <input type="text" value="1" id="qtyInput" class="quantity-input" readonly>
                            <button class="quantity-btn" onclick="increaseQty()">+</button>
                        </div>
                        
                        <button class="btn-add-cart">
                            <i class="bi bi-bag"></i> Add to Bag
                        </button>
                        
                        <button class="btn-wishlist-action" title="Add to Wishlist">
                            <i class="bi bi-heart"></i>
                        </button>
                    </div>

                    <!-- Accordion Details -->
                    <div class="accordion product-info-accordion" id="productDetailAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingDetails">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDetails" aria-expanded="true" aria-controls="collapseDetails">
                                    Details & Composition
                                </button>
                            </h2>
                            <div id="collapseDetails" class="accordion-collapse collapse show" aria-labelledby="headingDetails" data-bs-parent="#productDetailAccordion">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Outer: 100% Organic Cotton Gabardine</li>
                                        <li>Lining: 100% Viscose (certified sustainable)</li>
                                        <li>Double-breasted horn button closure</li>
                                        <li>Removable self-tie waist belt</li>
                                        <li>Water-repellent finish</li>
                                        <li>Dry clean only</li>
                                        <li>Made in Portugal</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingShipping">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseShipping" aria-expanded="false" aria-controls="collapseShipping">
                                    Shipping & Returns
                                </button>
                            </h2>
                            <div id="collapseShipping" class="accordion-collapse collapse" aria-labelledby="headingShipping" data-bs-parent="#productDetailAccordion">
                                <div class="accordion-body">
                                    <p><strong>Shipping:</strong> Free shipping is applied to orders over $150. For other orders standard shipping rates apply. Delivery time is 2-4 business days.</p>
                                    <p><strong>Returns:</strong> We offer free returns and exchanges within 30 days of shipment. Items must be in their original unworn condition with tags attached.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Recommendation Widget -->
        @include('partials.related-products')
    </div>
@endsection

@push('scripts')
<script>
    function increaseQty() {
        const qtyInput = document.getElementById('qtyInput');
        let val = parseInt(qtyInput.value);
        if(!isNaN(val)) {
            qtyInput.value = val + 1;
        }
    }

    function decreaseQty() {
        const qtyInput = document.getElementById('qtyInput');
        let val = parseInt(qtyInput.value);
        if(!isNaN(val) && val > 1) {
            qtyInput.value = val - 1;
        }
    }

    // Toggle color swatch selection
    document.querySelectorAll('.color-option').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.color-option').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            const titleEl = this.closest('.mb-4').querySelector('.variant-selector-title');
            titleEl.textContent = 'Color: ' + this.getAttribute('title');
        });
    });

    // Toggle size selection
    document.querySelectorAll('.size-option').forEach(el => {
        el.addEventListener('click', function() {
            document.querySelectorAll('.size-option').forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            const titleEl = this.closest('.mb-4').querySelector('.variant-selector-title');
            titleEl.textContent = 'Size: ' + this.textContent;
        });
    });
</script>
@endpush
