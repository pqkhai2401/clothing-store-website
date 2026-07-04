@extends('layouts.admin')

@section('title', 'Sửa nhà cung cấp: ' . $supplier->name)

@push('styles')
    @include('admin.suppliers.styles')
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa nhà cung cấp</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $supplier->id }} — <strong>{{ $supplier->name }}</strong></p>
        </div>
        <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.suppliers.list') }}" class="text-decoration-none">Nhà cung cấp</a> <span class="mx-1">/</span> Sửa</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card edit-card shadow-sm">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Thông tin nhà cung cấp</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.suppliers.update', $supplier->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="edit-field">
                            <label for="name">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $supplier->name) }}" required autofocus>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label for="phone">Số điện thoại</label>
                            <input type="text" id="phone" name="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $supplier->phone) }}">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $supplier->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field">
                            <label for="address">Địa chỉ</label>
                            <input type="text" id="address" name="address"
                                class="form-control @error('address') is-invalid @enderror"
                                value="{{ old('address', $supplier->address) }}">
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-field mb-0">
                            <label for="note">Ghi chú</label>
                            <textarea id="note" name="note" rows="3"
                                class="form-control @error('note') is-invalid @enderror">{{ old('note', $supplier->note) }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="edit-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.suppliers.list') }}" class="btn btn-light border">
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
