@php
    $sumSubtotal = $subtotal ?? 0;
    $sumShipping = $shippingFee ?? ($sumSubtotal == 0 ? 0 : ($shipping ?? 0));
    $sumTotal = $sumSubtotal + $sumShipping;
@endphp

<div class="{{ $class ?? 'order-summary-card' }}">
    @if(!isset($hideTitle) || !$hideTitle)
        <h2 class="summary-title text-uppercase font-semibold tracking-wider fs-7 mb-4">Tóm tắt đơn hàng</h2>
    @endif

    <div class="summary-row d-flex justify-content-between mb-3 fs-7">
        <span class="text-muted">Tạm tính</span>
        <span class="font-semibold" id="cart-subtotal">{{ number_format($sumSubtotal, 0, ',', '.') }}đ</span>
    </div>

    <div class="summary-row d-flex justify-content-between mb-3 fs-7">
        <span class="text-muted">Phí vận chuyển</span>
        <span class="font-semibold" id="cart-shipping">
            @if($sumShipping == 0)
                MIỄN PHÍ
            @else
                {{ number_format($sumShipping, 0, ',', '.') }}đ
            @endif
        </span>
    </div>

    @if(isset($showPromoForm) && $showPromoForm)
        <form class="promo-form my-4 d-flex gap-2" onsubmit="event.preventDefault(); alert('Đã áp dụng mã giảm giá!');">
            <input type="text" class="form-control promo-input text-uppercase" placeholder="MÃ GIẢM GIÁ">
            <button type="submit" class="btn btn-outline-dark promo-btn">ÁP DỤNG</button>
        </form>
    @endif

    <div class="summary-row total-row d-flex justify-content-between pt-3 border-top mt-3 font-semibold fs-6">
        <span>Tổng cộng</span>
        <span id="cart-total">{{ number_format($sumTotal, 0, ',', '.') }}đ</span>
    </div>
    
    @if(isset($buttonText) && $buttonText)
        <div class="mt-4">
            <a href="{{ $buttonUrl ?? '#' }}" class="btn btn-black w-100 btn-checkout text-uppercase fs-7 font-semibold py-3 d-flex align-items-center justify-content-center">
                {{ $buttonText }}
            </a>
        </div>
    @endif
</div>
