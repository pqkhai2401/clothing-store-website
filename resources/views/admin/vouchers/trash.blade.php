@extends('layouts.admin')

@section('title', 'Thùng rác voucher')

@push('styles')
    @include('admin.vouchers.styles')
@endpush

@section('content')
    <div class="container-fluid py-4 voucher-trash-page" style="padding-top: 0px;">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1 product-header-title" style="margin: 0;">Thùng rác voucher</h1>
                <p class="product-header-desc mb-0">Danh sách các voucher đã xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route('admin.vouchers.list') }}" class="btn btn-outline-secondary fw-semibold voucher-trash-back-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form method="GET" action="{{ route('admin.vouchers.trash') }}" class="mb-3">
            <input type="search" name="search" data-admin-search
                value="{{ $keyword }}"
                class="form-control product-trash-search"
                placeholder="Tìm kiếm theo mã voucher..."
                autocomplete="off">
        </form>

        <div data-admin-table-area>
            @include('admin.vouchers.partials.trash-table')
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
