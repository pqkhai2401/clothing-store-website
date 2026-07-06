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
                <div class="product-header-actions">
                    <button type="button" class="btn btn-light border product-action-btn" onclick="exportProducts()">
                        <i class="fa-solid fa-file-excel me-1" style="color:#16A34A;"></i> Xuất file Excel
                    </button>
                    <a href="{{ route('admin.products.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm sản phẩm
                    </a>
                    <a href="{{ route('admin.products.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            <div class="product-toolbar">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="productRealtimeSearch" class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên sản phẩm, danh mục,..." autocomplete="off">

                    @php
                        $selectedCatLabel = 'Tất cả danh mục';
                        if (!empty($parentCategoryId)) {
                            $parentCat = $categories->firstWhere('id', $parentCategoryId);
                            $selectedCatLabel = $parentCat ? $parentCat->name . ' (tất cả)' : 'Tất cả danh mục';
                        } else {
                            foreach ($categories as $parent) {
                                foreach ($parent->childrenCategories as $child) {
                                    if ((string)$categoryId === (string)$child->id) {
                                        $selectedCatLabel = $child->name;
                                        break 2;
                                    }
                                }
                            }
                        }
                    @endphp
                    <input type="hidden" name="category_id" data-admin-filter id="productCategoryFilter" value="{{ $categoryId ?? '' }}">
                    <input type="hidden" name="parent_category_id" data-admin-filter id="productParentCategoryFilter" value="{{ $parentCategoryId ?? '' }}">
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
                                <button type="button" class="hk-cat-item {{ !$categoryId && empty($parentCategoryId) ? 'is-active' : '' }}"
                                    data-type="all" data-value="" data-label="Tất cả danh mục">Tất cả danh mục</button>
                                @foreach($categories as $parent)
                                    <button type="button"
                                        class="hk-cat-item {{ (string) ($parentCategoryId ?? '') === (string) $parent->id ? 'is-active' : '' }}"
                                        data-type="parent"
                                        data-value="{{ $parent->id }}"
                                        data-label="{{ $parent->name }} (tất cả)">
                                        {{ $parent->name }}
                                    </button>
                                    @foreach($parent->childrenCategories as $child)
                                        <button type="button"
                                            class="hk-cat-item ps-4 {{ (string)$categoryId === (string)$child->id ? 'is-active' : '' }}"
                                            data-type="child"
                                            data-value="{{ $child->id }}"
                                            data-label="{{ $child->name }}">
                                            {{ $child->name }}
                                        </button>
                                    @endforeach
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @php
                        $selectedSizeLabel = 'Tất cả size';
                        foreach ($sizes as $size) {
                            if ((string) ($sizeId ?? '') === (string) $size->id) {
                                $selectedSizeLabel = $size->name;
                                break;
                            }
                        }
                    @endphp
                    <input type="hidden" name="size_id" data-admin-filter id="productSizeFilter" value="{{ $sizeId ?? '' }}">
                    <div class="hk-cat-filter product-compact-filter" id="hkProductSizeFilter">
                        <button type="button" class="hk-cat-trigger" id="hkProductSizeTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkProductSizeLabel">{{ $selectedSizeLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkProductSizePanel" hidden>
                            <div class="hk-cat-list" id="hkProductSizeList" role="listbox">
                                <button type="button" class="hk-cat-item {{ !($sizeId ?? '') ? 'is-active' : '' }}" data-value="" data-label="Tất cả size">Tất cả size</button>
                                @foreach($sizes as $size)
                                    <button type="button"
                                        class="hk-cat-item {{ (string) ($sizeId ?? '') === (string) $size->id ? 'is-active' : '' }}"
                                        data-value="{{ $size->id }}"
                                        data-label="{{ $size->name }}">
                                        {{ $size->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @php
                        $selectedColorLabel = 'Tất cả màu';
                        foreach ($colors as $color) {
                            if ((string) ($colorId ?? '') === (string) $color->id) {
                                $selectedColorLabel = $color->name;
                                break;
                            }
                        }
                    @endphp
                    <input type="hidden" name="color_id" data-admin-filter id="productColorFilter" value="{{ $colorId ?? '' }}">
                    <div class="hk-cat-filter product-compact-filter" id="hkProductColorFilter">
                        <button type="button" class="hk-cat-trigger" id="hkProductColorTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkProductColorLabel">{{ $selectedColorLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkProductColorPanel" hidden>
                            <div class="hk-cat-list" id="hkProductColorList" role="listbox">
                                <button type="button" class="hk-cat-item {{ !($colorId ?? '') ? 'is-active' : '' }}" data-value="" data-label="Tất cả màu">Tất cả màu</button>
                                @foreach($colors as $color)
                                    <button type="button"
                                        class="hk-cat-item {{ (string) ($colorId ?? '') === (string) $color->id ? 'is-active' : '' }}"
                                        data-value="{{ $color->id }}"
                                        data-label="{{ $color->name }}">
                                        {{ $color->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @php
                        $selectedBrandLabel = 'Tất cả thương hiệu';
                        foreach ($brands as $brand) {
                            if ((string) ($brandId ?? '') === (string) $brand->id) {
                                $selectedBrandLabel = $brand->name;
                                break;
                            }
                        }
                    @endphp
                    <input type="hidden" name="brand_id" data-admin-filter id="productBrandFilter" value="{{ $brandId ?? '' }}">
                    <div class="hk-cat-filter product-compact-filter" id="hkProductBrandFilter">
                        <button type="button" class="hk-cat-trigger" id="hkProductBrandTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkProductBrandLabel">{{ $selectedBrandLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkProductBrandPanel" hidden>
                            <div class="hk-cat-list" id="hkProductBrandList" role="listbox">
                                <button type="button" class="hk-cat-item {{ !($brandId ?? '') ? 'is-active' : '' }}" data-value="" data-label="Tất cả thương hiệu">Tất cả thương hiệu</button>
                                @foreach($brands as $brand)
                                    <button type="button"
                                        class="hk-cat-item {{ (string) ($brandId ?? '') === (string) $brand->id ? 'is-active' : '' }}"
                                        data-value="{{ $brand->id }}"
                                        data-label="{{ $brand->name }}">
                                        {{ $brand->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-toolbar-right">
                    @php
                        $stockStatusVal = request('stock_status', $stockStatus ?? '');
                    @endphp
                    <input type="hidden" name="stock_status" data-admin-filter id="productStockStatusFilter" value="{{ $stockStatusVal }}">
                    <button type="button" class="product-low-stock-chip {{ $stockStatusVal === 'low_stock' ? 'is-active' : '' }}" id="productLowStockChip"
                        title="Lọc sản phẩm có tổng tồn kho dưới 10">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Sắp hết hàng
                    </button>

                    @php
                        $statusVal = request('status', $status ?? '');
                        $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Đang bán', '0' => 'Ẩn'];
                    @endphp
                    <input type="hidden" name="status" data-admin-filter id="productStatusFilter" value="{{ $statusVal }}">
                    <div class="hk-cat-filter product-status-filter" id="hkProductStatusFilter">
                        <button type="button" class="hk-cat-trigger" id="hkProductStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkProductStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkProductStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkProductStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Đang bán">Đang bán</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Ẩn">Ẩn</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div data-admin-table-area>
                @include('admin.products.partials.table')
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.products.scripts')
    @include('admin.partials.realtime-table')
@endpush
