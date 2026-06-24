@extends('layouts.admin')

@section('title', 'Quản lý sản phẩm')

@section('css')
    <style>
        .product-table thead th {
            background: #ffffff;
            color: #111827;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            white-space: nowrap;
        }

        .product-table tbody td {
            color: #374151;
            font-size: 13px;
        }

        .product-table tbody tr:nth-child(odd) {
            background: #f3f3f3;
        }

        .page-action-btn {
            min-height: 32px;
            padding: 7px 14px;
            border-radius: 2px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .page-action-btn.btn-primary {
            background: #174761;
            border-color: #174761;
        }

        .product-thumb {
            width: 52px;
            height: 52px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .product-thumb-placeholder {
            width: 52px;
            height: 52px;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 18px;
        }

        .status-badge {
            border-radius: 2px;
            font-size: 10px;
            font-weight: 800;
            line-height: 1;
            padding: 4px 6px;
        }

        .price-display {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .price-sale {
            font-weight: 700;
            color: #dc2626;
        }

        .price-original {
            font-size: 11px;
            color: #9ca3af;
            text-decoration: line-through;
        }

        .price-normal {
            font-weight: 700;
            color: #111827;
        }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h4 fw-bold mb-0" style="color:#174761;">Quản lý sản phẩm</h1>
            </div>
            <div class="small text-muted">
                Trang chủ <span class="mx-1">/</span> Sản phẩm
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
            <a href="#" class="btn btn-primary page-action-btn">
                <i class="fa-solid fa-plus me-1"></i> THÊM SẢN PHẨM
            </a>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.products.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5 col-lg-4">
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ $keyword }}"
                                    placeholder="Tìm theo tên sản phẩm...">
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-3">
                            <select name="category_id" class="form-select" style="font-size:13px;">
                                <option value="">Tất cả danh mục</option>
                                @foreach($categories as $parent)
                                    <optgroup label="{{ $parent->name }}">
                                        @foreach($parent->childrenCategories as $child)
                                            <option value="{{ $child->id }}"
                                                {{ (string)$categoryId === (string)$child->id ? 'selected' : '' }}>
                                                {{ $child->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                            @if($keyword || $categoryId)
                                <a href="{{ route('admin.products.list') }}" class="btn btn-outline-secondary fw-semibold ms-1">
                                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered product-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:60px;">ID</th>
                                <th style="width:70px;">Hình ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th style="width:160px;">Danh mục</th>
                                <th style="width:130px;">Giá</th>
                                <th style="width:100px;">Trạng thái</th>
                                <th class="text-center" style="width:110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="ps-3 text-muted" style="opacity:0.45;">{{ $product->id }}</td>

                                    <td>
                                        @if($product->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}"
                                                alt="{{ $product->name }}"
                                                class="product-thumb">
                                        @else
                                            <div class="product-thumb-placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size:13px;">
                                            {{ $product->name }}
                                        </div>
                                        @if($product->brand)
                                            <div class="text-muted" style="font-size:11px;">{{ $product->brand->name ?? '' }}</div>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="text-muted" style="font-size:12px;">
                                            {{ $product->category?->name ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        @if($product->sale_price && $product->sale_price < $product->price)
                                            <div class="price-display">
                                                <span class="price-sale">{{ number_format($product->sale_price, 0, ',', '.') }}₫</span>
                                                <span class="price-original">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                            </div>
                                        @else
                                            <span class="price-normal">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                        @endif
                                    </td>

                                    <td>
                                        <span class="badge status-badge {{ $product->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $product->status ? 'Đang bán' : 'Ẩn' }}
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="#"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Sửa">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Xóa"
                                                data-delete-url="{{ route('admin.products.destroy', $product->id) }}"
                                                data-delete-name="{{ $product->name }}"
                                                data-delete-type="sản phẩm">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px; display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có sản phẩm nào</div>
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
