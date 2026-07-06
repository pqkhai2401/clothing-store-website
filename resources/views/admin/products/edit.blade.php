@extends('layouts.admin')

@section('title', 'Sửa sản phẩm: ' . $product->name)

@push('styles')
    @include('admin.products.styles')
    <style>
    .create-header-title {
        font-size: 25px;
        font-weight: 800;
        color: #000 !important;
        margin-bottom: 4px;
    }
    .create-header-desc {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    /* ── Hình ảnh 3 slots ── */
    .img-slots {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
    }
    .img-slot {
        border: 2px dashed #d1d5db;
        border-radius: 10px;
        aspect-ratio: 1 / 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
        overflow: hidden;
        position: relative;
        background: #fafafa;
    }
    .img-slot:hover, .img-slot.drag-over { border-color: #174761; background: #f0f9ff; }
    .img-slot .slot-placeholder { text-align: center; padding: 8px; pointer-events: none; }
    .img-slot .slot-placeholder i { font-size: 22px; color: #9ca3af; }
    .img-slot .slot-placeholder p { font-size: 11px; color: #9ca3af; margin: 4px 0 0; }
    .img-slot .slot-badge {
        position: absolute; top: 6px; left: 6px;
        background: #174761; color: #fff;
        font-size: 10px; font-weight: 700;
        padding: 2px 7px; border-radius: 99px;
        z-index: 1;
    }
    .img-slot img.slot-preview {
        width: 100%; height: 100%;
        object-fit: cover;
        position: absolute; inset: 0;
    }
    .img-slot .slot-remove {
        display: none;
        position: absolute; top: 6px; right: 6px;
        width: 22px; height: 22px; border-radius: 50%;
        background: rgba(0,0,0,0.55); color: #fff;
        border: 0; font-size: 11px;
        align-items: center; justify-content: center;
        cursor: pointer; z-index: 2;
    }
    .img-slot.has-image .slot-remove { display: flex; }
    .img-slot.has-image .slot-placeholder { display: none; }

    /* ── hk-cat trigger inside form ── */
    .hk-cat-form .hk-cat-trigger {
        min-height: 37px;
        border-radius: 6px;
        font-size: 13px;
        border-color: #ced4da;
    }
    .hk-cat-form .hk-cat-trigger:hover,
    .hk-cat-form .hk-cat-trigger.is-open { border-color: #174761; }
    .hk-cat-form .hk-cat-panel { width: 100%; }
    .hk-cat-form.is-invalid .hk-cat-trigger { border-color: #dc3545; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    @include('admin.products.partials.edit-content', ['standalone' => true])
</main>
@endsection
