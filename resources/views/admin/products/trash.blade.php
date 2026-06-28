@extends('layouts.admin')

@section('title', 'Thùng rác — Sản phẩm')

@push('styles')
    @include('admin.products.styles')
@endpush

@section('content')
    <div class="container-fluid py-4" style="padding-top: 0px;">
        <x-notification />

        {{-- Page header --}}
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1 product-header-title " style="margin: 0;">Thùng rác sản phẩm</h1>
                <p class="product-header-desc mb-0">Danh sách các sản phẩm đã xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route('admin.products.list') }}" class="btn btn-outline-secondary fw-semibold" style="border-radius: 10px; font-size: 13px;">
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
        <div class="product-trash-table-wrap">
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
                                <td class="ps-4 product-trash-id">{{ $product->id }}</td>
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
                                    <div class="fw-bold">{{ $product->name }}</div>
                                    @if($product->brand)
                                        <div class="product-trash-brand">{{ $product->brand->name }}</div>
                                    @endif
                                </td>
                                <td class="product-trash-cat">{{ $product->category?->name ?? '—' }}</td>
                                <td>
                                    @if($product->discount > 0)
                                        <div class="trash-price-sale">
                                            {{ number_format($product->price * (100 - $product->discount) / 100, 0, ',', '.') }}₫
                                        </div>
                                        <div class="trash-price-original">
                                            {{ number_format($product->price, 0, ',', '.') }}₫
                                        </div>
                                    @else
                                        <span class="trash-price-normal">
                                            {{ number_format($product->price, 0, ',', '.') }}₫
                                        </span>
                                    @endif
                                </td>
                                <td class="product-trash-date">
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
                                    <div class="fw-semibold text-muted">Thùng rác trống</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
                @include('layouts.components.pagination', ['paginator' => $products, 'itemLabel' => 'sản phẩm'])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
