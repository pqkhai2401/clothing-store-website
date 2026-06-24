@extends('layouts.admin')

@section('title', 'Quản lý màu sắc')

@section('css')
    <style>
        .mgmt-table thead th {
            background: #ffffff; color: #111827;
            font-size: 12px; font-weight: 800; white-space: nowrap;
        }
        .mgmt-table tbody td { color: #374151; font-size: 13px; }
        .mgmt-table tbody tr:nth-child(odd) { background: #f3f3f3; }
        .page-action-btn {
            min-height: 32px; padding: 7px 14px; border-radius: 2px;
            font-size: 12px; font-weight: 800; text-transform: uppercase;
        }
        .page-action-btn.btn-primary { background: #174761; border-color: #174761; }
        .badge-count {
            background: #e9ecef; color: #374151;
            border-radius: 12px; font-size: 11px; font-weight: 700; padding: 3px 8px;
        }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">Quản lý màu sắc</h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> Màu sắc</div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3">
            <a href="#" class="btn btn-primary page-action-btn">
                <i class="fa-solid fa-plus me-1"></i> Thêm màu sắc
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.colors.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ $keyword }}" placeholder="Tìm theo tên màu sắc...">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                            @if($keyword)
                                <a href="{{ route('admin.colors.list') }}" class="btn btn-outline-secondary fw-semibold ms-1">
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
                                <th class="ps-3" style="width:60px;">ID</th>
                                <th>Tên màu sắc</th>
                                <th style="width:160px;">Số biến thể SP</th>
                                <th style="width:180px;">Ngày tạo</th>
                                <th class="text-center" style="width:110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($colors as $color)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $color->id }}</td>
                                    <td class="fw-semibold">{{ $color->name }}</td>
                                    <td><span class="badge-count">{{ $color->product_variants_count }}</span></td>
                                    <td class="text-muted" style="font-size:12px;">
                                        {{ $color->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <a href="#" class="btn btn-sm btn-outline-warning" title="Sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Xóa"
                                                data-delete-url="{{ route('admin.colors.destroy', $color->id) }}"
                                                data-delete-name="{{ $color->name }}"
                                                data-delete-type="màu sắc">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có màu sắc nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $colors])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
