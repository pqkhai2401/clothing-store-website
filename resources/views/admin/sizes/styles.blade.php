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
    .edit-actions { display: flex; gap: 10px; padding-top: 6px; }
    .edit-actions .btn { min-height: 36px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 18px; }
</style>


{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
<style>
    .attribute-name-link {
        color: #0f172a;
        text-decoration: none;
    }

    .attribute-name-link:hover {
        color: #15803d;
        text-decoration: underline;
        text-underline-offset: 4px;
    }

    .size-table-wrap {
        border: 1px solid #e5e7eb;
        border-bottom: 0;
        border-radius: 12px 12px 0 0;
        overflow: hidden;
        background: #fff;
    }

    .size-table thead th {
        background: #f8fafc;
        border-bottom: 1px solid #e5e7eb;
        color: #334155;
    }

    .size-table tbody td {
        padding-top: 16px;
        padding-bottom: 16px;
        border-color: #edf2f7;
        color: #0f172a;
    }

    .size-group-badge,
    .size-weight-chip {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 13px;
        font-weight: 700;
    }

    .size-group-badge {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        max-width: 100%;
        padding-left: 10px;
        padding-right: 10px;
        white-space: nowrap;
    }

    .size-weight-chip {
        min-width: 42px;
        justify-content: center;
        background: #eef6ff;
        color: #075985;
        border: 1px solid #bfdbfe;
    }

    .size-count-link {
        color: #0f172a;
        font-weight: 800;
        text-decoration: underline;
        text-decoration-style: dotted;
        text-underline-offset: 4px;
    }

    .size-count-link:hover {
        color: #15803d;
    }

    .status-badge--inactive {
        background: #f1f5f9;
        color: #64748b;
        border-color: #e2e8f0;
    }

    [data-theme="dark"] .attribute-name-link {
        color: #f8fafc;
    }

    [data-theme="dark"] .attribute-name-link:hover {
        color: #86efac;
    }

    [data-theme="dark"] .size-table-wrap,
    [data-theme="dark"] .size-table tbody td {
        border-color: #334155;
        background: #0f172a;
        color: #e5e7eb;
    }

    [data-theme="dark"] .size-table thead th {
        background: #111827;
        color: #e5e7eb;
        border-color: #334155;
    }

    [data-theme="dark"] .size-group-badge,
    [data-theme="dark"] .status-badge--inactive {
        background: #1e293b;
        color: #cbd5e1;
        border-color: #334155;
    }

    [data-theme="dark"] .size-weight-chip {
        background: rgba(14, 165, 233, .14);
        color: #7dd3fc;
        border-color: rgba(125, 211, 252, .28);
    }

    [data-theme="dark"] .size-count-link {
        color: #f8fafc;
    }
</style>


{{-- Styles extracted from trash.blade.php --}}
<style>
        .mgmt-table thead th { background:#fff; color:#111827; font-size:12px; font-weight:800; white-space:nowrap; }
        .mgmt-table tbody td { color:#374151; font-size:13px; }
        .mgmt-table tbody tr:nth-child(odd) { background:#f3f3f3; }
        .page-action-btn { min-height:32px; padding:7px 14px; border-radius:2px; font-size:12px; font-weight:800; text-transform:uppercase; }
        .deleted-at { font-size:11px; color:#9ca3af; }
    </style>
