<style>
    .product-admin-page {
        background: #f8fafc;
        min-height: calc(100vh - 56px);
        padding-top: 0 !important;
    }

    .product-header-title {
        color: #000000 !important;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: 0;
        margin-top: 15px;
    }

    .product-header-desc {
        color: #64748b;
        font-size: 16px;
    }

    .product-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
    }

    /* ── Thẻ tổng hợp số liệu sản phẩm ── */
    .product-stat-row {
        margin-top: 8px;
        margin-bottom: 20px;
    }
    .product-stat-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 16px 20px;
        height: 100%;
    }
    .product-stat-label {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .03em;
        color: #64748B;
        margin-bottom: 8px;
    }
    .product-stat-value {
        font-size: 26px;
        font-weight: 800;
        color: #0F172A;
        line-height: 1;
    }
    .product-stat-value--success { color: #16A34A; }
    .product-stat-value--danger { color: #DC2626; }

    [data-theme="dark"] .product-stat-card {
        background: #0F1B31 !important;
        border-color: #22324D !important;
    }
    [data-theme="dark"] .product-stat-label { color: #94A3B8 !important; }
    [data-theme="dark"] .product-stat-value { color: #F8FAFC !important; }
    [data-theme="dark"] .product-stat-value--success { color: #4ADE80 !important; }
    [data-theme="dark"] .product-stat-value--danger { color: #F87171 !important; }

    .product-toolbar {
        display: flex;
        flex-direction: column;
        gap: 16px;
        margin: 30px 0 16px;
    }

    /* ── Ô tìm kiếm to, chiếm trọn hàng riêng để dễ gõ nội dung ── */
    .product-search-row {
        position: relative;
        width: 100%;
    }

    .product-search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        font-size: 16px;
        pointer-events: none;
    }

    .product-search {
        min-height: 52px;
        width: 100%;
        border: 1.5px solid #D8E0EA;
        border-radius: 12px;
        background: #fff;
        box-shadow: none;
        font-size: 15px;
        color: #0F172A;
        padding-left: 46px;
    }

    .product-search::placeholder {
        color: #94A3B8;
    }

    .product-search:focus {
        border-color: #16A34A;
        box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.12);
    }

    /* ── Hàng bộ lọc: cho phép xuống dòng, giãn khoảng cách rộng rãi hơn ── */
    .product-toolbar-filters-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px 20px;
    }

    .product-toolbar-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex: 1 1 auto;
        flex-wrap: wrap;
        min-width: 0;
    }

    /* Custom category dropdown */
    .hk-cat-filter {
        position: relative;
        width: 220px;
        flex: 0 0 220px;
    }

    .product-compact-filter {
        width: 145px;
        flex: 0 0 145px;
    }

    .product-compact-filter .hk-cat-panel {
        width: 180px;
    }

    .hk-cat-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        min-height: 38px;
        padding: 0 16px;
        border: 1px solid #D8E0EA;
        border-radius: 999px;
        background: #fff;
        box-shadow: none;
        font-size: 14px;
        color: #0F172A;
        text-align: left;
        cursor: pointer;
        transition: border-color .15s;
    }

    .hk-cat-trigger:hover,
    .hk-cat-trigger.is-open {
        border-color: #16A34A;
    }

    .hk-cat-trigger-label {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .hk-cat-arrow {
        font-size: 11px;
        color: #6b7280;
        transition: transform .2s;
        flex-shrink: 0;
    }

    .hk-cat-trigger.is-open .hk-cat-arrow {
        transform: rotate(180deg);
    }

    .hk-cat-panel {
        position: absolute;
        top: calc(100% + 6px);
        right: 0;
        z-index: 1050;
        width: 240px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .hk-cat-search-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 12px 8px;
        border-bottom: 1px solid #f3f4f6;
    }

    .hk-cat-search-icon {
        font-size: 13px;
        color: #9ca3af;
        flex-shrink: 0;
    }

    .hk-cat-search-input {
        flex: 1;
        border: 0;
        outline: none;
        font-size: 13px;
        color: #111827;
        background: transparent;
    }

    .hk-cat-search-input::placeholder {
        color: #9ca3af;
    }

    .hk-cat-list {
        max-height: 240px;
        overflow-y: auto;
        padding: 6px 0;
    }

    .hk-cat-item {
        display: block;
        width: 100%;
        padding: 8px 14px;
        border: 0;
        background: transparent;
        font-size: 13px;
        color: #374151;
        text-align: left;
        cursor: pointer;
        transition: background .12s;
    }

    .hk-cat-item:hover {
        background: #f3f4f6;
    }

    .hk-cat-item.is-active {
        color: #111827;
        font-weight: 700;
        background: #f0f9ff;
    }

    .hk-cat-empty {
        padding: 12px 14px;
        font-size: 13px;
        color: #9ca3af;
        text-align: center;
    }

    .product-tool-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-toolbar-right {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 14px;
    }

    .product-toolbar-right .product-status-filter {
        width: 210px;
        flex: 0 0 210px;
    }

    .product-low-stock-chip {
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
        height: 38px;
        padding: 0 16px;
        border: 1px solid #FCA5A5;
        border-radius: 999px;
        background: #fff;
        color: #DC2626;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }
    .product-low-stock-chip:hover {
        background: #FEF2F2;
        border-color: #F87171;
    }
    .product-low-stock-chip.is-active {
        background: #DC2626;
        border-color: #DC2626;
        color: #fff;
    }

    .product-action-btn {
        min-height: 38px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
    }

    .product-action-btn.btn-dark {
        background: #16A34A !important;
        border-color: #16A34A !important;
        color: #ffffff !important;
    }

    .product-action-btn.btn-dark:hover {
        background: #15803D !important;
        border-color: #15803D !important;
        color: #ffffff !important;
    }

    .product-action-btn.btn-light {
        background: #ffffff !important;
        border: 1.5px solid #F87171 !important;
        color: #DC2626 !important;
    }

    .product-action-btn.btn-light:hover {
        background: #FEF2F2 !important;
        border-color: #EF4444 !important;
        color: #B91C1C !important;
    }

    .product-action-btn--neutral {
        background: #ffffff !important;
        border: 1.5px solid #D8E0EA !important;
        color: #334155 !important;
    }

    .product-action-btn--neutral:hover {
        background: #F8FAFC !important;
        border-color: #CBD5E1 !important;
        color: #0F172A !important;
    }

    .product-table-wrap {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
    }

    /* ── Cho phép kéo ngang để xem hết cột (ID, ảnh, tên, danh mục, giá, tồn kho, trạng thái, nổi bật, thao tác...) ── */
    .product-table-wrap .table-responsive {
        overflow-x: auto !important;
        overflow-y: visible !important;
        scrollbar-width: thin;
        scrollbar-color: #CBD5E1 #F1F5F9;
    }
    .product-table-wrap .table-responsive::-webkit-scrollbar {
        height: 10px;
    }
    .product-table-wrap .table-responsive::-webkit-scrollbar-track {
        background: #F1F5F9;
    }
    .product-table-wrap .table-responsive::-webkit-scrollbar-thumb {
        background: #CBD5E1;
        border-radius: 999px;
    }
    .product-table-wrap .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94A3B8;
    }

    .product-table {
        margin: 0;
        min-width: 1420px;
        --bs-table-hover-bg: #F8FAFC;
    }

    .product-table thead th {
        height: 48px;
        padding: 0 16px;
        background: #F8FAFC;
        color: #334155;
        border-bottom: 1px solid #E2E8F0;
        border-right: 1px solid #E2E8F0;
        font-size: 13px;
        font-weight: 800;
        line-height: 1;
        vertical-align: middle;
        white-space: nowrap;
    }

    .product-table thead th:last-child {
        border-right: 0;
    }

    .product-table tbody td {
        min-height: 64px;
        padding: 15px 16px;
        color: #0F172A;
        border-bottom: 1px solid #E2E8F0;
        border-right: 1px solid #E2E8F0;
        font-size: 13px;
        vertical-align: middle;
    }

    .product-table tbody td:last-child {
        border-right: 0;
    }

    .product-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .product-check {
        width: 18px;
        height: 18px;
        cursor: pointer;
        border-color: #d1d5db;
        border-radius: 5px;
    }

    .product-sort-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0;
        color: inherit;
        background: transparent;
        border: 0;
        font: inherit;
        font-weight: 800;
    }

    .product-sort-icon {
        display: inline-flex;
        align-items: center;
        min-width: 20px;
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
    }

    .product-sort-btn.is-active {
        color: #ffffff;
        font-weight: 800;
    }

    .product-sort-btn.is-active .product-sort-icon {
        color: #ffffff;
        font-weight: 900;
    }

    .product-thumb {
        width: 56px;
        height: 56px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
    }

    .product-thumb-placeholder {
        width: 56px;
        height: 56px;
        border-radius: 4px;
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9ca3af;
        font-size: 16px;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 13px;
        min-height: 28px;
        line-height: 1;
        white-space: nowrap;
    }

    .status-badge--active {
        background: #ECFDF5;
        border: 1.5px solid #86EFAC;
        color: #16A34A;
    }

    .status-badge--inactive {
        background: #F8FAFC;
        border: 1.5px solid #CBD5E1;
        color: #64748B;
    }

    .product-status-switch-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .product-status-switch {
        width: 38px;
        height: 20px;
        cursor: pointer;
        background-color: #CBD5E1;
        border-color: #CBD5E1;
    }
    .product-status-switch:checked {
        background-color: #16A34A;
        border-color: #16A34A;
    }
    .product-status-switch:focus {
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        border-color: #16A34A;
    }
    .product-status-switch-label {
        font-size: 12px;
        font-weight: 700;
        color: #64748B;
        white-space: nowrap;
    }
    .product-status-switch:checked ~ .product-status-switch-label {
        color: #16A34A;
    }

    /* ── Sản phẩm nổi bật (ngôi sao) ── */
    .product-featured-star {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 0;
        background: transparent;
        color: #cbd5e1;
        font-size: 15px;
        cursor: pointer;
        transition: color 0.15s, background 0.15s, transform 0.1s;
    }
    .product-featured-star:hover {
        background: #fef3c7;
        color: #f59e0b;
    }
    .product-featured-star:active { transform: scale(0.9); }
    .product-featured-star.is-active { color: #f59e0b; }
    [data-theme="dark"] .product-featured-star { color: #475569; }
    [data-theme="dark"] .product-featured-star:hover { background: rgba(245,158,11,.12); color: #f59e0b; }
    [data-theme="dark"] .product-featured-star.is-active { color: #f59e0b; }

    /* ── Sửa nhanh Giá/Giảm giá (double-click) ── */
    .product-quickedit-cell { cursor: pointer; position: relative; min-width: 150px; }
    .product-quickedit-form {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        background: #fff;
        border: 1.5px solid #174761;
        border-radius: 8px;
        padding: 8px;
        position: absolute;
        top: -8px;
        left: 0;
        z-index: 20;
        box-shadow: 0 8px 24px rgba(0,0,0,.12);
        cursor: default;
    }
    .product-quickedit-row { display: flex; flex-direction: column; gap: 2px; }
    .product-quickedit-row label { font-size: 10px; font-weight: 700; color: #6b7280; text-transform: uppercase; white-space: nowrap; }
    .product-quickedit-input {
        width: 100px;
        height: 32px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        padding: 0 8px;
        font-size: 13px;
        outline: none;
    }
    .product-quickedit-input:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
    .product-quickedit-actions { display: flex; gap: 4px; }
    .product-quickedit-save, .product-quickedit-cancel {
        width: 32px; height: 32px; border-radius: 6px; border: 0;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .product-quickedit-save { background: #dcfce7; color: #16a34a; }
    .product-quickedit-save:hover { background: #bbf7d0; }
    .product-quickedit-cancel { background: #f3f4f6; color: #6b7280; }
    .product-quickedit-cancel:hover { background: #e5e7eb; }
    [data-theme="dark"] .product-quickedit-form { background: #0F1B31; border-color: #3B82F6; }
    [data-theme="dark"] .product-quickedit-input { background: #0F1B31; border-color: #2A3B59; color: #E2E8F0; }
    [data-theme="dark"] .product-quickedit-row label { color: #94A3B8; }

    .price-display {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .price-sale {
        font-weight: 800;
        color: #dc2626;
    }

    .price-original {
        font-size: 11px;
        color: #9ca3af;
        text-decoration: line-through;
    }

    .price-normal {
        font-weight: 800;
        color: #111827;
    }

    .product-more-btn {
        width: 34px;
        height: 34px;
        border: 1px solid transparent;
        border-radius: 10px;
        color: #0F172A;
        background: #F1F5F9;
        font-weight: 900;
    }

    .product-more-btn:hover {
        background: #E2E8F0;
        color: #020617;
    }

    .product-row-menu {
        min-width: 168px;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
    }

    .product-row-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        border-radius: 7px;
        font-size: 14px;
        font-weight: 600;
    }

    @media (max-width: 767.98px) {
        .product-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .product-toolbar-left,
        .product-header-actions,
        .product-tool-actions,
        .product-toolbar-right {
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .product-search {
            width: 100%;
        }
    }

    /* ── Dark mode overrides ────────────────────────────────── */
    [data-theme="dark"] .product-admin-page {
        background: #0A1428;
    }

    [data-theme="dark"] .product-header-title {
        color: #F8FAFC !important;
    }

    [data-theme="dark"] .product-header-desc {
        color: #94A3B8 !important;
    }

    [data-theme="dark"] .product-search {
        background: #101C33 !important;
        border-color: #2A3B59 !important;
        color: #E2E8F0 !important;
        box-shadow: none;
    }

    [data-theme="dark"] .product-search:focus {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
    }

    [data-theme="dark"] .product-search::placeholder {
        color: #64748B !important;
    }

    [data-theme="dark"] .hk-cat-trigger {
        background: #101C33 !important;
        border-color: #2A3B59 !important;
        color: #E2E8F0 !important;
    }

    [data-theme="dark"] .hk-cat-trigger:hover,
    [data-theme="dark"] .hk-cat-trigger.is-open {
        border-color: #3B82F6 !important;
    }

    [data-theme="dark"] .hk-cat-arrow { color: #94A3B8 !important; }

    [data-theme="dark"] .hk-cat-panel {
        background: #0F1B31 !important;
        border-color: #22324D !important;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5) !important;
    }

    [data-theme="dark"] .hk-cat-search-wrap { border-color: #22324D !important; }
    [data-theme="dark"] .hk-cat-search-input { color: #E2E8F0 !important; }
    [data-theme="dark"] .hk-cat-search-input::placeholder { color: #64748B !important; }
    [data-theme="dark"] .hk-cat-item { color: #CBD5E1 !important; }
    [data-theme="dark"] .hk-cat-item:hover { background: #162843 !important; }
    [data-theme="dark"] .hk-cat-item.is-active {
        background: rgba(59,130,246,0.15) !important;
        color: #93C5FD !important;
    }
    [data-theme="dark"] .hk-cat-empty { color: #64748B !important; }

    [data-theme="dark"] .product-table-wrap {
        background: #0F1B31 !important;
        border-color: #22324D !important;
    }
    [data-theme="dark"] .product-table-wrap .table-responsive {
        scrollbar-color: #334155 #0F1B31;
    }
    [data-theme="dark"] .product-table-wrap .table-responsive::-webkit-scrollbar-track {
        background: #0F1B31;
    }
    [data-theme="dark"] .product-table-wrap .table-responsive::-webkit-scrollbar-thumb {
        background: #334155;
    }
    [data-theme="dark"] .product-table-wrap .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #475569;
    }

    [data-theme="dark"] .product-table {
        --bs-table-hover-bg: #1A3050;
    }

    [data-theme="dark"] .product-table thead th {
        background: #0C1830 !important;
        color: #94A3B8 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .product-table tbody td {
        color: #E2E8F0 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .product-sort-btn.is-active {
        color: #ffffff !important;
    }

    [data-theme="dark"] .product-sort-btn.is-active .product-sort-icon {
        color: #ffffff !important;
    }

    [data-theme="dark"] .product-sort-icon {
        color: #60A5FA !important;
    }

    [data-theme="dark"] .status-badge--active {
        background: rgba(34,197,94,0.14) !important;
        border-color: rgba(34,197,94,0.35) !important;
        color: #86EFAC !important;
    }

    [data-theme="dark"] .status-badge--inactive {
        background: rgba(100,116,139,0.14) !important;
        border-color: rgba(148,163,184,0.30) !important;
        color: #CBD5E1 !important;
    }

    [data-theme="dark"] .product-thumb {
        border-color: #22324D !important;
    }

    [data-theme="dark"] .product-thumb-placeholder {
        background: #0C1830 !important;
        border-color: #22324D !important;
        color: #64748B !important;
    }

    [data-theme="dark"] .price-normal { color: #E2E8F0 !important; }
    [data-theme="dark"] .price-original { color: #64748B !important; }

    [data-theme="dark"] .product-more-btn {
        background: #16243A !important;
        border: 1px solid #2A3B59 !important;
        color: #E2E8F0 !important;
    }

    [data-theme="dark"] .product-more-btn:hover {
        background: #1D3150 !important;
        border-color: #3B82F6 !important;
    }

    [data-theme="dark"] .product-row-menu {
        background: #0F1B31 !important;
        border-color: #22324D !important;
        box-shadow: 0 16px 40px rgba(0,0,0,0.5) !important;
    }
</style>


{{-- Styles extracted from edit.blade.php --}}
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control,
    .edit-field .form-select { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus,
    .edit-field .form-select:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
    .edit-field .form-text { font-size: 12px; color: var(--hk-text-3, #6b7280); margin-top: 4px; }
    .section-label { font-size: 11px; font-weight: 800; color: #174761; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; padding-bottom: 6px; border-bottom: 2px solid #e5e7eb; }
    .thumb-preview { width: 120px; height: 120px; object-fit: cover; border-radius: 6px; border: 1px solid var(--hk-border, #e5e7eb); }
    .thumb-placeholder { width: 120px; height: 120px; border-radius: 6px; border: 1px dashed var(--hk-border, #d1d5db); background: var(--hk-bg-th, #f9fafb); display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 28px; }
    .edit-actions { display: flex; gap: 10px; padding-top: 10px; }
    .edit-actions .btn { min-height: 38px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 20px; }
    .form-check-label { font-size: 13px; font-weight: 600; }
</style>


{{-- Styles extracted from trash.blade.php --}}
<style>
        /* ── Page header ───────────────────────────────────── */
        .product-header-title {
            color: #020617 !important;
            font-size: 25px;
            font-weight: 800;
        }

        .product-header-desc {
            color: #64748B;
            font-size: 14px;
        }

        [data-theme="dark"] .product-header-title { color: #F8FAFC !important; }
        [data-theme="dark"] .product-header-desc  { color: #94A3B8 !important; }

        /* ── Thumbnail ─────────────────────────────────────── */
        .product-trash-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
        }

        .product-trash-thumb-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            font-size: 15px;
        }

        /* ── Search ────────────────────────────────────────── */
        .product-trash-search {
            min-height: 38px;
            border: 1px solid #D8E0EA;
            border-radius: 10px;
            font-size: 13px;
            color: #0F172A;
            background: #ffffff;
            width: 320px;
            box-shadow: none;
        }

        .product-trash-search:focus {
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
            color: #0F172A;
        }

        .product-trash-search::placeholder {
            color: #94A3B8;
        }

        /* ── Table ─────────────────────────────────────────── */
        .product-trash-table-wrap {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
        }

        .product-trash-table thead th {
            color: #334155;
            font-weight: 800;
            font-size: 13px;
            padding: 13px 16px;
            border-bottom: 1px solid #E2E8F0;
            border-right: 1px solid #E2E8F0;
            background: #F8FAFC;
            white-space: nowrap;
        }

        .product-trash-table thead th:last-child {
            border-right: 0;
        }

        .product-trash-table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: #0F172A;
            border-bottom: 1px solid #E2E8F0;
            border-right: 1px solid #E2E8F0;
            vertical-align: middle;
        }

        .product-trash-table tbody td:last-child {
            border-right: 0;
        }

        .product-trash-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .product-trash-table tbody tr:hover td {
            background: #F8FAFC;
        }

        /* ── ID + muted text ───────────────────────────────── */
        .product-trash-id { color: #64748B; font-weight: 600; }
        .product-trash-brand { font-size: 12px; color: #94A3B8; margin-top: 2px; }
        .product-trash-cat { color: #475569; }
        .product-trash-date { color: #475569; }

        /* ── Price ─────────────────────────────────────────── */
        .trash-price-sale { font-weight: 800; color: #DC2626; font-size: 13px; }
        .trash-price-original { font-size: 11px; color: #94A3B8; text-decoration: line-through; }
        .trash-price-normal { font-weight: 700; color: #0F172A; }

        /* ── Dark mode ─────────────────────────────────────── */
        [data-theme="dark"] .product-trash-thumb { border-color: #22324D; }
        [data-theme="dark"] .product-trash-thumb-placeholder {
            background: #0C1830; border-color: #22324D; color: #64748B;
        }
        [data-theme="dark"] .product-trash-search {
            background: #101C33 !important; border-color: #2A3B59 !important;
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .product-trash-search:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        [data-theme="dark"] .product-trash-search::placeholder { color: #64748B !important; }
        [data-theme="dark"] .product-trash-table-wrap {
            background: #0F1B31 !important; border-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table thead th {
            background: #0C1830 !important; color: #94A3B8 !important;
            border-bottom-color: #22324D !important; border-right-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table tbody td {
            color: #E2E8F0 !important;
            border-bottom-color: #22324D !important; border-right-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table tbody tr:hover td {
            background: #1A3050 !important;
        }
        [data-theme="dark"] .product-trash-id { color: #94A3B8 !important; }
        [data-theme="dark"] .product-trash-brand { color: #64748B !important; }
        [data-theme="dark"] .product-trash-cat { color: #CBD5E1 !important; }
        [data-theme="dark"] .product-trash-date { color: #94A3B8 !important; }
        [data-theme="dark"] .trash-price-normal { color: #E2E8F0 !important; }
        [data-theme="dark"] .trash-price-original { color: #64748B !important; }

    /* ── Add-item modal (brands / colors / sizes) ───────────── */
    .hk-add-modal .modal-content {
        border: 1px solid #d8dee6;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(15,23,42,0.14);
    }
    .hk-add-modal .modal-content > form {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .hk-add-modal .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 18px 22px 16px;
        flex-shrink: 0;
    }
    .hk-add-modal .modal-title {
        font-size: 15px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #0f172a;
    }
    .hk-add-modal .modal-body {
        padding: 24px 22px 12px;
        flex: 1 1 auto;
        overflow-y: auto;
    }
    .hk-add-modal .modal-footer {
        padding: 12px 22px 18px;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
        gap: 10px;
    }
    .hk-add-modal .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #374151;
        margin-bottom: 6px;
    }
    .hk-add-modal .form-control {
        border-radius: 6px;
        font-size: 14px;
        border-color: #d1d5db;
        min-height: 40px;
    }
    .hk-add-modal .form-control:focus {
        border-color: #16A34A;
        box-shadow: 0 0 0 3px rgba(22,163,74,.12);
    }
    .hk-add-modal .form-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 5px;
    }
    .hk-add-modal .invalid-feedback {
        font-size: 12px;
    }
    [data-theme="dark"] .hk-add-modal .modal-content {
        background: #111827 !important;
        border-color: #22324D !important;
        box-shadow: 0 20px 50px rgba(0,0,0,0.55) !important;
    }
    [data-theme="dark"] .hk-add-modal .modal-header {
        background: #0C1830 !important;
        border-color: #22324D !important;
    }
    [data-theme="dark"] .hk-add-modal .modal-title { color: #F8FAFC !important; }
    [data-theme="dark"] .hk-add-modal .modal-footer { border-color: #22324D !important; }
    [data-theme="dark"] .hk-add-modal .form-label { color: #CBD5E1 !important; }
    [data-theme="dark"] .hk-add-modal .form-control {
        background: #0F1B31 !important;
        border-color: #2A3B59 !important;
        color: #E2E8F0 !important;
    }
    [data-theme="dark"] .hk-add-modal .form-control:focus {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15) !important;
    }
    [data-theme="dark"] .hk-add-modal .form-text { color: #64748B !important; }

    /* ── Panel "Sửa sản phẩm" trượt từ phải ── */
    .product-edit-offcanvas { width: min(1100px, 95vw) !important; }

    /* ── Rich text editor (Quill) cho Mô tả — cho phép kéo lên/xuống để đổi chiều cao ── */
    #descriptionEditor {
        min-height: 220px;
        background: #fff;
        font-size: 14px;
    }
    .ql-toolbar.ql-snow {
        border-radius: 6px 6px 0 0;
        background: #f9fafb;
    }
    .ql-container.ql-snow {
        border-radius: 0 0 6px 6px;
        display: block;
        width: 100% !important;
        height: auto !important;
        resize: vertical !important;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        min-height: 150px;
        max-height: 480px;
    }
    .ql-editor {
        min-height: 150px;
        height: 100% !important;
        color: #111827 !important;
    }
    .ql-editor.ql-blank::before {
        color: #9ca3af !important;
        font-style: normal;
    }
    .edit-field.is-invalid .ql-toolbar,
    .edit-field.is-invalid .ql-container {
        border-color: #dc3545;
    }

    /* ── Dark mode ── */
    [data-theme="dark"] #descriptionEditor { background: #0F1B31 !important; }
    [data-theme="dark"] .ql-toolbar.ql-snow {
        background: #0F1B31 !important;
        border-color: #2A3B59 !important;
    }
    [data-theme="dark"] .ql-container.ql-snow { border-color: #2A3B59 !important; }
    [data-theme="dark"] .ql-editor { color: #E2E8F0 !important; }
    [data-theme="dark"] .ql-editor.ql-blank::before { color: #64748B !important; }
    [data-theme="dark"] .ql-snow .ql-stroke { stroke: #94A3B8 !important; }
    [data-theme="dark"] .ql-snow .ql-fill { fill: #94A3B8 !important; }
    [data-theme="dark"] .ql-snow .ql-picker { color: #94A3B8 !important; }
    [data-theme="dark"] .ql-toolbar.ql-snow button:hover .ql-stroke,
    [data-theme="dark"] .ql-toolbar.ql-snow button.ql-active .ql-stroke {
        stroke: #3B82F6 !important;
    }
    [data-theme="dark"] .ql-toolbar.ql-snow button:hover .ql-fill,
    [data-theme="dark"] .ql-toolbar.ql-snow button.ql-active .ql-fill {
        fill: #3B82F6 !important;
    }
    </style>
