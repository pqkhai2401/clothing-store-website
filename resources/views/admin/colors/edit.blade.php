@extends('layouts.admin')

@section('title', 'Sửa màu sắc: ' . $color->name)

@push('styles')
    @include('admin.colors.styles')
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa màu sắc</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $color->id }} — <strong>{{ $color->name }}</strong></p>
        </div>
        <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.colors.list') }}" class="text-decoration-none">Màu sắc</a> <span class="mx-1">/</span> Sửa</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card edit-card shadow-sm">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Thông tin màu sắc</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.colors.update', $color->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="edit-field">
                            <label for="name">Tên màu sắc <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $color->name) }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="edit-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.colors.list') }}" class="btn btn-light border">
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
