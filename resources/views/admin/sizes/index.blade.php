@extends('layouts.admin')

@section('title', 'Quản lý kích thước')

@push('styles')
    @include('admin.sizes.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý kích thước</h1>
                <p class="product-header-desc mb-0">Danh sách kích thước dùng cho biến thể sản phẩm.</p>
            </div>

            <form method="GET" action="{{ route('admin.sizes.list') }}" id="sizeSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="sizeRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên kích thước..." autocomplete="off">
                </div>
                <div class="product-tool-actions">
                    <a href="{{ route('admin.sizes.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm kích thước
                    </a>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.sizes.partials.table')
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
