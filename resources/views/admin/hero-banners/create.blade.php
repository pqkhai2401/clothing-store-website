@extends('layouts.admin')

@section('title', 'Thêm banner trang chủ')

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Thêm banner trang chủ</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">Tạo banner mới cho khu vực hero ở đầu trang chủ.</p>
        </div>
        <div class="small text-muted">
            Trang chủ <span class="mx-1">/</span>
            <a href="{{ route('admin.hero-banners.list') }}" class="text-decoration-none">Banner trang chủ</a>
            <span class="mx-1">/</span> Thêm mới
        </div>
    </div>

    <form method="POST" action="{{ route('admin.hero-banners.store') }}" enctype="multipart/form-data">
        @csrf
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
                                   value="{{ old('title') }}" placeholder="Ví dụ: PHONG CÁCH TỐI GIẢN">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subtitle" class="form-label fw-bold">Mô tả ngắn</label>
                            <input type="text" id="subtitle" name="subtitle"
                                   class="form-control @error('subtitle') is-invalid @enderror"
                                   value="{{ old('subtitle') }}" placeholder="Ví dụ: Nâng tầm phong cách hàng ngày...">
                            @error('subtitle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="button_text" class="form-label fw-bold">Chữ trên nút bấm</label>
                                <input type="text" id="button_text" name="button_text"
                                       class="form-control @error('button_text') is-invalid @enderror"
                                       value="{{ old('button_text') }}" placeholder="Ví dụ: Khám Phá Hàng Mới">
                                @error('button_text')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="button_link" class="form-label fw-bold">Đường dẫn khi bấm nút</label>
                                <input type="text" id="button_link" name="button_link"
                                       class="form-control @error('button_link') is-invalid @enderror"
                                       value="{{ old('button_link') }}" placeholder="/san-pham">
                                @error('button_link')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label fw-bold">Ảnh banner <span class="text-danger">*</span></label>
                            <input type="file" id="image" name="image"
                                   class="form-control @error('image') is-invalid @enderror" accept="image/*" required>
                            <div class="form-text">Định dạng hỗ trợ: jpg, png, webp. Dung lượng tối đa: 2MB. Nên dùng ảnh ngang, độ phân giải lớn (vd 1920x1080).</div>
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
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active') ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="is_active">Bật hiển thị trên trang chủ</label>
                            </div>
                            <div class="form-text">Có thể tạo sẵn nhiều banner rồi bật/tắt bất cứ lúc nào mà không cần upload lại ảnh.</div>
                        </div>

                        <div class="mb-3">
                            <label for="sort_order" class="form-label fw-bold">Thứ tự hiển thị</label>
                            <input type="number" id="sort_order" name="sort_order" min="0"
                                   class="form-control @error('sort_order') is-invalid @enderror"
                                   value="{{ old('sort_order', 0) }}">
                            <div class="form-text">Số nhỏ hơn hiển thị trước trong slider.</div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="start_date" class="form-label fw-bold">Bắt đầu hiển thị</label>
                            <input type="datetime-local" id="start_date" name="start_date"
                                   class="form-control @error('start_date') is-invalid @enderror"
                                   value="{{ old('start_date') }}">
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="end_date" class="form-label fw-bold">Kết thúc hiển thị</label>
                            <input type="datetime-local" id="end_date" name="end_date"
                                   class="form-control @error('end_date') is-invalid @enderror"
                                   value="{{ old('end_date') }}">
                            <div class="form-text">Để trống nếu banner hiển thị vô thời hạn (đến khi tự tắt).</div>
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
