{{-- Kế thừa toàn bộ design system dùng chung của admin (header, toolbar, dropdown, product-table) --}}
@include('admin.products.styles')

{{-- Styles extracted from index.blade.php --}}
<style>
        .stars { color: #f59e0b; font-size: 13px; letter-spacing: 1px; }
        .stars .empty { color: #d1d5db; }
        .product-thumb {
            width: 40px; height: 40px; object-fit: cover;
            border-radius: 4px; border: 1px solid #e5e7eb;
        }

        /* ===== Badge trạng thái kiểm duyệt ===== */
        .rv-badge {
            display: inline-block; padding: 3px 9px; border-radius: 12px;
            font-size: 11px; font-weight: 700; line-height: 1.4; white-space: nowrap;
        }
        .rv-badge-pending  { background: #f3f4f6; color: #4b5563; }
        .rv-badge-approved { background: #dcfce7; color: #166534; }
        .rv-badge-rejected { background: #fee2e2; color: #991b1b; }
        .rv-badge-flagged  { background: #fef3c7; color: #92400e; }

        /* Thông tin điểm tin cậy AI */
        .rv-ai-info {
            margin-top: 4px; font-size: 11px; color: #6b7280; cursor: help;
        }
        .rv-ai-info i { color: #6366f1; margin-right: 2px; }

        /* ===== Nút thao tác dạng ô vuông (đồng bộ với trang Quản lý đơn hàng) ===== */
        .rv-row-action-btn {
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
        .rv-row-action-btn:hover { background: #E2E8F0; color: #020617; }
        .rv-row-action-btn.text-success:hover { background: #dcfce7; color: #166534; }
        .rv-row-action-btn.text-warning:hover { background: #fef3c7; color: #92400e; }
        .rv-row-action-btn.text-danger:hover { background: #fee2e2; color: #991b1b; }

        [data-theme="dark"] .rv-row-action-btn {
            background: #101C33 !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .rv-row-action-btn:hover {
            background: #162843 !important;
            color: #fff !important;
        }
    </style>


{{-- Styles extracted from trash.blade.php --}}
<style>
        .mgmt-table thead th { background:#fff; color:#111827; font-size:12px; font-weight:800; white-space:nowrap; }
        .mgmt-table tbody td { color:#374151; font-size:13px; }
        .mgmt-table tbody tr:nth-child(odd) { background:#f3f3f3; }
        .page-action-btn { min-height:32px; padding:7px 14px; border-radius:2px; font-size:12px; font-weight:800; text-transform:uppercase; }
        .deleted-at { font-size:11px; color:#9ca3af; }
        .stars { color:#f59e0b; font-size:12px; }
        .stars .empty { color:#d1d5db; }
        .product-thumb { width:36px; height:36px; object-fit:cover; border-radius:3px; border:1px solid #e5e7eb; }
    </style>
