@extends('layouts.admin')

@section('title', 'Phiếu xuất kho ' . $stockIssue->code)

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { display: inline-block; white-space: nowrap; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    .gr-badge--cancelled { background: #e2e8f0; color: #475569; }
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
        @include('admin.inventory.stock-issues.partials.show-content', ['stockIssue' => $stockIssue])
    </div>
</main>

@include('admin.partials.print-preview-modal')
@endsection
