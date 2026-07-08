{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
<style>
    /* Bảng ít cột hơn bảng sản phẩm — đè min-width:1420px kế thừa từ .product-table dùng chung
       bằng đúng tổng độ rộng cột của trang này. */
    #voucherTable { min-width: 1180px; }

    /* ── Kiểu giảm giá (% / tiền mặt) ── */
    .voucher-type-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 13px;
        min-height: 28px;
        line-height: 1;
        white-space: nowrap;
    }
    .voucher-type-badge--percentage { background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; }
    .voucher-type-badge--fixed      { background: #F0F9FF; border: 1.5px solid #BAE6FD; color: #0369A1; }

    .voucher-code {
        display: inline-block;
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        color: #374151;
        background: #F1F5F9;
        padding: 3px 7px;
        border-radius: 5px;
        white-space: nowrap;
    }

    /* ── Hạn dùng: badge còn hạn / sắp hết hạn / đã hết hạn ── */
    .voucher-expiry-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        min-height: 22px;
        line-height: 1;
        white-space: nowrap;
    }
    .voucher-expiry-badge--active  { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; }
    .voucher-expiry-badge--soon    { background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; }
    .voucher-expiry-badge--expired { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; }

    /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
    .voucher-status-dropdown { position: relative; display: inline-block; width: auto; }
    .voucher-status-trigger {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-width: 1.5px;
        border-style: solid;
        cursor: pointer;
    }
    .voucher-status-trigger:hover { filter: brightness(0.97); }
    .voucher-status-trigger:focus { outline: none; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15); }
    .voucher-status-caret { font-size: 9px; opacity: .65; transition: transform .15s; }
    .voucher-status-trigger.is-open .voucher-status-caret { transform: rotate(180deg); }
    .voucher-status-dropdown .hk-cat-panel { left: 0; right: auto; width: 170px; }

    /* ── Nút Sửa/Xóa: icon thuần túy ── */
    .voucher-row-action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border: 1px solid transparent;
        border-radius: 8px;
        color: #0F172A;
        background: #F1F5F9;
        font-size: 13px;
        transition: background .15s, color .15s;
    }
    .voucher-row-action-btn:hover {
        background: #E2E8F0;
        color: #020617;
    }
    .voucher-row-action-btn[data-delete-url]:hover {
        background: #FEF2F2;
        color: #DC2626;
    }

    /* ── Lọc theo khoảng ngày hiệu lực ── */
    .voucher-date-range {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .voucher-date-input {
        min-height: 38px;
        width: 155px;
        border: 1px solid #D8E0EA;
        border-radius: 10px;
        font-size: 13px;
    }
    .voucher-date-input:focus {
        border-color: #16A34A;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
    }
    .voucher-date-sep {
        color: #94A3B8;
        font-weight: 700;
    }

    /* Gộp bảng + thanh phân trang thành 1 khối liền */
    .product-admin-page .voucher-table-wrap,
    .product-admin-page .voucher-pagination-bar {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    }
    .product-admin-page .voucher-table-wrap { border-radius: 12px 12px 0 0 !important; }
    .product-admin-page .voucher-pagination-bar {
        border-radius: 0 0 12px 12px !important;
        border-color: #E2E8F0 !important;
    }
    [data-theme="dark"] .product-admin-page .voucher-pagination-bar {
        background: #0F1B31 !important;
        border-color: #22324D !important;
    }

    /* ── Dark mode ── */
    [data-theme="dark"] .voucher-type-badge--percentage { background: rgba(59,130,246,0.12) !important; border-color: rgba(59,130,246,0.3) !important; color: #93C5FD !important; }
    [data-theme="dark"] .voucher-type-badge--fixed      { background: rgba(14,165,233,0.12) !important; border-color: rgba(14,165,233,0.3) !important; color: #7DD3FC !important; }
    [data-theme="dark"] .voucher-expiry-badge--active   { background: rgba(34,197,94,0.12) !important; border-color: rgba(34,197,94,0.3) !important; color: #86EFAC !important; }
    [data-theme="dark"] .voucher-expiry-badge--soon     { background: rgba(251,191,36,0.12) !important; border-color: rgba(251,191,36,0.3) !important; color: #FCD34D !important; }
    [data-theme="dark"] .voucher-expiry-badge--expired  { background: rgba(239,68,68,0.12) !important; border-color: rgba(239,68,68,0.3) !important; color: #FCA5A5 !important; }
    [data-theme="dark"] .voucher-code {
        background: #162843 !important;
        color: #CBD5E1 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-panel {
        background: #101C33 !important;
        border-color: #2A3B59 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-item {
        color: #E2E8F0 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-item:hover {
        background: #162843 !important;
    }
    [data-theme="dark"] .voucher-row-action-btn {
        background: #101C33 !important;
        color: #CBD5E1 !important;
    }
    [data-theme="dark"] .voucher-row-action-btn:hover {
        background: #162843 !important;
        color: #fff !important;
    }
    [data-theme="dark"] .voucher-row-action-btn[data-delete-url]:hover {
        background: rgba(239,68,68,0.12) !important;
        color: #FCA5A5 !important;
    }

    /* ── COMPACT VIEW (Tương đương hiệu ứng Zoom 90%) ── */
    #voucherFilterForm .product-toolbar-left { gap: 6px !important; }
    #voucherFilterForm .product-search { width: 190px !important; flex: 0 0 190px !important; }
    #voucherFilterForm .hk-cat-filter { width: 150px !important; flex: 0 0 150px !important; }
    #voucherFilterForm .voucher-date-range { gap: 4px !important; }
    #voucherFilterForm .voucher-date-input {
        width: 130px !important;
        min-height: 34px !important;
        height: 34px !important;
        font-size: 12px !important;
        padding: 0 10px !important;
    }
    .product-admin-page .voucher-type-badge,
    .product-admin-page .status-badge {
        font-size: 11px !important;
        padding: 3px 10px !important;
        min-height: 22px !important;
    }
    .product-admin-page .voucher-code {
        font-size: 11px !important;
        padding: 2px 5px !important;
    }
</style>


{{-- Styles extracted from create/edit forms --}}
<style>
    .voucher-form-card { border: 1px solid var(--hk-border, #E2E8F0); border-radius: 14px; background: var(--hk-bg-card, #fff); }
    .voucher-form-card .card-header {
        background: var(--hk-bg-card, #fff);
        border-bottom: 1px solid var(--hk-border, #E2E8F0);
        padding: 14px 20px;
        border-radius: 14px 14px 0 0;
    }
    .voucher-form-card .card-header span { font-size: 14px; font-weight: 800; color: var(--hk-text-1, #0F172A); }
    .voucher-form-card .card-body { padding: 20px; }

    .voucher-field { margin-bottom: 16px; }
    .voucher-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .voucher-field .form-control,
    .voucher-field .form-select {
        min-height: 34px;
        font-size: 13px;
        border-color: #D8E0EA;
        background: var(--hk-bg-input, #fff);
        color: var(--hk-text-1, #111);
        border-radius: 8px;
    }
    .voucher-field .form-control:focus,
    .voucher-field .form-select:focus {
        border-color: #16A34A;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
    }
    .voucher-field .form-text { font-size: 12px; color: #94A3B8; margin-top: 4px; }
    .voucher-field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

    .voucher-code-input { text-transform: uppercase; letter-spacing: .03em; font-weight: 700; }

    .voucher-status-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 34px;
        padding: 6px 2px;
    }
    .voucher-status-toggle .form-check-input { width: 40px; height: 22px; cursor: pointer; }
    .voucher-status-toggle .form-check-input:checked { background-color: #16A34A; border-color: #16A34A; }
    .voucher-status-toggle label { font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin: 0; cursor: pointer; }

    .voucher-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 4px;
    }
    .voucher-form-actions .btn {
        min-height: 38px;
        padding: 8px 20px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
    }
    .voucher-form-actions .btn-dark {
        background: #059669 !important;
        border-color: #059669 !important;
        color: #fff !important;
    }
    .voucher-form-actions .btn-dark:hover { background: #047857 !important; border-color: #047857 !important; }
    .voucher-form-actions .btn-light {
        background: #fff !important;
        border: 1.5px solid #D8E0EA !important;
        color: #64748B !important;
    }
    .voucher-form-actions .btn-light:hover {
        background: #F8FAFC !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
    }

    @media (max-width: 767.98px) {
        .voucher-field-row { grid-template-columns: 1fr; }
    }
</style>
