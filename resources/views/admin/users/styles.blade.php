<style>
    .account-table thead th {
        background: #ffffff;
        color: #111827;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        white-space: nowrap;
    }

    .account-table tbody td {
        color: #374151;
        font-size: 13px;
        height: 42px;
    }

    .account-table tbody tr:nth-child(odd) {
        background: #f3f3f3;
    }

    .page-action-btn {
        min-height: 32px;
        padding: 7px 14px;
        border-radius: 2px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .page-action-btn.btn-primary {
        background: #174761;
        border-color: #174761;
    }

    .page-action-btn.btn-outline-secondary {
        color: #174761;
        border-color: #9fb3bf;
        background: #ffffff;
    }

    .status-badge {
        border-radius: 2px;
        font-size: 10px;
        font-weight: 800;
        line-height: 1;
        padding: 4px 6px;
    }

    .account-modal .modal-content {
        border: 1px solid #d8dee6;
        border-radius: 4px;
    }

    .account-modal .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #d8dee6;
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

    .account-ajax-alert {
        position: fixed;
        top: 18px;
        right: 18px;
        z-index: 2000;
        min-width: 280px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
    }

    .account-modal-loading {
        padding: 28px 12px;
        color: #6b7280;
        text-align: center;
        font-size: 14px;
        font-weight: 700;
    }
</style>
