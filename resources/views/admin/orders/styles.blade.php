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

        /* ── Chip chọn nhanh theo kỳ ── */
        .order-period-chips {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .order-period-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            height: 38px;
            padding: 0 14px;
            border: 1px solid #D8E0EA;
            border-radius: 999px;
            background: #fff;
            color: #374151;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .order-period-chip:hover {
            background: #F1F5F9;
            border-color: #94A3B8;
        }
        .order-period-chip.is-active {
            background: #16A34A;
            border-color: #16A34A;
            color: #fff;
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
        [data-theme="dark"] .order-period-chip {
            background: #101B2E !important;
            border-color: #22314A !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .order-period-chip:hover {
            background: #162843 !important;
            border-color: #2E4361 !important;
        }
        [data-theme="dark"] .order-period-chip.is-active {
            background: #16A34A !important;
            border-color: #16A34A !important;
            color: #fff !important;
        }

        /* ── COMPACT VIEW (Tương đương hiệu ứng Zoom 90%) ── */
        .product-admin-page {
            font-size: 13px;
        }
        .product-admin-page .product-header-title {
            font-size: 1.4rem !important; /* Thu nhỏ tiêu đề trang */
            margin-bottom: 2px !important;
        }
        .product-admin-page .product-header-desc {
            font-size: 0.8rem !important;
        }

        /* Thu nhỏ dòng chứa các thẻ thống kê (Stats Cards) */
        .product-admin-page .row.g-4 {
            --bs-gutter-y: 0.75rem !important;
            --bs-gutter-x: 0.75rem !important;
        }
        .product-admin-page .card-body {
            padding: 10px 14px !important; /* Giảm padding thẻ thống kê */
        }
        .product-admin-page .card-body .text-muted {
            font-size: 11px !important; /* Thu nhỏ nhãn thẻ */
        }
        .product-admin-page .card-body h3, 
        .product-admin-page .card-body .fw-bold {
            font-size: 1.5rem !important; /* Thu nhỏ số liệu */
        }

        /* Thu nhỏ thanh công cụ (Toolbar & Filters) */
        .product-admin-page .product-toolbar {
            margin-top: 12px !important;
            margin-bottom: 12px !important;
            gap: 8px !important;
        }
        .product-admin-page .product-search,
        .product-admin-page .hk-cat-trigger,
        .product-admin-page .order-period-chip,
        .product-admin-page .order-date-input {
            min-height: 34px !important; /* Giảm chiều cao từ 38px xuống 34px */
            height: 34px !important;
            font-size: 12px !important;
            padding: 0 10px !important;
        }
        .product-admin-page .order-period-chips {
            gap: 4px !important; /* Thu hẹp khoảng cách các nút kỳ */
        }

        /* Thu gọn thanh lọc đơn hàng để "Từ ngày" lên chung 1 hàng với trạng thái/thanh toán */
        .product-admin-page .product-toolbar-left {
            gap: 6px !important;
        }
        .product-admin-page .product-search {
            width: 170px !important;
            flex: 0 0 170px !important;
        }
        .product-admin-page .hk-cat-filter {
            width: 138px !important;
            flex: 0 0 138px !important;
        }
        .product-admin-page .order-period-chip {
            padding: 0 8px !important;
        }
        .product-admin-page .order-date-range {
            gap: 4px !important;
        }
        .product-admin-page .order-date-input {
            width: 100px !important;
        }

        /* Thu nhỏ bảng dữ liệu (Table) */
        .product-admin-page .product-table th,
        .product-admin-page .product-table td {
            padding: 6px 8px !important; /* Thu hẹp khoảng cách các dòng */
            font-size: 12.5px !important; /* Giảm cỡ chữ dòng */
        }
        .product-admin-page .order-badge,
        .product-admin-page .payment-badge {
            font-size: 11px !important;
            padding: 3px 10px !important;
            min-height: 22px !important; /* Thu nhỏ các huy hiệu */
        }
        .product-admin-page .order-code {
            font-size: 11px !important;
            padding: 2px 5px !important;
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
