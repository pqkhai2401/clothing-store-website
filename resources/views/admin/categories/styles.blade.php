{{-- Styles extracted from edit.blade.php --}}
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control,
    .edit-field .form-select { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus,
    .edit-field .form-select:focus { border-color: #000; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
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

        /* Bảng ít cột hơn bảng sản phẩm — đè min-width:1420px kế thừa từ .product-table dùng chung
           bằng đúng tổng độ rộng cột của trang này. KHÔNG dùng table-layout:fixed (đóng cứng độ
           rộng, không co giãn khi thu nhỏ/phóng to trang) — giữ width:100% mặc định của Bootstrap
           để trình duyệt tự giãn đều các cột theo tỉ lệ, giống cách trang Đơn hàng hoạt động. */
        #catTable {
            min-width: 1120px;
        }

        /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
        .category-status-dropdown { position: relative; display: inline-block; width: auto; }
        .category-status-trigger {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-width: 1.5px;
            border-style: solid;
            cursor: pointer;
        }
        .category-status-trigger:hover { filter: brightness(0.97); }
        .category-status-trigger:focus { outline: none; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15); }
        .category-status-caret { font-size: 9px; opacity: .65; transition: transform .15s; }
        .category-status-trigger.is-open .category-status-caret { transform: rotate(180deg); }
        .category-status-dropdown .hk-cat-panel { left: 0; right: auto; width: 170px; }

        /* ── Nút Sửa/Xóa: icon thuần túy, không khung/nền, chỉ đổi màu rõ khi hover ── */
        .row-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border: none;
            background: transparent;
            color: #94A3B8;
            font-size: 14px;
            transition: color .15s;
        }
        .row-action-btn[data-delete-url]:hover { color: #DC2626; }
        .row-action-btn:not([data-delete-url]):hover { color: #0F172A; }

        [data-theme="dark"] .row-action-btn { color: #64748B; }
        [data-theme="dark"] .row-action-btn:not([data-delete-url]):hover { color: #F8FAFC; }
        [data-theme="dark"] .row-action-btn[data-delete-url]:hover { color: #F87171; }

        /* ── COMPACT VIEW: chi tiết bổ sung riêng cho trang này ── */
        .product-admin-page .product-header-actions { margin-top: 0 !important; gap: 8px !important; }
        .product-admin-page .status-badge { font-size: 11px !important; padding: 3px 10px !important; min-height: 22px !important; }
        .product-admin-page .row-action-btn { width: 26px !important; height: 26px !important; font-size: 12px !important; }

        /* Thanh tìm kiếm gọn lại, không kéo dài hết chiều ngang */
        .product-admin-page .product-search {
            min-height: 36px !important;
            max-width: 360px;
            font-size: 13px !important;
            border-radius: 8px !important;
        }
        .product-admin-page .hk-cat-filter {
            width: 190px !important;
            flex: 0 0 190px !important;
        }

        /* Màu nút hành động dịu hơn, bớt "gắt" */
        .product-admin-page .product-action-btn { border-radius: 8px !important; }
        .product-admin-page .product-action-btn.btn-dark {
            background: #059669 !important;
            border-color: #059669 !important;
        }
        .product-admin-page .product-action-btn.btn-dark:hover {
            background: #047857 !important;
            border-color: #047857 !important;
        }
        .product-admin-page .product-action-btn.btn-light {
            background: #ffffff !important;
            border: 1.5px solid #D8E0EA !important;
            color: #64748B !important;
        }
        .product-admin-page .product-action-btn.btn-light:hover {
            background: #FEF2F2 !important;
            border-color: #F87171 !important;
            color: #DC2626 !important;
        }

        /* Gộp bảng + thanh phân trang thành 1 khối liền, đồng nhất với các trang khác */
        .product-admin-page .category-table-wrap,
        .product-admin-page .category-pagination-bar {
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        }
        .product-admin-page .category-table-wrap { border-radius: 12px 12px 0 0 !important; }
        .product-admin-page .category-pagination-bar {
            border-radius: 0 0 12px 12px !important;
            border-color: #E2E8F0 !important;
        }
        [data-theme="dark"] .product-admin-page .category-pagination-bar {
            background: #0F1B31 !important;
            border-color: #22324D !important;
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
