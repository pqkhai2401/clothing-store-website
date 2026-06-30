@extends('layouts.admin')

@section('title', 'Sửa kích thước: ' . $size->name)

@push('styles')
    @include('admin.sizes.styles')
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa kích thước</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $size->id }} - <strong>{{ $size->name }}</strong></p>
        </div>
        <div class="small text-muted">
            Trang chủ <span class="mx-1">/</span>
            <a href="{{ route('admin.sizes.list') }}" class="text-decoration-none">Kích thước</a>
            <span class="mx-1">/</span> Sửa
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="edit-field">
                                    <label for="category_group">Nhóm danh mục <span class="text-danger">*</span></label>
                                    <select id="category_group" name="category_group"
                                        class="form-select @error('category_group') is-invalid @enderror" required>
                                        @foreach($categoryGroups as $value => $label)
                                            <option value="{{ $value }}" @selected(old('category_group', $size->category_group) === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="edit-field">
                                    <label for="sort_weight">Thứ tự hiển thị <span class="text-danger">*</span></label>
                                    <input type="number" min="0" id="sort_weight" name="sort_weight"
                                        class="form-control @error('sort_weight') is-invalid @enderror"
                                        value="{{ old('sort_weight', $size->sort_weight) }}" required>
                                    @error('sort_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="edit-field">
                            <label for="status">Trạng thái <span class="text-danger">*</span></label>
                            <select id="status" name="status"
                                class="form-select @error('status') is-invalid @enderror" required>
                                <option value="1" @selected((string) old('status', $size->status) === '1')>Hoạt động</option>
                                <option value="0" @selected((string) old('status', $size->status) === '0')>Ẩn</option>
                            </select>
                            @error('status')
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
