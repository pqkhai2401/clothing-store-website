@extends('layouts.app')

@section('title', 'Your Shopping Bag | NOIR')

@section('css')
<style>
    .cart-title {
        font-family: var(--font-serif);
        font-size: 32px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 40px;
    }

    /* Cart Table / List */
    .cart-item-row {
        border-bottom: 1px solid var(--border-color);
        padding: 25px 0;
        display: flex;
        align-items: center;
    }

    .cart-item-row:first-child {
        border-top: 1px solid var(--border-color);
    }

    .cart-item-img-wrapper {
        width: 100px;
        height: 130px;
        background-color: var(--hover-bg);
        overflow: hidden;
        margin-right: 20px;
    }

    .cart-item-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cart-item-details {
        flex: 1;
    }

    .cart-item-category {
        font-size: 10px;
        text-transform: uppercase;
        color: var(--muted-text);
        letter-spacing: 1px;
        margin-bottom: 5px;
    }

    .cart-item-name {
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .cart-item-name a {
        color: var(--primary-color);
    }

    .cart-item-meta {
        font-size: 12px;
        color: var(--muted-text);
        margin-bottom: 5px;
    }

    .cart-item-meta span {
        margin-right: 15px;
    }

    .cart-item-remove {
        background: none;
        border: none;
        color: var(--muted-text);
        font-size: 12px;
        text-decoration: underline;
        cursor: pointer;
        padding: 0;
        margin-top: 10px;
        text-underline-offset: 3px;
        transition: color 0.3s;
    }

    .cart-item-remove:hover {
        color: #d9534f;
    }

    /* Quantity input inside cart */
    .cart-quantity-selector {
        display: flex;
        border: 1px solid var(--border-color);
        width: 110px;
        height: 40px;
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
        font-size: 14px;
    }

    .cart-quantity-input {
        border: none;
        width: 44px;
        text-align: center;
        font-size: 13px;
        font-weight: 500;
        outline: none;
    }

    .cart-item-price-col {
        text-align: right;
        min-width: 100px;
        font-weight: 600;
        font-size: 15px;
    }

    @media (max-width: 576px) {
        .cart-item-row {
            flex-wrap: wrap;
            position: relative;
        }
        
        .cart-item-price-col {
            position: absolute;
            bottom: 25px;
            right: 0;
        }
        
        .cart-item-details {
            min-width: calc(100% - 120px);
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 my-5">
    <h1 class="cart-title">Your Shopping Bag</h1>

    <div class="row">
        <!-- Cart Items List Column -->
        <div class="col-lg-8">
            <div class="cart-items-wrapper">
                <!-- Item 1 -->
                <div class="cart-item-row" id="cart-item-1">
                    <div class="cart-item-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop" alt="Premium Oversized Trench" class="cart-item-img">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-category">Outerwear</div>
                        <h3 class="cart-item-name"><a href="{{ url('/products/premium-oversized-trench') }}">Premium Oversized Trench</a></h3>
                        <div class="cart-item-meta">
                            <span>Color: <strong>Sand</strong></span>
                            <span>Size: <strong>XS/S</strong></span>
                        </div>
                        <div class="cart-quantity-selector">
                            <button class="cart-quantity-btn" onclick="decreaseCartQty('qty-item-1', 245.00, 'price-item-1')">-</button>
                            <input type="text" value="1" id="qty-item-1" class="cart-quantity-input" readonly>
                            <button class="cart-quantity-btn" onclick="increaseCartQty('qty-item-1', 245.00, 'price-item-1')">+</button>
                        </div>
                        <button type="button" class="cart-item-remove" onclick="removeCartItem('cart-item-1', 245.00)">Remove</button>
                    </div>
                    <div class="cart-item-price-col" id="price-item-1">
                        $245.00
                    </div>
                </div>

                <!-- Item 2 -->
                <div class="cart-item-row" id="cart-item-2">
                    <div class="cart-item-img-wrapper">
                        <img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop" alt="Structured Cotton Shirt" class="cart-item-img">
                    </div>
                    <div class="cart-item-details">
                        <div class="cart-item-category">Shirts</div>
                        <h3 class="cart-item-name"><a href="#">Structured Cotton Shirt</a></h3>
                        <div class="cart-item-meta">
                            <span>Color: <strong>White</strong></span>
                            <span>Size: <strong>M</strong></span>
                        </div>
                        <div class="cart-quantity-selector">
                            <button class="cart-quantity-btn" onclick="decreaseCartQty('qty-item-2', 89.00, 'price-item-2')">-</button>
                            <input type="text" value="1" id="qty-item-2" class="cart-quantity-input" readonly>
                            <button class="cart-quantity-btn" onclick="increaseCartQty('qty-item-2', 89.00, 'price-item-2')">+</button>
                        </div>
                        <button type="button" class="cart-item-remove" onclick="removeCartItem('cart-item-2', 89.00)">Remove</button>
                    </div>
                    <div class="cart-item-price-col" id="price-item-2">
                        $89.00
                    </div>
                </div>
            </div>

            <!-- Continue Shopping Link -->
            <div class="mt-4">
                <a href="{{ url('/products') }}" class="text-dark text-uppercase font-semibold fs-7 tracking-wider"><i class="bi bi-arrow-left me-2"></i> Continue Shopping</a>
            </div>
        </div>

        <!-- Order Summary Side Column -->
        <div class="col-lg-4 mt-5 mt-lg-0">
            @include('partials.order-summary', [
                'subtotal' => 334.00,
                'tax' => 0.00,
                'showPromoForm' => true,
                'buttonText' => 'Proceed to Checkout',
                'buttonUrl' => url('/checkout')
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentSubtotal = 334.00;

    function updateSummary() {
        document.getElementById('cart-subtotal').textContent = '$' + currentSubtotal.toFixed(2);
        
        let shippingVal = 0.00;
        if (currentSubtotal > 150 || currentSubtotal === 0) {
            document.getElementById('cart-shipping').textContent = 'FREE';
            shippingVal = 0.00;
        } else {
            shippingVal = 15.00;
            document.getElementById('cart-shipping').textContent = '$15.00';
        }
        
        let totalVal = currentSubtotal + shippingVal;
        document.getElementById('cart-total').textContent = '$' + totalVal.toFixed(2);
    }

    function increaseCartQty(inputId, unitPrice, priceId) {
        const input = document.getElementById(inputId);
        let val = parseInt(input.value);
        if(!isNaN(val)) {
            input.value = val + 1;
            
            // Update item price
            let newItemPrice = (val + 1) * unitPrice;
            document.getElementById(priceId).textContent = '$' + newItemPrice.toFixed(2);
            
            // Update subtotal
            currentSubtotal += unitPrice;
            updateSummary();
        }
    }

    function decreaseCartQty(inputId, unitPrice, priceId) {
        const input = document.getElementById(inputId);
        let val = parseInt(input.value);
        if(!isNaN(val) && val > 1) {
            input.value = val - 1;
            
            // Update item price
            let newItemPrice = (val - 1) * unitPrice;
            document.getElementById(priceId).textContent = '$' + newItemPrice.toFixed(2);
            
            // Update subtotal
            currentSubtotal -= unitPrice;
            updateSummary();
        }
    }

    function removeCartItem(itemId, unitPrice) {
        const row = document.getElementById(itemId);
        if (row) {
            const input = row.querySelector('.cart-quantity-input');
            const qty = parseInt(input.value);
            
            // Update subtotal
            currentSubtotal -= (qty * unitPrice);
            
            // Remove element
            row.remove();
            
            updateSummary();
            
            // If empty, show empty state
            const remainingRows = document.querySelectorAll('.cart-item-row');
            if (remainingRows.length === 0) {
                document.querySelector('.cart-items-wrapper').innerHTML = `
                    <div class="empty-state-container text-center py-5 my-5">
                        <div class="empty-state-icon mb-4">
                            <i class="bi bi-bag-x text-muted opacity-50" style="font-size: 4rem;"></i>
                        </div>
                        <h3 class="empty-state-title text-uppercase font-semibold tracking-wider mb-3 fs-5">Your shopping bag is empty</h3>
                        <p class="empty-state-message text-muted max-w-md mx-auto mb-4" style="max-width: 450px;">You have no items in your shopping bag. Start exploring our collections to add items.</p>
                        <a href="{{ url('/products') }}" class="btn btn-black text-uppercase font-semibold fs-7 py-3 px-5">
                            Return to Shop
                        </a>
                    </div>
                `;
            }
        }
    }
</script>
@endpush
