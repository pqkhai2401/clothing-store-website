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
                        @if($product->discount > 0)
                            <span class="original-price">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                            <span class="sale-price">{{ number_format($product->final_price, 0, ',', '.') }}đ</span>
                            <span class="discount-badge">-{{ $product->discount }}%</span>
                        @else
                            <span>{{ number_format($product->price, 0, ',', '.') }}đ</span>
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
                                    <p>Vui lòng tham khảo bảng size chuẩn của HK Store để chọn size phù hợp nhất với bạn.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

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

                // Nếu đã chọn đủ cả Màu và Size -> gọi API kiểm tra biến thể
                if (selectedSizeId) {
                    checkVariant();
                }
            });
        });

        // ===== Xử lý click chọn Kích thước =====
        sizeBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Bỏ active tất cả, thêm active cho nút được chọn
                sizeBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                selectedSizeId = parseInt(btn.getAttribute('data-size-id'));

                // Nếu đã chọn đủ cả Màu và Size -> gọi API kiểm tra biến thể
                if (selectedColorId) {
                    checkVariant();
                }
            });
        });

        // ===== Gọi API kiểm tra biến thể (Fetch API) =====
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
                if (data.found) {
                    // Cập nhật SKU
                    skuEl.textContent = data.sku || 'N/A';

                    // Cập nhật giá hiển thị
                    updatePriceDisplay(data.price, data.discount, data.final_price);

                    // Cập nhật thông tin tồn kho
                    currentStock = data.stock;
                    updateStockDisplay(data.stock);

                    // Cập nhật ảnh biến thể: nếu có ảnh riêng thì đổi, không thì giữ ảnh gốc
                    if (data.image) {
                        mainImage.src = resolveImageUrl(data.image);
                    } else {
                        mainImage.src = originalThumbnail;
                    }
                } else {
                    // Biến thể không tồn tại
                    skuEl.textContent = 'N/A';
                    currentStock = 0;
                    stockInfo.innerHTML = '<span class="out-of-stock">Biến thể không có sẵn</span>';
                    disableButtons();
                }
            })
            .catch(function () {
                stockInfo.innerHTML = '<span class="out-of-stock">Lỗi khi kiểm tra. Vui lòng thử lại.</span>';
            });
        }

        // ===== Cập nhật hiển thị giá =====
        function updatePriceDisplay(price, discount, finalPrice) {
            var priceNum = parseFloat(price);
            var discountNum = parseInt(discount) || 0;
            var finalNum = parseFloat(finalPrice);

            if (discountNum > 0) {
                priceBlock.innerHTML =
                    '<span class="original-price">' + formatCurrency(priceNum) + 'đ</span>' +
                    '<span class="sale-price">' + formatCurrency(finalNum) + 'đ</span>' +
                    '<span class="discount-badge">-' + discountNum + '%</span>';
            } else {
                priceBlock.innerHTML = '<span>' + formatCurrency(priceNum) + 'đ</span>';
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
            if (!isNaN(val) && val < currentStock) {
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
                alert('Vui lòng chọn màu sắc.');
                return false;
            }

            if (!selectedSizeId) {
                alert('Vui lòng chọn kích thước.');
                return false;
            }

            if (currentStock <= 0) {
                alert('Biến thể đã chọn hiện đã hết hàng.');
                return false;
            }

            if (selectedQuantity() > currentStock) {
                alert('Số lượng vượt quá tồn kho hiện có.');
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
                    alert(message);
                    return;
                }

                alert(data.message || 'Đã thêm sản phẩm vào giỏ hàng.');
                window.location.href = redirectToCheckout
                    ? (data.checkout_url || '{{ route('checkout.index') }}')
                    : (data.cart_url || '{{ route('cart.index') }}');
            } catch (error) {
                alert('Có lỗi xảy ra khi thêm sản phẩm vào giỏ hàng. Vui lòng thử lại.');
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

            fetch('/wishlist/toggle/' + productId, {
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
