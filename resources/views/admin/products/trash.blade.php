@extends('layouts.admin')

@section('title', 'Thùng rác — Sản phẩm')

@section('css')
    <style>
        .mgmt-table thead th { background:#fff; color:#111827; font-size:12px; font-weight:800; white-space:nowrap; }
        .mgmt-table tbody td { color:#374151; font-size:13px; }
        .mgmt-table tbody tr:nth-child(odd) { background:#f3f3f3; }
        .page-action-btn { min-height:32px; padding:7px 14px; border-radius:2px; font-size:12px; font-weight:800; text-transform:uppercase; }
        .deleted-at { font-size:11px; color:#9ca3af; }
        .product-thumb { width:44px; height:44px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb; }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">
                <i class="fa-solid fa-trash me-2 text-danger" style="font-size:18px;"></i>Thùng rác — Sản phẩm
            </h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.products.list') }}" class="text-muted">Sản phẩm</a> <span class="mx-1">/</span> Thùng rác</div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.products.list') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.products.trash') }}">
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
                                <a href="{{ route('admin.products.trash') }}" class="btn btn-outline-secondary fw-semibold ms-1"><i class="fa-solid fa-xmark me-1"></i> Xóa lọc</a>
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
                                <th style="width:60px;">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th style="width:150px;">Danh mục</th>
                                <th style="width:130px;">Giá</th>
                                <th style="width:140px;">Đã xóa lúc</th>
                                <th class="text-center" style="width:130px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $product->id }}</td>
                                    <td>
                                        @if($product->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}" class="product-thumb" alt="">
                                        @else
                                            <div style="width:44px;height:44px;background:#f3f4f6;border-radius:4px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa-regular fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-muted">{{ $product->name }}</td>
                                    <td class="text-muted" style="font-size:12px;">{{ $product->category?->name ?? '—' }}</td>
                                    <td style="font-weight:600; color:#6b7280;">{{ number_format($product->price, 0, ',', '.') }}₫</td>
                                    <td><span class="deleted-at">{{ $product->deleted_at?->format('d/m/Y H:i') }}</span></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Khôi phục">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn"
                                                data-delete-url="{{ route('admin.products.forceDelete', $product->id) }}"
                                                data-delete-name="{{ $product->name }}"
                                                data-delete-type="sản phẩm (vĩnh viễn)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
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
                @include('layouts.components.pagination', ['paginator' => $products])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
