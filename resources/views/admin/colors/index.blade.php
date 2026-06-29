@extends('layouts.admin')

@section('title', 'Quản lý màu sắc')

@push('styles')
    @include('admin.colors.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý màu sắc</h1>
                <p class="product-header-desc mb-0">Danh sách màu sắc dùng cho biến thể sản phẩm.</p>
                <div class="product-header-actions">
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm màu sắc
                    </a>
                    <a href="{{ route('admin.colors.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.colors.list') }}" id="colorSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="colorRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên màu sắc..." autocomplete="off">
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.colors.partials.table')
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
