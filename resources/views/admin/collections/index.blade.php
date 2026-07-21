@extends('layouts.admin')

@section('title', 'Quản lý Bộ sưu tập')

@push('styles')
    @include('admin.products.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý Bộ sưu tập</h1>
                    <p class="product-header-desc mb-0">Danh sách bộ sưu tập thời trang theo mùa và chủ đề.</p>
                </div>
                <div class="product-header-actions">
                    <a href="{{ route('admin.collections.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm bộ sưu tập
                    </a>
                </div>
            </div>

            <div class="product-table-wrap mt-3">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="collectionTable">
                        <thead>
                            <tr>
                                <th class="ps-3" style="width: 60px;">ID</th>
                                <th style="width: 120px;">Banner</th>
                                <th>Tên bộ sưu tập</th>
                                <th>Slug</th>
                                <th>Mô tả</th>
                                <th class="text-center" style="width: 130px;">Số sản phẩm</th>
                                <th style="width: 130px;">Trạng thái</th>
                                <th class="text-center pe-3" style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($collections as $collection)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $collection->id }}</td>
                                    <td>
                                        @if($collection->banner)
                                            <img src="{{ asset($collection->banner) }}" alt="{{ $collection->name }}"
                                                 style="width: 100px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #e5e7eb;">
                                        @else
                                            <span class="text-muted" style="font-size:12px;">Không có ảnh</span>
                                        @endif
                                    </td>
                                    <td><span class="fw-bold text-dark">{{ $collection->name }}</span></td>
                                    <td><span class="collection-slug">{{ $collection->slug }}</span></td>
                                    <td class="text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $collection->description }}
                                    </td>
                                    <td class="text-center">
                                        <span class="cl-count-badge">{{ $collection->products_count }}</span>
                                    </td>
                                    <td>
                                        @if($collection->status)
                                            <span class="cl-badge cl-badge--active">Hiển thị</span>
                                        @else
                                            <span class="cl-badge cl-badge--inactive">Ẩn</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('admin.collections.edit', $collection->id) }}" class="cl-row-action-btn" title="Sửa">
                                                <i class="fa-solid fa-pen-clip"></i>
                                            </a>
                                            <form action="{{ route('admin.collections.destroy', $collection->id) }}" method="POST"
                                                  onsubmit="event.preventDefault(); window.showConfirm({title: 'Xác nhận xóa', message: 'Bạn có chắc chắn muốn xóa bộ sưu tập này không?', type: 'danger'}).then(ok => { if(ok) this.submit(); });" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="cl-row-action-btn text-danger" title="Xóa">
                                                    <i class="fa-regular fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Không có bộ sưu tập nào.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($collections->hasPages())
                <div class="bg-white border border-top-0 rounded-bottom px-3 py-3 d-flex justify-content-end">
                    {{ $collections->links() }}
                </div>
            @endif
        </section>
    </div>

    <style>
        /* ── Bộ sưu tập: badge + nút thao tác đồng bộ với trang Đơn hàng ── */
        .collection-slug {
            display: inline-block;
            font-family: monospace;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            background: #F1F5F9;
            padding: 3px 7px;
            border-radius: 5px;
            white-space: nowrap;
        }
        .cl-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 28px;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            background: #EEF2F7;
            color: #475569;
        }
        .cl-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 11px;
            line-height: 1;
            white-space: nowrap;
        }
        .cl-badge--active   { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; }
        .cl-badge--inactive { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; }
        .cl-row-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border: 1px solid transparent;
            border-radius: 8px;
            color: #0F172A;
            background: #F1F5F9;
            font-size: 13px;
            transition: background .15s, color .15s;
        }
        .cl-row-action-btn:hover { background: #E2E8F0; color: #020617; }
        .cl-row-action-btn.text-danger:hover { background: #fee2e2; color: #991b1b; }

        [data-theme="dark"] .collection-slug,
        [data-theme="dark"] .cl-count-badge {
            background: #162843 !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .cl-badge--active   { background: rgba(34,197,94,0.12) !important; border-color: rgba(34,197,94,0.3) !important; color: #86EFAC !important; }
        [data-theme="dark"] .cl-badge--inactive { background: rgba(239,68,68,0.12) !important; border-color: rgba(239,68,68,0.3) !important; color: #FCA5A5 !important; }
        [data-theme="dark"] .cl-row-action-btn {
            background: #101C33 !important;
            color: #CBD5E1 !important;
        }
        [data-theme="dark"] .cl-row-action-btn:hover {
            background: #162843 !important;
            color: #fff !important;
        }
    </style>
@endsection
