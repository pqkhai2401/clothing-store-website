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

    /* Bảng ít cột hơn bảng sản phẩm — đè min-width:1420px kế thừa từ .product-table dùng chung
       bằng đúng tổng độ rộng cột của trang này. KHÔNG dùng table-layout:fixed (đóng cứng độ
       rộng, không co giãn khi thu nhỏ/phóng to trang) — giữ width:100% mặc định của Bootstrap
       để trình duyệt tự giãn đều các cột theo tỉ lệ, giống cách trang Đơn hàng hoạt động. */
    #brandTable {
        min-width: 1120px;
    }

    /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
    .brand-status-dropdown { position: relative; display: inline-block; width: auto; }
    .brand-status-trigger {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-width: 1.5px;
        border-style: solid;
        cursor: pointer;
    }
    .brand-status-trigger:hover { filter: brightness(0.97); }
    .brand-status-trigger:focus { outline: none; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15); }
    .brand-status-caret { font-size: 9px; opacity: .65; transition: transform .15s; }
    .brand-status-trigger.is-open .brand-status-caret { transform: rotate(180deg); }
    .brand-status-dropdown .hk-cat-panel { left: 0; right: auto; width: 170px; }

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
    .row-action-btn[data-edit-id]:hover { color: #0F172A; }
    .row-action-btn[data-delete-url]:hover { color: #DC2626; }

    [data-theme="dark"] .row-action-btn { color: #64748B; }
    [data-theme="dark"] .row-action-btn[data-edit-id]:hover { color: #F8FAFC; }
    [data-theme="dark"] .row-action-btn[data-delete-url]:hover { color: #F87171; }

    /* ── COMPACT VIEW (Tương đương hiệu ứng Zoom 90%) ── */
    .product-admin-page .product-header-title { font-size: 1.4rem !important; margin-bottom: 2px !important; margin-top: 12px !important; }
    .product-admin-page .product-header-desc { font-size: 0.8rem !important; }
    .product-admin-page .product-header-actions { margin-top: 0 !important; gap: 8px !important; }
    .product-admin-page .product-toolbar { margin: 12px 0 !important; gap: 8px !important; }
    .product-admin-page .product-table th,
    .product-admin-page .product-table td {
        padding: 6px 8px !important;
        font-size: 12.5px !important;
    }
    .product-admin-page .status-badge { font-size: 11px !important; padding: 3px 10px !important; min-height: 22px !important; }
    .product-admin-page .row-action-btn { width: 26px !important; height: 26px !important; font-size: 12px !important; }

    /* Thanh tìm kiếm gọn lại, không kéo dài hết chiều ngang */
    .product-admin-page .product-search {
        min-height: 36px !important;
        max-width: 440px;
        font-size: 13px !important;
        border-radius: 8px !important;
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
    .product-admin-page .brand-table-wrap,
    .product-admin-page .brand-pagination-bar {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    }
    .product-admin-page .brand-table-wrap { border-radius: 12px 12px 0 0 !important; }
    .product-admin-page .brand-pagination-bar {
        border-radius: 0 0 12px 12px !important;
        border-color: #E2E8F0 !important;
    }
    [data-theme="dark"] .product-admin-page .brand-pagination-bar {
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
