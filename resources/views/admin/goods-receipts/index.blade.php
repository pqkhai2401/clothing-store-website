@extends('layouts.admin')

@section('title', 'Phiếu nhập kho')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    </style>
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Phiếu nhập kho</h1>
                <p class="product-header-desc mb-0">Danh sách phiếu nhập kho từ nhà cung cấp.</p>
                <div class="product-header-actions">
                    <a href="{{ route('admin.goods-receipts.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Tạo phiếu nhập kho
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.goods-receipts.list') }}" id="goodsReceiptSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="goodsReceiptRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm theo mã phiếu hoặc tên nhà cung cấp..." autocomplete="off">
                    <select name="status" class="form-select" style="max-width:180px;" data-admin-filter>
                        <option value="">Tất cả trạng thái</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Nháp</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Hoàn tất</option>
                    </select>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.goods-receipts.partials.table')
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
