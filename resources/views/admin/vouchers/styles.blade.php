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
    .voucher-expiry-badge--paused  { background: #F1F5F9; border: 1.5px solid #CBD5E1; color: #64748B; }

    .product-action-btn--trash {
        background: #fff !important;
        border: 1.5px solid #FCA5A5 !important;
        color: #DC2626 !important;
    }
    .product-action-btn--trash:hover {
        background: #FEF2F2 !important;
        border-color: #EF4444 !important;
        color: #B91C1C !important;
    }

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
    .voucher-status-dropdown .hk-cat-panel { display: none !important; left: 0; right: auto; width: 170px; }
    .voucher-status-shared-panel {
        position: fixed !important;
        z-index: 3000 !important;
        width: 180px !important;
        max-height: none !important;
        overflow: visible !important;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18) !important;
    }

    .voucher-edit-modal[hidden] { display: none !important; }
    .voucher-edit-modal {
        position: fixed;
        inset: 0;
        z-index: 3060;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .voucher-edit-modal__overlay {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.52);
        backdrop-filter: blur(3px);
    }
    .voucher-edit-modal__dialog {
        position: relative;
        width: min(980px, calc(100vw - 48px));
        max-height: calc(100vh - 48px);
        overflow: hidden;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(15, 23, 42, 0.28);
        display: flex;
        flex-direction: column;
    }
    .voucher-edit-modal__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding: 20px 24px;
        border-bottom: 1px solid #E2E8F0;
    }
    .voucher-edit-modal__header h2 {
        margin: 0;
        color: #0F172A;
        font-size: 22px;
        font-weight: 900;
    }
    .voucher-edit-modal__header p {
        margin: 4px 0 0;
        color: #64748B;
        font-size: 13px;
    }
    .voucher-edit-modal__close {
        width: 38px;
        height: 38px;
        border: 0;
        border-radius: 10px;
        background: #F1F5F9;
        color: #64748B;
        font-size: 17px;
    }
    .voucher-edit-modal__body {
        overflow: auto;
        padding: 22px 24px 24px;
    }
    .voucher-edit-modal__body .app-main,
    .voucher-edit-modal__body main {
        padding: 0 !important;
    }
    .voucher-edit-modal__body .voucher-form-actions {
        position: sticky;
        bottom: -24px;
        margin: 18px -24px -24px;
        padding: 14px 24px;
        background: #F8FAFC;
        border-top: 1px solid #E2E8F0;
    }

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
    [data-theme="dark"] .voucher-expiry-badge--paused   { background: rgba(148,163,184,0.12) !important; border-color: rgba(148,163,184,0.3) !important; color: #CBD5E1 !important; }
    [data-theme="dark"] .voucher-code {
        background: #162843 !important;
        color: #CBD5E1 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-panel,
    [data-theme="dark"] .voucher-status-shared-panel {
        background: #101C33 !important;
        border-color: #2A3B59 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-item,
    [data-theme="dark"] .voucher-status-shared-panel .hk-cat-item {
        color: #E2E8F0 !important;
    }
    [data-theme="dark"] .voucher-status-dropdown .hk-cat-item:hover,
    [data-theme="dark"] .voucher-status-shared-panel .hk-cat-item:hover {
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
    #voucherFilterForm #hkVoucherTypeDrop { width: 190px !important; flex-basis: 190px !important; }
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

    .voucher-trash-back-btn {
        min-height: 42px;
        padding: 9px 18px;
        border-radius: 10px !important;
        font-size: 13px;
        color: #475569;
        background: #fff;
    }
    .voucher-trash-table-wrap {
        overflow: hidden;
        border: 1px solid #D8E0EA;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.04), 0 2px 4px -2px rgb(0 0 0 / 0.04);
    }
    .voucher-trash-table {
        margin-bottom: 0;
        color: #0F172A;
    }
    .voucher-trash-table thead th {
        height: 58px;
        background: #F1F5F9;
        border-bottom: 1px solid #D8E0EA;
        color: #334155;
        font-size: 14px;
        font-weight: 800;
        vertical-align: middle;
        white-space: nowrap;
    }
    .voucher-trash-table tbody td {
        height: 62px;
        border-color: #E2E8F0;
        vertical-align: middle;
        font-size: 13px;
    }
    .voucher-trash-id {
        color: #64748B;
        font-weight: 700;
    }
    .voucher-trash-value {
        color: #0F172A;
        font-size: 14px;
        font-weight: 800;
    }
    .voucher-trash-sub,
    .voucher-trash-date-range,
    .voucher-trash-date {
        color: #64748B;
        font-size: 12px;
    }
    .voucher-trash-table .btn-sm {
        min-height: 32px;
        border-radius: 8px;
        font-size: 12px;
    }
    .voucher-trash-pagination {
        background: #fff;
        border-color: #E2E8F0 !important;
    }
    [data-theme="dark"] .voucher-trash-table-wrap,
    [data-theme="dark"] .voucher-trash-pagination {
        background: #0F1B31;
        border-color: #22324D !important;
    }
    [data-theme="dark"] .voucher-trash-table thead th {
        background: #14233A;
        border-color: #22324D;
        color: #CBD5E1;
    }
    [data-theme="dark"] .voucher-trash-table tbody td {
        border-color: #22324D;
        color: #E2E8F0;
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

    #description,
    .voucher-field textarea {
        height: auto !important;
        resize: vertical !important;
        min-height: 97px !important;
        font-size: 13.5px !important;
        line-height: 1.5 !important;
    }

    @media (max-width: 767.98px) {
        .voucher-field-row { grid-template-columns: 1fr; }
    }
</style>
