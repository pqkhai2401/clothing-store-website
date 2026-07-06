@extends('layouts.admin')

@section('title', 'Thùng rác - Đánh giá')

@push('styles')
    @include('admin.reviews.styles')
@endpush

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">
                <i class="fa-solid fa-trash me-2 text-danger" style="font-size:18px;"></i>Thùng rác - Đánh giá
            </h1>
            <div class="small text-muted">
                Trang chủ <span class="mx-1">/</span>
                <a href="{{ route('admin.reviews.list') }}" class="text-muted">Đánh giá</a>
                <span class="mx-1">/</span> Thùng rác
            </div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.reviews.list') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.reviews.trash') }}" class="row g-2 align-items-center">
                    <input type="hidden" name="per_page" value="{{ $perPage }}">
                    <div class="col-md-5 col-lg-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input type="search" name="search" data-admin-search
                                class="form-control"
                                value="{{ $keyword }}"
                                placeholder="Tìm trong thùng rác..."
                                autocomplete="off">
                        </div>
                    </div>
                    @if($keyword)
                        <div class="col-auto">
                            <a href="{{ route('admin.reviews.trash') }}" class="btn btn-outline-secondary fw-semibold">
                                <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div data-admin-table-area>
                @include('admin.reviews.partials.trash-table')
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
