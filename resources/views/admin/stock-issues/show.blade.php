@extends('layouts.admin')

@section('title', 'Phiếu xuất kho ' . $stockIssue->code)

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    .gr-btn-emerald { background: #0f9d58; border-color: #0f9d58; color: #fff; }
    .gr-btn-emerald:hover { background: #0c7a45; border-color: #0c7a45; color: #fff; }

    .si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .si-show-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .si-show-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .si-show-product { display: flex; align-items: center; gap: 10px; }
    .si-show-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .si-show-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; display: inline-block; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="mb-3">
        <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}" class="btn btn-light border fw-bold btn-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <div class="mx-auto" style="max-width: 900px;">
        @include('admin.stock-issues.partials.show-content', ['stockIssue' => $stockIssue])
    </div>
</main>
@endsection
