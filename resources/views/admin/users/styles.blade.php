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
        width: min(380px, 100%);
        min-height: 38px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        font-size: 14px;
    }

    .account-search:focus {
        border-color: #111827;
        box-shadow: none;
    }

    .account-tool-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .account-action-btn {
        min-height: 38px;
        padding: 8px 14px;
        border-radius: 8px;
        font-weight: 800;
        font-size: 13px;
    }

    .account-action-btn.btn-dark {
        background: #020617;
        border-color: #020617;
    }

    .account-table-wrap {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 9px;
    }

    .account-table {
        margin: 0;
        --bs-table-hover-bg: #f8fafc;
    }

    .account-table thead th {
        height: 48px;
        padding: 0 16px;
        background: #fff;
        color: #020617;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
        font-size: 14px;
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
        color: #0f172a;
        border-bottom: 1px solid #e5e7eb;
        border-right: 1px solid #e5e7eb;
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
        display: inline-block;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border: 1.5px solid #d1d5db;
        color: #374151;
        background: transparent;
        line-height: 1.4;
    }

    .status-badge {
        display: inline-block;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        line-height: 1.4;
        background: transparent;
    }

    .status-badge--active {
        border: 1.5px solid #16a34a;
        color: #16a34a;
    }

    .status-badge--inactive {
        border: 1.5px solid #9ca3af;
        color: #6b7280;
    }

    .account-more-btn {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 10px;
        color: #111827;
        background: #f3f4f6;
        font-weight: 900;
    }

    .account-more-btn:hover {
        background: #e5e7eb;
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
</style>
