@extends('layouts.admin')

@section('title', 'Thùng rác — Sản phẩm')

@section('css')
    <style>
        /* ── Page header ───────────────────────────────────── */
        .product-header-title {
            color: #020617 !important;
            font-size: 25px;
            font-weight: 800;
        }

        .product-header-desc {
            color: #64748B;
            font-size: 14px;
        }

        [data-theme="dark"] .product-header-title { color: #F8FAFC !important; }
        [data-theme="dark"] .product-header-desc  { color: #94A3B8 !important; }

        /* ── Thumbnail ─────────────────────────────────────── */
        .product-trash-thumb {
            width: 48px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
        }

        .product-trash-thumb-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 6px;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94A3B8;
            font-size: 15px;
        }

        /* ── Search ────────────────────────────────────────── */
        .product-trash-search {
            min-height: 38px;
            border: 1px solid #D8E0EA;
            border-radius: 10px;
            font-size: 13px;
            color: #0F172A;
            background: #ffffff;
            width: 320px;
            box-shadow: none;
        }

        .product-trash-search:focus {
            border-color: #16A34A;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.12);
            color: #0F172A;
        }

        .product-trash-search::placeholder {
            color: #94A3B8;
        }

        /* ── Table ─────────────────────────────────────────── */
        .product-trash-table-wrap {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
        }

        .product-trash-table thead th {
            color: #334155;
            font-weight: 800;
            font-size: 13px;
            padding: 13px 16px;
            border-bottom: 1px solid #E2E8F0;
            background: #F8FAFC;
            white-space: nowrap;
        }

        .product-trash-table tbody td {
            padding: 14px 16px;
            font-size: 13px;
            color: #0F172A;
            border-bottom: 1px solid #E2E8F0;
            vertical-align: middle;
        }

        .product-trash-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .product-trash-table tbody tr:hover td {
            background: #F8FAFC;
        }

        /* ── ID + muted text ───────────────────────────────── */
        .product-trash-id { color: #64748B; font-weight: 600; }
        .product-trash-brand { font-size: 12px; color: #94A3B8; margin-top: 2px; }
        .product-trash-cat { color: #475569; }
        .product-trash-date { color: #475569; }

        /* ── Price ─────────────────────────────────────────── */
        .trash-price-sale { font-weight: 800; color: #DC2626; font-size: 13px; }
        .trash-price-original { font-size: 11px; color: #94A3B8; text-decoration: line-through; }
        .trash-price-normal { font-weight: 700; color: #0F172A; }

        /* ── Dark mode ─────────────────────────────────────── */
        [data-theme="dark"] .product-trash-thumb { border-color: #22324D; }
        [data-theme="dark"] .product-trash-thumb-placeholder {
            background: #0C1830; border-color: #22324D; color: #64748B;
        }
        [data-theme="dark"] .product-trash-search {
            background: #101C33 !important; border-color: #2A3B59 !important;
            color: #E2E8F0 !important;
        }
        [data-theme="dark"] .product-trash-search:focus {
            border-color: #3B82F6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        [data-theme="dark"] .product-trash-search::placeholder { color: #64748B !important; }
        [data-theme="dark"] .product-trash-table-wrap {
            background: #0F1B31 !important; border-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table thead th {
            background: #0C1830 !important; color: #94A3B8 !important;
            border-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table tbody td {
            color: #E2E8F0 !important; border-color: #22324D !important;
        }
        [data-theme="dark"] .product-trash-table tbody tr:hover td {
            background: #1A3050 !important;
        }
        [data-theme="dark"] .product-trash-id { color: #94A3B8 !important; }
        [data-theme="dark"] .product-trash-brand { color: #64748B !important; }
        [data-theme="dark"] .product-trash-cat { color: #CBD5E1 !important; }
        [data-theme="dark"] .product-trash-date { color: #94A3B8 !important; }
        [data-theme="dark"] .trash-price-normal { color: #E2E8F0 !important; }
        [data-theme="dark"] .trash-price-original { color: #64748B !important; }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        {{-- Page header --}}
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1 product-header-title">Thùng rác sản phẩm</h1>
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
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
@endpush
