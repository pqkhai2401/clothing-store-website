@extends('layouts.admin')

@section('title', 'Sửa kích thước: ' . $size->name)

@section('css')
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
    .edit-actions { display: flex; gap: 10px; padding-top: 6px; }
    .edit-actions .btn { min-height: 36px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 18px; }
</style>
@endsection

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa kích thước</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $size->id }} — <strong>{{ $size->name }}</strong></p>
        </div>
        <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.sizes.list') }}" class="text-decoration-none">Kích thước</a> <span class="mx-1">/</span> Sửa</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card edit-card shadow-sm">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Thông tin kích thước</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.sizes.update', $size->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="edit-field">
                            <label for="name">Tên kích thước <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $size->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="edit-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.sizes.list') }}" class="btn btn-light border">
                                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
