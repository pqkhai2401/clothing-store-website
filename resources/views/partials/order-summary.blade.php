@php
    $sumSubtotal = $subtotal ?? 0;
    $sumTax = $tax ?? 0;
    $shippingThreshold = 150;
    
    if ($sumSubtotal >= $shippingThreshold || $sumSubtotal == 0) {
        $sumShipping = 0;
    } else {
        $sumShipping = $shipping ?? 15;
    }
    
    $sumTotal = $sumSubtotal + $sumShipping + $sumTax;
@endphp

<div class="{{ $class ?? 'order-summary-card' }}">
    @if(!isset($hideTitle) || !$hideTitle)
        <h2 class="summary-title text-uppercase font-semibold tracking-wider fs-7 mb-4">Order Summary</h2>
    @endif
    
    <div class="summary-row d-flex justify-content-between mb-3 fs-7">
        <span class="text-muted">Subtotal</span>
        <span class="font-semibold" id="cart-subtotal">${{ number_format($sumSubtotal, 2) }}</span>
    </div>
    
    <div class="summary-row d-flex justify-content-between mb-3 fs-7">
        <span class="text-muted">Shipping</span>
        <span class="font-semibold" id="cart-shipping">
            @if($sumShipping == 0)
                FREE
            @else
                ${{ number_format($sumShipping, 2) }}
            @endif
        </span>
    </div>
    
    @if($sumTax > 0 || isset($showTaxAlways))
        <div class="summary-row d-flex justify-content-between mb-3 fs-7">
            <span class="text-muted">Estimated Tax</span>
            <span class="font-semibold" id="cart-tax">${{ number_format($sumTax, 2) }}</span>
        </div>
    @endif
    
    @if(isset($showPromoForm) && $showPromoForm)
        <form class="promo-form my-4 d-flex gap-2" onsubmit="event.preventDefault(); alert('Coupon applied!');">
            <input type="text" class="form-control promo-input text-uppercase" placeholder="PROMO CODE">
            <button type="submit" class="btn btn-outline-dark promo-btn">APPLY</button>
        </form>
    @endif
    
    <div class="summary-row total-row d-flex justify-content-between pt-3 border-top mt-3 font-semibold fs-6">
        <span>Total</span>
        <span id="cart-total">${{ number_format($sumTotal, 2) }}</span>
    </div>
    
    @if(isset($buttonText) && $buttonText)
        <div class="mt-4">
            <a href="{{ $buttonUrl ?? '#' }}" class="btn btn-black w-100 btn-checkout text-uppercase fs-7 font-semibold py-3 d-flex align-items-center justify-content-center">
                {{ $buttonText }}
            </a>
        </div>
    @endif
</div>
