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
    .edit-actions { display: flex; gap: 10px; padding-top: 6px; }
    .edit-actions .btn { min-height: 36px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 18px; }
</style>


{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
    <style>
        .parent-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #DBEAFE;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }
        .child-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #86EFAC;
        }
        .slug-code {
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        [data-theme="dark"] .parent-tag {
            background: rgba(59,130,246,0.15) !important;
            color: #93C5FD !important;
            border-color: rgba(59,130,246,0.3) !important;
        }
        [data-theme="dark"] .child-tag {
            background: rgba(34,197,94,0.12) !important;
            color: #86EFAC !important;
            border-color: rgba(34,197,94,0.3) !important;
        }
        [data-theme="dark"] .slug-code {
            color: #94A3B8 !important;
            background: #162843 !important;
        }
        .attribute-name-link {
            color: #0f172a;
            text-decoration: none;
        }
        .attribute-name-link:hover {
            color: #15803d;
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        [data-theme="dark"] .attribute-name-link {
            color: #f8fafc;
        }
        [data-theme="dark"] .attribute-name-link:hover {
            color: #86efac;
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
