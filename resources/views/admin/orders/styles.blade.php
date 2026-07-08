{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
    <style>
        /* Bảng đơn hàng có ít cột hơn bảng sản phẩm — thu hẹp min-width dùng chung
           để cột "Khách hàng" không bị kéo dãn ra khoảng trắng thừa không cần thiết */
        #orderTable { min-width: 1260px; }

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

        /* ── Sổ xuống chọn nhanh trạng thái đơn/thanh toán ngay trong bảng ── */
        .oc-row-dropdown { position: relative; display: inline-block; width: auto; }
        .oc-row-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-width: 1.5px;
            border-style: solid;
            cursor: pointer;
        }
        .oc-row-trigger:hover { filter: brightness(0.97); }
        .oc-row-trigger:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        }
        .oc-row-caret {
            font-size: 9px;
            opacity: .65;
            transition: transform .15s;
        }
        .oc-row-trigger.is-open .oc-row-caret { transform: rotate(180deg); }

        /* Mở từ trái qua phải, rộng vừa nội dung thay vì lệch phải/quá cứng như select mặc định */
        .oc-row-dropdown .hk-cat-panel {
            left: 0;
            right: auto;
            width: 190px;
        }
        .oc-row-panel .hk-cat-list .hk-cat-item { font-size: 13px; }

        /* ── Nút Xem/Cập nhật hiển thị trực tiếp, không cần mở menu "..." ── */
        .order-row-action-btn {
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
        .order-row-action-btn:hover {
            background: #E2E8F0;
            color: #020617;
        }

        /* ── Dropdown chọn kỳ linh hoạt (Năm/Quý/Tháng) ── */
        .order-period-dropdown { width: 170px; flex: 0 0 170px; }
        .order-period-panel {
            left: 0;
            right: auto;
            width: 295px;
            padding: 14px;
        }
        .order-period-quick-row {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }
        .order-period-quick-btn {
            flex: 1;
            height: 34px;
            border: 1px solid #D8E0EA;
            border-radius: 8px;
            background: #fff;
            color: #374151;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: background .15s, border-color .15s;
        }
        .order-period-quick-btn:hover {
            background: #F0FDF4;
            border-color: #16A34A;
            color: #16A34A;
        }
        .order-period-quick-btn.order-period-clear-btn:hover {
            background: #FEF2F2 !important;
            border-color: #EF4444 !important;
            color: #EF4444 !important;
        }
        .order-period-year-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .order-period-year-row label {
            font-size: 12.5px;
            font-weight: 700;
            color: #6b7280;
            white-space: nowrap;
        }
        .order-period-year-filter {
            width: 110px;
            flex: 0 0 110px;
        }
        .order-period-year-filter .hk-cat-trigger {
            min-height: 34px;
            padding: 0 10px;
            font-size: 13px;
        }
        .order-period-year-filter .hk-cat-panel {
            left: 0;
            right: auto;
            width: 110px;
        }
        .order-period-year-filter .hk-cat-list {
            max-height: 220px;
        }
        .order-period-section { margin-bottom: 12px; }
        .order-period-section:last-child { margin-bottom: 0; }
        .order-period-section-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #94A3B8;
            margin-bottom: 8px;
        }
        .order-period-grid {
            display: grid;
            gap: 6px;
        }
        .order-period-grid--quarter { grid-template-columns: repeat(4, 1fr); }
        .order-period-grid--month { grid-template-columns: repeat(4, 1fr); }
        .order-period-cell {
            height: 30px;
            border: 1px solid #D8E0EA;
            border-radius: 6px;
            background: #fff;
            color: #374151;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .order-period-cell:hover {
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
        [data-theme="dark"] .oc-row-panel {
            background: #101C33 !important;
            border-color: #2A3B59 !important;
        }
        [data-theme="dark"] .oc-row-panel .hk-cat-item {
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .oc-row-panel .hk-cat-item:hover {
            background: #162843 !important;
        }
        [data-theme="dark"] .order-row-action-btn {
            background: #101C33 !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .order-row-action-btn:hover {
            background: #162843 !important;
            color: #fff !important;
        }
        [data-theme="dark"] .order-period-quick-btn,
        [data-theme="dark"] .order-period-cell {
            background: #101B2E !important;
            border-color: #22314A !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .order-period-quick-btn:hover,
        [data-theme="dark"] .order-period-cell:hover {
            background: #16A34A !important;
            border-color: #16A34A !important;
            color: #fff !important;
        }
        [data-theme="dark"] .order-period-section-title {
            color: #000000 !important;
        }
        [data-theme="dark"] .order-period-year-row label {
            color: #94A3B8 !important;
        }
        /* ── COMPACT VIEW ORDER SPECIFIC OVERRIDES ── */
        #orderFilterForm .product-toolbar-left {
            gap: 6px !important;
        }
        #orderFilterForm .product-search {
            width: 170px !important;
            flex: 0 0 170px !important;
        }
        #orderFilterForm .hk-cat-filter {
            width: 138px !important;
            flex: 0 0 138px !important;
        }
        #orderFilterForm .order-period-dropdown {
            width: 150px !important;
            flex: 0 0 150px !important;
        }
        #orderFilterForm .order-period-year-filter {
            width: 110px !important;
            flex: 0 0 110px !important;
        }
        #orderFilterForm .order-date-range {
            gap: 4px !important;
        }
        #orderFilterForm .order-date-input {
            width: 118px !important;
            min-height: 34px !important;
            height: 34px !important;
            font-size: 12px !important;
            padding: 0 10px !important;
        }
        .product-admin-page .order-badge,
        .product-admin-page .payment-badge {
            font-size: 11px !important;
            padding: 3px 10px !important;
            min-height: 22px !important;
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
