@extends('layouts.admin')

@section('title', 'Thùng rác — Sản phẩm')

@section('css')
    <style>
        .product-trash-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .product-trash-thumb-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
            font-size: 15px;
        }

        .product-trash-search {
            min-height: 36px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 13px;
            color: #111827;
            width: 300px;
        }

        .product-trash-search:focus {
            border-color: #6b7280;
            box-shadow: none;
            color: #111827;
        }

        .product-trash-search::placeholder {
            color: #9ca3af;
        }

        .product-trash-table thead th {
            color: #111827;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            white-space: nowrap;
        }

        .product-trash-table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: #111827;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .product-trash-table tbody tr:last-child td {
            border-bottom: 0;
        }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        {{-- Page header --}}
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color: #111827;">Thùng rác sản phẩm</h1>
                <div class="text-muted" style="font-size: 14px;">Danh sách các sản phẩm đã xóa mềm và có thể khôi phục.</div>
            </div>
            <a href="{{ route('admin.products.list') }}" class="btn btn-light border fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        {{-- Search bar (ngoài card, dưới header) --}}
        <form method="GET" action="{{ route('admin.products.trash') }}" class="mb-3 d-flex align-items-center gap-2">
            <input type="search" name="keyword" value="{{ $keyword }}"
                class="form-control product-trash-search"
                placeholder="Tìm kiếm theo tên sản phẩm..."
                autocomplete="off">
            @if($keyword)
                <a href="{{ route('admin.products.trash') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                </a>
            @endif
        </form>

        {{-- Table card --}}
        <div class="card border shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 product-trash-table">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 70px;">ID</th>
                                <th style="width: 70px;">Ảnh</th>
                                <th>Tên sản phẩm</th>
                                <th>Danh mục</th>
                                <th>Giá</th>
                                <th>Ngày xóa</th>
                                <th class="text-end pe-4">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td class="ps-4 fw-semibold" style="color: #6b7280;">{{ $product->id }}</td>
                                    <td>
                                        @if($product->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}" class="product-trash-thumb" alt="{{ $product->name }}">
                                        @else
                                            <div class="product-trash-thumb-placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-bold" style="color: #111827;">{{ $product->name }}</div>
                                        @if($product->brand)
                                            <div style="font-size: 12px; color: #6b7280;">{{ $product->brand->name }}</div>
                                        @endif
                                    </td>
                                    <td style="color: #374151;">{{ $product->category?->name ?? '—' }}</td>
                                    <td>
                                        @if($product->discount > 0)
                                            <div class="fw-bold" style="color: #dc2626; font-size: 13px;">
                                                {{ number_format($product->price * (100 - $product->discount) / 100, 0, ',', '.') }}₫
                                            </div>
                                            <div style="font-size: 11px; color: #9ca3af; text-decoration: line-through;">
                                                {{ number_format($product->price, 0, ',', '.') }}₫
                                            </div>
                                        @else
                                            <span class="fw-semibold" style="color: #111827;">
                                                {{ number_format($product->price, 0, ',', '.') }}₫
                                            </span>
                                        @endif
                                    </td>
                                    <td style="font-size: 13px; color: #374151;">
                                        {{ $product->deleted_at?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex gap-2">
                                            <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                                    <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-danger fw-semibold"
                                                data-delete-url="{{ route('admin.products.forceDelete', $product->id) }}"
                                                data-delete-name="{{ $product->name }}"
                                                data-delete-type="sản phẩm (vĩnh viễn)">
                                                <i class="fa-solid fa-trash me-1"></i> Xóa vĩnh viễn
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                                        <div class="fw-semibold" style="color: #374151;">Thùng rác trống</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $products, 'itemLabel' => 'sản phẩm'])
            </div>
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
