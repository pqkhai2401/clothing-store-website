@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@push('styles')
    @include('admin.products.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
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
                        placeholder="Tìm kiếm theo tên sản phẩm, danh mục,..." autocomplete="off">

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
                                        <span class="status-badge {{ $product->status ? 'status-badge--active' : 'status-badge--inactive' }}">
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
                @include('layouts.components.pagination', [
                    'paginator'     => $products,
                    'itemLabel'     => 'sản phẩm',
                    'bulkDeleteUrl' => route('admin.products.bulkDelete'),
                ])
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.products.scripts')
@endpush
