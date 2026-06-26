@extends('layouts.admin')

@section('title', 'Thùng rác — Đánh giá')

@section('css')
    <style>
        .mgmt-table thead th { background:#fff; color:#111827; font-size:12px; font-weight:800; white-space:nowrap; }
        .mgmt-table tbody td { color:#374151; font-size:13px; }
        .mgmt-table tbody tr:nth-child(odd) { background:#f3f3f3; }
        .page-action-btn { min-height:32px; padding:7px 14px; border-radius:2px; font-size:12px; font-weight:800; text-transform:uppercase; }
        .deleted-at { font-size:11px; color:#9ca3af; }
        .stars { color:#f59e0b; font-size:12px; }
        .stars .empty { color:#d1d5db; }
        .product-thumb { width:36px; height:36px; object-fit:cover; border-radius:3px; border:1px solid #e5e7eb; }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">
                <i class="fa-solid fa-trash me-2 text-danger" style="font-size:18px;"></i>Thùng rác — Đánh giá
            </h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.reviews.list') }}" class="text-muted">Đánh giá</a> <span class="mx-1">/</span> Thùng rác</div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.reviews.list') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.reviews.trash') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="keyword" class="form-control" value="{{ $keyword }}" placeholder="Tìm trong thùng rác...">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold"><i class="fa-solid fa-filter me-1"></i> Lọc</button>
                            @if($keyword)
                                <a href="{{ route('admin.reviews.trash') }}" class="btn btn-outline-secondary fw-semibold ms-1"><i class="fa-solid fa-xmark me-1"></i> Xóa lọc</a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mgmt-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:60px;">ID</th>
                                <th style="width:50px;">Ảnh</th>
                                <th style="width:200px;">Sản phẩm</th>
                                <th style="width:140px;">Khách hàng</th>
                                <th style="width:100px;">Đánh giá</th>
                                <th>Nhận xét</th>
                                <th style="width:130px;">Đã xóa lúc</th>
                                <th class="text-center" style="width:130px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $review->id }}</td>
                                    <td>
                                        @if($review->product?->thumbnail)
                                            <img src="{{ asset($review->product->thumbnail) }}" class="product-thumb" alt="">
                                        @else
                                            <div style="width:36px;height:36px;background:#f3f4f6;border-radius:3px;border:1px solid #e5e7eb;"></div>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">{{ $review->product?->name ?? 'SP đã bị xóa' }}</td>
                                    <td class="text-muted" style="font-size:12px;">{{ $review->user?->username ?? '—' }}</td>
                                    <td>
                                        <div class="stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="{{ $i <= $review->rating ? 'fa-solid' : 'fa-regular empty' }} fa-star"></i>
                                            @endfor
                                        </div>
                                    </td>
                                    <td style="font-size:12px;">{{ \Illuminate\Support\Str::limit($review->comment, 60) ?? '—' }}</td>
                                    <td><span class="deleted-at">{{ $review->deleted_at?->format('d/m/Y H:i') }}</span></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <form method="POST" action="{{ route('admin.reviews.restore', $review->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Khôi phục">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn"
                                                data-delete-url="{{ route('admin.reviews.forceDelete', $review->id) }}"
                                                data-delete-name="{{ $review->user?->username ?? 'đánh giá này' }}"
                                                data-delete-type="đánh giá (vĩnh viễn)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fa-solid fa-trash text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Thùng rác trống</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $reviews, 'itemLabel' => 'đánh giá'])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
