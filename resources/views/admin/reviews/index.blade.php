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
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ $keyword }}" placeholder="Tên SP, KH, nội dung...">
                            </div>
                        </div>
                        <div class="col-md-2 col-lg-2">
                            <select name="rating" class="form-select" style="font-size:13px;">
                                <option value="">Tất cả sao</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" {{ (string)$ratingFilter === (string)$i ? 'selected' : '' }}>
                                        {{ $i }} sao
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                            @if($keyword || $ratingFilter)
                                <a href="{{ route('admin.reviews.list') }}" class="btn btn-outline-secondary fw-semibold ms-1">
                                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                                </a>
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
                                <th class="hk-cb-th"><input type="checkbox" class="hk-cb-all"></th>
                                <th class="ps-3" style="width:60px;">ID</th>
                                <th style="width:60px;">Ảnh</th>
                                <th style="width:200px;">Sản phẩm</th>
                                <th style="width:150px;">Khách hàng</th>
                                <th style="width:110px;">Đánh giá</th>
                                <th>Nhận xét</th>
                                <th style="width:110px;">Ngày</th>
                                <th class="text-center" style="width:80px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                                <tr>
                                    <td class="hk-cb-td"><input type="checkbox" class="hk-cb-row" value="{{ $review->id }}"></td>
                                    <td class="ps-3" style="opacity:.45;">{{ $review->id }}</td>
                                    <td>
                                        @if($review->product?->thumbnail)
                                            <img src="{{ asset($review->product->thumbnail) }}"
                                                class="product-thumb" alt="">
                                        @else
                                            <div style="width:40px;height:40px;background:#f3f4f6;border-radius:4px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa-regular fa-image text-muted" style="font-size:12px;"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:12px; line-height:1.4;">
                                            {{ $review->product?->name ?? 'SP đã bị xóa' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="font-size:12px;">{{ $review->user?->username ?? '—' }}</div>
                                        @if($review->user?->email)
                                            <div class="text-muted" style="font-size:11px;">{{ $review->user->email }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="stars">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i class="fa-solid fa-star"></i>
                                                @else
                                                    <i class="fa-regular fa-star empty"></i>
                                                @endif
                                            @endfor
                                        </div>
                                        <div class="text-muted" style="font-size:11px;">{{ $review->rating }}/5</div>
                                    </td>
                                    <td>
                                        <span style="font-size:12px;">
                                            {{ $review->comment ? \Illuminate\Support\Str::limit($review->comment, 80) : '—' }}
                                        </span>
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        {{ $review->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa"
                                            data-delete-url="{{ route('admin.reviews.destroy', $review->id) }}"
                                            data-delete-name="{{ $review->user?->username ?? 'đánh giá này' }}"
                                            data-delete-type="đánh giá">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có đánh giá nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $reviews, 'itemLabel' => 'đánh giá', 'bulkDeleteUrl' => route('admin.reviews.bulkDelete')])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
