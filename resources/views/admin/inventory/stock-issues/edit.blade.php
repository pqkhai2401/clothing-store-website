@extends('layouts.admin')

@section('title', 'Chỉnh sửa phiếu xuất kho')

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-header-title { font-size: 25px; font-weight: 800; color: #000 !important; margin-bottom: 4px; }
    .gr-header-desc  { color: #64748b; font-size: 14px; margin: 0; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="gr-header-title">Chỉnh sửa phiếu xuất kho {{ $stockIssue->code }}</h1>
            <p class="gr-header-desc">Điều chỉnh kho hàng và danh sách mặt hàng của phiếu nháp.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12">
            @include('admin.inventory.stock-issues.partials.edit-content')
        </div>
    </div>
</main>
@endsection
