{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
    <style>
        /* ── Order status chips ────────────────────────────────── */
        .order-badge {
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

        .order-badge--pending   { background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; }
        .order-badge--processing { background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; }
        .order-badge--shipping  { background: #F0F9FF; border: 1.5px solid #BAE6FD; color: #0369A1; }
        .order-badge--completed { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; }
        .order-badge--cancelled { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; }

        .payment-badge {
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

        .payment-badge--paid   { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; }
        .payment-badge--unpaid { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; }

        .order-code {
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            background: #F1F5F9;
            padding: 3px 7px;
            border-radius: 5px;
        }

        /* ── Lọc theo khoảng thời gian ── */
        .order-date-range {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .order-date-input {
            min-height: 38px;
            width: 155px;
            border: 1px solid #D8E0EA;
            border-radius: 10px;
            font-size: 13px;
        }
        .order-date-input:focus {
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
        }
        .order-date-sep {
            color: #94A3B8;
            font-weight: 700;
        }

        /* ── Dark mode ────────────────────────────────── */
        [data-theme="dark"] .order-badge--pending   { background: rgba(251,191,36,0.12) !important; border-color: rgba(251,191,36,0.3) !important; color: #FCD34D !important; }
        [data-theme="dark"] .order-badge--processing { background: rgba(59,130,246,0.12) !important; border-color: rgba(59,130,246,0.3) !important; color: #93C5FD !important; }
        [data-theme="dark"] .order-badge--shipping  { background: rgba(14,165,233,0.12) !important; border-color: rgba(14,165,233,0.3) !important; color: #7DD3FC !important; }
        [data-theme="dark"] .order-badge--completed { background: rgba(34,197,94,0.12) !important; border-color: rgba(34,197,94,0.3) !important; color: #86EFAC !important; }
        [data-theme="dark"] .order-badge--cancelled { background: rgba(239,68,68,0.12) !important; border-color: rgba(239,68,68,0.3) !important; color: #FCA5A5 !important; }
        [data-theme="dark"] .payment-badge--paid    { background: rgba(34,197,94,0.12) !important; border-color: rgba(34,197,94,0.3) !important; color: #86EFAC !important; }
        [data-theme="dark"] .payment-badge--unpaid  { background: rgba(239,68,68,0.12) !important; border-color: rgba(239,68,68,0.3) !important; color: #FCA5A5 !important; }
        [data-theme="dark"] .order-code {
            background: #162843 !important;
            color: #CBD5E1 !important;
        }
    </style>


{{-- Styles extracted from show.blade.php --}}
<style>
        .info-label { font-weight: 700; font-size: 13px; color: #6b7280; min-width: 150px; }
        .info-value { font-size: 13px; color: #111827; font-weight: 600; }
        .section-title { font-size: 13px; font-weight: 800; color: #174761; text-transform: uppercase; letter-spacing: .04em; }
        .item-table thead th { font-size: 12px; font-weight: 800; background: #f9fafb; }
        .update-card .form-select,
        .update-card .form-control { font-size: 13px; }
        .update-card label { font-size: 13px; font-weight: 700; color: #374151; }
        .item-table tbody td { font-size: 13px; }
        .status-badge { border-radius: 2px; font-size: 11px; font-weight: 800; padding: 4px 8px; }
        .product-thumb { width: 44px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #e5e7eb; }
    </style>
