@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@section('css')
    <style>
        .product-admin-page {
            background: #f8fafc;
            min-height: calc(100vh - 56px);
            padding-top: 0 !important;
        }

        .product-header-title {
            color: #020617;
            font-size: 25px;
            font-weight: 800;
            letter-spacing: 0;
            margin-top: 15px;
        }

        .product-header-desc {
            color: #64748b;
            font-size: 16px;
        }

        .product-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin: 30px 0 16px;
        }

        .product-toolbar-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1 1 auto;
            flex-wrap: nowrap;
            min-width: 0;
        }

        .product-search {
            min-height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 14px;
            width: min(380px, 100%);
            flex: 0 1 380px;
        }

        .product-search:focus {
            border-color: #111827;
            box-shadow: none;
        }

        /* Custom category dropdown */
        .hk-cat-filter {
            position: relative;
            width: 220px;
            flex: 0 0 220px;
        }

        .hk-cat-trigger {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            width: 100%;
            min-height: 38px;
            padding: 0 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 14px;
            color: #374151;
            text-align: left;
            cursor: pointer;
            transition: border-color .15s;
        }

        .hk-cat-trigger:hover,
        .hk-cat-trigger.is-open {
            border-color: #111827;
        }

        .hk-cat-trigger-label {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .hk-cat-arrow {
            font-size: 11px;
            color: #6b7280;
            transition: transform .2s;
            flex-shrink: 0;
        }

        .hk-cat-trigger.is-open .hk-cat-arrow {
            transform: rotate(180deg);
        }

        .hk-cat-panel {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            z-index: 1050;
            width: 240px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 8px 32px rgba(15, 23, 42, 0.14);
            overflow: hidden;
        }

        .hk-cat-search-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 12px 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .hk-cat-search-icon {
            font-size: 13px;
            color: #9ca3af;
            flex-shrink: 0;
        }

        .hk-cat-search-input {
            flex: 1;
            border: 0;
            outline: none;
            font-size: 13px;
            color: #111827;
            background: transparent;
        }

        .hk-cat-search-input::placeholder {
            color: #9ca3af;
        }

        .hk-cat-list {
            max-height: 240px;
            overflow-y: auto;
            padding: 6px 0;
        }

        .hk-cat-item {
            display: block;
            width: 100%;
            padding: 8px 14px;
            border: 0;
            background: transparent;
            font-size: 13px;
            color: #374151;
            text-align: left;
            cursor: pointer;
            transition: background .12s;
        }

        .hk-cat-item:hover {
            background: #f3f4f6;
        }

        .hk-cat-item.is-active {
            color: #111827;
            font-weight: 700;
            background: #f0f9ff;
        }

        .hk-cat-empty {
            padding: 12px 14px;
            font-size: 13px;
            color: #9ca3af;
            text-align: center;
        }

        .product-tool-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .product-action-btn {
            min-height: 38px;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 13px;
        }

        .product-action-btn.btn-dark {
            background: #020617;
            border-color: #020617;
        }

        .product-table-wrap {
            overflow: hidden;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
        }

        .product-table {
            margin: 0;
            --bs-table-hover-bg: #f8fafc;
        }

        .product-table thead th {
            height: 48px;
            padding: 0 16px;
            background: #fff;
            color: #020617;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            vertical-align: middle;
            white-space: nowrap;
        }

        .product-table tbody td {
            min-height: 64px;
            padding: 15px 16px;
            color: #0f172a;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            vertical-align: middle;
        }

        .product-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .product-check {
            width: 18px;
            height: 18px;
            cursor: pointer;
            border-color: #d1d5db;
            border-radius: 5px;
        }

        .product-sort-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0;
            color: inherit;
            background: transparent;
            border: 0;
            font: inherit;
            font-weight: 800;
        }

        .product-sort-icon {
            display: inline-flex;
            align-items: center;
            min-width: 20px;
            color: #111827;
            font-size: 15px;
            font-weight: 800;
            line-height: 1;
        }

        .product-sort-btn.is-active {
            padding: 8px 10px;
            border-radius: 10px;
            background: #f3f4f6;
        }

        .product-sort-btn.is-active .product-sort-icon {
            font-size: 16px;
        }

        .product-thumb {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .product-thumb-placeholder {
            width: 56px;
            height: 56px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 16px;
        }

        .status-badge {
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 9px;
        }

        .price-display {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .price-sale {
            font-weight: 800;
            color: #dc2626;
        }

        .price-original {
            font-size: 11px;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .price-normal {
            font-weight: 800;
            color: #111827;
        }

        .product-more-btn {
            width: 34px;
            height: 34px;
            border: 0;
            border-radius: 10px;
            color: #111827;
            background: #f3f4f6;
            font-weight: 900;
        }

        .product-more-btn:hover {
            background: #e5e7eb;
        }

        .product-row-menu {
            min-width: 168px;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 9px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
        }

        .product-row-menu .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 38px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
        }

        @media (max-width: 767.98px) {
            .product-toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .product-toolbar-left,
            .product-tool-actions {
                flex-wrap: wrap;
                justify-content: flex-end;
            }

            .product-search,
            .product-filter {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý sản phẩm</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả sản phẩm trong hệ thống.</p>
            </div>

            <div class="product-toolbar">
                <div class="product-toolbar-left">
                    <input type="search" id="productRealtimeSearch" class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên sản phẩm..." autocomplete="off"> 
                        
                    @php
                        $selectedCatLabel = 'Tất cả danh mục';
                        foreach ($categories as $parent) {
                            foreach ($parent->childrenCategories as $child) {
                                if ((string)$categoryId === (string)$child->id) {
                                    $selectedCatLabel = $child->name;
                                    break 2;
                                }
                            }
                        }
                    @endphp
                    <input type="hidden" id="productCategoryFilter" value="{{ $categoryId ?? '' }}">
                    <div class="hk-cat-filter" id="hkCatFilter">
                        <button type="button" class="hk-cat-trigger" id="hkCatTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkCatLabel">{{ $selectedCatLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkCatPanel" hidden>
                            <div class="hk-cat-search-wrap">
                                <i class="fa-solid fa-magnifying-glass hk-cat-search-icon"></i>
                                <input type="text" class="hk-cat-search-input" id="hkCatSearch" placeholder="Tìm danh mục..." autocomplete="off">
                            </div>
                            <div class="hk-cat-list" id="hkCatList" role="listbox">
                                <button type="button" class="hk-cat-item {{ !$categoryId ? 'is-active' : '' }}" data-value="" data-label="Tất cả danh mục">Tất cả danh mục</button>
                                @foreach($categories as $parent)
                                    @foreach($parent->childrenCategories as $child)
                                        <button type="button"
                                            class="hk-cat-item {{ (string)$categoryId === (string)$child->id ? 'is-active' : '' }}"
                                            data-value="{{ $child->id }}"
                                            data-label="{{ $child->name }}">
                                            {{ $child->name }}
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-tool-actions">
                    <a href="{{ route('admin.products.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
                    </a>
                </div>
            </div>

            <div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="productTable">
                        <thead>
                            <tr>
                                <th style="width: 58px;">
                                    <input type="checkbox" class="form-check-input product-check hk-cb-all" id="productCheckAll">
                                </th>
                                <th style="width: 86px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th style="width: 76px;">Ảnh</th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên sản phẩm <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="category">
                                        Danh mục <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="brand">
                                        Thương hiệu <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="price" data-sort-type="number">
                                        Giá <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="stock" data-sort-type="number">
                                        Tổng tồn kho <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="status" data-sort-type="number">
                                        Trạng thái <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-end pe-4" style="width: 96px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $effectivePrice = ($product->sale_price && $product->sale_price < $product->price)
                                        ? $product->sale_price
                                        : $product->price;
                                @endphp
                                <tr data-product-row="{{ $product->id }}"
                                    data-search-text="{{ Str::lower($product->name.' '.$product->category?->name.' '.$product->brand?->name) }}"
                                    data-category-id="{{ $product->category_id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check hk-cb-row product-row-check" value="{{ $product->id }}">
                                    </td>
                                    <td data-sort-value="{{ $product->id }}">{{ $product->id }}</td>

                                    <td>
                                        @if($product->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="product-thumb">
                                        @else
                                            <div class="product-thumb-placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        @endif
                                    </td>

                                    <td data-cell="name" data-sort-value="{{ $product->name }}">
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    </td>

                                    <td data-cell="category" data-sort-value="{{ $product->category?->name ?? '' }}">
                                        <span class="fw-semibold">{{ $product->category?->name ?? '—' }}</span>
                                    </td>

                                    <td data-cell="brand" data-sort-value="{{ $product->brand?->name ?? '' }}">
                                        <span class="fw-semibold">{{ $product->brand?->name ?? '—' }}</span>
                                    </td>

                                    <td data-cell="price" data-sort-value="{{ $effectivePrice }}">
                                        @php $discounted = $product->discount > 0; @endphp
                                        @if($discounted)
                                            <div class="price-display">
                                                <span class="price-sale">{{ number_format($product->price * (100 - $product->discount) / 100, 0, ',', '.') }}₫</span>
                                                <span class="price-original">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                            </div>
                                        @else
                                            <span class="price-normal">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                        @endif
                                    </td>

                                    @php $totalStock = (int) ($product->product_variants_sum_stock ?? 0); @endphp
                                    <td data-cell="stock" data-sort-value="{{ $totalStock }}">
                                        <span class="{{ $totalStock > 0 ? 'fw-semibold text-dark' : 'text-danger fw-semibold' }}">
                                            {{ number_format($totalStock) }}
                                        </span>
                                    </td>

                                    <td data-cell="status" data-sort-value="{{ $product->status ? 1 : 0 }}">
                                        <span class="badge status-badge {{ $product->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $product->status ? 'Đang bán' : 'Ẩn' }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.products.edit', $product->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.products.destroy', $product->id) }}"
                                                    data-delete-name="{{ $product->name }}"
                                                    data-delete-type="sản phẩm">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px; display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có sản phẩm nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', ['paginator' => $products, 'itemLabel' => 'sản phẩm', 'bulkDeleteUrl' => route('admin.products.bulkDelete')])
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('productTable');
            const searchInput = document.getElementById('productRealtimeSearch');
            const categoryFilter = document.getElementById('productCategoryFilter');
            const checkAll = document.getElementById('productCheckAll');

            if (!table) return;

            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr[data-product-row]'));
            let sortState = { key: 'id', dir: 'asc' };

            function normalize(value) {
                return String(value || '').toLowerCase().trim();
            }

            function filterRows() {
                const keyword = normalize(searchInput?.value);
                const categoryId = String(categoryFilter?.value || '');

                rows.forEach(row => {
                    const matchesKeyword = !keyword || normalize(row.dataset.searchText).includes(keyword);
                    const matchesCategory = !categoryId || String(row.dataset.categoryId || '') === categoryId;
                    row.hidden = !(matchesKeyword && matchesCategory);
                });

                if (checkAll) {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }
            }

            function cellValue(row, key, type) {
                if (key === 'id') {
                    return Number(row.children[1]?.dataset.sortValue || 0);
                }

                const cell = row.querySelector(`[data-cell="${key}"]`);
                const value = cell?.dataset.sortValue ?? cell?.innerText ?? '';

                return type === 'number' ? Number(value || 0) : normalize(value);
            }

            function setSortIcon(button, dir) {
                table.querySelectorAll('.product-sort-btn').forEach(btn => {
                    const icon = btn.querySelector('.product-sort-icon');
                    btn.classList.remove('is-active');
                    if (icon) icon.textContent = '↑↓';
                });

                const icon = button.querySelector('.product-sort-icon');
                button.classList.add('is-active');
                if (icon) {
                    icon.textContent = dir === 'asc' ? '↑' : '↓';
                }
            }

            table.querySelectorAll('.product-sort-btn').forEach(button => {
                button.addEventListener('click', function () {
                    const key = this.dataset.sortKey;
                    const type = this.dataset.sortType || 'text';
                    const dir = sortState.key === key && sortState.dir === 'asc' ? 'desc' : 'asc';
                    sortState = { key, dir };

                    rows.sort((a, b) => {
                        const valueA = cellValue(a, key, type);
                        const valueB = cellValue(b, key, type);

                        if (valueA < valueB) return dir === 'asc' ? -1 : 1;
                        if (valueA > valueB) return dir === 'asc' ? 1 : -1;
                        return 0;
                    }).forEach(row => tbody.appendChild(row));

                    setSortIcon(this, dir);
                    filterRows();
                });
            });

            searchInput?.addEventListener('input', filterRows);
            categoryFilter?.addEventListener('change', filterRows);

            // Custom category dropdown
            const catTrigger = document.getElementById('hkCatTrigger');
            const catPanel   = document.getElementById('hkCatPanel');
            const catSearch  = document.getElementById('hkCatSearch');
            const catLabel   = document.getElementById('hkCatLabel');
            const catList    = document.getElementById('hkCatList');

            function openCatPanel() {
                catPanel.hidden = false;
                catTrigger.classList.add('is-open');
                catTrigger.setAttribute('aria-expanded', 'true');
                catSearch.value = '';
                showAllCatItems();
                catSearch.focus();
            }

            function closeCatPanel() {
                catPanel.hidden = true;
                catTrigger.classList.remove('is-open');
                catTrigger.setAttribute('aria-expanded', 'false');
            }

            function showAllCatItems() {
                catList.querySelectorAll('.hk-cat-item').forEach(btn => btn.hidden = false);
                const empty = catList.querySelector('.hk-cat-empty');
                if (empty) empty.remove();
            }

            catTrigger?.addEventListener('click', function () {
                catPanel.hidden ? openCatPanel() : closeCatPanel();
            });

            catSearch?.addEventListener('input', function () {
                const q = this.value.toLowerCase().trim();
                let visible = 0;
                catList.querySelectorAll('.hk-cat-item').forEach(btn => {
                    const match = !q || btn.textContent.toLowerCase().includes(q);
                    btn.hidden = !match;
                    if (match) visible++;
                });
                const existing = catList.querySelector('.hk-cat-empty');
                if (visible === 0 && !existing) {
                    const msg = document.createElement('div');
                    msg.className = 'hk-cat-empty';
                    msg.textContent = 'Không tìm thấy danh mục';
                    catList.appendChild(msg);
                } else if (visible > 0 && existing) {
                    existing.remove();
                }
            });

            catList?.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                catList.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                catLabel.textContent = btn.dataset.label;
                if (categoryFilter) {
                    categoryFilter.value = btn.dataset.value;
                    categoryFilter.dispatchEvent(new Event('change'));
                }
                closeCatPanel();
            });

            document.addEventListener('click', function (e) {
                if (!catPanel?.hidden && !document.getElementById('hkCatFilter')?.contains(e.target)) {
                    closeCatPanel();
                }
            });

            checkAll?.addEventListener('change', function () {
                rows.filter(row => !row.hidden).forEach(row => {
                    const checkbox = row.querySelector('.product-row-check');
                    if (checkbox) checkbox.checked = this.checked;
                });
            });
        });
    </script>
@endpush
