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

    .gr-status-filter { width: 160px; flex: 0 0 160px; }
    .gr-status-filter .hk-cat-panel { width: 160px; }

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
                    @elseif($tab === 'outbound')
                        <a href="{{ route('admin.stock-issues.create') }}" class="btn gr-btn-emerald product-action-btn">
                            <i class="fa-solid fa-plus me-1"></i> Tạo phiếu xuất kho
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
                        @php
                            $inboundStatusLabelMap = ['' => 'Tất cả trạng thái', 'draft' => 'Nháp', 'completed' => 'Hoàn tất'];
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
                            $outboundStatusLabelMap = ['' => 'Tất cả trạng thái', 'draft' => 'Nháp', 'issued' => 'Đã xuất kho'];
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

            @endif
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
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

        wireHkFilterDropdown({
            root: 'hkGrInboundStatusFilter', trigger: 'hkGrInboundStatusTrigger', panel: 'hkGrInboundStatusPanel',
            label: 'hkGrInboundStatusLabel', list: 'hkGrInboundStatusList', hidden: 'grInboundStatusFilter',
        });

        wireHkFilterDropdown({
            root: 'hkGrOutboundStatusFilter', trigger: 'hkGrOutboundStatusTrigger', panel: 'hkGrOutboundStatusPanel',
            label: 'hkGrOutboundStatusLabel', list: 'hkGrOutboundStatusList', hidden: 'grOutboundStatusFilter',
        });

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
