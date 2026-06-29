@extends('layouts.admin')

@section('title', 'Thùng rác — Kích thước')

@push('styles')
    @include('admin.sizes.styles')
@endpush

@section('content')
    <div class="container-fluid py-4" style="padding-top: 0px;">
        <x-notification />

        {{-- Page header --}}
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1 product-header-title" style="margin: 0;">Thùng rác kích thước</h1>
                <p class="product-header-desc mb-0">Danh sách các kích thước đã xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route('admin.sizes.list') }}" class="btn btn-outline-secondary fw-semibold" style="border-radius: 10px; font-size: 13px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại kích thước
            </a>
        </div>

        {{-- Search bar --}}
        <div class="mb-3 d-flex align-items-center gap-2">
            <input type="search" name="search" value="{{ $keyword }}"
                class="form-control product-trash-search"
                placeholder="Tìm kiếm theo tên kích thước..."
                autocomplete="off"
                data-admin-search>
            @if($keyword)
                <a href="{{ route('admin.sizes.trash') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                </a>
            @endif
        </div>

        {{-- Table area --}}
        <div data-admin-table-area>
            @include('admin.sizes.partials.trash-table')
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
