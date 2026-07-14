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
    .edit-actions { display: flex; gap: 10px; padding-top: 6px; }
    .edit-actions .btn { min-height: 36px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 18px; }
</style>


{{-- Styles extracted from index.blade.php --}}
@include('admin.products.styles')
<style>
    /* Bảng ít cột hơn bảng sản phẩm — đè min-width:1420px kế thừa từ .product-table dùng chung
       bằng đúng tổng độ rộng cột của trang này. KHÔNG dùng table-layout:fixed (sẽ "đóng cứng"
       độ rộng, không co giãn khi thu nhỏ/phóng to trang) — để .table giữ width:100% mặc định
       của Bootstrap, trình duyệt tự giãn đều tất cả các cột theo tỉ lệ khi có thêm khoảng trống,
       giống hệt cách trang Đơn hàng đang hoạt động. */
    #sizeTable {
        min-width: 1119px;
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

    .size-group-badge {
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        border-radius: 999px;
        padding: 5px 12px;
        font-size: 13px;
        font-weight: 700;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
        max-width: 100%;
        padding-left: 10px;
        padding-right: 10px;
        white-space: nowrap;
    }

    /* Số thứ tự hiển thị thuần túy — bỏ khung bầu dục để không bị nhầm là nút bấm/nhãn */
    .size-weight-chip {
        display: inline-flex;
        align-items: center;
        font-size: 13px;
        font-weight: 400;
        color: #334155;
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
        color: #cbd5e1;
    }

    [data-theme="dark"] .size-count-link {
        color: #f8fafc;
    }

    /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
    .size-status-dropdown { position: relative; display: inline-block; width: auto; }
    .size-status-trigger {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-width: 1.5px;
        border-style: solid;
        cursor: pointer;
    }
    .size-status-trigger:hover { filter: brightness(0.97); }
    .size-status-trigger:focus { outline: none; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15); }
    .size-status-caret { font-size: 9px; opacity: .65; transition: transform .15s; }
    .size-status-trigger.is-open .size-status-caret { transform: rotate(180deg); }
    .size-status-dropdown .hk-cat-panel {
        left: 0;
        right: auto;
        width: 150px;
    }

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
    .product-admin-page .size-table th,
    .product-admin-page .size-table td {
        padding: 6px 8px !important; /* Thu hẹp khoảng cách các dòng, giống trang Đơn hàng */
        font-size: 12.5px !important;
    }
    .product-admin-page .status-badge,
    .product-admin-page .size-group-badge { font-size: 11px !important; padding: 3px 10px !important; min-height: 22px !important; }
    .product-admin-page .size-weight-chip { font-size: 12px !important; }
    .product-admin-page .row-action-btn { width: 26px !important; height: 26px !important; font-size: 12px !important; }

    /* ── A. Thanh tìm kiếm gọn lại, không kéo dài hết chiều ngang ── */
    .product-admin-page .product-search {
        min-height: 36px !important;
        max-width: 440px;
        font-size: 13px !important;
        border-radius: 8px !important;
    }

    /* ── A. Màu nút hành động dịu hơn, bớt "gắt" ── */
    .product-admin-page .product-action-btn {
        border-radius: 8px !important;
    }
    .product-admin-page .product-action-btn.btn-dark {
        background: #059669 !important;
        border-color: #059669 !important;
    }
    .product-admin-page .product-action-btn.btn-dark:hover {
        background: #047857 !important;
        border-color: #047857 !important;
    }
    /* "Thùng rác" là nút phụ: viền/chữ xám mặc định, chỉ chuyển đỏ khi hover */
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

    /* ── C. Gộp bảng + thanh phân trang thành 1 khối liền, đồng nhất với các trang khác:
           bo góc trên ở khung bảng, bo góc dưới ở thanh phân trang, không có khoảng cách giữa 2 phần ── */
    .product-admin-page .size-table-wrap,
    .product-admin-page .size-pagination-bar {
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
    }
    .product-admin-page .size-table-wrap {
        border-radius: 12px 12px 0 0 !important;
    }
    .product-admin-page .size-pagination-bar {
        border-radius: 0 0 12px 12px !important;
        border-color: #E2E8F0 !important;
    }

    /* ── B. Đồng bộ độ đậm chữ cột "Số SP dùng" với các cột số khác ── */
    .product-admin-page .size-count-link { font-weight: 400 !important; }

    [data-theme="dark"] .product-admin-page .size-pagination-bar {
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
