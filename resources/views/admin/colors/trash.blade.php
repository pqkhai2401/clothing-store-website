@extends('layouts.admin')

@section('title', 'Thùng rác — Màu sắc')

@push('styles')
    @include('admin.colors.styles')
@endpush

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">
                <i class="fa-solid fa-trash me-2 text-danger" style="font-size:18px;"></i>Thùng rác — Màu sắc
            </h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.colors.list') }}" class="text-muted">Màu sắc</a> <span class="mx-1">/</span> Thùng rác</div>
        </div>

        <div class="mb-3">
            <a href="{{ route('admin.colors.list') }}" class="btn btn-outline-secondary page-action-btn">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.colors.trash') }}">
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
                                <a href="{{ route('admin.colors.trash') }}" class="btn btn-outline-secondary fw-semibold ms-1"><i class="fa-solid fa-xmark me-1"></i> Xóa lọc</a>
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
                                <th style="width:160px;">Đã xóa lúc</th>
                                <th class="text-center" style="width:160px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($colors as $color)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $color->id }}</td>
                                    <td class="fw-semibold text-muted">{{ $color->name }}</td>
                                    <td><span class="deleted-at">{{ $color->deleted_at?->format('d/m/Y H:i') }}</span></td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <form method="POST" action="{{ route('admin.colors.restore', $color->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Khôi phục">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Xóa vĩnh viễn"
                                                data-delete-url="{{ route('admin.colors.forceDelete', $color->id) }}"
                                                data-delete-name="{{ $color->name }}"
                                                data-delete-type="màu sắc (vĩnh viễn)">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
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
                @include('layouts.components.pagination', ['paginator' => $colors, 'itemLabel' => 'màu sắc'])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
