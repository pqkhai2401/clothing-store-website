@extends('layouts.admin')

@section('title', 'Quản lý đánh giá')

@push('styles')
    @include('admin.reviews.styles')
@endpush

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">Quản lý đánh giá</h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> Đánh giá</div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="{{ route('admin.reviews.trash') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-trash me-1"></i> Thùng rác
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.reviews.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4 col-lg-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="search" name="search" data-admin-search class="form-control"
                                    value="{{ $keyword }}" placeholder="Tên SP, KH, nội dung...">
                            </div>
                        </div>
                        <div class="col-md-2 col-lg-2">
                            <select name="rating" data-admin-filter class="form-select" style="font-size:13px;">
                                <option value="">Tất cả sao</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ (string)$ratingFilter === (string)$i ? 'selected' : '' }}>
                                        {{ $i }} sao
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2 col-lg-2">
                            <select name="status" data-admin-filter class="form-select" style="font-size:13px;">
                                <option value="">Tất cả trạng thái</option>
                                <option value="pending"  {{ $statusFilter === 'pending'  ? 'selected' : '' }}>Chờ duyệt</option>
                                <option value="approved" {{ $statusFilter === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                <option value="flagged"  {{ $statusFilter === 'flagged'  ? 'selected' : '' }}>Chờ Admin (gắn cờ)</option>
                                <option value="rejected" {{ $statusFilter === 'rejected' ? 'selected' : '' }}>Bị từ chối</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                            @if($keyword || $ratingFilter || $statusFilter)
                                <a href="{{ route('admin.reviews.list') }}" class="btn btn-outline-secondary fw-semibold ms-1">
                                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div data-admin-table-area>
                @include('admin.reviews.partials.table')
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
