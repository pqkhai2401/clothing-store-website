{{-- Styles extracted from edit.blade.php --}}
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
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

    [data-theme="dark"] .attribute-name-link {
        color: #f8fafc;
    }

    [data-theme="dark"] .attribute-name-link:hover {
        color: #86efac;
    }
</style>


{{-- Color swatch & hex picker --}}
<style>
    .color-swatch {
        display: inline-block;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 1.5px solid rgba(0, 0, 0, 0.13);
        flex-shrink: 0;
        vertical-align: middle;
    }
    .color-swatch--empty {
        background: repeating-linear-gradient(
            45deg, #e5e7eb, #e5e7eb 3px, #f9fafb 3px, #f9fafb 6px
        );
    }
    [data-theme="dark"] .color-swatch { border-color: rgba(255, 255, 255, 0.18); }
    [data-theme="dark"] .color-swatch--empty {
        background: repeating-linear-gradient(
            45deg, #374151, #374151 3px, #1f2937 3px, #1f2937 6px
        );
    }
    .color-hex-picker {
        width: 44px;
        height: 36px;
        padding: 2px 4px;
        cursor: pointer;
        border: 1px solid var(--hk-border, #ced4da);
        border-radius: 4px;
        background: none;
        flex-shrink: 0;
    }
    .color-hex-input {
        font-family: monospace;
        max-width: 130px;
        letter-spacing: 0.04em;
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
