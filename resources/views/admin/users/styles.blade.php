<style>
    .account-admin-page {
        background: #f8fafc;
        min-height: calc(100vh - 56px);
        padding-top: 0 !important;
    }

    .account-header-title {
        color: #020617;
        font-size: 25px;
        font-weight: 800;
        letter-spacing: 0;
        margin-top: 15px;
    }

    .account-header-desc {
        color: #64748b;
        font-size: 16px;
    }

    .account-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin: 30px 0 16px;
    }

    .account-search {
        width: min(340px, 100%);
        min-height: 38px;
        border: 1px solid #D8E0EA;
        border-radius: 10px;
        background: #fff;
        box-shadow: none;
        font-size: 14px;
        color: #0F172A;
        flex: 0 1 340px;
    }

    .account-search::placeholder {
        color: #94A3B8;
    }

    .account-search:focus {
        border-color: #16A34A;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
    }

    /* ── Toolbar left group ─────────────────────────── */
    .account-toolbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1 1 auto;
        flex-wrap: nowrap;
        min-width: 0;
    }

    /* ── Custom status dropdown ─────────────────────── */
    .hk-cat-filter {
        position: relative;
        width: 200px;
        flex: 0 0 200px;
    }

    .hk-cat-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        width: 100%;
        min-height: 38px;
        padding: 0 12px;
        border: 1px solid #D8E0EA;
        border-radius: 10px;
        background: #fff;
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
        left: 0;
        z-index: 1050;
        width: 220px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
        overflow: hidden;
    }

    .hk-cat-list {
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

    .account-tool-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .account-action-btn {
        min-height: 38px;
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
    }

    .account-action-btn.btn-dark {
        background: #16A34A !important;
        border-color: #16A34A !important;
        color: #ffffff !important;
    }

    .account-action-btn.btn-dark:hover {
        background: #15803D !important;
        border-color: #15803D !important;
        color: #ffffff !important;
    }

    .account-action-btn.btn-light {
        background: #ffffff !important;
        border: 1.5px solid #F87171 !important;
        color: #DC2626 !important;
    }

    .account-action-btn.btn-light:hover {
        background: #FEF2F2 !important;
        border-color: #EF4444 !important;
        color: #B91C1C !important;
    }

    .account-table-wrap {
        overflow: hidden;
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
    }

    .account-table {
        margin: 0;
        --bs-table-hover-bg: #F8FAFC;
    }

    .account-table thead th {
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

    .account-table thead th:last-child {
        border-right: 0;
    }

    .account-table tbody td {
        min-height: 64px;
        padding: 15px 16px;
        color: #0F172A;
        border-bottom: 1px solid #E2E8F0;
        border-right: 1px solid #E2E8F0;
        font-size: 13px;
        vertical-align: middle;
    }

    .account-table tbody td:last-child {
        border-right: 0;
    }

    .account-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .account-check {
        width: 18px;
        height: 18px;
        cursor: pointer;
        border-color: #d1d5db;
        border-radius: 5px;
    }

    .account-sort-btn {
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

    .account-sort-icon {
        display: inline-flex;
        align-items: center;
        min-width: 20px;
        color: #111827;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
    }

    .account-sort-btn.is-active {
        padding: 8px 10px;
        border-radius: 10px;
        background: #f3f4f6;
    }

    .account-sort-btn.is-active .account-sort-icon {
        color: #111827;
        font-size: 16px;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 5px 13px;
        min-height: 28px;
        border: 1.5px solid #CBD5E1;
        color: #475569;
        background: #F8FAFC;
        line-height: 1;
        white-space: nowrap;
    }

    .role-badge[data-role="admin"] {
        background: #EFF6FF;
        border-color: #BFDBFE;
        color: #2563EB;
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
        background: #FFF7F7;
        border: 1.5px solid #FECACA;
        color: #DC2626;
    }

    .account-more-btn {
        width: 34px;
        height: 34px;
        border: 1px solid transparent;
        border-radius: 10px;
        color: #0F172A;
        background: #F1F5F9;
        font-weight: 900;
    }

    .account-more-btn:hover {
        background: #E2E8F0;
        color: #020617;
    }

    .account-row-menu {
        min-width: 168px;
        padding: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
    }

    .account-row-menu .dropdown-item {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 38px;
        border-radius: 7px;
        font-size: 14px;
        font-weight: 600;
    }

    .account-modal .modal-content {
        border: 1px solid #d8dee6;
        border-radius: 8px;
        overflow: hidden;
    }

    .account-modal .modal-content > form {
        flex: 1 1 auto;
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }

    .account-modal .modal-body {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }

    .account-modal .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #d8dee6;
        flex-shrink: 0;
    }

    .account-modal .modal-footer {
        flex-shrink: 0;
    }

    .account-modal .modal-title {
        font-size: 16px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .account-modal .form-label {
        font-size: 13px;
        font-weight: 700;
    }

    .account-modal .form-control,
    .account-modal .form-select {
        border-radius: 3px;
        font-size: 14px;
    }

    .account-modal .is-invalid {
        border-color: #dc3545;
    }

    .account-modal-readonly {
        display: grid;
        grid-template-columns: 180px minmax(0, 1fr);
        border: 1px solid #d8dee6;
        border-bottom: 0;
    }

    .account-modal-readonly:last-child {
        border-bottom: 1px solid #d8dee6;
    }

    .account-modal-readonly-label {
        padding: 9px 12px;
        background: #e9ecef;
        border-right: 1px solid #d8dee6;
        font-size: 13px;
        font-weight: 700;
    }

    .account-modal-readonly-value {
        padding: 9px 12px;
        font-size: 13px;
        font-weight: 600;
        word-break: break-word;
    }

    .role-field--locked {
        opacity: 0.45;
        pointer-events: none;
        user-select: none;
    }

    .account-modal-loading {
        padding: 28px 12px;
        color: #6b7280;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }

    @media (max-width: 767.98px) {
        .account-toolbar {
            align-items: stretch;
            flex-direction: column;
        }

        .account-tool-actions {
            justify-content: flex-end;
        }
    }

    /* ── Dark mode overrides ────────────────────────────────── */
    [data-theme="dark"] .account-admin-page {
        background: #0A1428;
    }

    [data-theme="dark"] .account-header-title {
        color: #F8FAFC !important;
    }

    [data-theme="dark"] .account-header-desc {
        color: #94A3B8 !important;
    }

    [data-theme="dark"] .account-search {
        background: #101C33 !important;
        border-color: #2A3B59 !important;
        color: #E2E8F0 !important;
        box-shadow: none;
    }

    [data-theme="dark"] .account-search:focus {
        border-color: #3B82F6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.15) !important;
    }

    [data-theme="dark"] .account-search::placeholder {
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

    [data-theme="dark"] .hk-cat-item { color: #CBD5E1 !important; }
    [data-theme="dark"] .hk-cat-item:hover { background: #162843 !important; }
    [data-theme="dark"] .hk-cat-item.is-active {
        background: rgba(59,130,246,0.15) !important;
        color: #93C5FD !important;
    }

    [data-theme="dark"] .account-table-wrap {
        background: #0F1B31 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .account-table {
        --bs-table-hover-bg: #1A3050;
    }

    [data-theme="dark"] .account-table thead th {
        background: #0C1830 !important;
        color: #94A3B8 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .account-table tbody td {
        color: #E2E8F0 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .account-sort-btn.is-active {
        background: rgba(59,130,246,0.12) !important;
    }

    [data-theme="dark"] .account-sort-icon {
        color: #60A5FA !important;
    }

    .system-admin-chip {
        display: inline-flex;
        align-items: center;
        height: 22px;
        padding: 0 9px;
        border-radius: 999px;
        border: 1px solid #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    [data-theme="dark"] .role-badge {
        background: rgba(148,163,184,0.14) !important;
        border-color: rgba(148,163,184,0.32) !important;
        color: #CBD5E1 !important;
    }

    [data-theme="dark"] .role-badge[data-role="admin"] {
        background: rgba(59,130,246,0.16) !important;
        border-color: rgba(96,165,250,0.45) !important;
        color: #BFDBFE !important;
    }

    [data-theme="dark"] .status-badge--active {
        background: rgba(34,197,94,0.14) !important;
        border-color: rgba(34,197,94,0.35) !important;
        color: #86EFAC !important;
    }

    [data-theme="dark"] .status-badge--inactive {
        background: rgba(239,68,68,0.12) !important;
        border-color: rgba(252,165,165,0.35) !important;
        color: #FCA5A5 !important;
    }

    [data-theme="dark"] .account-more-btn {
        background: #16243A !important;
        border: 1px solid #2A3B59 !important;
        color: #E2E8F0 !important;
    }

    [data-theme="dark"] .account-more-btn:hover {
        background: #1D3150 !important;
        border-color: #3B82F6 !important;
    }

    [data-theme="dark"] .account-row-menu {
        background: #0F1B31 !important;
        border-color: #22324D !important;
        box-shadow: 0 16px 40px rgba(0,0,0,0.5) !important;
    }

    [data-theme="dark"] .account-modal .modal-header {
        background: #0C1830 !important;
        border-color: #22324D !important;
    }

    [data-theme="dark"] .account-modal .modal-title {
        color: #F8FAFC !important;
    }
</style>
