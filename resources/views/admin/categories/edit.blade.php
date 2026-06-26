@extends('layouts.admin')

@section('title', 'Sửa danh mục: ' . $category->name)

@section('css')
<style>
    .edit-card { border: 1px solid var(--hk-border, #d8dee6); border-radius: 6px; }
    .edit-card .card-header { background: var(--hk-bg-card, #fff); border-bottom: 1px solid var(--hk-border, #d8dee6); padding: 12px 18px; }
    .edit-field { margin-bottom: 18px; }
    .edit-field label { display: block; font-size: 13px; font-weight: 700; color: var(--hk-text-2, #374151); margin-bottom: 6px; }
    .edit-field .form-control,
    .edit-field .form-select { font-size: 13px; border-color: var(--hk-border, #ced4da); background: var(--hk-bg-input, #fff); color: var(--hk-text-1, #111); }
    .edit-field .form-control:focus,
    .edit-field .form-select:focus { border-color: #174761; box-shadow: 0 0 0 2px rgba(23,71,97,.12); }
    .edit-field .form-text { font-size: 12px; color: var(--hk-text-3, #6b7280); margin-top: 4px; }
    .edit-actions { display: flex; gap: 10px; padding-top: 6px; }
    .edit-actions .btn { min-height: 36px; font-size: 13px; font-weight: 700; border-radius: 4px; padding: 6px 18px; }
</style>
@endsection

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa danh mục</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">ID: {{ $category->id }} — <strong>{{ $category->name }}</strong></p>
        </div>
        <div class="small text-muted">Trang chủ <span class="mx-1">/</span> <a href="{{ route('admin.categories.list') }}" class="text-decoration-none">Danh mục</a> <span class="mx-1">/</span> Sửa</div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
            <div class="card edit-card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-bold" style="font-size:14px;">Thông tin danh mục</span>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.categories.update', $category->id) }}">
                        @csrf
                        @method('PUT')

                        {{-- Tên danh mục --}}
                        <div class="edit-field">
                            <label for="name">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $category->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Slug --}}
                        <div class="edit-field">
                            <label for="slug">Slug</label>
                            <input type="text" id="slug" name="slug"
                                class="form-control @error('slug') is-invalid @enderror"
                                value="{{ old('slug', $category->slug) }}"
                                placeholder="Tự động sinh từ tên nếu để trống">
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Để trống để tự động tạo từ tên. Chỉ dùng chữ thường, số và dấu gạch ngang.</div>
                        </div>

                        {{-- Danh mục cha --}}
                        <div class="edit-field">
                            <label for="parent_id">Danh mục cha</label>
                            <select id="parent_id" name="parent_id"
                                class="form-select @error('parent_id') is-invalid @enderror">
                                <option value="">— Không có (đây là danh mục gốc) —</option>
                                @foreach($parentOptions as $parent)
                                    <option value="{{ $parent->id }}"
                                        {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>
                                        {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Chọn danh mục cha nếu đây là danh mục con.</div>
                        </div>

                        <div class="edit-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Cập nhật
                            </button>
                            <a href="{{ route('admin.categories.list') }}" class="btn btn-light border">
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
