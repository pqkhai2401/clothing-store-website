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
        appearance: none;
        -webkit-appearance: none;
        font-size: 12px;
        color: #2563eb;
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 4px;
        border: 0 !important;
        background: none !important;
        padding: 0 !important;
        outline: none !important;
        box-shadow: none !important;
    }

    .checkout-block-title .btn-link-sm:hover {
        text-decoration: underline;
    }

    .checkout-block-title .btn-link-sm:focus,
    .checkout-block-title .btn-link-sm:focus-visible,
    .checkout-block-title .btn-link-sm:active {
        border: 0 !important;
        outline: 0 !important;
        box-shadow: none !important;
    }

    /* ── Form ── */
    .form-control, .form-select {
        border-radius: 4px;
        border: 1px solid #d1d5db;
        font-size: 13px;
        padding: 10px 14px;
        height: auto;
    }

    textarea.form-control {
        height: auto !important;
        min-height: 80px !important;
        resize: vertical !important;
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
        cursor: pointer;
        user-select: none;
    }

    .extra-option-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        flex-shrink: 0;
    }

    /* Alt-receiver expandable panel */
    .alt-receiver-panel {
        overflow: hidden;
        max-height: 0;
        opacity: 0;
        transition: max-height 0.32s ease, opacity 0.25s ease, margin-top 0.25s ease;
        margin-top: 0;
    }

    .alt-receiver-panel.open {
        max-height: 200px;
        opacity: 1;
        margin-top: 16px;
    }

    .alt-receiver-inner {
        background: #f9fafb;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 18px 20px;
    }

    .alt-receiver-gender {
        display: flex;
        gap: 24px;
        margin-bottom: 14px;
    }

    .alt-receiver-gender label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
    }

    .alt-receiver-gender input[type="radio"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
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

    /* ══ Address Book Modal ══ */

    @keyframes addrOverlayIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }

    @keyframes addrCardIn {
        from { opacity: 0; transform: translateY(28px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1);    }
    }

    @keyframes addrStepFadeIn {
        from { opacity: 0; transform: translateX(12px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    #addressBookOverlay.is-open {
        animation: addrOverlayIn 0.22s ease forwards;
    }

    .addr-modal-card {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 500px;
        position: relative;
        max-height: 88vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 32px 80px rgba(0,0,0,0.22), 0 8px 24px rgba(0,0,0,0.10);
        animation: addrCardIn 0.3s cubic-bezier(0.34, 1.28, 0.64, 1) forwards;
        overflow: hidden;
    }

    /* ── Header bar ── */
    .addr-modal-header {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 28px 20px;
        border-bottom: 1px solid #f0f0f0;
        position: relative;
        flex-shrink: 0;
    }

    .addr-modal-title {
        font-family: var(--font-serif);
        font-size: 20px;
        font-weight: 700;
        text-align: center;
        margin: 0;
        letter-spacing: 0.3px;
    }

    .addr-modal-close {
        position: absolute;
        top: 50%;
        right: 18px;
        transform: translateY(-50%);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #f3f4f6;
        color: #374151;
        border: none;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        line-height: 1;
    }

    .addr-modal-close:hover {
        background: #111;
        color: #fff;
        transform: translateY(-50%) rotate(90deg);
    }

    /* ── Back button (step 2 header) ── */
    .addr-modal-back {
        position: absolute;
        top: 50%;
        left: 18px;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 6px;
        transition: background 0.2s, color 0.2s;
    }

    .addr-modal-back:hover { background: #f3f4f6; color: #111; }

    /* ── Body (scrollable) ── */
    .addr-modal-body {
        padding: 22px 28px 26px;
        overflow-y: auto;
        flex: 1;
    }

    .addr-modal-body::-webkit-scrollbar { width: 4px; }
    .addr-modal-body::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }

    /* ── Steps ── */
    .addr-step { animation: addrStepFadeIn 0.22s ease forwards; }

    /* ── Loading skeleton ── */
    .addr-loading {
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 4px 0;
    }

    .addr-skeleton {
        height: 72px;
        border-radius: 12px;
        background: linear-gradient(90deg, #f0f0f0 25%, #e8e8e8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
    }

    @keyframes shimmer {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ── Address list item ── */
    .addr-list-item {
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .addr-list-item:hover {
        border-color: #111;
        background: #fafafa;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    }

    .addr-list-item.selected {
        border-color: #111;
        background: #f9f9f7;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .addr-list-radio {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: 2px solid #d1d5db;
        flex-shrink: 0;
        margin-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: border-color 0.2s;
    }

    .addr-list-item.selected .addr-list-radio {
        border-color: #111;
    }

    .addr-list-radio-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #111;
        opacity: 0;
        transition: opacity 0.15s;
    }

    .addr-list-item.selected .addr-list-radio-dot { opacity: 1; }

    .addr-list-content { flex: 1; min-width: 0; }

    .addr-list-item-top {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .addr-list-item-main {
        font-size: 13px;
        font-weight: 600;
        color: #111;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .addr-list-item-detail {
        font-size: 12px;
        color: #6b7280;
        line-height: 1.5;
    }

    .addr-default-badge {
        font-size: 10px;
        font-weight: 600;
        background: #f0fdf4;
        color: #16a34a;
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 2px 8px;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .addr-item-delete {
        background: none;
        border: none;
        color: #d1d5db;
        cursor: pointer;
        padding: 4px;
        font-size: 14px;
        flex-shrink: 0;
        border-radius: 6px;
        transition: color 0.2s, background 0.2s;
        line-height: 1;
        margin-top: -2px;
    }

    .addr-item-delete:hover { color: #ef4444; background: #fef2f2; }

    /* ── Empty state ── */
    .addr-empty-state {
        text-align: center;
        padding: 32px 0 16px;
    }

    .addr-empty-icon {
        width: 56px;
        height: 56px;
        background: #f3f4f6;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 22px;
        color: #9ca3af;
    }

    .addr-empty-title {
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .addr-empty-sub {
        font-size: 12px;
        color: #9ca3af;
    }

    /* ── Footer (add button) ── */
    .addr-modal-footer {
        padding: 16px 28px 24px;
        border-top: 1px solid #f0f0f0;
        flex-shrink: 0;
    }

    .addr-add-btn {
        width: 100%;
        height: 48px;
        border-radius: 12px;
        border: 1.5px dashed #d1d5db;
        background: #fafafa;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: border-color 0.2s, background 0.2s, color 0.2s;
    }

    .addr-add-btn:hover {
        border-color: #111;
        background: #fff;
        color: #111;
    }

    /* ── Form inputs (floating label style — matches login) ── */
    .addr-field {
        position: relative;
        margin-bottom: 0;
    }

    .addr-input {
        width: 100%;
        height: 52px;
        padding: 0 16px;
        border: 1.5px solid #d1d5db;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 500;
        color: #111;
        background: #fff;
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .addr-input::placeholder { color: transparent; }

    .addr-field-label {
        position: absolute;
        top: 26px;
        left: 14px;
        z-index: 2;
        max-width: calc(100% - 32px);
        padding: 0 5px;
        color: #9ca3af;
        background: #fff;
        font-size: 15px;
        font-weight: 500;
        line-height: 1;
        pointer-events: none;
        transform: translateY(-50%);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .addr-input:focus + .addr-field-label,
    .addr-input:not(:placeholder-shown) + .addr-field-label {
        top: 0;
        font-size: 12px;
        color: #555;
        font-weight: 600;
    }

    .addr-input:focus { border-color: #111; box-shadow: 0 0 0 3px rgba(0,0,0,0.07); }

    .addr-input.is-invalid { border-color: #ef4444; }
    .addr-input.is-invalid + .addr-field-label { color: #ef4444; }
    .addr-input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(239,68,68,0.12); }

    .addr-err {
        font-size: 11px;
        color: #ef4444;
        margin-top: 5px;
        padding-left: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .addr-err:not(:empty)::before { content: '⚠'; font-size: 10px; }

    /* ── Checkbox (default) ── */
    .addr-default-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        cursor: pointer;
        padding: 12px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        transition: border-color 0.2s, background 0.2s;
    }

    .addr-default-label:hover { border-color: #9ca3af; }
    .addr-default-label:has(input:checked) { border-color: #111; background: #fafafa; }

    .addr-default-label input {
        width: 16px;
        height: 16px;
        accent-color: #111;
        flex-shrink: 0;
    }

    /* ── Action buttons ── */
    .addr-form-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 20px;
    }

    .addr-btn {
        height: 48px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.5px;
        cursor: pointer;
        border: 1.5px solid #e5e7eb;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .addr-btn-outline {
        background: #fff;
        color: #374151;
        border-color: #e5e7eb;
    }

    .addr-btn-outline:hover { background: #f3f4f6; border-color: #d1d5db; }

    .addr-btn-black {
        background: #111;
        color: #fff;
        border-color: #111;
    }

    .addr-btn-black:hover { background: #333; border-color: #333; }
    .addr-btn-black:disabled { background: #9ca3af; border-color: #9ca3af; cursor: not-allowed; }

    /* ── Success toast ── */
    .addr-toast {
        position: fixed;
        bottom: 28px;
        left: 50%;
        transform: translateX(-50%) translateY(20px);
        background: #111;
        color: #fff;
        padding: 12px 22px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
        opacity: 0;
        transition: opacity 0.25s, transform 0.25s;
        z-index: 2000;
        pointer-events: none;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    }

    .addr-toast.show {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    /* ── Apply flash on checkout fields ── */
    @keyframes fieldFlash {
        0%   { background: #fef9c3; }
        100% { background: transparent; }
    }

    .field-flash { animation: fieldFlash 1s ease forwards; }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5 my-5">

    {{-- Nút "Quay lại" đặt ngay trên cùng khối nội dung trang thanh toán --}}
    <x-back-button />

    <div class="checkout-grid">

        <!-- ══ LEFT COLUMN: Shipping + Payment ══ -->
        <div>
            <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}">
                @csrf

                <!-- Shipping Info Block -->
                <div class="checkout-block" data-has-old="{{ (old('city') || old('ward') || old('apartment_number')) ? '1' : '0' }}">
                    <div class="checkout-block-title">
                        <span>Thông tin vận chuyển</span>
                        <button type="button" class="btn-link-sm" id="openAddressBookBtn">
                            <i class="bi bi-book"></i> Chọn từ sổ địa chỉ
                        </button>
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
                                    value="{{ auth()->user()->username }}">
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

                        <!-- Tỉnh/Thành phố -->
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="province" class="form-label">Tỉnh/Thành phố</label>
                                <select id="province" name="city"
                                    class="form-select @error('city') is-invalid @enderror" required
                                    data-selected="{{ old('city', $address->city ?? '') }}">
                                    <option value="" selected disabled>Chọn tỉnh/thành phố</option>
                                </select>
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Phường/Xã -->
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="ward" class="form-label">Phường/Xã</label>
                                <select id="ward" name="ward"
                                    class="form-select @error('ward') is-invalid @enderror" required disabled
                                    data-selected="{{ old('ward', $address->ward ?? '') }}">
                                    <option value="" selected disabled>Chọn tỉnh/thành phố trước</option>
                                </select>
                                @error('ward')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Street address -->
                        <div class="col-12">
                            <label for="apartment_number" class="form-label">Địa chỉ (số nhà, tên đường)</label>
                            <input type="text" name="apartment_number" id="apartment_number"
                                class="form-control @error('apartment_number') is-invalid @enderror"
                                value="{{ old('apartment_number', $address->apartment_number ?? '') }}"
                                placeholder="Nhập số nhà, tên đường" required>
                            @error('apartment_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Note -->
                        <div class="col-12">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea name="note" id="note" rows="3"
                                class="form-control @error('note') is-invalid @enderror"
                                placeholder="Nhập ghi chú giao hàng (ví dụ: giao giờ hành chính, gọi trước khi giao...)">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Extra UI options -->
                    <label class="extra-option-row" for="altReceiverCb">
                        <input type="checkbox" id="altReceiverCb" name="_alt_receiver">
                        <span style="font-weight:600;">Gọi người khác nhận hàng (nếu có)</span>
                    </label>

                    <!-- Expandable alt-receiver panel -->
                    <div class="alt-receiver-panel" id="altReceiverPanel">
                        <div class="alt-receiver-inner">
                            <div class="alt-receiver-gender">
                                <label>
                                    <input type="radio" name="_alt_gender" value="nam" checked> Nam
                                </label>
                                <label>
                                    <input type="radio" name="_alt_gender" value="nu"> Nữ
                                </label>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" name="_alt_name" class="form-control"
                                           placeholder="Họ và tên người nhận">
                                </div>
                                <div class="col-md-6">
                                    <input type="tel" name="_alt_phone" class="form-control"
                                           placeholder="Số điện thoại người nhận">
                                </div>
                            </div>
                        </div>
                    </div>

                    <label class="extra-option-row" for="vatInvoiceCb">
                        <input type="checkbox" id="vatInvoiceCb" name="_vat_invoice">
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


                <!-- Items list -->
                <div class="checkout-cart-items-list">
                    @foreach ($cartItems as $item)
                        @php
                            $variant  = $item->productVariant;
                            $product  = $variant->product;
                            $image    = $variant->image ?: $product->thumbnail;
                            $original = $variant->price * $item->quantity;
                            $final    = $variant->final_price * $item->quantity;
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
                $totalOriginal = $cartItems->sum(fn($i) => $i->productVariant->price * $i->quantity);
                $totalSavings  = $totalOriginal - $subtotal;
            @endphp
            <div class="checkout-block" id="checkoutSummary" data-subtotal="{{ $subtotal }}" data-shipping="{{ $shippingFee }}">
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

                <!-- Voucher input -->
                <div style="display:flex; align-items:center; gap:12px; padding:10px 0;">
                    <span class="checkout-summary-label" style="white-space:nowrap;">Mã Voucher</span>
                    <div style="position:relative; flex:1; min-width:0;">
                        <input type="text" id="voucherCodeInput" placeholder="Nhập mã voucher"
                            style="width:100%; padding:9px 36px 9px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:14px; text-transform:uppercase;">
                        <button type="button" id="voucherClearBtn" aria-label="Xóa mã"
                            style="display:none; position:absolute; right:8px; top:50%; transform:translateY(-50%); width:20px; height:20px; border:none; border-radius:50%; background:#9ca3af; color:#fff; font-size:12px; line-height:1; cursor:pointer; align-items:center; justify-content:center;">&times;</button>
                    </div>
                    <button type="button" id="voucherApplyBtn" class="btn btn-outline-dark"
                        style="padding:9px 18px; font-size:14px; white-space:nowrap; border-radius:8px;">Áp dụng</button>
                    <input type="hidden" id="voucherCodeHidden" name="voucher_code" form="checkoutForm" value="">
                </div>
                <div id="voucherMessage" style="font-size:12px; margin:-6px 0 4px;"></div>

                <!-- Voucher discount (shown once applied) -->
                <div class="checkout-summary-row" id="voucherDiscountRow" style="display:none;">
                    <span class="checkout-summary-label">Voucher giảm giá</span>
                    <div class="checkout-summary-value" id="voucherDiscountValue" style="color:#16a34a; font-weight:600;">0đ</div>
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
                        <div class="checkout-total-value" id="checkoutTotalValue">{{ number_format($total, 0, ',', '.') }}đ</div>
                        @if($totalSavings > 0)
                            <div style="font-size:11px; color:#f97316; margin-top:2px;">Đã giảm {{ number_format($totalSavings, 0, ',', '.') }}đ trên giá gốc</div>
                        @endif
                    </div>
                </div>

                <!-- Policy + Submit -->
                <div class="checkout-policy-row">
                    <input type="checkbox" id="agree_policy_btn" form="checkoutForm" name="agree_policy"
                        value="1" @checked(old('agree_policy'))>
                    <label for="agree_policy_btn" style="cursor:pointer;">
                        Nếu bạn không hài lòng với sản phẩm của chúng tôi? Liên hệ ngay qua Page Facebook hoặc số điện thoại hotline để được hỗ trợ đổi trả trong vòng 7 ngày kể từ khi nhận hàng.
                        {{-- <a href="#" class="text-primary text-decoration-underline">Tìm hiểu thêm Tại đây</a> --}}
                    </label>
                </div>

                <button type="submit" form="checkoutForm" id="placeOrderBtn" class="btn btn-black btn-place-order">
                    Đặt hàng ngay
                </button>
            </div>

        </div><!-- /right col -->

    </div><!-- /checkout-grid -->
</div>

{{-- ══ Address Book Modal ══ --}}
<div id="addressBookOverlay"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); backdrop-filter:blur(3px); z-index:1050; align-items:center; justify-content:center; padding:16px;">

    <div class="addr-modal-card" id="addressBookCard">

        {{-- ── Step 1: List ── --}}
        <div id="addrStepList" class="addr-step" style="display:flex; flex-direction:column; height:100%;">
            <div class="addr-modal-header">
                <h3 class="addr-modal-title">Sổ địa chỉ</h3>
                <button class="addr-modal-close" id="closeAddressBookBtn" type="button" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="addr-modal-body" id="addrListBody">
                <div class="addr-loading">
                    <div class="addr-skeleton"></div>
                    <div class="addr-skeleton" style="opacity:.6;"></div>
                </div>
            </div>

            <div class="addr-modal-footer">
                <button type="button" class="addr-add-btn" id="openAddFormBtn">
                    <i class="bi bi-plus-circle"></i> Thêm địa chỉ mới
                </button>
            </div>
        </div>

        {{-- ── Step 2: Add form ── --}}
        <div id="addrStepForm" class="addr-step" style="display:none; flex-direction:column; height:100%;">
            <div class="addr-modal-header">
                <button class="addr-modal-back" id="backToListBtn" type="button">
                    <i class="bi bi-arrow-left"></i> Quay lại
                </button>
                <h3 class="addr-modal-title">Thêm địa chỉ</h3>
                <button class="addr-modal-close" id="closeAddressBookBtn2" type="button" title="Đóng">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="addr-modal-body">
                <form id="addAddressForm" novalidate>
                    @csrf
                    <div class="row g-3">
                        {{-- Name --}}
                        <div class="col-6">
                            <div class="addr-field">
                                <input type="text" class="addr-input" id="addrFormName"
                                       placeholder=" " autocomplete="off">
                                <label class="addr-field-label" for="addrFormName">Họ tên</label>
                            </div>
                        </div>
                        {{-- Phone --}}
                        <div class="col-6">
                            <div class="addr-field">
                                <input type="tel" class="addr-input" id="addrFormPhone"
                                       placeholder=" " autocomplete="off">
                                <label class="addr-field-label" for="addrFormPhone">Số điện thoại</label>
                            </div>
                        </div>
                        {{-- Street address --}}
                        <div class="col-12">
                            <div class="addr-field">
                                <input type="text" class="addr-input" name="apartment_number" id="addrFormApartment"
                                       placeholder=" " required>
                                <label class="addr-field-label" for="addrFormApartment">Địa chỉ (số nhà, tên đường)</label>
                            </div>
                            <div class="addr-err" id="errApartment"></div>
                        </div>
                        {{-- Ward --}}
                        <div class="col-12">
                            <div class="addr-field">
                                <input type="text" class="addr-input" name="ward" id="addrFormWard"
                                       placeholder=" " required>
                                <label class="addr-field-label" for="addrFormWard">Phường/Xã</label>
                            </div>
                            <div class="addr-err" id="errWard"></div>
                        </div>
                        {{-- City --}}
                        <div class="col-12">
                            <div class="addr-field">
                                <input type="text" class="addr-input" name="city" id="addrFormCity"
                                       placeholder=" " required>
                                <label class="addr-field-label" for="addrFormCity">Tỉnh/Thành phố</label>
                            </div>
                            <div class="addr-err" id="errCity"></div>
                        </div>
                        {{-- Default --}}
                        <div class="col-12">
                            <label class="addr-default-label">
                                <input type="checkbox" id="addrFormDefault" name="is_default" value="1">
                                <span>Đặt làm địa chỉ mặc định</span>
                            </label>
                        </div>
                    </div>

                    <div class="addr-form-actions">
                        <button type="button" class="addr-btn addr-btn-outline" id="cancelFormBtn">Huỷ</button>
                        <button type="submit" class="addr-btn addr-btn-black" id="saveAddressBtn">
                            <i class="bi bi-check2"></i> Lưu địa chỉ
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- Success toast --}}
<div class="addr-toast" id="addrToast"></div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cards        = document.querySelectorAll('[data-payment-card]');
        const policyTop    = document.getElementById('agree_policy_btn');
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

        /* ── Alt-receiver panel toggle ── */
        const altCb    = document.getElementById('altReceiverCb');
        const altPanel = document.getElementById('altReceiverPanel');

        if (altCb && altPanel) {
            altCb.addEventListener('change', function () {
                altPanel.classList.toggle('open', altCb.checked);
            });
        }

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

<script>
/* ══ Voucher: nhập mã giảm giá tại checkout ══ */
(function () {
    const summaryEl    = document.getElementById('checkoutSummary');
    const input        = document.getElementById('voucherCodeInput');
    const applyBtn     = document.getElementById('voucherApplyBtn');
    const clearBtn     = document.getElementById('voucherClearBtn');
    const hidden       = document.getElementById('voucherCodeHidden');
    const msgEl        = document.getElementById('voucherMessage');
    const discountRow  = document.getElementById('voucherDiscountRow');
    const discountVal  = document.getElementById('voucherDiscountValue');
    const totalEl      = document.getElementById('checkoutTotalValue');

    if (!summaryEl || !input || !applyBtn) return;

    const subtotal    = parseFloat(summaryEl.dataset.subtotal) || 0;
    const shippingFee = parseFloat(summaryEl.dataset.shipping) || 0;

    function formatVnd(amount) {
        return Math.round(amount).toLocaleString('vi-VN') + 'đ';
    }

    function setMessage(text, isError) {
        msgEl.textContent = text || '';
        msgEl.style.color = isError ? '#dc2626' : '#16a34a';
    }

    function syncClearBtn() {
        clearBtn.style.display = input.value ? 'flex' : 'none';
    }

    function showApplied(code, discountAmount) {
        hidden.value = code;
        discountVal.textContent = '-' + formatVnd(discountAmount);
        discountRow.style.display = 'flex';
        input.disabled = true;
        applyBtn.disabled = true;
        totalEl.textContent = formatVnd(Math.max(subtotal - discountAmount, 0) + shippingFee);
    }

    function clearApplied() {
        hidden.value = '';
        input.value = '';
        input.disabled = false;
        applyBtn.disabled = false;
        discountRow.style.display = 'none';
        setMessage('');
        syncClearBtn();
        totalEl.textContent = formatVnd(subtotal + shippingFee);
    }

    input.addEventListener('input', syncClearBtn);
    syncClearBtn();

    applyBtn.addEventListener('click', async function () {
        const code = input.value.trim();
        if (!code) {
            setMessage('Vui lòng nhập mã giảm giá.', true);
            return;
        }

        applyBtn.disabled = true;
        setMessage('Đang kiểm tra mã...', false);

        try {
            const res = await fetch('{{ route("api.vouchers.apply") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ code: code, subtotal: subtotal }),
            });
            const data = await res.json();

            if (!res.ok || !data.success) {
                setMessage(data.message || 'Mã giảm giá không hợp lệ.', true);
                applyBtn.disabled = false;
                return;
            }

            showApplied(code, data.discount_amount);
            setMessage('Áp dụng mã giảm giá thành công!', false);
        } catch (e) {
            setMessage('Có lỗi xảy ra, vui lòng thử lại.', true);
            applyBtn.disabled = false;
        }
    });

    clearBtn.addEventListener('click', clearApplied);
})();
</script>

<script>
(function () {
    const provinceSelect = document.getElementById('province');
    const wardSelect = document.getElementById('ward');

    if (!provinceSelect || !wardSelect) return;

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fillSelect(select, items, placeholder) {
        const options = items.map(item =>
            `<option value="${escapeHtml(item.name)}" data-code="${item.code}">${escapeHtml(item.name)}</option>`
        ).join('');
        select.innerHTML = `<option value="" selected disabled>${escapeHtml(placeholder)}</option>` + options;
    }

    function resetSelect(select, placeholder, disabled) {
        select.innerHTML = `<option value="" selected disabled>${escapeHtml(placeholder)}</option>`;
        select.disabled = disabled;
    }

    function selectByName(select, name) {
        const match = Array.from(select.options).find(opt => opt.value === name);
        if (match) select.value = name;
        return match;
    }

    /* ── Gọi API lấy danh sách Phường/Xã theo mã Tỉnh/Thành phố đã chọn ── */
    async function loadWards(provinceCode, selectedWardName) {
        if (!provinceCode) {
            resetSelect(wardSelect, 'Chọn tỉnh/thành phố trước', true);
            return;
        }

        resetSelect(wardSelect, 'Đang tải...', true);

        try {
            const res = await fetch(`/api/location/provinces/${provinceCode}/wards`, { headers: { 'Accept': 'application/json' } });
            const wards = await res.json();

            if (!wards.length) {
                resetSelect(wardSelect, 'Không có dữ liệu phường/xã', true);
                return;
            }

            fillSelect(wardSelect, wards, 'Chọn phường/xã');
            wardSelect.disabled = false;

            if (selectedWardName) {
                selectByName(wardSelect, selectedWardName);
            }
        } catch {
            resetSelect(wardSelect, 'Không thể tải danh sách', false);
        }
    }

    provinceSelect.addEventListener('change', function () {
        const option = provinceSelect.selectedOptions[0];
        loadWards(option?.dataset.code || '');
    });

    window.applyCheckoutLocation = async function (cityName, wardName) {
        if (cityName) {
            const matched = selectByName(provinceSelect, cityName);
            if (matched) {
                await loadWards(matched.dataset.code, wardName);
            }
        }
    };

    document.addEventListener('DOMContentLoaded', async function () {
        try {
            const res = await fetch('{{ route('location.provinces') }}', { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            fillSelect(provinceSelect, data, 'Chọn tỉnh/thành phố');
        } catch {
            resetSelect(provinceSelect, 'Không thể tải danh sách', false);
            return;
        }

        const oldCity = provinceSelect.dataset.selected;
        if (oldCity) {
            const matched = selectByName(provinceSelect, oldCity);
            if (matched) {
                await loadWards(matched.dataset.code, wardSelect.dataset.selected);
            }
        }

        await prefillDefaultAddress();
    });

    /* ── Gọi API lấy địa chỉ mặc định của user để điền sẵn form (bỏ qua nếu form đang hiển thị lại do lỗi validate) ── */
    async function prefillDefaultAddress() {
        const shippingBlock = document.querySelector('[data-has-old]');
        if (shippingBlock?.dataset.hasOld === '1') return;

        try {
            const res = await fetch('{{ route('addresses.index') }}', {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;

            const addresses = await res.json();
            const defaultAddr = addresses.find(a => a.is_default) || addresses[0];
            if (!defaultAddr) return;

            await window.applyCheckoutLocation?.(defaultAddr.city, defaultAddr.ward);

            const apartmentInput = document.getElementById('apartment_number');
            if (apartmentInput && !apartmentInput.value) {
                apartmentInput.value = defaultAddr.apartment_number;
            }
        } catch {
            /* im lặng bỏ qua, người dùng vẫn có thể tự nhập */
        }
    }
})();
</script>

<script>
/* ══ Address Book Modal ══ */
(function () {
    const csrf     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const overlay  = document.getElementById('addressBookOverlay');
    const stepList = document.getElementById('addrStepList');
    const stepForm = document.getElementById('addrStepForm');
    const listBody = document.getElementById('addrListBody');
    const toast    = document.getElementById('addrToast');

    /* ── Open / close ── */
    function openModal() {
        overlay.style.display = 'flex';
        overlay.classList.add('is-open');
        showStep('list');
        loadAddresses();
    }

    function closeModal() {
        overlay.style.display = 'none';
        overlay.classList.remove('is-open');
        resetForm();
    }

    function showStep(step) {
        if (step === 'list') {
            stepList.style.display = 'flex';
            stepForm.style.display = 'none';
        } else {
            stepList.style.display = 'none';
            stepForm.style.display = 'flex';
            setTimeout(() => document.getElementById('addrFormApartment')?.focus(), 80);
        }
    }

    /* ── Triggers ── */
    document.getElementById('openAddressBookBtn')?.addEventListener('click', openModal);
    document.getElementById('closeAddressBookBtn')?.addEventListener('click', closeModal);
    document.getElementById('closeAddressBookBtn2')?.addEventListener('click', closeModal);
    document.getElementById('openAddFormBtn')?.addEventListener('click', () => showStep('form'));
    document.getElementById('backToListBtn')?.addEventListener('click', () => { resetForm(); showStep('list'); });
    document.getElementById('cancelFormBtn')?.addEventListener('click', () => { resetForm(); showStep('list'); });

    overlay?.addEventListener('click', e => { if (e.target === overlay) closeModal(); });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && overlay?.style.display === 'flex') closeModal();
    });

    /* ── Toast notification ── */
    let toastTimer;
    function showToast(msg, icon = '✓') {
        if (!toast) return;
        toast.innerHTML = `<span>${icon}</span> ${msg}`;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 2800);
    }

    /* ── Flash checkout fields after apply ── */
    function flashField(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('field-flash');
        void el.offsetWidth;
        el.classList.add('field-flash');
        el.addEventListener('animationend', () => el.classList.remove('field-flash'), { once: true });
    }

    /* ── Load address list ── */
    async function loadAddresses() {
        listBody.innerHTML = `
            <div class="addr-loading">
                <div class="addr-skeleton"></div>
                <div class="addr-skeleton" style="opacity:.55;"></div>
            </div>`;

        try {
            const res  = await fetch('{{ route('addresses.index') }}', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();
            renderList(data);
        } catch {
            listBody.innerHTML = `<div class="addr-empty-state">
                <div class="addr-empty-icon"><i class="bi bi-wifi-off"></i></div>
                <div class="addr-empty-title">Không thể tải danh sách</div>
                <div class="addr-empty-sub">Vui lòng kiểm tra kết nối và thử lại.</div>
            </div>`;
        }
    }

    function renderList(addresses) {
        if (!addresses.length) {
            listBody.innerHTML = `
                <div class="addr-empty-state">
                    <div class="addr-empty-icon"><i class="bi bi-geo-alt"></i></div>
                    <div class="addr-empty-title">Chưa có địa chỉ nào</div>
                    <div class="addr-empty-sub">Thêm địa chỉ để thanh toán nhanh hơn.</div>
                </div>`;
            return;
        }

        listBody.innerHTML = addresses.map(addr => `
            <div class="addr-list-item" tabindex="0"
                 data-addr-id="${addr.id}"
                 data-city="${esc(addr.city)}"
                 data-ward="${esc(addr.ward)}"
                 data-apartment="${esc(addr.apartment_number)}">

                <div class="addr-list-radio">
                    <div class="addr-list-radio-dot"></div>
                </div>

                <div class="addr-list-content">
                    <div class="addr-list-item-top">
                        <div class="addr-list-item-main">${esc(addr.apartment_number)}</div>
                        ${addr.is_default ? '<span class="addr-default-badge">Mặc định</span>' : ''}
                        <button class="addr-item-delete" data-delete-addr="${addr.id}" title="Xóa địa chỉ">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </div>
                    <div class="addr-list-item-detail">
                        ${[addr.ward, addr.city].filter(Boolean).join(' Â· ')}
                    </div>
                </div>
            </div>
        `).join('');

        /* click/Enter to apply */
        listBody.querySelectorAll('.addr-list-item').forEach(item => {
            const apply = e => {
                if (e.target.closest('[data-delete-addr]')) return;
                /* deselect all, select this */
                listBody.querySelectorAll('.addr-list-item').forEach(i => i.classList.remove('selected'));
                item.classList.add('selected');

                setTimeout(() => {
                    applyAddress(item);
                    closeModal();
                }, 180);
            };

            item.addEventListener('click', apply);
            item.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); apply(e); } });
        });

        /* delete */
        listBody.querySelectorAll('[data-delete-addr]').forEach(btn => {
            btn.addEventListener('click', async e => {
                e.stopPropagation();
                const item = btn.closest('.addr-list-item');

                /* shake animation */
                item.style.transition = 'transform 0.1s';
                item.style.transform  = 'translateX(-6px)';
                setTimeout(() => item.style.transform = 'translateX(0)', 120);

                if (!confirm('Xóa địa chỉ này?')) return;

                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                btn.disabled  = true;

                try {
                    await fetch(`/tai-khoan/dia-chi/${btn.dataset.deleteAddr}`, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    });

                    item.style.transition = 'opacity 0.25s, transform 0.25s';
                    item.style.opacity    = '0';
                    item.style.transform  = 'translateX(16px)';
                    setTimeout(() => {
                        item.remove();
                        if (!listBody.querySelectorAll('.addr-list-item').length) {
                            listBody.innerHTML = `
                                <div class="addr-empty-state">
                                    <div class="addr-empty-icon"><i class="bi bi-geo-alt"></i></div>
                                    <div class="addr-empty-title">Chưa có địa chỉ nào</div>
                                    <div class="addr-empty-sub">Thêm địa chỉ để thanh toán nhanh hơn.</div>
                                </div>`;
                        }
                    }, 260);
                } catch {
                    showToast('Không thể xóa địa chỉ.', '✕');
                    btn.innerHTML = '<i class="bi bi-trash3"></i>';
                    btn.disabled  = false;
                }
            });
        });
    }

    async function applyAddress(item) {
        setVal('apartment_number', item.dataset.apartment);
        await window.applyCheckoutLocation?.(item.dataset.city, item.dataset.ward);
        ['apartment_number', 'ward', 'province'].forEach(flashField);
        showToast('Đã áp dụng địa chỉ');
    }

    function setVal(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value;
    }

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    /* ── Save new address ── */
    document.getElementById('addAddressForm')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        const body = {
            apartment_number: val('addrFormApartment'),
            ward:             val('addrFormWard'),
            city:             val('addrFormCity'),
            is_default:       document.getElementById('addrFormDefault')?.checked ? 1 : 0,
        };

        const saveBtn = document.getElementById('saveAddressBtn');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';

        try {
            const res  = await fetch('{{ route('addresses.store') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                body: JSON.stringify(body),
            });
            const data = await res.json();

            if (!res.ok) {
                if (data.errors) {
                    Object.entries(data.errors).forEach(([field, msgs]) => {
                        const key    = capitalise(field);
                        const errEl  = document.getElementById('err' + key);
                        const inpEl  = document.getElementById('addrForm' + key);
                        if (errEl) errEl.textContent = msgs[0];
                        if (inpEl) inpEl.classList.add('is-invalid');
                    });
                }
                return;
            }

            const addr = data.address;
            setVal('apartment_number', addr.apartment_number);
            await window.applyCheckoutLocation?.(addr.city, addr.ward);

            const phoneInput = document.getElementById('addrFormPhone');
            if (phoneInput?.value) setVal('phone', phoneInput.value);

            ['apartment_number', 'ward', 'province'].forEach(flashField);
            closeModal();
            showToast('Địa chỉ đã được lưu và áp dụng');
        } catch {
            showToast('Đã có lỗi. Vui lòng thử lại.', '✕');
        } finally {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check2"></i> Lưu địa chỉ';
        }
    });

    function val(id) { return (document.getElementById(id)?.value || '').trim(); }

    function capitalise(str) {
        return str.replace(/_([a-z])/g, (_, c) => c.toUpperCase())
                  .replace(/^[a-z]/, c => c.toUpperCase());
    }

    function clearErrors() {
        document.querySelectorAll('.addr-err').forEach(el => el.textContent = '');
        document.querySelectorAll('.addr-input.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function resetForm() {
        document.getElementById('addAddressForm')?.reset();
        clearErrors();
    }
})();
</script>
@endpush
