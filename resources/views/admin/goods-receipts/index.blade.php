@extends('layouts.admin')

@section('title', 'Quản lý kho hàng')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { display: inline-block; white-space: nowrap; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    .gr-badge--in-stock { background: #dcfce7; color: #166534; }
    .gr-badge--low-stock { background: #fef3c7; color: #92400e; }
    .gr-badge--out-of-stock { background: #fee2e2; color: #991b1b; }
    .gr-badge--cancelled { background: #e2e8f0; color: #475569; }

    /* ── Chi tiết phiếu xuất kho / nhập kho ── */
    .si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .si-show-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .si-show-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .si-show-product { display: flex; align-items: center; gap: 10px; }
    .si-show-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .si-show-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; display: inline-block; }
    .si-show-dialog { max-width: min(1040px, 94vw); }
    .si-show-modal-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 70px rgba(15, 23, 42, .24); }
    .si-show-modal-body { max-height: min(78vh, 760px); overflow: auto; background: #f8fafc; padding: 18px; }
    .gr-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .gr-show-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .gr-show-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .gr-show-product { display: flex; align-items: center; gap: 10px; }
    .gr-show-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .gr-show-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; display: inline-block; }

    /* ── Trạng thái phiếu xuất kho: pill đổi trạng thái ngay trên bảng ── */
    .gr-row-status-trigger {
        display: inline-flex; align-items: center; gap: 6px;
        min-height: 30px; padding: 0 10px; white-space: nowrap;
        border: 1px solid #fde68a; border-radius: 999px;
        background: #fef3c7; color: #92400e;
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .02em;
        cursor: pointer; transition: border-color .15s, background .15s;
    }
    .gr-row-status-trigger:hover,
    .gr-row-status-trigger.is-open { border-color: #f59e0b; background: #fef0c7; }
    .gr-row-status-trigger i { transition: transform .2s; }
    .gr-row-status-trigger.is-open i { transform: rotate(180deg); }

    .gr-row-status-shared-panel { position: fixed; z-index: 1080; }

    .gr-receipt-modal-dialog { max-width: min(1120px, 94vw); }
    .gr-receipt-modal-content {
        border: 0;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .24);
    }
    .gr-receipt-modal-body {
        max-height: min(82vh, 780px);
        overflow: auto;
        background: #f8fafc;
        padding: 20px;
    }

    /* ── Tabs ── */
    .gr-tabs {
        display: flex;
        gap: 28px;
        border-bottom: 1.5px solid #e5e7eb;
        margin: 18px 0 20px;
    }
    .gr-tab {
        padding: 10px 2px 12px;
        font-size: 14px;
        font-weight: 600;
        color: #9ca3af;
        text-decoration: none;
        border-bottom: 2.5px solid transparent;
        margin-bottom: -1.5px;
        transition: color .15s, border-color .15s;
    }
    .gr-tab:hover { color: #374151; }
    .gr-tab.is-active { color: #111827; font-weight: 700; border-bottom-color: #16a34a; }

    /* ── Header actions ── */
    .product-admin-page .product-header-actions {
        margin-top: 15px !important;
    }

    .gr-btn-navy {
        background: #1e293b; border-color: #1e293b; color: #fff;
    }
    .gr-btn-navy:hover { background: #0f172a; border-color: #0f172a; color: #fff; }
    .gr-btn-emerald {
        background: #0f9d58; border-color: #0f9d58; color: #fff;
    }
    .gr-btn-emerald:hover { background: #0c7a45; border-color: #0c7a45; color: #fff; }

    /* ── Metric cards ── */
    .gr-metric-card {
        background: #fff;
        border: 1.5px solid #e5e7eb;
        border-radius: 14px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        height: 100%;
        transition: box-shadow .15s ease, transform .15s ease;
    }
    .gr-metric-card:hover {
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.1);
        transform: translateY(-2px);
    }
    .gr-metric-card--danger {
        background: linear-gradient(135deg, #fff, #fef2f2 140%);
        border-color: #fecaca;
    }
    .gr-metric-card--clickable { cursor: pointer; }
    .gr-metric-card--clickable.is-active {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
    }
    .gr-metric-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .gr-metric-icon--blue  { background: #dbeafe; color: #2563eb; }
    .gr-metric-icon--green { background: #dcfce7; color: #16a34a; }
    .gr-metric-icon--red   { background: #fee2e2; color: #dc2626; }
    .gr-metric-label { font-size: 12px; color: #6b7280; font-weight: 600; }
    .gr-metric-value { font-size: 19px; font-weight: 600; color: #111827; }
    .gr-metric-value--danger { color: #dc2626; }
    .gr-metric-sub { font-size: 11px; font-weight: 600; color: #dc2626; margin-left: 6px; }

    /* ── Search & filter bar ── */
    .gr-filter-bar { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; flex-wrap: wrap; }
    .gr-filter-bar .product-search { flex: 1 1 auto; min-width: 200px; max-width: 360px; }
    .gr-filter-icon-btn {
        width: 38px; height: 38px; border-radius: 8px; border: 1.5px solid #d1d5db; background: #fff;
        color: #6b7280; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;
    }
    .gr-filter-icon-btn:hover { background: #f9fafb; }

    .gr-status-filter { width: 160px; flex: 0 0 160px; }
    .gr-status-filter .hk-cat-panel { width: 160px; }

    /* ── Row product cell ── */
    #inventoryOverviewTable {
        min-width: 1070px;
        table-layout: fixed;
    }
    #inventoryOverviewTable thead th,
    #inventoryOverviewTable tbody td {
        padding: 9px 10px;
    }
    .gr-ov-product { display: flex; align-items: center; gap: 9px; min-width: 0; }
    .gr-ov-thumb { width: 38px; height: 38px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .gr-ov-name { display: block; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .gr-ov-variant { font-size: 12px; color: #6b7280; }
    .si-hidden-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
    .si-hidden-scrollbar::-webkit-scrollbar { width: 0; height: 0; }

    .skc-dialog { max-width: min(1120px, 94vw); }
    .skc-content { border: 0; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 70px rgba(15, 23, 42, .22); }
    #stockCardDocumentModal { z-index: 1075; }
    .modal-backdrop + .modal-backdrop { z-index: 1070; }
    .skc-doc-dialog { max-width: min(940px, 92vw); }
    .skc-doc-content { border: 0; border-radius: 16px; overflow: hidden; box-shadow: 0 28px 80px rgba(15, 23, 42, .28); }
    .skc-doc-header { background: #fff; padding: 16px 18px; }
    .skc-doc-title { margin: 0; color: #0f172a; font-size: 17px; font-weight: 800; }
    .skc-doc-body { max-height: min(72vh, 720px); overflow: auto; background: #f8fafc; padding: 18px; }
    .skc-header { padding: 18px 22px; background: #fff; }
    .skc-product { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .skc-thumb { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; background: #f3f4f6; border: 1px solid #e5e7eb; }
    .skc-title { margin: 0 0 6px; color: #0f172a; font-size: 18px; font-weight: 800; line-height: 1.25; }
    .skc-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; color: #64748b; font-size: 13px; }
    .skc-meta strong { color: #0f172a; }
    .skc-dot { width: 4px; height: 4px; border-radius: 999px; background: #cbd5e1; }
    .skc-body { padding: 18px 22px 22px; background: #f8fafc; }
    .skc-table-wrap { overflow: auto; border: 1px solid #e2e8f0; border-radius: 14px; background: #fff; }
    .skc-table { width: 100%; min-width: 880px; border-collapse: collapse; font-size: 13px; }
    .skc-table thead th {
        padding: 13px 14px; background: #f1f5f9; color: #475569;
        font-weight: 800; text-align: left; white-space: nowrap; border-bottom: 1px solid #e2e8f0;
    }
    .skc-table tbody td { padding: 13px 14px; color: #0f172a; border-bottom: 1px solid #eef2f7; vertical-align: middle; }
    .skc-table tbody tr:last-child td { border-bottom: 0; }
    .skc-date, .skc-user { color: #475569 !important; font-weight: 600; white-space: nowrap; }
    .skc-doc-link { color: #2563eb; font-weight: 800; text-decoration: none; white-space: nowrap; }
    .skc-doc-link:hover { text-decoration: underline; }
    .skc-doc-link--static { color: #334155; }
    .skc-badge {
        display: inline-flex; align-items: center; justify-content: center;
        min-height: 26px; padding: 4px 10px; border-radius: 999px;
        font-size: 12px; font-weight: 800; white-space: nowrap;
    }
    .skc-badge--in { background: #dcfce7; color: #166534; }
    .skc-badge--out { background: #dbeafe; color: #1d4ed8; }
    .skc-badge--cancel { background: #f3e8ff; color: #7e22ce; }
    .skc-badge--adjust { background: #ffedd5; color: #c2410c; }
    .skc-qty { font-weight: 900; white-space: nowrap; }
    .skc-qty--pos { color: #16a34a; }
    .skc-qty--neg { color: #dc2626; }
    .skc-ending { color: #0f172a; font-weight: 900; }
    .skc-empty { padding: 34px 16px !important; text-align: center; color: #94a3b8 !important; font-weight: 700; }
    </style>
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý kho hàng</h1>
                    <p class="product-header-desc mb-0">Theo dõi tồn kho thực tế và thực hiện điều chỉnh số lượng.</p>
                </div>
                <div class="product-header-actions">
                    {{-- @if(($tab ?? 'overview') === 'overview') --}}
                       
                    @if($tab === 'inbound')
                        <a href="{{ route('admin.goods-receipts.trash') }}" class="btn btn-light border product-action-btn">
                            <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                        </a>
                        <button type="button" class="btn btn-dark product-action-btn"
                            data-bs-toggle="offcanvas" data-bs-target="#goodsReceiptOffcanvas">
                            <i class="fa-solid fa-plus me-1"></i> Tạo phiếu nhập kho
                        </button>
                    @elseif($tab === 'outbound')
                        <a href="{{ route('admin.stock-issues.trash') }}" class="btn btn-light border product-action-btn">
                            <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                        </a>
                        <button type="button" class="btn gr-btn-emerald product-action-btn"
                            data-bs-toggle="offcanvas" data-bs-target="#stockIssueOffcanvas">
                            <i class="fa-solid fa-plus me-1"></i> Tạo phiếu xuất kho
                        </button>
                    @elseif($tab === 'stocktake')
                        <a href="{{ route('admin.stocktakes.trash') }}" class="btn btn-light border product-action-btn">
                            <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                        </a>
                        <button type="button" class="btn gr-btn-emerald product-action-btn"
                            data-bs-toggle="modal" data-bs-target="#stocktakeModal">
                            <i class="fa-regular fa-clipboard me-1"></i> Lập phiếu kiểm kê
                        </button>
                    @endif
                </div>
            </div>

            {{-- ── Tabs ── --}}
            <div class="gr-tabs">
                <a href="{{ route('admin.goods-receipts.list', ['tab' => 'overview']) }}"
                    class="gr-tab {{ ($tab ?? 'overview') === 'overview' ? 'is-active' : '' }}">Tổng quan kho</a>
                <a href="{{ route('admin.goods-receipts.list', ['tab' => 'inbound']) }}"
                    class="gr-tab {{ ($tab ?? '') === 'inbound' ? 'is-active' : '' }}">Đơn nhập hàng</a>
                <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}"
                    class="gr-tab {{ ($tab ?? '') === 'outbound' ? 'is-active' : '' }}">Đơn xuất kho</a>
                <a href="{{ route('admin.goods-receipts.list', ['tab' => 'stocktake']) }}"
                    class="gr-tab {{ ($tab ?? '') === 'stocktake' ? 'is-active' : '' }}">Phiếu kiểm kê</a>
            </div>

            @if(($tab ?? 'overview') === 'overview')

                {{-- ── Metric cards ── --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="gr-metric-card">
                            <div class="gr-metric-icon gr-metric-icon--blue"><i class="fa-solid fa-box"></i></div>
                            <div>
                                <div class="gr-metric-label mb-1">Tổng sản phẩm tồn kho</div>
                                <div class="gr-metric-value">{{ number_format($totalStock) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gr-metric-card">
                            <div class="gr-metric-icon gr-metric-icon--blue"><i class="fa-solid fa-sack-dollar"></i></div>
                            <div>
                                <div class="gr-metric-label mb-1">Tổng giá trị tồn kho</div>
                                <div class="gr-metric-value">{{ number_format($totalValue, 0, ',', '.') }} VNĐ</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="gr-metric-card gr-metric-card--danger gr-metric-card--clickable {{ $stockStatus === 'low_stock' ? 'is-active' : '' }}"
                            id="grLowStockCard" title="Bấm để lọc danh sách sản phẩm sắp hết hàng">
                            <div class="gr-metric-icon gr-metric-icon--red"><i class="fa-solid fa-triangle-exclamation"></i></div>
                            <div>
                                <div class="gr-metric-label mb-1">Cảnh báo sắp hết hàng</div>
                                <div class="gr-metric-value gr-metric-value--danger">
                                    {{ number_format($lowStockCount) }}
                                    <span class="gr-metric-sub">Mã hàng</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Search & filter ── --}}
                <form method="GET" action="{{ route('admin.goods-receipts.list') }}" id="inventorySearchForm">
                    <input type="hidden" name="tab" value="overview">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="gr-filter-bar">
                        <input type="search" name="search" data-admin-search id="inventoryRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm kiếm sản phẩm, màu, SKU..." autocomplete="off">
                        @php
                            $categoryLabel = 'Tất cả danh mục';
                            foreach ($categories as $cat) {
                                if ((string) $categoryId === (string) $cat->id) { $categoryLabel = $cat->name; break; }
                                foreach ($cat->childrenCategories as $child) {
                                    if ((string) $categoryId === (string) $child->id) { $categoryLabel = $child->name; break 2; }
                                }
                            }
                        @endphp
                        <input type="hidden" name="category_id" id="grCategoryFilter" data-admin-filter value="{{ $categoryId }}">
                        <div class="hk-cat-filter" id="hkGrCategoryFilter">
                            <button type="button" class="hk-cat-trigger" id="hkGrCategoryTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkGrCategoryLabel">{{ $categoryLabel }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkGrCategoryPanel" hidden>
                                <div class="hk-cat-search-wrap">
                                    <i class="fa-solid fa-magnifying-glass hk-cat-search-icon"></i>
                                    <input type="text" class="hk-cat-search-input" id="hkGrCategorySearch" placeholder="Tìm danh mục..." autocomplete="off">
                                </div>
                                <div class="hk-cat-list" id="hkGrCategoryList" role="listbox">
                                    <button type="button" class="hk-cat-item {{ !$categoryId ? 'is-active' : '' }}" data-value="" data-label="Tất cả danh mục">Tất cả danh mục</button>
                                    @foreach($categories as $cat)
                                        <button type="button" class="hk-cat-item {{ (string) $categoryId === (string) $cat->id ? 'is-active' : '' }}" data-value="{{ $cat->id }}" data-label="{{ $cat->name }}">{{ $cat->name }}</button>
                                        @foreach($cat->childrenCategories as $child)
                                            <button type="button" class="hk-cat-item ps-4" data-value="{{ $child->id }}" data-label="{{ $child->name }}">&nbsp;&nbsp;{{ $child->name }}</button>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        @php
                            $stockStatusLabelMap = ['' => 'Tất cả trạng thái', 'in_stock' => 'Còn hàng', 'low_stock' => 'Sắp hết hàng', 'out_of_stock' => 'Hết hàng'];
                        @endphp
                        <input type="hidden" name="stock_status" id="grStockStatusFilter" data-admin-filter value="{{ $stockStatus }}">
                        <div class="hk-cat-filter gr-status-filter" id="hkGrStockStatusFilter">
                            <button type="button" class="hk-cat-trigger" id="hkGrStockStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkGrStockStatusLabel">{{ $stockStatusLabelMap[$stockStatus] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkGrStockStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkGrStockStatusList" role="listbox">
                                    @foreach($stockStatusLabelMap as $value => $label)
                                        <button type="button" class="hk-cat-item {{ $stockStatus === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- <button type="button" class="gr-filter-icon-btn" title="Bộ lọc nâng cao (đang phát triển)"
                            onclick="alert('Bộ lọc nâng cao đang được phát triển.')">
                            <i class="fa-solid fa-sliders"></i>
                        </button> --}}
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.overview-table')
                </div>

                <div class="modal fade" id="stockCardModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered skc-dialog">
                        <div class="modal-content skc-content" id="stockCardModalContent">
                            <div class="modal-body text-center py-5">
                                <div class="spinner-border text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="stockCardDocumentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered skc-doc-dialog">
                        <div class="modal-content skc-doc-content">
                            <div class="modal-header skc-doc-header border-bottom">
                                <h2 class="skc-doc-title" id="stockCardDocumentTitle">Chi tiết chứng từ</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body skc-doc-body" id="stockCardDocumentBody">
                                <div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

            @elseif($tab === 'inbound')

                <form method="GET" action="{{ route('admin.goods-receipts.list') }}" id="goodsReceiptSearchForm"
                      class="product-toolbar">
                    <input type="hidden" name="tab" value="inbound">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="product-toolbar-left">
                        <input type="search" name="search" data-admin-search id="goodsReceiptRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm theo mã phiếu hoặc tên nhà cung cấp..." autocomplete="off">
                        @php
                            $inboundStatusLabelMap = ['' => 'Tất cả trạng thái', 'draft' => 'Nháp', 'completed' => 'Hoàn tất', 'adjusted' => 'Đã điều chỉnh', 'cancelled' => 'Đã hủy'];
                        @endphp
                        <input type="hidden" name="status" id="grInboundStatusFilter" data-admin-filter value="{{ $status }}">
                        <div class="hk-cat-filter gr-status-filter" id="hkGrInboundStatusFilter">
                            <button type="button" class="hk-cat-trigger" id="hkGrInboundStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkGrInboundStatusLabel">{{ $inboundStatusLabelMap[$status] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkGrInboundStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkGrInboundStatusList" role="listbox">
                                    @foreach($inboundStatusLabelMap as $value => $label)
                                        <button type="button" class="hk-cat-item {{ $status === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.table')
                </div>

                @include('admin.goods-receipts.partials.create-modal', [
                    'suppliers' => $suppliers ?? collect(),
                    'variants' => $goodsReceiptVariants ?? collect(),
                ])

                <div class="modal fade" id="goodsReceiptShowModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered gr-receipt-modal-dialog">
                        <div class="modal-content gr-receipt-modal-content">
                            <div class="modal-header border-bottom bg-white">
                                <h2 class="modal-title fw-bold" style="font-size:18px;color:#1f2937;">Chi tiết phiếu nhập kho</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body gr-receipt-modal-body" id="goodsReceiptShowBody">
                                <div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal điều chỉnh phiếu nhập kho -->
                <div class="modal fade" id="goodsReceiptAdjustModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" style="max-width: 550px;">
                        <div class="modal-content" style="border: 0; border-radius: 14px; overflow: hidden; box-shadow: 0 20px 50px rgba(15,23,42,.18);">
                            <div class="modal-header border-bottom bg-white py-3 px-4">
                                <h5 class="modal-title fw-bold" style="font-size: 16.5px; color: #0F172A;" id="grAdjustModalTitle">Điều chỉnh phiếu nhập kho</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <form id="goodsReceiptAdjustForm" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="modal-body p-4 bg-light">
                                    <div class="alert alert-warning border border-warning-subtle d-flex gap-2" style="font-size: 12.5px; border-radius: 10px; background: #FFFBEB; color: #92400E;">
                                        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                                        <div>
                                            <strong>Lưu ý quan trọng:</strong> Hệ thống sẽ tự động sinh một phiếu xuất kho tương ứng để khấu trừ số lượng sản phẩm đã cộng sai khi hoàn tất.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold d-block" style="font-size: 13px; color: #374151;">Phương án xử lý <span class="text-danger">*</span></label>
                                        <div class="d-flex flex-column gap-2 mt-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="adjustment_type" id="adj_cancel" value="cancel" checked style="cursor: pointer;">
                                                <label class="form-check-label" for="adj_cancel" style="cursor: pointer; font-size:13px; font-weight:600;">
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5" style="font-size:11px;">Hủy bỏ phiếu</span> (Phiếu nhập bị sai và hủy bỏ hẳn)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="adjustment_type" id="adj_correct" value="adjust" style="cursor: pointer;">
                                                <label class="form-check-label" for="adj_correct" style="cursor: pointer; font-size:13px; font-weight:600;">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis px-2 py-0.5" style="font-size:11px;">Điều chỉnh phiếu</span> (Sẽ nhập lại phiếu mới chính xác thay thế)
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <label for="adjustment_reason" class="form-label fw-bold" style="font-size: 13px; color: #374151;">Lý do điều chỉnh / hủy <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="adjustment_reason" name="adjustment_reason" rows="3" 
                                            placeholder="Nhập lý do chi tiết tại đây..." required style="border-radius: 8px; font-size: 13px; border-color: #D8E0EA; resize: none;"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top bg-white py-3 px-4 d-flex justify-content-end gap-2">
                                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 13px; font-weight: 700; min-height:38px;">Hủy</button>
                                    <button type="submit" class="btn btn-dark" style="background: #059669 !important; border-color: #059669 !important; border-radius: 8px; font-size: 13px; font-weight: 700; min-height:38px;">Xác nhận</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- Offcanvas chỉnh sửa phiếu nhập kho (nháp) -->
                <div class="offcanvas offcanvas-end gr-offcanvas" tabindex="-1" id="goodsReceiptEditOffcanvas" aria-labelledby="goodsReceiptEditOffcanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="offcanvas-title mb-0" id="goodsReceiptEditOffcanvasLabel" style="font-size: 18px; font-weight: 800; color: #0F172A;">Chỉnh sửa phiếu nhập kho</h2>
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            </div>
                            <p class="mb-0 text-muted" style="font-size:13px;">Chỉnh sửa nhà cung cấp và danh sách mặt hàng của phiếu nháp.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
                    </div>
                    <div class="offcanvas-body flex-grow-1 overflow-auto p-0" id="goodsReceiptEditBody">
                        <div class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status"></div>
                        </div>
                    </div>
                </div>

                <!-- Offcanvas chỉnh sửa phiếu xuất kho (nháp) -->
                <div class="offcanvas offcanvas-end gr-offcanvas" tabindex="-1" id="stockIssueEditOffcanvas" aria-labelledby="stockIssueEditOffcanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="offcanvas-title mb-0" id="stockIssueEditOffcanvasLabel" style="font-size: 18px; font-weight: 800; color: #0F172A;">Chỉnh sửa phiếu xuất kho</h2>
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            </div>
                            <p class="mb-0 text-muted" style="font-size:13px;">Chỉnh sửa loại xuất, kho xuất và danh sách mặt hàng của phiếu nháp.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
                    </div>
                    <div class="offcanvas-body flex-grow-1 overflow-auto p-0" id="stockIssueEditBody">
                        <div class="text-center py-5">
                            <div class="spinner-border text-secondary" role="status"></div>
                        </div>
                    </div>
                </div>

                <div class="hk-cat-panel gr-row-status-shared-panel" id="grRowStatusPanel" hidden style="width:160px;">
                    <div class="hk-cat-list">
                        <button type="button" class="hk-cat-item is-active" data-value="draft">Nháp</button>
                        <button type="button" class="hk-cat-item" id="grRowStatusIssueBtn" data-value="complete">Hoàn tất</button>
                    </div>
                </div>

            @elseif($tab === 'outbound')

                <form method="GET" action="{{ route('admin.goods-receipts.list') }}" id="stockIssueSearchForm"
                      class="product-toolbar">
                    <input type="hidden" name="tab" value="outbound">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="product-toolbar-left">
                        <input type="search" name="search" data-admin-search id="stockIssueRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm theo mã phiếu xuất..." autocomplete="off">
                        @php
                            $outboundStatusLabelMap = ['' => 'Tất cả trạng thái', 'draft' => 'Nháp', 'completed' => 'Đã xuất kho', 'cancelled' => 'Đã hủy'];
                        @endphp
                        <input type="hidden" name="status" id="grOutboundStatusFilter" data-admin-filter value="{{ $status }}">
                        <div class="hk-cat-filter gr-status-filter" id="hkGrOutboundStatusFilter">
                            <button type="button" class="hk-cat-trigger" id="hkGrOutboundStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkGrOutboundStatusLabel">{{ $outboundStatusLabelMap[$status] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkGrOutboundStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkGrOutboundStatusList" role="listbox">
                                    @foreach($outboundStatusLabelMap as $value => $label)
                                        <button type="button" class="hk-cat-item {{ $status === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.outbound-table')
                </div>

                @include('admin.stock-issues.partials.create-modal', [
                    'variants' => $stockIssueVariants ?? collect(),
                ])

                <div class="modal fade" id="stockIssueShowModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered si-show-dialog">
                        <div class="modal-content si-show-modal-content">
                            <div class="modal-header border-bottom bg-white">
                                <h2 class="modal-title fw-bold" style="font-size:16px;">Chi tiết phiếu xuất kho</h2>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                            </div>
                            <div class="modal-body si-show-modal-body" id="stockIssueShowBody">
                                <div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Panel trạng thái dùng chung cho mọi dòng bảng, nổi ra ngoài (position:fixed) để không bị cắt bởi vùng cuộn của bảng. --}}
                <div class="hk-cat-panel gr-row-status-shared-panel" id="grRowStatusPanel" hidden style="width:160px;">
                    <div class="hk-cat-list">
                        <button type="button" class="hk-cat-item is-active" data-value="draft">Nháp</button>
                        <button type="button" class="hk-cat-item" id="grRowStatusIssueBtn" data-value="issue">Đã xuất kho</button>
                    </div>
                </div>

            @elseif($tab === 'stocktake')

                <form method="GET" action="{{ route('admin.goods-receipts.list') }}" id="stocktakeSearchForm"
                      class="product-toolbar">
                    <input type="hidden" name="tab" value="stocktake">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="product-toolbar-left">
                        <input type="search" name="search" data-admin-search id="stocktakeRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm theo mã phiếu kiểm kê..." autocomplete="off">
                        @php
                            $stocktakeStatusLabelMap = ['' => 'Tất cả trạng thái', 'pending' => 'Chờ xử lý', 'approved' => 'Đã duyệt', 'rejected' => 'Đã hủy'];
                        @endphp
                        <input type="hidden" name="status" id="grStocktakeStatusFilter" data-admin-filter value="{{ $status }}">
                        <div class="hk-cat-filter gr-status-filter" id="hkGrStocktakeStatusFilter">
                            <button type="button" class="hk-cat-trigger" id="hkGrStocktakeStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkGrStocktakeStatusLabel">{{ $stocktakeStatusLabelMap[$status] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkGrStocktakeStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkGrStocktakeStatusList" role="listbox">
                                    @foreach($stocktakeStatusLabelMap as $value => $label)
                                        <button type="button" class="hk-cat-item {{ $status === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.stocktake-table')
                </div>

                @include('admin.goods-receipts.partials.stocktake-modal', [
                    'variants' => $stocktakeVariants ?? collect(),
                    'stocktakeCode' => $stocktakeCodePreview ?? null,
                ])

                <div class="modal fade" id="stocktakeDetailModal" tabindex="-1" aria-labelledby="stocktakeDetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered stkd-modal-dialog">
                        <div class="modal-content" id="stocktakeDetailModalContent">
                            <div class="modal-body text-center py-5">
                                <div class="spinner-border text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hk-cat-panel gr-row-status-shared-panel" id="grRowStatusPanel" hidden style="width:160px;">
                    <div class="hk-cat-list">
                        <button type="button" class="hk-cat-item is-active" data-value="pending">Chờ xử lý</button>
                        <button type="button" class="hk-cat-item" data-value="approved" data-status-action-key="approve">Đã duyệt</button>
                        <button type="button" class="hk-cat-item" data-value="rejected" data-status-action-key="reject">Đã hủy</button>
                    </div>
                </div>

            @endif
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('layouts.components.confirm.update')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        /* ── Generic pill dropdown wiring (rounded trigger, panel opens from the right) ── */
        function wireHkFilterDropdown(config) {
            const root    = document.getElementById(config.root);
            const trigger = document.getElementById(config.trigger);
            const panel   = document.getElementById(config.panel);
            const label   = document.getElementById(config.label);
            const list    = document.getElementById(config.list);
            const hidden  = document.getElementById(config.hidden);
            const search  = config.search ? document.getElementById(config.search) : null;
            if (!root || !trigger || !panel || !list || !hidden) return null;

            function showAllItems() {
                list.querySelectorAll('.hk-cat-item').forEach(function (item) { item.hidden = false; });
                list.querySelector('.hk-cat-empty')?.remove();
            }

            function open() {
                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
                if (search) {
                    search.value = '';
                    showAllItems();
                    search.focus();
                }
            }

            function close() {
                panel.hidden = true;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', function () {
                panel.hidden ? open() : close();
            });

            function select(value, itemLabel) {
                list.querySelectorAll('.hk-cat-item').forEach(function (item) {
                    item.classList.toggle('is-active', item.dataset.value === String(value));
                });
                if (label) label.textContent = itemLabel;
                hidden.value = value || '';
            }

            list.addEventListener('click', function (event) {
                const btn = event.target.closest('.hk-cat-item');
                if (!btn) return;
                select(btn.dataset.value, btn.dataset.label);
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                close();
            });

            search?.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                let visible = 0;

                list.querySelectorAll('.hk-cat-item').forEach(function (item) {
                    const match = !q || item.textContent.toLowerCase().includes(q);
                    item.hidden = !match;
                    if (match) visible++;
                });

                const existing = list.querySelector('.hk-cat-empty');
                if (visible === 0 && !existing) {
                    const msg = document.createElement('div');
                    msg.className = 'hk-cat-empty';
                    msg.textContent = 'Không tìm thấy danh mục';
                    list.appendChild(msg);
                } else if (visible > 0 && existing) {
                    existing.remove();
                }
            });

            document.addEventListener('click', function (event) {
                if (!panel.hidden && !root.contains(event.target)) close();
            });

            return { select: select };
        }

        const categoryDropdown = wireHkFilterDropdown({
            root: 'hkGrCategoryFilter', trigger: 'hkGrCategoryTrigger', panel: 'hkGrCategoryPanel',
            label: 'hkGrCategoryLabel', list: 'hkGrCategoryList', hidden: 'grCategoryFilter', search: 'hkGrCategorySearch',
        });

        const stockStatusDropdown = wireHkFilterDropdown({
            root: 'hkGrStockStatusFilter', trigger: 'hkGrStockStatusTrigger', panel: 'hkGrStockStatusPanel',
            label: 'hkGrStockStatusLabel', list: 'hkGrStockStatusList', hidden: 'grStockStatusFilter',
        });

        /* ── Thẻ kho biến thể: nạp lịch sử giao dịch qua AJAX vào modal ── */
        (function () {
            const stockCardModal = document.getElementById('stockCardModal');
            const stockCardContent = document.getElementById('stockCardModalContent');
            const documentModal = document.getElementById('stockCardDocumentModal');
            const documentTitle = document.getElementById('stockCardDocumentTitle');
            const documentBody = document.getElementById('stockCardDocumentBody');
            if (!stockCardModal || !stockCardContent) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-stock-card-trigger]');
                if (!trigger) return;

                const url = trigger.dataset.stockCardUrl;
                if (!url) return;

                stockCardContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                bootstrap.Modal.getOrCreateInstance(stockCardModal).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => { stockCardContent.innerHTML = data.html; })
                    .catch(() => {
                        stockCardContent.innerHTML = '<div class="modal-body text-danger p-4">Không thể tải thẻ kho. Vui lòng thử lại.</div>';
                    });
            });

            document.addEventListener('click', function (e) {
                const docLink = e.target.closest('[data-stock-card-document-trigger]');
                if (!docLink || !stockCardContent.contains(docLink)) return;
                e.preventDefault();

                const url = docLink.dataset.documentUrl || docLink.getAttribute('href');
                if (!url || !documentModal || !documentBody) return;

                if (documentTitle) {
                    documentTitle.textContent = `Chi tiết chứng từ ${docLink.dataset.documentCode || ''}`.trim();
                }
                documentBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                bootstrap.Modal.getOrCreateInstance(documentModal).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => { documentBody.innerHTML = data.html; })
                    .catch(() => {
                        documentBody.innerHTML = '<div class="text-danger p-4">Không thể tải chi tiết chứng từ. Vui lòng thử lại.</div>';
                    });
            });

            stockCardModal.addEventListener('hidden.bs.modal', function () {
                stockCardContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });

            documentModal?.addEventListener('hidden.bs.modal', function () {
                documentBody.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                if (stockCardModal.classList.contains('show')) {
                    document.body.classList.add('modal-open');
                }
            });
        })();

        wireHkFilterDropdown({
            root: 'hkGrInboundStatusFilter', trigger: 'hkGrInboundStatusTrigger', panel: 'hkGrInboundStatusPanel',
            label: 'hkGrInboundStatusLabel', list: 'hkGrInboundStatusList', hidden: 'grInboundStatusFilter',
        });

        wireHkFilterDropdown({
            root: 'hkGrOutboundStatusFilter', trigger: 'hkGrOutboundStatusTrigger', panel: 'hkGrOutboundStatusPanel',
            label: 'hkGrOutboundStatusLabel', list: 'hkGrOutboundStatusList', hidden: 'grOutboundStatusFilter',
        });

        wireHkFilterDropdown({
            root: 'hkGrStocktakeStatusFilter', trigger: 'hkGrStocktakeStatusTrigger', panel: 'hkGrStocktakeStatusPanel',
            label: 'hkGrStocktakeStatusLabel', list: 'hkGrStocktakeStatusList', hidden: 'grStocktakeStatusFilter',
        });

        /* ── Xem chi tiết phiếu kiểm kê: nạp nội dung qua AJAX vào modal dùng chung ──
           Dùng event delegation vì các dòng bảng được nạp lại qua AJAX. */
        (function () {
            const detailModal = document.getElementById('stocktakeDetailModal');
            const detailContent = document.getElementById('stocktakeDetailModalContent');
            if (!detailModal || !detailContent) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-stocktake-show-trigger]');
                if (!trigger) return;
                const url = trigger.dataset.showUrl;
                if (!url) return;

                detailContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                bootstrap.Modal.getOrCreateInstance(detailModal).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => { if (!res.ok) throw new Error('request-failed'); return res.json(); })
                    .then(data => { detailContent.innerHTML = data.html; })
                    .catch(() => {
                        detailContent.innerHTML = '<div class="modal-body text-danger p-4">Không thể tải chi tiết phiếu kiểm kê. Vui lòng thử lại.</div>';
                    });
            });

            detailModal.addEventListener('hidden.bs.modal', function () {
                detailContent.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });

            // Duyệt/Hủy phiếu kiểm kê: dùng modal xác nhận riêng của hệ thống thay cho confirm() gốc của trình duyệt.
            document.addEventListener('click', function (e) {
                const rejectBtn = e.target.closest('[data-stocktake-reject-trigger]');
                if (rejectBtn) {
                    window.openUpdateConfirmModal({
                        title: 'Hủy bỏ phiếu kiểm kê?',
                        message: `Bạn có chắc chắn muốn hủy bỏ phiếu kiểm kê "${rejectBtn.dataset.code}"? Phiếu sẽ chuyển sang trạng thái Đã hủy và không thể khôi phục.`,
                        updateUrl: rejectBtn.dataset.rejectUrl,
                        updateMethod: 'PATCH',
                    });
                    return;
                }

                const approveBtn = e.target.closest('[data-stocktake-approve-trigger]');
                if (approveBtn) {
                    window.openUpdateConfirmModal({
                        title: 'Xác nhận cân bằng kho?',
                        message: `Hệ thống sẽ tự động tạo các phiếu nhập/xuất kho tương ứng để cân bằng số lượng thực tế cho phiếu "${approveBtn.dataset.code}". Hành động này không thể hoàn tác.`,
                        updateUrl: approveBtn.dataset.approveUrl,
                        updateMethod: 'PATCH',
                    });
                }
            });
        })();

        /* ── Đổi trạng thái phiếu nhập/xuất ngay trên bảng (panel dùng chung, nổi position:fixed) ──
           Dùng event delegation trên document vì các dòng bảng được nạp lại qua AJAX.
           Panel được đặt bên ngoài vùng bảng có overflow, nên luôn hiển thị đầy đủ, không bị cắt. */
        (function () {
            const sharedPanel = document.getElementById('grRowStatusPanel');
            const actionBtn   = document.getElementById('grRowStatusIssueBtn');
            if (!sharedPanel) return;

            function closePanel() {
                sharedPanel.hidden = true;
                document.querySelectorAll('.gr-row-status-trigger.is-open').forEach(t => t.classList.remove('is-open'));
            }

            function datasetKeyFor(action, suffix) {
                return 'status' + action.charAt(0).toUpperCase() + action.slice(1) + suffix;
            }

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('.gr-row-status-trigger');
                if (trigger) {
                    const wasOpenForThisTrigger = !sharedPanel.hidden && trigger.classList.contains('is-open');
                    closePanel();

                    if (!wasOpenForThisTrigger) {
                        const rect = trigger.getBoundingClientRect();
                        sharedPanel.style.top  = (rect.bottom + 6) + 'px';
                        sharedPanel.style.left = Math.max(8, rect.right - 160) + 'px';
                        if (actionBtn) {
                            actionBtn.dataset.statusAction = trigger.dataset.statusAction || 'issue';
                            actionBtn.dataset.statusUrl = trigger.dataset.statusUrl || trigger.dataset.issueUrl;
                            actionBtn.dataset.statusCode = trigger.dataset.statusCode || trigger.dataset.issueCode;
                            actionBtn.dataset.statusConfirm = trigger.dataset.statusConfirm
                                || `Xuất kho phiếu "${trigger.dataset.issueCode}" sẽ trừ tồn kho ngay lập tức. Tiếp tục?`;
                        }
                        sharedPanel.querySelectorAll('[data-status-action-key]').forEach(function (item) {
                            const action = item.dataset.statusActionKey;
                            item.dataset.statusUrl = trigger.dataset[datasetKeyFor(action, 'Url')] || '';
                            item.dataset.statusCode = trigger.dataset.statusCode || trigger.dataset.issueCode || '';
                            item.dataset.statusConfirm = trigger.dataset[datasetKeyFor(action, 'Confirm')] || '';
                        });
                        sharedPanel.hidden = false;
                        trigger.classList.add('is-open');
                    }
                    return;
                }

                const item = e.target.closest('#grRowStatusPanel .hk-cat-item');
                if (item) {
                    const isGenericAction = item.dataset.statusActionKey && item.dataset.statusUrl;
                    const isLegacyAction = actionBtn && item.dataset.value === actionBtn.dataset.statusAction;

                    if (isGenericAction || isLegacyAction) {
                        const url = isGenericAction ? item.dataset.statusUrl : actionBtn.dataset.statusUrl;
                        const message = (isGenericAction ? item.dataset.statusConfirm : actionBtn.dataset.statusConfirm)
                            || 'Bạn có chắc chắn muốn cập nhật trạng thái phiếu này?';
                        if (url && confirm(message)) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                                <input type="hidden" name="_method" value="PATCH">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    }
                    closePanel();
                    return;
                }

                if (!e.target.closest('.gr-row-status-trigger') && !e.target.closest('#grRowStatusPanel')) {
                    closePanel();
                }
            });

            window.addEventListener('scroll', closePanel, true);
            window.addEventListener('resize', closePanel);
        })();

        /* ── Xem chi tiết phiếu nhập kho: popup giữa màn hình, nạp nội dung qua AJAX ──
           Dùng event delegation vì các dòng bảng được nạp lại qua AJAX. */
        (function () {
            const modalEl = document.getElementById('goodsReceiptShowModal');
            const body = document.getElementById('goodsReceiptShowBody');
            if (!modalEl || !body) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-goods-receipt-show-trigger]');
                if (!trigger) return;

                const url = trigger.dataset.showUrl;
                if (!url) return;

                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                bootstrap.Modal.getOrCreateInstance(modalEl).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => { body.innerHTML = data.html; })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger p-4">Không thể tải chi tiết phiếu nhập kho. Vui lòng thử lại.</div>';
                    });
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });
        })();

        (function () {
            const adjustModalEl = document.getElementById('goodsReceiptAdjustModal');
            const adjustForm    = document.getElementById('goodsReceiptAdjustForm');
            const adjustTitle   = document.getElementById('grAdjustModalTitle');
            const reasonInput   = document.getElementById('adjustment_reason');
            const showModalEl   = document.getElementById('goodsReceiptShowModal');

            if (!adjustModalEl || !adjustForm) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-goods-receipt-adjust-trigger]');
                if (!trigger) return;

                e.preventDefault();
                const code = trigger.dataset.code || '';
                const url = trigger.dataset.adjustUrl;

                adjustForm.action = url;
                if (adjustTitle) adjustTitle.textContent = `Điều chỉnh phiếu nhập kho ${code}`;
                if (reasonInput) reasonInput.value = '';

                // Đóng modal chi tiết trước để tránh chồng backdrop
                if (showModalEl) {
                    bootstrap.Modal.getOrCreateInstance(showModalEl).hide();
                }

                bootstrap.Modal.getOrCreateInstance(adjustModalEl).show();
            });
        }());

        /* ── Xem chi tiết phiếu xuất kho: popup AJAX giống thẻ kho ──
           Dùng event delegation vì các dòng bảng được nạp lại qua AJAX. */
        (function () {
            const modalEl = document.getElementById('stockIssueShowModal');
            const body = document.getElementById('stockIssueShowBody');
            if (!modalEl || !body) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-stock-issue-show-trigger]');
                if (!trigger) return;

                const url = trigger.dataset.showUrl;
                if (!url) return;

                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
                bootstrap.Modal.getOrCreateInstance(modalEl).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => { body.innerHTML = data.html; })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger p-4">Không thể tải chi tiết phiếu xuất kho. Vui lòng thử lại.</div>';
                    });
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });
        })();

        /* ── Chỉnh sửa phiếu nhập kho (nháp): nạp form qua AJAX vào Offcanvas ── */
        (function () {
            const offcanvasEl = document.getElementById('goodsReceiptEditOffcanvas');
            const body        = document.getElementById('goodsReceiptEditBody');
            const showModalEl = document.getElementById('goodsReceiptShowModal');

            if (!offcanvasEl || !body) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-goods-receipt-edit-trigger]');
                if (!trigger) return;

                e.preventDefault();
                const url = trigger.dataset.editUrl;
                if (!url) return;

                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';

                // Đóng modal chi tiết nếu đang mở để tránh đè backdrop
                if (showModalEl) {
                    bootstrap.Modal.getOrCreateInstance(showModalEl).hide();
                }

                // Mở offcanvas chỉnh sửa
                bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();

                // Gửi request AJAX lấy HTML form
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => {
                        body.innerHTML = data.html;
                        // Execute internal script tag inside data.html manually
                        const scripts = body.getElementsByTagName('script');
                        for (let i = 0; i < scripts.length; i++) {
                            eval(scripts[i].innerText);
                        }
                    })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger p-4">Không thể tải nội dung chỉnh sửa. Vui lòng thử lại.</div>';
                    });
            });

            offcanvasEl.addEventListener('hidden.bs.offcanvas', function () {
                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });
        })();

        /* ── Chỉnh sửa phiếu xuất kho (nháp): nạp form qua AJAX vào Offcanvas ── */
        (function () {
            const offcanvasEl = document.getElementById('stockIssueEditOffcanvas');
            const body        = document.getElementById('stockIssueEditBody');
            const showModalEl = document.getElementById('goodsReceiptShowModal');

            if (!offcanvasEl || !body) return;

            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('[data-stock-issue-edit-trigger]');
                if (!trigger) return;

                e.preventDefault();
                const url = trigger.dataset.editUrl;
                if (!url) return;

                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';

                if (showModalEl) {
                    bootstrap.Modal.getOrCreateInstance(showModalEl).hide();
                }

                bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();

                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => {
                        if (!res.ok) throw new Error('request-failed');
                        return res.json();
                    })
                    .then(data => {
                        body.innerHTML = data.html;
                        const scripts = body.getElementsByTagName('script');
                        for (let i = 0; i < scripts.length; i++) {
                            eval(scripts[i].innerText);
                        }
                    })
                    .catch(() => {
                        body.innerHTML = '<div class="text-danger p-4">Không thể tải nội dung chỉnh sửa. Vui lòng thử lại.</div>';
                    });
            });

            offcanvasEl.addEventListener('hidden.bs.offcanvas', function () {
                body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
            });
        })();

        /* ── Low-stock alert card: click to filter table by "Sắp hết hàng" ── */
        var card   = document.getElementById('grLowStockCard');
        var filter = document.getElementById('grStockStatusFilter');
        if (!card || !filter || !stockStatusDropdown) return;

        card.addEventListener('click', function () {
            var next = filter.value === 'low_stock' ? '' : 'low_stock';
            var nextLabel = next === 'low_stock' ? 'Sắp hết hàng' : 'Tất cả trạng thái';
            stockStatusDropdown.select(next, nextLabel);
            card.classList.toggle('is-active', next === 'low_stock');
            filter.dispatchEvent(new Event('change'));
        });
    })();
    </script>
@endpush
