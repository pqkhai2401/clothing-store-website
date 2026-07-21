@extends('layouts.admin')

@section('title', 'Sửa banner trang chủ')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Sửa banner trang chủ</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">Cập nhật nội dung, ảnh và trạng thái hiển thị của banner.</p>
        </div>
        <div class="small text-muted">
            Trang chủ <span class="mx-1">/</span>
            <a href="{{ route('admin.hero-banners.list') }}" class="text-decoration-none">Banner trang chủ</a>
            <span class="mx-1">/</span> Sửa
        </div>
    </div>

    <form method="POST" action="{{ route('admin.hero-banners.update', $heroBanner->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <span class="fw-bold text-uppercase text-secondary" style="font-size: 13px;">Nội dung banner</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold">Tiêu đề</label>
                            <input type="text" id="title" name="title"
                                   class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $heroBanner->title) }}">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label fw-bold">Mô tả ngắn</label>
                            <input type="text" id="subtitle" name="subtitle"
                                   class="form-control @error('subtitle') is-invalid @enderror"
                                   value="{{ old('subtitle', $heroBanner->subtitle) }}">
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="button_text" class="form-label fw-bold">Chữ trên nút bấm</label>
                                <input type="text" id="button_text" name="button_text"
                                       class="form-control @error('button_text') is-invalid @enderror"
                                       value="{{ old('button_text', $heroBanner->button_text) }}">
                                @error('button_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="button_link" class="form-label fw-bold">Đường dẫn khi bấm nút</label>
                                <input type="text" id="button_link" name="button_link"
                                       class="form-control @error('button_link') is-invalid @enderror"
                                       value="{{ old('button_link', $heroBanner->button_link) }}">
                                @error('button_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Ảnh hiện tại</label>
                            <img src="{{ asset($heroBanner->image_path) }}" alt="{{ $heroBanner->title }}"
                                 class="img-thumbnail mb-2" style="width: 260px; height: 130px; object-fit: cover;">
                            <input type="file" id="image" name="image"
                                   class="form-control @error('image') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Để trống nếu không muốn thay ảnh. Định dạng: jpg, png, webp. Tối đa 2MB.</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm h-100 d-flex flex-column">
                    <div class="card-header bg-white py-3">
                        <span class="fw-bold text-uppercase text-secondary" style="font-size: 13px;">Hiển thị</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">Trạng thái</label>
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $heroBanner->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Bật hiển thị trên trang chủ</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label fw-bold">Thứ tự hiển thị</label>
                            <input type="number" id="sort_order" name="sort_order" min="0"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', $heroBanner->sort_order) }}">
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label fw-bold">Bắt đầu hiển thị</label>
                            <input type="datetime-local" id="start_date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date', optional($heroBanner->start_date)->format('Y-m-d\TH:i')) }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label fw-bold">Kết thúc hiển thị</label>
                            <input type="datetime-local" id="end_date" name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date', optional($heroBanner->end_date)->format('Y-m-d\TH:i')) }}">
                            <div class="form-text">Để trống nếu banner hiển thị vô thời hạn (đến khi tự tắt).</div>
                            @if($heroBanner->isExpired())
                                <div class="form-text text-danger fw-bold mt-1">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i>Banner đã hết hạn hiển thị{{ $heroBanner->is_active ? ' nhưng vẫn đang bật — trang chủ sẽ tự ẩn banner này.' : '.' }}
                                </div>
                            @endif
                            @error('end_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3 d-flex justify-content-end gap-2 border-top mt-auto">
                        <a href="{{ route('admin.hero-banners.list') }}" class="btn btn-light border fw-bold" style="min-height: 40px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary fw-bold" style="min-height: 40px; min-width: 120px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu lại
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>
@endsection
