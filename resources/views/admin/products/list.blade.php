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

        .product-search,
        .product-filter {
            min-height: 38px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            font-size: 14px;
        }

        .product-search {
            width: min(380px, 100%);
            flex: 0 1 380px;
        }

        .product-filter {
            width: 230px;
            flex: 0 0 230px;
        }

        .product-search:focus,
        .product-filter:focus {
            border-color: #111827;
            box-shadow: none;
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
                        
                    <select id="productCategoryFilter" class="form-select product-filter">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $parent)
                            <optgroup label="{{ $parent->name }}">
                                @foreach($parent->childrenCategories as $child)
                                    <option value="{{ $child->id }}" @selected((string)$categoryId === (string)$child->id)>
                                        {{ $child->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
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
                                    <button type="button" class="product-sort-btn" data-sort-key="price" data-sort-type="number">
                                        Giá <span class="product-sort-icon">↑↓</span>
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
                                        @if($product->brand)
                                            <div class="text-muted small">{{ $product->brand->name ?? '' }}</div>
                                        @endif
                                    </td>

                                    <td data-cell="category" data-sort-value="{{ $product->category?->name ?? '' }}">
                                        <span class="fw-semibold">{{ $product->category?->name ?? 'Chưa có danh mục' }}</span>
                                    </td>

                                    <td data-cell="price" data-sort-value="{{ $effectivePrice }}">
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <div class="price-display">
                                                <span class="price-sale">{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                                                <span class="price-original">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                            </div>
                                        @else
                                            <span class="price-normal">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                        @endif
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
                                    <td colspan="8" class="text-center py-5">
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

            checkAll?.addEventListener('change', function () {
                rows.filter(row => !row.hidden).forEach(row => {
                    const checkbox = row.querySelector('.product-row-check');
                    if (checkbox) checkbox.checked = this.checked;
                });
            });
        });
    </script>
@endpush
