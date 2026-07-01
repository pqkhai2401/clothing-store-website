@extends('layouts.app')

@section('title', 'Giỏ hàng | NOIR')

@section('css')
<style>
    .cart-page-wrapper {
        padding-bottom: 90px; /* space for sticky bar */
    }

    .cart-page-title {
        font-family: var(--font-serif);
        font-size: 28px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 0;
    }

    /* ── Cart table header ── */
    .cart-table-header {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        background: #f9fafb;
        border: 1px solid var(--border-color);
        border-bottom: none;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
    }

    /* ── Cart item row ── */
    .cart-item-row {
        border: 1px solid var(--border-color);
        border-bottom: none;
        padding: 18px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        background: #fff;
        transition: background 0.2s;
    }

    .cart-item-row:last-of-type {
        border-bottom: 1px solid var(--border-color);
    }

    .cart-item-row.is-checked {
        background: #fafaf8;
    }

    /* item checkbox */
    .cart-item-cb {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-color);
    }

    .cart-item-img-wrapper {
        width: 88px;
        height: 110px;
        background-color: var(--hover-bg);
        overflow: hidden;
        flex-shrink: 0;
    }

    .cart-item-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-details {
        flex: 1;
        min-width: 0;
    }

    .cart-item-category {
        font-size: 10px;
        text-transform: uppercase;
        color: var(--muted-text);
        letter-spacing: 1px;
        margin-bottom: 4px;
    }

    .cart-item-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .cart-item-name a { color: var(--primary-color); }

    .cart-item-variant {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    /* Quantity selector */
    .cart-quantity-selector {
        display: inline-flex;
        border: 1px solid var(--border-color);
        height: 36px;
        align-items: center;
        margin-top: 10px;
    }

    .cart-quantity-btn {
        background: none;
        border: none;
        width: 32px;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 13px;
        color: #374151;
    }

    .cart-quantity-btn:hover { background: #f3f4f6; }
    .cart-quantity-btn:disabled { opacity: 0.35; cursor: not-allowed; }

    .cart-quantity-input {
        border: none;
        width: 38px;
        text-align: center;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        background: transparent;
    }

    /* Individual item remove */
    .cart-item-remove {
        background: none;
        border: none;
        color: #9ca3af;
        font-size: 11px;
        cursor: pointer;
        padding: 0;
        margin-top: 8px;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.2s;
    }

    .cart-item-remove:hover { color: #ef4444; }
    .cart-item-remove:disabled { opacity: 0.4; cursor: not-allowed; }

    /* ── Variant chips ── */
    .cart-variant-selectors {
        display: flex;
        gap: 8px;
        margin-top: 8px;
        flex-wrap: wrap;
    }

    .cart-variant-chip-wrap {
        position: relative;
    }

    .cart-variant-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border: 1px solid #d1d5db;
        border-radius: 20px;
        background: #fff;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
        white-space: nowrap;
    }

    .cart-variant-chip:hover { border-color: #111; }
    .cart-variant-chip.is-loading { opacity: 0.5; pointer-events: none; }

    .cart-variant-chip i {
        font-size: 10px;
        transition: transform 0.15s;
    }

    .cart-variant-chip.open i { transform: rotate(180deg); }

    .cart-variant-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        min-width: 140px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        z-index: 200;
        padding: 6px 0;
        animation: dropIn 0.12s ease;
    }

    @keyframes dropIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .cart-variant-option {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 14px;
        font-size: 13px;
        cursor: pointer;
        color: #374151;
        transition: background 0.1s;
    }

    .cart-variant-option:hover { background: #f9fafb; }

    .cart-variant-option.active {
        font-weight: 700;
        color: #111;
    }

    .cart-variant-option .check-icon {
        color: #111;
        font-size: 12px;
    }

    .cart-variant-option.out-of-stock {
        color: #9ca3af;
        text-decoration: line-through;
        cursor: not-allowed;
    }

    /* Price column */
    .cart-item-price-col {
        text-align: right;
        flex-shrink: 0;
        min-width: 100px;
    }

    .cart-item-price-final {
        font-size: 15px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .cart-item-price-original {
        font-size: 11px;
        text-decoration: line-through;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Right column summary ── */
    .cart-summary-card {
        border: 1px solid var(--border-color);
        padding: 24px;
        background: #fff;
        position: sticky;
        top: 85px;
    }

    .cart-summary-title {
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border-color);
    }

    .summary-line {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        margin-bottom: 12px;
        align-items: flex-start;
    }

    .summary-line-label { color: #6b7280; }

    .summary-total-line {
        display: flex;
        justify-content: space-between;
        font-size: 16px;
        font-weight: 700;
        padding-top: 14px;
        border-top: 2px solid var(--primary-color);
        margin-top: 6px;
    }

    .summary-savings-note {
        font-size: 11px;
        color: #f97316;
        margin-top: 6px;
        text-align: right;
    }

    .btn-checkout-main {
        width: 100%;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.5px;
        margin-top: 18px;
        border-radius: 2px;
    }

    .btn-checkout-main:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .selected-hint {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 10px;
        text-align: center;
    }

    /* ── Sticky bottom bar ── */
    .cart-bottom-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 100;
        background: #ffffff;
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -4px 16px rgba(0,0,0,0.07);
        padding: 0 0;
    }

    .cart-bottom-bar-inner {
        max-width: 1400px;
        margin: 0 auto;
        padding: 14px 40px;
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .bar-select-all-group {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .bar-select-all-cb {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--primary-color);
    }

    .bar-select-all-label {
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        white-space: nowrap;
    }

    .bar-divider {
        width: 1px;
        height: 20px;
        background: var(--border-color);
        flex-shrink: 0;
    }

    .bar-action-btn {
        background: none;
        border: none;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 4px;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: background 0.2s, color 0.2s;
        white-space: nowrap;
    }

    .bar-action-btn:hover { background: #f3f4f6; }

    .bar-action-btn.btn-delete { color: #ef4444; }
    .bar-action-btn.btn-delete:hover { background: #fef2f2; }

    .bar-action-btn.btn-wishlist { color: #6b7280; }

    .bar-spacer { flex: 1; }

    .bar-total-section {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        flex-shrink: 0;
    }

    .bar-total-main {
        font-size: 14px;
        font-weight: 400;
        color: #374151;
    }

    .bar-total-amount {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .bar-savings {
        font-size: 11px;
        color: #9ca3af;
    }

    .bar-checkout-btn {
        background: var(--primary-color);
        color: #ffffff;
        border: none;
        height: 46px;
        padding: 0 28px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        border-radius: 2px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background 0.2s;
    }

    .bar-checkout-btn:hover { background: #222; color: #fff; }

    .bar-checkout-btn:disabled,
    .bar-checkout-btn[aria-disabled="true"] {
        background: #d1d5db;
        cursor: not-allowed;
        pointer-events: none;
    }

    @media (max-width: 768px) {
        .cart-bottom-bar-inner { padding: 12px 16px; gap: 10px; }
        .bar-action-btn.btn-wishlist { display: none; }
        .bar-total-main { font-size: 12px; }
        .bar-total-amount { font-size: 15px; }
        .bar-checkout-btn { padding: 0 16px; font-size: 12px; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 my-5 cart-page-wrapper">

    <!-- Page heading -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="cart-page-title">Giỏ hàng</h1>
        @if($cartItems->isNotEmpty())
            <a href="{{ url('/products') }}" class="text-muted text-decoration-none" style="font-size:13px;">
                <i class="bi bi-arrow-left me-1"></i> Tiếp tục mua sắm
            </a>
        @endif
    </div>

    @if($cartItems->isNotEmpty())
    <div class="row g-4">

        <!-- ── Left: Cart items ── -->
        <div class="col-lg-8">

            <!-- Table header -->
            <div class="cart-table-header">
                <span style="flex:1;">Sản phẩm</span>
                <span style="width:120px; text-align:center;">Số lượng</span>
                <span style="width:110px; text-align:right;">Thành tiền</span>
            </div>

            <!-- Items -->
            <div id="cartItemsWrapper">
                @foreach ($cartItems as $item)
                    @php
                        $variant      = $item->productVariant;
                        $product      = $variant->product;
                        $image        = $variant->image ?: $product->thumbnail;
                        $unitPrice    = $product->final_price;
                        $unitOriginal = $product->price;
                        $linePrice    = $unitPrice * $item->quantity;
                        $lineOriginal = $unitOriginal * $item->quantity;
                        $savings      = $lineOriginal - $linePrice;
                        $allVariants  = $product->productVariants->map(fn($v) => [
                            'id'         => $v->id,
                            'color_id'   => $v->color_id,
                            'color_name' => $v->color?->name,
                            'size_id'    => $v->size_id,
                            'size_name'  => $v->size?->name,
                            'stock'      => $v->stock,
                        ])->values();
                    @endphp
                    <div class="cart-item-row"
                         data-cart-item="{{ $item->id }}"
                         data-unit-price="{{ $unitPrice }}"
                         data-unit-original="{{ $unitOriginal }}"
                         data-quantity="{{ $item->quantity }}"
                         data-variant-id="{{ $variant->id }}"
                         data-color-id="{{ $variant->color_id }}"
                         data-size-id="{{ $variant->size_id }}"
                         data-variants='@json($allVariants)'>

                        <!-- Checkbox -->
                        <input type="checkbox"
                               class="cart-item-cb"
                               data-item-cb="{{ $item->id }}"
                               checked>

                        <!-- Image -->
                        <div class="cart-item-img-wrapper">
                            <img src="{{ $image }}" alt="{{ $product->name }}" class="cart-item-img">
                        </div>

                        <!-- Details -->
                        <div class="cart-item-details">
                            <div class="cart-item-category">{{ $product->category->name ?? 'Sản phẩm' }}</div>
                            <h3 class="cart-item-name">
                                <a href="{{ url('/products/'.$product->slug) }}">{{ $product->name }}</a>
                            </h3>
                            <div class="cart-item-variant" data-variant-label>
                                @if($variant->color)<span>{{ $variant->color->name }}</span>@endif
                                @if($variant->color && $variant->size)<span class="mx-1">/</span>@endif
                                @if($variant->size)<span>{{ $variant->size->name }}</span>@endif
                            </div>

                            {{-- Variant selectors --}}
                            <div class="cart-variant-selectors">
                                @if($variant->color)
                                <div class="cart-variant-chip-wrap" data-chip-type="color">
                                    <button class="cart-variant-chip" data-chip-btn>
                                        {{ $variant->color->name }} <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <div class="cart-variant-dropdown" style="display:none;" data-chip-dropdown></div>
                                </div>
                                @endif
                                @if($variant->size)
                                <div class="cart-variant-chip-wrap" data-chip-type="size">
                                    <button class="cart-variant-chip" data-chip-btn>
                                        {{ $variant->size->name }} <i class="bi bi-chevron-down"></i>
                                    </button>
                                    <div class="cart-variant-dropdown" style="display:none;" data-chip-dropdown></div>
                                </div>
                                @endif
                            </div>

                            <div class="cart-quantity-selector"
                                 data-quantity-selector
                                 data-item-id="{{ $item->id }}"
                                 data-stock="{{ $variant->stock }}">
                                <button type="button" class="cart-quantity-btn" data-qty-decrease>−</button>
                                <input type="text" value="{{ $item->quantity }}" class="cart-quantity-input" data-qty-input readonly>
                                <button type="button" class="cart-quantity-btn" data-qty-increase>+</button>
                            </div>

                            <button type="button"
                                    class="cart-item-remove"
                                    data-cart-remove
                                    data-item-id="{{ $item->id }}">
                                <i class="bi bi-trash3"></i> Xóa
                            </button>
                        </div>

                        <!-- Price -->
                        <div class="cart-item-price-col">
                            <div class="cart-item-price-final" data-item-price>
                                {{ number_format($linePrice, 0, ',', '.') }}đ
                            </div>
                            @if($savings > 0)
                                <div class="cart-item-price-original" data-item-original>
                                    {{ number_format($lineOriginal, 0, ',', '.') }}đ
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        <!-- ── Right: Summary ── -->
        <div class="col-lg-4">
            <div class="cart-summary-card" id="cartSummaryCard">
                <div class="cart-summary-title">Chi tiết thanh toán</div>

                <div class="summary-line">
                    <span class="summary-line-label">Đã chọn (<span id="summaryCount">{{ $cartItems->count() }}</span> sản phẩm)</span>
                    <span id="summarySubtotal" style="font-weight:600;">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                </div>

                <div class="summary-line">
                    <span class="summary-line-label">Phí vận chuyển</span>
                    <span id="summaryShipping" style="color:#16a34a; font-weight:600;">
                        {{ $shippingFee == 0 ? 'Miễn phí' : number_format($shippingFee, 0, ',', '.').'đ' }}
                    </span>
                </div>

                <div class="summary-line">
                    <span class="summary-line-label">Voucher giảm giá</span>
                    <span>0đ</span>
                </div>

                <div class="summary-total-line">
                    <span>Thành tiền</span>
                    <span id="summaryTotal">{{ number_format($total, 0, ',', '.') }}đ</span>
                </div>

                <div class="summary-savings-note" id="summarySavings" style="{{ ($subtotal < array_sum(array_map(fn($i) => $i->productVariant->product->price * $i->quantity, $cartItems->all())) ) ? '' : 'display:none;' }}">
                    @php
                        $totalOriginal = $cartItems->sum(fn($i) => $i->productVariant->product->price * $i->quantity);
                        $totalSavings = $totalOriginal - $subtotal;
                    @endphp
                    @if($totalSavings > 0)
                        Tiết kiệm {{ number_format($totalSavings, 0, ',', '.') }}đ
                    @endif
                </div>

                <a href="{{ route('checkout.index') }}"
                   id="checkoutBtn"
                   class="btn btn-black btn-checkout-main">
                    Tiến hành thanh toán <i class="bi bi-arrow-right ms-1"></i>
                </a>

                <p class="selected-hint" id="checkoutHint" style="display:none;">
                    Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.
                </p>
            </div>
        </div>

    </div><!-- /row -->

    @else
    <!-- Empty state -->
    <div class="text-center py-5 my-5">
        <i class="bi bi-bag-x text-muted" style="font-size: 4rem; opacity:.4;"></i>
        <h3 class="text-uppercase mt-4 mb-3" style="font-size:18px; letter-spacing:1px;">Giỏ hàng của bạn đang trống</h3>
        <p class="text-muted mb-4" style="max-width:420px; margin:0 auto 20px;">Bạn chưa có sản phẩm nào trong giỏ hàng. Hãy khám phá các bộ sưu tập của chúng tôi.</p>
        <a href="{{ url('/products') }}" class="btn btn-black text-uppercase py-3 px-5" style="font-size:12px; letter-spacing:1.5px; font-weight:700;">
            Khám phá sản phẩm
        </a>
    </div>
    @endif

</div>

<!-- ══ Sticky bottom bar (shown only when cart has items) ══ -->
@if($cartItems->isNotEmpty())
<div class="cart-bottom-bar" id="cartBottomBar">
    <div class="cart-bottom-bar-inner">

        <!-- Select all -->
        <div class="bar-select-all-group">
            <input type="checkbox" class="bar-select-all-cb" id="selectAllCb" checked>
            <label for="selectAllCb" class="bar-select-all-label">
                Chọn tất cả (<span id="barSelectedCount">{{ $cartItems->count() }}</span>)
            </label>
        </div>

        <div class="bar-divider"></div>

        <!-- Delete selected -->
        <button type="button" class="bar-action-btn btn-delete" id="barDeleteBtn">
            <i class="bi bi-trash3"></i> Xóa
        </button>

        <!-- Save to wishlist -->
        <button type="button" class="bar-action-btn btn-wishlist" id="barWishlistBtn">
            <i class="bi bi-heart"></i> Lưu vào mục đã thích
        </button>

        <div class="bar-spacer"></div>

        <!-- Total -->
        <div class="bar-total-section">
            <div class="bar-total-main">
                Tổng cộng (<span id="barCountLabel">{{ $cartItems->count() }}</span> sản phẩm):
                <span class="bar-total-amount" id="barTotal">{{ number_format($total, 0, ',', '.') }}đ</span>
            </div>
            <div class="bar-savings" id="barSavings">
                @if($totalSavings > 0)
                    Tiết kiệm {{ number_format($totalSavings, 0, ',', '.') }}đ
                @endif
            </div>
        </div>

        <!-- Checkout button -->
        <a href="{{ route('checkout.index') }}"
           class="bar-checkout-btn"
           id="barCheckoutBtn">
            Tiến hành thanh toán <i class="bi bi-arrow-right"></i>
        </a>

    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const wrapper   = document.getElementById('cartItemsWrapper');
    if (!wrapper) return;

    const SHIPPING_THRESHOLD = {{ \App\Services\CartPricingService::FREE_SHIPPING_THRESHOLD }};
    const SHIPPING_FEE       = {{ \App\Services\CartPricingService::SHIPPING_FEE }};

    /* ── helpers ── */
    function money(v) { return Math.round(v).toLocaleString('vi-VN') + 'đ'; }

    function allItemRows() {
        return Array.from(wrapper.querySelectorAll('[data-cart-item]'));
    }

    function checkedItemRows() {
        return allItemRows().filter(row => {
            const cb = row.querySelector('[data-item-cb]');
            return cb && cb.checked;
        });
    }

    /* ── recalculate and update UI ── */
    function recalcSelected() {
        const selected = checkedItemRows();
        const count    = selected.length;

        let subtotal = 0;
        let original = 0;

        selected.forEach(row => {
            const qty     = parseInt(row.dataset.quantity, 10) || 0;
            const price   = parseFloat(row.dataset.unitPrice) || 0;
            const org     = parseFloat(row.dataset.unitOriginal) || 0;
            subtotal += price * qty;
            original += org  * qty;
        });

        const shipping = subtotal === 0 ? 0 : (subtotal >= SHIPPING_THRESHOLD ? 0 : SHIPPING_FEE);
        const total    = subtotal + shipping;
        const savings  = original - subtotal;

        /* right column */
        setText('summaryCount',    count);
        setText('summarySubtotal', money(subtotal));
        setText('summaryShipping', shipping === 0 ? 'Miễn phí' : money(shipping));
        setText('summaryTotal',    money(total));

        const savingsEl = document.getElementById('summarySavings');
        if (savingsEl) {
            savingsEl.style.display = savings > 0 ? '' : 'none';
            savingsEl.textContent = savings > 0 ? 'Tiết kiệm ' + money(savings) : '';
        }

        /* checkout button state */
        const checkoutBtn = document.getElementById('checkoutBtn');
        const hint        = document.getElementById('checkoutHint');
        if (checkoutBtn) {
            if (count === 0) {
                checkoutBtn.setAttribute('aria-disabled', 'true');
                checkoutBtn.style.pointerEvents = 'none';
                checkoutBtn.style.opacity = '0.4';
                if (hint) hint.style.display = '';
            } else {
                checkoutBtn.removeAttribute('aria-disabled');
                checkoutBtn.style.pointerEvents = '';
                checkoutBtn.style.opacity = '';
                if (hint) hint.style.display = 'none';
            }
        }

        /* bottom bar */
        setText('barCountLabel',    count);
        setText('barSelectedCount', count);
        setText('barTotal',         money(total));

        const barSavings = document.getElementById('barSavings');
        if (barSavings) {
            barSavings.textContent = savings > 0 ? 'Tiết kiệm ' + money(savings) : '';
        }

        const barCheckoutBtn = document.getElementById('barCheckoutBtn');
        if (barCheckoutBtn) {
            barCheckoutBtn.setAttribute('aria-disabled', count === 0 ? 'true' : 'false');
            barCheckoutBtn.style.pointerEvents = count === 0 ? 'none' : '';
        }

        /* select all checkbox state */
        const all    = allItemRows().length;
        const selectAllCb = document.getElementById('selectAllCb');
        if (selectAllCb) {
            selectAllCb.checked       = count > 0 && count === all;
            selectAllCb.indeterminate = count > 0 && count < all;
        }
    }

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    /* ── checkbox: individual ── */
    wrapper.addEventListener('change', function (e) {
        if (e.target.matches('[data-item-cb]')) {
            recalcSelected();
        }
    });

    /* ── select all ── */
    const selectAllCb = document.getElementById('selectAllCb');
    if (selectAllCb) {
        selectAllCb.addEventListener('change', function () {
            allItemRows().forEach(row => {
                const cb = row.querySelector('[data-item-cb]');
                if (cb) cb.checked = selectAllCb.checked;
            });
            recalcSelected();
        });
    }

    /* ── API calls ── */
    async function apiPatch(itemId, quantity) {
        const r = await fetch(`/cart/${itemId}`, {
            method: 'PATCH',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ quantity }),
        });
        if (!r.ok) throw new Error('Không thể cập nhật số lượng.');
        return r.json();
    }

    async function apiDelete(itemId) {
        const r = await fetch(`/cart/${itemId}`, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });
        if (!r.ok) throw new Error('Không thể xóa sản phẩm.');
        return r.json();
    }

    function removeRowFromDOM(itemId) {
        wrapper.querySelector(`[data-cart-item="${itemId}"]`)?.remove();
    }

    function showEmptyIfNeeded() {
        if (allItemRows().length === 0) {
            document.getElementById('cartBottomBar')?.remove();
            wrapper.innerHTML = `
                <div class="text-center py-5 my-4">
                    <i class="bi bi-bag-x text-muted" style="font-size:3.5rem;opacity:.4;"></i>
                    <h3 class="text-uppercase mt-4 mb-3" style="font-size:17px;">Giỏ hàng của bạn đang trống</h3>
                    <a href="/products" class="btn btn-black py-3 px-5 text-uppercase" style="font-size:12px;letter-spacing:1.5px;">Khám phá sản phẩm</a>
                </div>`;
        }
    }

    /* ── quantity +/− ── */
    wrapper.addEventListener('click', async function (e) {
        const selector  = e.target.closest('[data-quantity-selector]');
        const removeBtn = e.target.closest('[data-cart-remove]');

        /* quantity change */
        if (selector && (e.target.matches('[data-qty-increase]') || e.target.matches('[data-qty-decrease]'))) {
            const itemId = selector.dataset.itemId;
            const input  = selector.querySelector('[data-qty-input]');
            const stock  = parseInt(selector.dataset.stock, 10);
            let qty      = parseInt(input.value, 10);

            qty = e.target.matches('[data-qty-increase]') ? Math.min(qty + 1, stock) : Math.max(qty - 1, 1);

            try {
                const data    = await apiPatch(itemId, qty);
                input.value   = data.item_quantity;

                /* update row dataset and price display */
                const row     = wrapper.querySelector(`[data-cart-item="${itemId}"]`);
                if (row) {
                    row.dataset.quantity = data.item_quantity;
                    const priceEl = row.querySelector('[data-item-price]');
                    if (priceEl) priceEl.textContent = money(data.item_subtotal);
                    const orgEl   = row.querySelector('[data-item-original]');
                    if (orgEl) {
                        const lineOrg = parseFloat(row.dataset.unitOriginal) * data.item_quantity;
                        orgEl.textContent = money(lineOrg);
                    }
                }
                recalcSelected();
            } catch (err) { alert(err.message); }
            return;
        }

        /* individual remove */
        if (removeBtn) {
            removeBtn.disabled = true;
            try {
                await apiDelete(removeBtn.dataset.itemId);
                removeRowFromDOM(removeBtn.dataset.itemId);
                recalcSelected();
                showEmptyIfNeeded();
            } catch (err) {
                alert(err.message);
                removeBtn.disabled = false;
            }
        }
    });

    /* ── Bar: delete selected ── */
    document.getElementById('barDeleteBtn')?.addEventListener('click', async function () {
        const rows = checkedItemRows();
        if (rows.length === 0) { alert('Bạn chưa chọn sản phẩm nào.'); return; }
        if (!confirm(`Xóa ${rows.length} sản phẩm đã chọn?`)) return;

        this.disabled = true;
        for (const row of rows) {
            const itemId = row.dataset.cartItem;
            try {
                await apiDelete(itemId);
                removeRowFromDOM(itemId);
            } catch (err) { /* skip */ }
        }
        recalcSelected();
        showEmptyIfNeeded();
        this.disabled = false;
    });

    /* ── Bar: wishlist (stub) ── */
    document.getElementById('barWishlistBtn')?.addEventListener('click', function () {
        const rows = checkedItemRows();
        if (rows.length === 0) { alert('Bạn chưa chọn sản phẩm nào.'); return; }
        alert('Tính năng "Lưu vào mục đã thích" sẽ sớm ra mắt!');
    });

    /* ══ Variant chip dropdowns ══ */

    function getVariants(row) {
        try { return JSON.parse(row.dataset.variants || '[]'); } catch { return []; }
    }

    function buildDropdown(dropdown, options, activeId) {
        dropdown.innerHTML = options.map(opt => `
            <div class="cart-variant-option${opt.id === activeId ? ' active' : ''}${opt.stock === 0 ? ' out-of-stock' : ''}"
                 data-variant-opt="${opt.variantId}"
                 title="${opt.stock === 0 ? 'Hết hàng' : ''}">
                ${opt.name}
                ${opt.id === activeId ? '<i class="bi bi-check2 check-icon"></i>' : ''}
            </div>
        `).join('');
    }

    function openDropdown(row, chipWrap, type) {
        const btn      = chipWrap.querySelector('[data-chip-btn]');
        const dropdown = chipWrap.querySelector('[data-chip-dropdown]');
        const variants = getVariants(row);
        const colorId  = parseInt(row.dataset.colorId) || null;
        const sizeId   = parseInt(row.dataset.sizeId) || null;

        if (type === 'color') {
            // Unique colors, pick variant matching current size when possible
            const seen = new Set();
            const options = [];
            variants.forEach(v => {
                if (v.color_id && !seen.has(v.color_id)) {
                    seen.add(v.color_id);
                    // prefer matching size
                    const preferred = variants.find(x => x.color_id === v.color_id && x.size_id === sizeId && x.stock > 0)
                                   || variants.find(x => x.color_id === v.color_id && x.stock > 0)
                                   || variants.find(x => x.color_id === v.color_id);
                    options.push({ id: v.color_id, name: v.color_name, stock: preferred?.stock ?? 0, variantId: preferred?.id });
                }
            });
            buildDropdown(dropdown, options, colorId);
        } else {
            // Unique sizes, pick variant matching current color when possible
            const seen = new Set();
            const options = [];
            variants.forEach(v => {
                if (v.size_id && !seen.has(v.size_id)) {
                    seen.add(v.size_id);
                    const preferred = variants.find(x => x.size_id === v.size_id && x.color_id === colorId && x.stock > 0)
                                   || variants.find(x => x.size_id === v.size_id && x.stock > 0)
                                   || variants.find(x => x.size_id === v.size_id);
                    options.push({ id: v.size_id, name: v.size_name, stock: preferred?.stock ?? 0, variantId: preferred?.id });
                }
            });
            buildDropdown(dropdown, options, sizeId);
        }

        dropdown.style.display = '';
        btn.classList.add('open');
    }

    function closeAllDropdowns() {
        document.querySelectorAll('[data-chip-dropdown]').forEach(d => {
            d.style.display = 'none';
        });
        document.querySelectorAll('[data-chip-btn]').forEach(b => {
            b.classList.remove('open');
        });
    }

    // Click chip btn → open/close dropdown
    wrapper.addEventListener('click', function (e) {
        const btn      = e.target.closest('[data-chip-btn]');
        const optEl    = e.target.closest('[data-variant-opt]');

        if (btn) {
            e.stopPropagation();
            const chipWrap = btn.closest('[data-chip-type]');
            const dropdown = chipWrap.querySelector('[data-chip-dropdown]');
            const row      = btn.closest('[data-cart-item]');
            const type     = chipWrap.dataset.chipType;
            const isOpen   = dropdown.style.display !== 'none';

            closeAllDropdowns();
            if (!isOpen) openDropdown(row, chipWrap, type);
            return;
        }

        if (optEl) {
            e.stopPropagation();
            if (optEl.classList.contains('out-of-stock')) return;

            const newVariantId = parseInt(optEl.dataset.variantOpt);
            if (!newVariantId) return;

            const row      = optEl.closest('[data-cart-item]');
            const itemId   = row.dataset.cartItem;
            const chipWrap = optEl.closest('[data-chip-type]');
            const btn      = chipWrap.querySelector('[data-chip-btn]');

            if (parseInt(row.dataset.variantId) === newVariantId) {
                closeAllDropdowns();
                return;
            }

            closeAllDropdowns();
            btn.classList.add('is-loading');

            fetch(`/cart/${itemId}/variant`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ product_variant_id: newVariantId }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) { alert(data.message || 'Không thể đổi variant.'); return; }

                // If merged into another row, remove this row and update the other
                if (data.merged) {
                    row.remove();
                    recalcSelected();
                    showEmptyIfNeeded();
                    return;
                }

                // Update row datasets
                row.dataset.variantId = data.variant_id;
                row.dataset.colorId   = data.color_id || '';
                row.dataset.sizeId    = data.size_id  || '';
                row.dataset.quantity  = data.item_quantity;

                // Update image
                const img = row.querySelector('.cart-item-img');
                if (img && data.variant_image) img.src = data.variant_image;

                // Update variant label text
                const label = row.querySelector('[data-variant-label]');
                if (label) {
                    const parts = [data.color_name, data.size_name].filter(Boolean);
                    label.innerHTML = parts.join(' <span class="mx-1">/</span> ');
                }

                // Update chip text (both chips since both may change)
                const chips = row.querySelectorAll('[data-chip-type]');
                chips.forEach(wrap => {
                    const t   = wrap.dataset.chipType;
                    const b   = wrap.querySelector('[data-chip-btn]');
                    const txt = t === 'color' ? data.color_name : data.size_name;
                    if (txt && b) {
                        b.childNodes[0].textContent = txt + ' ';
                    }
                });

                // Update quantity selector stock
                const sel = row.querySelector('[data-quantity-selector]');
                if (sel) sel.dataset.stock = data.stock;

                // Update price display
                const priceEl = row.querySelector('[data-item-price]');
                if (priceEl) priceEl.textContent = money(data.item_subtotal);

                recalcSelected();
            })
            .catch(() => alert('Đã có lỗi. Vui lòng thử lại.'))
            .finally(() => btn.classList.remove('is-loading'));

            return;
        }
    });

    // Close dropdowns on outside click
    document.addEventListener('click', closeAllDropdowns);

    /* initial render */
    recalcSelected();
});
</script>
@endpush
