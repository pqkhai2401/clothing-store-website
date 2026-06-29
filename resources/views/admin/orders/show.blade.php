@extends('layouts.admin')

@section('title', 'Chi tiết đơn hàng #' . $order->id)

@push('styles')
    @include('admin.orders.styles')
@endpush

@section('content')
    <main class="app-main container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
            <div>
                <h1 class="h4 fw-bold mb-0" style="color:#174761;">
                    Chi tiết đơn hàng
                    @if($order->order_code)
                        <span class="ms-2" style="font-family:monospace;font-size:16px;">{{ $order->order_code }}</span>
                    @endif
                </h1>
            </div>
            <div class="d-flex align-items-center gap-2">
                @php $badgeClass = $statusBadge[$order->status] ?? 'text-bg-secondary'; @endphp
                <span class="badge status-badge {{ $badgeClass }}">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
                <a href="{{ route('admin.orders.list') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row g-4">
            {{-- Thông tin đơn hàng --}}
            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <span class="section-title">Thông tin đơn hàng</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0" style="font-size:13px;">
                            <tr>
                                <td class="info-label ps-0">ID</td>
                                <td class="info-value" style="opacity:.45;">{{ $order->id }}</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Mã đơn</td>
                                <td class="info-value" style="font-family:monospace;">{{ $order->order_code ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Ngày đặt</td>
                                <td class="info-value">{{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Trạng thái</td>
                                <td><span class="badge status-badge {{ $badgeClass }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Thanh toán</td>
                                <td class="info-value">
                                    {{ $order->paymentMethod?->name ?? '—' }}
                                    —
                                    <span class="{{ $order->payment_status === 'paid' ? 'text-success fw-bold' : 'text-warning fw-bold' }}">
                                        {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Phí ship</td>
                                <td class="info-value">{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Tổng tiền</td>
                                <td class="info-value fw-bold" style="color:#174761; font-size:15px;">
                                    {{ number_format($order->total_money, 0, ',', '.') }}₫
                                </td>
                            </tr>
                            @if($order->note)
                                <tr>
                                    <td class="info-label ps-0">Ghi chú</td>
                                    <td class="info-value">{{ $order->note }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            {{-- Thông tin khách hàng --}}
            <div class="col-md-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-white border-bottom">
                        <span class="section-title">Thông tin khách hàng</span>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0" style="font-size:13px;">
                            <tr>
                                <td class="info-label ps-0">Họ tên</td>
                                <td class="info-value">{{ $order->user?->username ?? 'Khách vãng lai' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Email</td>
                                <td class="info-value">{{ $order->user?->email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="info-label ps-0">Số điện thoại</td>
                                <td class="info-value">{{ $order->phone ?? $order->user?->phone_number ?? '—' }}</td>
                            </tr>
                            @if($order->address)
                                <tr>
                                    <td class="info-label ps-0">Địa chỉ</td>
                                    <td class="info-value">
                                        {{ collect([$order->address->apartment_number, $order->address->ward, $order->address->district, $order->address->city])->filter()->join(', ') ?: '—' }}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danh sách sản phẩm --}}
        <div class="card border shadow-sm mt-4">
            <div class="card-header bg-white border-bottom">
                <span class="section-title">Sản phẩm trong đơn ({{ $order->orderItems->count() }})</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered item-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:60px;">#</th>
                                <th style="width:60px;">Ảnh</th>
                                <th>Sản phẩm</th>
                                <th style="width:100px;">Màu</th>
                                <th style="width:80px;">Size</th>
                                <th style="width:110px;">Đơn giá</th>
                                <th style="width:80px;">SL</th>
                                <th style="width:120px;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->orderItems as $index => $item)
                                @php
                                    $variant = $item->productVariant;
                                    $product = $variant?->product;
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td>
                                        @if($product?->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}" class="product-thumb" alt="">
                                        @else
                                            <div style="width:44px;height:44px;background:#f3f4f6;border-radius:4px;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                                                <i class="fa-regular fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $product?->name ?? 'Sản phẩm đã bị xóa' }}</div>
                                    </td>
                                    <td class="text-muted">{{ $variant?->color?->name ?? '—' }}</td>
                                    <td class="text-muted">{{ $variant?->size?->name ?? '—' }}</td>
                                    <td class="fw-semibold">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}₫</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="fw-bold" style="color:#174761;">
                                        {{ number_format(($item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}₫
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Không có sản phẩm nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="7" class="text-end fw-bold pe-3">Tổng cộng:</td>
                                <td class="fw-bold" style="color:#174761;font-size:15px;">
                                    {{ number_format($order->total_money, 0, ',', '.') }}₫
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        {{-- Form cập nhật trạng thái đơn hàng --}}
        <div class="card border shadow-sm mt-4 update-card">
            <div class="card-header bg-white border-bottom">
                <span class="section-title">Cập nhật đơn hàng</span>
            </div>
            <div class="card-body p-4">
                <x-notification />
                <form method="POST" action="{{ route('admin.orders.update', $order->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Trạng thái đơn hàng <span class="text-danger">*</span></label>
                            <select id="status" name="status"
                                class="form-select @error('status') is-invalid @enderror">
                                @foreach($statusLabels as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('status', $order->status) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="payment_status" class="form-label">Trạng thái thanh toán <span class="text-danger">*</span></label>
                            <select id="payment_status" name="payment_status"
                                class="form-select @error('payment_status') is-invalid @enderror">
                                @foreach($paymentStatusLabels as $key => $label)
                                    <option value="{{ $key }}"
                                        {{ old('payment_status', $order->payment_status) === $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="note" class="form-label">Ghi chú</label>
                            <input type="text" id="note" name="note"
                                class="form-control @error('note') is-invalid @enderror"
                                value="{{ old('note', $order->note) }}"
                                placeholder="Ghi chú nội bộ (tuỳ chọn)">
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary fw-bold" style="font-size:13px;">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Lưu thay đổi
                        </button>
                        <a href="{{ route('admin.orders.list') }}" class="btn btn-light border fw-semibold" style="font-size:13px;">
                            <i class="fa-solid fa-arrow-left me-1"></i> Danh sách đơn hàng
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection
