@extends('layouts.admin')

@section('title', 'Thùng rác — Phiếu xuất kho')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { display: inline-block; white-space: nowrap; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    </style>
@endpush

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-3 flex-wrap">
            <div>
                <h1 class="h3 fw-bold mb-1" style="color:#174761;">Thùng rác phiếu xuất kho</h1>
                <p class="mb-0 text-muted" style="font-size:13px;">Danh sách các phiếu xuất kho đã xóa mềm và có thể khôi phục.</p>
            </div>
            <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}" class="btn btn-outline-secondary fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <form method="GET" action="{{ route('admin.stock-issues.trash') }}" class="mb-3">
            <input type="search" name="search" data-admin-search
                value="{{ $keyword }}"
                class="form-control"
                style="max-width:320px;"
                placeholder="Tìm theo mã phiếu xuất..."
                autocomplete="off">
        </form>

        <div data-admin-table-area>
            @include('admin.stock-issues.partials.trash-table')
        </div>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
@endpush
