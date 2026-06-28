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
                    <input type="search" name="search" data-admin-search id="productRealtimeSearch" class="form-control product-search"
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
                    <input type="hidden" name="category_id" data-admin-filter id="productCategoryFilter" value="{{ $categoryId ?? '' }}">
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
