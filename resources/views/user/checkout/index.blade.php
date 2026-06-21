@extends('layouts.app')

@section('title', 'Checkout | NOIR')

@section('css')
<style>
    .checkout-title {
        font-family: var(--font-serif);
        font-size: 32px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 40px;
    }

    .checkout-section-title {
        font-family: var(--font-sans);
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 25px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--primary-color);
        color: var(--primary-color);
    }

    /* Form control styling override */
    .form-control, .form-select {
        border-radius: 0;
        border: 1px solid #cccccc;
        font-size: 13px;
        padding: 12px 15px;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    .form-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--primary-color);
        margin-bottom: 8px;
    }

    /* Checkout payment options */
    .payment-option-card {
        border: 1px solid var(--border-color);
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }

    .payment-option-card:hover {
        border-color: var(--primary-color);
    }

    .payment-option-card.active {
        border-color: var(--primary-color);
        background-color: var(--hover-bg);
    }

    .payment-option-input {
        margin-right: 15px;
    }

    .payment-option-label {
        font-weight: 500;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        flex: 1;
        margin-bottom: 0;
    }

    /* Order summary column in checkout */
    .checkout-summary-card {
        border: 1px solid var(--border-color);
        padding: 30px;
        background-color: #ffffff;
    }

    .checkout-summary-title {
        font-family: var(--font-sans);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border-color);
    }

    .checkout-summary-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .checkout-summary-item:last-of-type {
        border-bottom: none;
    }

    .checkout-summary-img-wrapper {
        width: 60px;
        height: 80px;
        background-color: var(--hover-bg);
        overflow: hidden;
        margin-right: 15px;
    }

    .checkout-summary-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .checkout-summary-details {
        flex: 1;
    }

    .checkout-summary-name {
        font-size: 13px;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .checkout-summary-meta {
        font-size: 11px;
        color: var(--muted-text);
    }

    .checkout-summary-price {
        font-weight: 600;
        font-size: 13px;
        text-align: right;
    }

    .btn-place-order {
        width: 100%;
        height: 52px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1.5px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 my-5">
    <h1 class="checkout-title">Checkout</h1>

    <div class="row">
        <!-- Billing/Shipping Form Column -->
        <div class="col-lg-7">
            <form onsubmit="event.preventDefault(); alert('Order placed successfully!'); window.location.href='{{ url('/') }}';">
                <!-- Shipping Address Section -->
                <div class="mb-5">
                    <h2 class="checkout-section-title">Shipping Address</h2>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" placeholder="John" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" placeholder="Doe" required>
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" placeholder="john.doe@example.com" required>
                        </div>
                        <div class="col-12">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" placeholder="+1 (212) 555-0199" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" placeholder="123 Fashion Street, Apt 4B" required>
                        </div>
                        <div class="col-md-5">
                            <label for="country" class="form-label">Country</label>
                            <select class="form-select" id="country" required>
                                <option value="">Choose...</option>
                                <option value="US">United States</option>
                                <option value="VN">Vietnam</option>
                                <option value="UK">United Kingdom</option>
                                <option value="FR">France</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" placeholder="New York" required>
                        </div>
                        <div class="col-md-3">
                            <label for="zip" class="form-label">Zip / Postal Code</label>
                            <input type="text" class="form-control" id="zip" placeholder="10001" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Method Section -->
                <div class="mb-5">
                    <h2 class="checkout-section-title">Payment Method</h2>
                    
                    <div class="payment-option-card active" onclick="selectPayment(this)">
                        <input class="payment-option-input" type="radio" name="paymentMethod" id="creditCard" value="card" checked>
                        <label class="payment-option-label" for="creditCard">
                            Credit / Debit Card <i class="bi bi-credit-card ms-2"></i>
                        </label>
                    </div>

                    <div id="creditCardForm" class="p-3 border border-top-0 mb-4 bg-light">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="cc-name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="cc-name" placeholder="John Doe">
                            </div>
                            <div class="col-md-6">
                                <label for="cc-number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="cc-number" placeholder="•••• •••• •••• ••••">
                            </div>
                            <div class="col-md-3">
                                <label for="cc-expiration" class="form-label">Expiration Date</label>
                                <input type="text" class="form-control" id="cc-expiration" placeholder="MM/YY">
                            </div>
                            <div class="col-md-3">
                                <label for="cc-cvv" class="form-label">CVV / CVC</label>
                                <input type="text" class="form-control" id="cc-cvv" placeholder="•••">
                            </div>
                        </div>
                    </div>

                    <div class="payment-option-card" onclick="selectPayment(this)">
                        <input class="payment-option-input" type="radio" name="paymentMethod" id="cod" value="cod">
                        <label class="payment-option-label" for="cod">
                            Cash on Delivery (COD) <i class="bi bi-cash ms-2"></i>
                        </label>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-black btn-place-order">
                        Place Order
                    </button>
                </div>
            </form>
        </div>

        <!-- Order Summary Column -->
        <div class="col-lg-5 mt-5 mt-lg-0">
            <div class="checkout-summary-card">
                <h2 class="checkout-summary-title">Your Order</h2>
                
                <div class="checkout-summary-list mb-4">
                    <!-- Item 1 Summary -->
                    <div class="checkout-summary-item">
                        <div class="checkout-summary-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop" alt="Premium Oversized Trench" class="checkout-summary-img">
                        </div>
                        <div class="checkout-summary-details">
                            <h3 class="checkout-summary-name">Premium Oversized Trench</h3>
                            <div class="checkout-summary-meta">Qty: 1 | Color: Sand | Size: XS/S</div>
                        </div>
                        <div class="checkout-summary-price">$245.00</div>
                    </div>
                    
                    <!-- Item 2 Summary -->
                    <div class="checkout-summary-item">
                        <div class="checkout-summary-img-wrapper">
                            <img src="https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop" alt="Structured Cotton Shirt" class="checkout-summary-img">
                        </div>
                        <div class="checkout-summary-details">
                            <h3 class="checkout-summary-name">Structured Cotton Shirt</h3>
                            <div class="checkout-summary-meta">Qty: 1 | Color: White | Size: M</div>
                        </div>
                        <div class="checkout-summary-price">$89.00</div>
                    </div>
                </div>

                @include('partials.order-summary', [
                    'subtotal' => 334.00,
                    'tax' => 0.00,
                    'class' => 'border-top pt-3',
                    'hideTitle' => true
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function selectPayment(element) {
        document.querySelectorAll('.payment-option-card').forEach(card => {
            card.classList.remove('active');
            card.querySelector('input[type="radio"]').checked = false;
        });
        
        element.classList.add('active');
        const radio = element.querySelector('input[type="radio"]');
        radio.checked = true;

        const creditCardForm = document.getElementById('creditCardForm');
        if (radio.value === 'card') {
            creditCardForm.style.display = 'block';
            creditCardForm.querySelectorAll('input').forEach(input => input.setAttribute('required', 'required'));
        } else {
            creditCardForm.style.display = 'none';
            creditCardForm.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
        }
    }
</script>
@endpush
