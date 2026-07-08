@extends('layouts.admin')

@section('title', 'Thêm voucher')

@push('styles')
    @include('admin.vouchers.styles')
    <style>
        .create-header-title { font-size: 25px; font-weight: 800; color: #000 !important; margin-bottom: 4px; }
        .create-header-desc { color: #64748b; font-size: 14px; margin: 0; }
        .voucher-create-page { padding-bottom: 92px; }
        .voucher-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 40px;
            padding: 8px 16px;
            border: 1.5px solid #CBD5E1;
            border-radius: 10px;
            background: #fff;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
           
        }
        .voucher-back-btn:hover {
            background: #F8FAFC;
            border-color: #94A3B8;
            color: #0F172A;
        }
        .voucher-create-sticky-actions {
            position: fixed;
            left: 250px;
            right: 0;
            bottom: 0;
            z-index: 1040;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            min-height: 76px;
            padding: 14px 32px;
            background: rgba(248, 250, 252, .96);
            border-top: 1px solid #D8E0EA;
            box-shadow: 0 -10px 24px rgba(15, 23, 42, .08);
            backdrop-filter: blur(10px);
           
        }
        .sidebar-collapse .voucher-create-sticky-actions {
            left: 0;
        }
        .voucher-create-sticky-actions .btn {
            min-width: 128px;
            min-height: 42px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 800;
        }
        .voucher-create-sticky-actions .btn-dark {
            background: #059669 !important;
            border-color: #059669 !important;
        }
        .voucher-create-sticky-actions .btn-dark:hover {
            background: #047857 !important;
            border-color: #047857 !important;
        }
        .voucher-create-sticky-actions .btn-light {
            background: #fff !important;
            border: 1.5px solid #D8E0EA !important;
            color: #475569 !important;
        }
        .voucher-create-sticky-actions .btn-light:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            color: #0F172A !important;
        }
        .voucher-status-toggle .form-check-input {
            width: 30px;
            height: 16px;
            margin-top: 0;
        }

        /* ── Dropdown tuỳ chỉnh (bo viền) thay cho <select> mặc định của trình duyệt ── */
        .voucher-field .vc-dropdown.hk-cat-filter { width: 100%; flex: none; }
        .vc-dropdown .hk-cat-trigger { min-height: 34px; font-size: 13px; }
        .vc-dropdown.is-invalid .hk-cat-trigger { border-color: #dc3545; }
        .vc-dropdown .hk-cat-panel { left: 0; right: auto; width: 100%; }
        @media (max-width: 991.98px) {
            .voucher-create-sticky-actions { left: 0; padding-inline: 16px; }
        }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4 voucher-create-page">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="create-header-title mb-1">Thêm voucher</h1>
            <p class="create-header-desc mb-0">Tạo mã giảm giá mới cho hệ thống.</p>
        </div>
        <a href="{{ route('admin.vouchers.list') }}" class="voucher-back-btn">
            <i class="fa-solid fa-arrow-left"></i> Quay l&#7841;i
        </a>
    </div>

    <form method="POST" action="{{ route('admin.vouchers.store') }}" id="voucherCreateForm">
        @csrf

        <div class="row">
            <div class="col-lg-7">
                <div class="card voucher-form-card shadow-sm mb-4">
                    <div class="card-header"><span>Thông tin chung</span></div>
                    <div class="card-body">
                        <div class="voucher-field">
                            <label for="code">Mã code <span class="text-danger">*</span></label>
                            <input type="text" id="code" name="code"
                                class="form-control voucher-code-input @error('code') is-invalid @enderror"
                                value="{{ old('code') }}" placeholder="VD: SALE50" maxlength="50" required autofocus>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="voucher-field">
                            <label>Kiểu giảm giá <span class="text-danger">*</span></label>
                            @php $oldType = old('type', 'percentage'); @endphp
                            <input type="hidden" name="type" id="vcTypeHidden" value="{{ $oldType }}">
                            <div class="hk-cat-filter vc-dropdown @error('type') is-invalid @enderror" id="vcTypeDrop">
                                <button type="button" class="hk-cat-trigger" id="vcTypeTrigger" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="hk-cat-trigger-label" id="vcTypeLabel">{{ $oldType === 'fixed' ? 'Tiền mặt' : 'Theo %' }}</span>
                                    <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                                </button>
                                <div class="hk-cat-panel" id="vcTypePanel" hidden>
                                    <div class="hk-cat-list" id="vcTypeList" role="listbox">
                                        <button type="button" class="hk-cat-item {{ $oldType === 'percentage' ? 'is-active' : '' }}" data-value="percentage" data-label="Theo %">Theo %</button>
                                        <button type="button" class="hk-cat-item {{ $oldType === 'fixed' ? 'is-active' : '' }}" data-value="fixed" data-label="Tiền mặt">Tiền mặt</button>
                                    </div>
                                </div>
                            </div>
                            @error('type') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <div class="voucher-field-row">
                            <div class="voucher-field">
                                <label for="value">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" id="value" name="value" min="0" step="0.01"
                                    class="form-control @error('value') is-invalid @enderror"
                                    value="{{ old('value') }}" placeholder="0" required>
                                <div class="form-text" id="vcValueHint">Nhập phần trăm giảm (0–100).</div>
                                @error('value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="voucher-field">
                                <label for="quantity">Số lượng phát hành <span class="text-danger">*</span></label>
                                <input type="number" id="quantity" name="quantity" min="1" step="1"
                                    class="form-control @error('quantity') is-invalid @enderror"
                                    value="{{ old('quantity', 100) }}" required>
                                @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="voucher-field-row">
                            <div class="voucher-field">
                                <label for="min_order_amount">Đơn hàng tối thiểu</label>
                                <input type="number" id="min_order_amount" name="min_order_amount" min="0" step="1000"
                                    class="form-control @error('min_order_amount') is-invalid @enderror"
                                    value="{{ old('min_order_amount', 0) }}">
                                <div class="form-text">Giá trị đơn hàng tối thiểu để áp dụng mã (VNĐ).</div>
                                @error('min_order_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="voucher-field">
                                <label for="max_discount_amount">Giảm tối đa</label>
                                <input type="number" id="max_discount_amount" name="max_discount_amount" min="0" step="1000"
                                    class="form-control @error('max_discount_amount') is-invalid @enderror"
                                    value="{{ old('max_discount_amount') }}" placeholder="Không giới hạn">
                                <div class="form-text">Chỉ áp dụng khi giảm theo %.</div>
                                @error('max_discount_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card voucher-form-card shadow-sm mb-4">
                    <div class="card-header"><span>Cấu hình nâng cao</span></div>
                    <div class="card-body">
                        <div class="voucher-field">
                            <label for="start_date">Thời gian bắt đầu <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="start_date" name="start_date"
                                class="form-control @error('start_date') is-invalid @enderror"
                                value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="voucher-field">
                            <label for="end_date">Thời gian hết hạn <span class="text-danger">*</span></label>
                            <input type="datetime-local" id="end_date" name="end_date"
                                class="form-control @error('end_date') is-invalid @enderror"
                                value="{{ old('end_date', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="voucher-field">
                            <label>Trạng thái kích hoạt</label>
                            <div class="voucher-status-toggle">
                                <input type="checkbox" class="form-check-input" id="status" name="status" value="1"
                                    {{ old('status', true) ? 'checked' : '' }}>
                                <label for="status">Cho phép sử dụng ngay sau khi tạo</label>
                            </div>
                        </div>

                        <div class="voucher-field mb-0">
                            <label for="description">Ghi chú</label>
                            <textarea id="description" name="description" rows="6"
                                class="form-control @error('description') is-invalid @enderror"
                                placeholder="Nhập mô tả hoặc điều kiện áp dụng cho voucher...">{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="voucher-create-sticky-actions">
            <a href="{{ route('admin.vouchers.list') }}" class="btn btn-light">
                <i class="fa-solid fa-arrow-left me-1"></i> H&#7911;y
            </a>
            <button type="submit" class="btn btn-dark">
                <i class="fa-solid fa-floppy-disk me-1"></i> L&#432;u voucher
            </button>
        </div>
    </form>
</main>
@endsection

@push('scripts')
<script>
(function () {
    /* ── Uppercase mã code khi gõ ── */
    const codeInput = document.getElementById('code');
    codeInput?.addEventListener('input', function () {
        const pos = this.selectionStart;
        this.value = this.value.toUpperCase();
        this.setSelectionRange(pos, pos);
    });

    /* ── Dropdown Kiểu giảm giá ── */
    const trigger = document.getElementById('vcTypeTrigger');
    const panel   = document.getElementById('vcTypePanel');
    const label   = document.getElementById('vcTypeLabel');
    const list    = document.getElementById('vcTypeList');
    const hidden  = document.getElementById('vcTypeHidden');
    const valueHint = document.getElementById('vcValueHint');

    const valueInput = document.getElementById('value');
    const maxDiscountInput = document.getElementById('max_discount_amount');

    function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
    function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

    function syncHint() {
        if (!valueHint) return;
        if (hidden.value === 'fixed') {
            valueHint.textContent = 'Nhập số tiền giảm cố định (VNĐ).';
            valueInput?.removeAttribute('max');
            if (maxDiscountInput) {
                maxDiscountInput.setAttribute('disabled', 'disabled');
                maxDiscountInput.value = '';
                maxDiscountInput.placeholder = 'Không áp dụng cho tiền mặt';
            }
        } else {
            valueHint.textContent = 'Nhập phần trăm giảm (0–100).';
            valueInput?.setAttribute('max', '100');
            if (valueInput && parseFloat(valueInput.value) > 100) {
                valueInput.value = 100;
            }
            if (maxDiscountInput) {
                maxDiscountInput.removeAttribute('disabled');
                maxDiscountInput.placeholder = 'Không giới hạn';
            }
        }
    }

    valueInput?.addEventListener('input', function () {
        if (hidden.value === 'percentage') {
            if (parseFloat(this.value) > 100) {
                this.value = 100;
            }
        }
    });

    trigger?.addEventListener('click', () => panel.hidden ? open() : close());

    list?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        label.textContent = btn.dataset.label;
        hidden.value = btn.dataset.value;
        syncHint();
        close();
    });

    document.addEventListener('click', function (e) {
        if (!panel.hidden && !document.getElementById('vcTypeDrop')?.contains(e.target)) close();
    });

    syncHint();
}());
</script>
@endpush
