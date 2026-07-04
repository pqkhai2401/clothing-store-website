@extends('layouts.admin')

@section('title', 'Quản lý kho hàng')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    .gr-badge--in-stock { background: #dcfce7; color: #166534; }
    .gr-badge--low-stock { background: #fef3c7; color: #92400e; }
    .gr-badge--out-of-stock { background: #fee2e2; color: #991b1b; }

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
    .gr-header-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
    .gr-header-row .product-header-actions { margin-top: 18px; }
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

    /* ── Row product cell ── */
    .gr-ov-product { display: flex; align-items: center; gap: 10px; }
    .gr-ov-thumb { width: 42px; height: 42px; border-radius: 8px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .gr-ov-variant { font-size: 12px; color: #6b7280; }
    </style>
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="gr-header-row">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý kho hàng</h1>
                    <p class="product-header-desc mb-0">Theo dõi tồn kho thực tế và thực hiện điều chỉnh số lượng.</p>
                </div>
                <div class="product-header-actions">
                    @if(($tab ?? 'overview') === 'overview')
                        <button type="button" class="btn gr-btn-navy product-action-btn"
                            onclick="alert('Tính năng Cập nhật nhanh số lượng đang được phát triển.')">
                            <i class="fa-solid fa-chart-column me-1"></i> Cập nhật nhanh số lượng
                        </button>
                        <button type="button" class="btn gr-btn-emerald product-action-btn"
                            onclick="alert('Tính năng Lập phiếu kiểm kê đang được phát triển.')">
                            <i class="fa-regular fa-clipboard me-1"></i> Lập phiếu kiểm kê
                        </button>
                    @elseif($tab === 'inbound')
                        <a href="{{ route('admin.goods-receipts.create') }}" class="btn btn-dark product-action-btn">
                            <i class="fa-solid fa-plus me-1"></i> Tạo phiếu nhập kho
                        </a>
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
                        <select name="category_id" class="form-select" style="max-width:200px;" data-admin-filter>
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string) $categoryId === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @foreach($cat->childrenCategories as $child)
                                    <option value="{{ $child->id }}" {{ (string) $categoryId === (string) $child->id ? 'selected' : '' }}>&nbsp;&nbsp;{{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                        <select name="stock_status" id="grStockStatusFilter" class="form-select" style="max-width:170px;" data-admin-filter>
                            <option value="">Tất cả trạng thái</option>
                            <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>Còn hàng</option>
                            <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Sắp hết hàng</option>
                            <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Hết hàng</option>
                        </select>
                        <button type="button" class="gr-filter-icon-btn" title="Bộ lọc nâng cao (đang phát triển)"
                            onclick="alert('Bộ lọc nâng cao đang được phát triển.')">
                            <i class="fa-solid fa-sliders"></i>
                        </button>
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.overview-table')
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
                        <select name="status" class="form-select" style="max-width:180px;" data-admin-filter>
                            <option value="">Tất cả trạng thái</option>
                            <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Nháp</option>
                            <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                        </select>
                    </div>
                </form>

                <div data-admin-table-area>
                    @include('admin.goods-receipts.partials.table')
                </div>

            @else

                <div class="text-center py-5">
                    <i class="fa-solid fa-truck-ramp-box text-muted mb-3" style="font-size:48px;display:block;"></i>
                    <h5 class="fw-bold text-muted">Tính năng Đơn xuất kho đang được phát triển</h5>
                    <p class="text-muted mb-0">Chức năng theo dõi hàng xuất kho sẽ sớm ra mắt.</p>
                </div>

            @endif
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        var card   = document.getElementById('grLowStockCard');
        var filter = document.getElementById('grStockStatusFilter');
        if (!card || !filter) return;

        card.addEventListener('click', function () {
            var next = filter.value === 'low_stock' ? '' : 'low_stock';
            filter.value = next;
            card.classList.toggle('is-active', next === 'low_stock');
            filter.dispatchEvent(new Event('change'));
        });
    })();
    </script>
@endpush
