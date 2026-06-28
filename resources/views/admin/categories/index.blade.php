@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@push('styles')
    @include('admin.categories.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý danh mục</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả danh mục sản phẩm trong hệ thống.</p>
            </div>

            <form method="GET" action="{{ route('admin.categories.list') }}" id="catSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="catRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên danh mục hoặc slug..." autocomplete="off">
                </div>
                <div class="product-tool-actions">
                    <a href="{{ route('admin.categories.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm danh mục
                    </a>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.categories.partials.table')
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
