@extends('layouts.app')

@section('title', 'Thanh toán | NOIR')

@section('css')
<style>
    /* ── Layout ── */
    .checkout-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
        gap: 32px;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .checkout-grid {
            grid-template-columns: 1fr;
        }

        .checkout-right-col {
            position: static !important;
            top: auto !important;
        }
    }

    .checkout-right-col {
        position: sticky;
        top: 80px;
    }

    /* ── Section blocks ── */
    .checkout-block {
        background: #ffffff;
        border: 1px solid var(--border-color);
        padding: 24px 28px;
        margin-bottom: 16px;
    }

    .checkout-block-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 20px;
        padding-bottom: 14px;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .checkout-block-title .btn-link-sm {
        font-size: 12px;
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .checkout-block-title .btn-link-sm:hover {
        text-decoration: underline;
    }

    /* ── Form ── */
    .form-control, .form-select {
        border-radius: 4px;
        border: 1px solid #d1d5db;
        font-size: 13px;
        padding: 10px 14px;
        height: auto;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 2px rgba(0,0,0,0.08);
        outline: none;
    }

    .form-control.is-invalid, .form-select.is-invalid {
        border-color: #ef4444;
    }

    .form-control:disabled, .form-control[readonly] {
        background-color: #f8f9fa;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .form-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
        color: #374151;
        margin-bottom: 6px;
    }

    .invalid-feedback {
        font-size: 11px;
        color: #ef4444;
    }

    .input-name-group {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 10px;
    }

    /* ── Policy checkbox bar ── */
    .policy-check-bar {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 0 4px;
        font-size: 12px;
        color: #6b7280;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
    }

    .policy-check-bar input[type="checkbox"] {
        margin-top: 1px;
        flex-shrink: 0;
    }

    .extra-option-row {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: #374151;
        margin-top: 14px;
    }

    /* ── Payment method cards ── */
    .payment-option-card {
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 14px 18px;
        margin-bottom: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.25s;
        background: #fff;
    }

    .payment-option-card:hover {
        border-color: #374151;
    }

    .payment-option-card.active {
        border-color: var(--primary-color);
        background-color: var(--hover-bg);
    }

    .payment-option-icon {
        width: 36px;
        height: 36px;
        object-fit: contain;
        flex-shrink: 0;
    }

    .payment-option-icon-placeholder {
        width: 36px;
        height: 36px;
        background: #e5e7eb;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
        color: #6b7280;
    }

    .payment-option-label {
        font-weight: 600;
        font-size: 13px;
        cursor: pointer;
        flex: 1;
        margin-bottom: 0;
    }

    .payment-option-desc {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }

    /* ── Cart items (right col) ── */
    .promo-notice-bar {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 12px;
        color: #1d4ed8;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 14px;
    }

    .checkout-cart-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 14px;
    }

    .checkout-cart-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .checkout-cart-item:last-of-type {
        border-bottom: none;
    }

    .checkout-cart-img {
        width: 68px;
        height: 88px;
        object-fit: cover;
        background: var(--hover-bg);
        flex-shrink: 0;
        border-radius: 2px;
    }

    .checkout-cart-details {
        flex: 1;
        min-width: 0;
    }

    .checkout-cart-name {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 4px;
        line-height: 1.4;
    }

    .checkout-cart-meta {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .checkout-cart-qty-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 500;
        color: #374151;
        margin-top: 4px;
    }

    .checkout-cart-price {
        text-align: right;
        flex-shrink: 0;
        min-width: 80px;
    }

    .checkout-cart-price .price-final {
        font-size: 14px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .checkout-cart-price .price-original {
        font-size: 11px;
        text-decoration: line-through;
        color: #9ca3af;
        margin-top: 2px;
    }

    /* ── Payment summary (right col bottom) ── */
    .checkout-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 10px 0;
        font-size: 13px;
        border-bottom: 1px dashed #e5e7eb;
    }

    .checkout-summary-row:last-of-type {
        border-bottom: none;
    }

    .checkout-summary-label {
        color: #6b7280;
    }

    .checkout-summary-value {
        font-weight: 500;
        text-align: right;
    }

    .checkout-summary-sub {
        font-size: 11px;
        color: #f97316;
        margin-top: 2px;
    }

    .checkout-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0 10px;
        border-top: 2px solid var(--primary-color);
        margin-top: 4px;
    }

    .checkout-total-label {
        font-size: 15px;
        font-weight: 700;
    }

    .checkout-total-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-color);
    }

    .btn-place-order {
        width: 100%;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.5px;
        border-radius: 4px;
        margin-top: 16px;
    }

    .btn-place-order:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }

    .checkout-policy-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        font-size: 11.5px;
        color: #6b7280;
        margin-top: 16px;
        line-height: 1.5;
    }

    .checkout-policy-row input {
        margin-top: 2px;
        flex-shrink: 0;
    }

    .checkout-cart-items-list {
        max-height: 420px;
        overflow-y: auto;
    }

    /* scrollbar thin */
    .checkout-cart-items-list::-webkit-scrollbar { width: 4px; }
    .checkout-cart-items-list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 my-5">

    <div class="checkout-grid">

        <!-- ══ LEFT COLUMN: Shipping + Payment ══ -->
        <div>
            <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}">
                @csrf

                <!-- Shipping Info Block -->
                <div class="checkout-block">
                    <div class="checkout-block-title">
                        <span>Thông tin vận chuyển</span>
                        <a href="#" class="btn-link-sm">
                            <i class="bi bi-book"></i> Chọn từ sổ địa chỉ
                        </a>
                    </div>
                    @error('agree_policy')
                        <div class="invalid-feedback d-block mb-2">{{ $message }}</div>
                    @enderror

                    <div class="row g-3">
                        <!-- Name row -->
                        <div class="col-md-7">
                            <label class="form-label">Họ tên</label>
                            <div class="input-name-group">
                                <select class="form-select" name="_title">
                                    <option value="anh">Anh/Chị</option>
                                    <option value="anh">Anh</option>
                                    <option value="chi">Chị</option>
                                </select>
                                <input type="text" class="form-control"
                                    value="{{ auth()->user()->username }}" readonly>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-5">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" name="phone" id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', auth()->user()->phone_number) }}" required>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email readonly -->
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control"
                                value="{{ auth()->user()->email }}" readonly>
                        </div>

                        <!-- Street address -->
                        <div class="col-12">
                            <label for="apartment_number" class="form-label">Địa chỉ (trước sắp nhập)</label>
                            <input type="text" name="apartment_number" id="apartment_number"
                                class="form-control @error('apartment_number') is-invalid @enderror"
                                value="{{ old('apartment_number', $address->apartment_number ?? '') }}"
                                placeholder="Nhập địa chỉ" required>
                            @error('apartment_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ward + District -->
                        <div class="col-md-6">
                            <label for="ward" class="form-label">Phường/Xã</label>
                            <input type="text" name="ward" id="ward"
                                class="form-control @error('ward') is-invalid @enderror"
                                value="{{ old('ward', $address->ward ?? '') }}"
                                placeholder="Nhập phường/xã" required>
                            @error('ward')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="district" class="form-label">Quận/Huyện</label>
                            <input type="text" name="district" id="district"
                                class="form-control @error('district') is-invalid @enderror"
                                value="{{ old('district', $address->district ?? '') }}"
                                placeholder="Nhập quận/huyện" required>
                            @error('district')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="col-12">
                            <label for="city" class="form-label">Tỉnh/Thành phố</label>
                            <input type="text" name="city" id="city"
                                class="form-control @error('city') is-invalid @enderror"
                                value="{{ old('city', $address->city ?? '') }}"
                                placeholder="Chọn tỉnh/thành phố" required>
                            @error('city')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label for="note" class="form-label">Ghi chú</label>
                            <input type="text" name="note" id="note"
                                class="form-control @error('note') is-invalid @enderror"
                                value="{{ old('note') }}"
                                placeholder="Nhập ghi chú">
                            @error('note')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Extra UI options -->
                    <label class="extra-option-row">
                        <input type="checkbox" name="_alt_receiver">
                        <span>Gọi người khác nhận hàng (nếu có)</span>
                    </label>
                    <label class="extra-option-row">
                        <input type="checkbox" name="_vat_invoice">
                        <span>Xuất hoá đơn VAT <i class="bi bi-info-circle" style="font-size:12px; color:#9ca3af;"></i></span>
                    </label>
                </div>

                <!-- Payment Method Block -->
                <div class="checkout-block">
                    <div class="checkout-block-title">
                        <span>Hình thức thanh toán</span>
                    </div>

                    @error('payment_method_id')
                        <div class="invalid-feedback d-block mb-3">{{ $message }}</div>
                    @enderror

                    @forelse ($paymentMethods as $method)
                        <div class="payment-option-card" data-payment-card>
                            <input type="radio" name="payment_method_id"
                                id="payment-{{ $method->id }}" value="{{ $method->id }}"
                                style="flex-shrink:0;"
                                @checked((string) old('payment_method_id') === (string) $method->id) required>

                            @if($method->image)
                                <img src="{{ $method->image }}" alt="{{ $method->name }}" class="payment-option-icon">
                            @else
                                <div class="payment-option-icon-placeholder"><i class="bi bi-credit-card"></i></div>
                            @endif

                            <div style="flex:1;">
                                <label class="payment-option-label" for="payment-{{ $method->id }}">
                                    {{ $method->name }}
                                </label>
                                @if($method->description)
                                    <div class="payment-option-desc">{{ $method->description }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted" style="font-size:13px;">Hiện chưa có phương thức thanh toán nào khả dụng.</p>
                    @endforelse
                </div>

            </form>
        </div>

        <!-- ══ RIGHT COLUMN: Cart items + Summary ══ -->
        <div class="checkout-right-col">

            <!-- Cart Items Block -->
            <div class="checkout-block">
                <div class="checkout-block-title">
                    <span>Giỏ hàng</span>
                    <a href="{{ route('cart.index') }}" class="btn-link-sm">
                        <i class="bi bi-pencil"></i> Chỉnh sửa
                    </a>
                </div>

                <!-- Promo notice -->
                <div class="promo-notice-bar">
                    <i class="bi bi-info-circle-fill"></i>
                    Yên tâm 60 ngày đổi trả · Miễn phí giao hàng đơn từ {{ number_format(\App\Services\CartPricingService::FREE_SHIPPING_THRESHOLD, 0, ',', '.') }}đ
                </div>

                <!-- Items list -->
                <div class="checkout-cart-items-list">
                    @foreach ($cartItems as $item)
                        @php
                            $variant  = $item->productVariant;
                            $product  = $variant->product;
                            $image    = $variant->image ?: $product->thumbnail;
                            $original = $product->price * $item->quantity;
                            $final    = $product->final_price * $item->quantity;
                            $savings  = $original - $final;
                        @endphp
                        <div class="checkout-cart-item">
                            <img src="{{ $image }}" alt="{{ $product->name }}" class="checkout-cart-img">
                            <div class="checkout-cart-details">
                                <div class="checkout-cart-name">{{ $product->name }}</div>
                                <div class="checkout-cart-meta">
                                    @if($variant->color){{ $variant->color->name }}@endif
                                    @if($variant->color && $variant->size) / @endif
                                    @if($variant->size){{ $variant->size->name }}@endif
                                </div>
                                <div class="checkout-cart-qty-badge">
                                    <i class="bi bi-bag" style="font-size:11px;"></i> x{{ $item->quantity }}
                                </div>
                            </div>
                            <div class="checkout-cart-price">
                                <div class="price-final">{{ number_format($final, 0, ',', '.') }}đ</div>
                                @if($savings > 0)
                                    <div class="price-original">{{ number_format($original, 0, ',', '.') }}đ</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Payment Summary Block -->
            @php
                $totalOriginal = $cartItems->sum(fn($i) => $i->productVariant->product->price * $i->quantity);
                $totalSavings  = $totalOriginal - $subtotal;
            @endphp
            <div class="checkout-block">
                <div class="checkout-block-title">
                    <span>Chi tiết thanh toán</span>
                </div>

                <!-- Subtotal -->
                <div class="checkout-summary-row">
                    <span class="checkout-summary-label">Tạm tính</span>
                    <div class="checkout-summary-value">
                        {{ number_format($subtotal, 0, ',', '.') }}đ
                        @if($totalSavings > 0)
                            <div class="checkout-summary-sub">(tiết kiệm {{ number_format($totalSavings, 0, ',', '.') }}đ)</div>
                        @endif
                    </div>
                </div>

                <!-- Voucher placeholder -->
                <div class="checkout-summary-row">
                    <span class="checkout-summary-label">Voucher giảm giá</span>
                    <div class="checkout-summary-value">0đ</div>
                </div>

                <!-- Shipping -->
                <div class="checkout-summary-row">
                    <span class="checkout-summary-label">Phí giao hàng</span>
                    <div class="checkout-summary-value">
                        @if($shippingFee == 0)
                            <span style="color: #16a34a; font-weight:600;">Miễn phí</span>
                        @else
                            {{ number_format($shippingFee, 0, ',', '.') }}đ
                        @endif
                    </div>
                </div>

                <!-- Total -->
                <div class="checkout-total-row">
                    <span class="checkout-total-label">Thành tiền</span>
                    <div class="text-end">
                        <div class="checkout-total-value">{{ number_format($total, 0, ',', '.') }}đ</div>
                        @if($totalSavings > 0)
                            <div style="font-size:11px; color:#f97316; margin-top:2px;">Đã giảm {{ number_format($totalSavings, 0, ',', '.') }}đ trên giá gốc</div>
                        @endif
                    </div>
                </div>

                <!-- Policy + Submit -->
                <div class="checkout-policy-row">
                    <input type="checkbox" id="agree_policy_btn" form="checkoutForm" name="agree_policy_mirror"
                        @checked(old('agree_policy'))>
                    <label for="agree_policy_btn" style="cursor:pointer;">
                        Bạn không hài lòng với sản phẩm của chúng tôi? Bạn hoàn toàn có thể trả lại sản phẩm.
                        <a href="#" class="text-primary text-decoration-underline">Tìm hiểu thêm Tại đây</a>
                    </label>
                </div>

                <button type="submit" form="checkoutForm" id="placeOrderBtn" class="btn btn-black btn-place-order">
                    Đặt hàng ngay
                </button>
            </div>

        </div><!-- /right col -->

    </div><!-- /checkout-grid -->
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards        = document.querySelectorAll('[data-payment-card]');
        const policyTop    = document.getElementById('agree_policy_top');
        const placeOrderBtn = document.getElementById('placeOrderBtn');

        /* ── Payment card active state ── */
        function syncActiveCards() {
            cards.forEach(function (card) {
                const radio = card.querySelector('input[type="radio"]');
                card.classList.toggle('active', radio.checked);
            });
        }

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                const radio = card.querySelector('input[type="radio"]');
                radio.checked = true;
                syncActiveCards();
            });
        });

        syncActiveCards();

        /* ── Submit button gating (policy checkbox) ── */
        function syncSubmitState() {
            placeOrderBtn.disabled = !(policyTop && policyTop.checked);
        }

        if (policyTop) {
            policyTop.addEventListener('change', syncSubmitState);
        }

        syncSubmitState();
    });
</script>
@endpush
