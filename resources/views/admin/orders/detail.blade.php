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
                @php
                    $orderBadgeCss = [
                        'pending'    => 'order-badge--pending',
                        'processing' => 'order-badge--processing',
                        'shipping'   => 'order-badge--shipping',
                        'completed'  => 'order-badge--completed',
                        'cancelled'  => 'order-badge--cancelled',
                    ];
                @endphp
                <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }}">
                    {{ $statusLabels[$order->status] ?? $order->status }}
                </span>
                <a href="{{ route('admin.orders.list') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
                </a>
                @if($order->status === 'pending')
                    <button type="button" class="btn btn-outline-danger btn-sm fw-semibold"
                        data-delete-url="{{ route('admin.orders.destroy', $order->id) }}"
                        data-delete-name="{{ $order->order_code ?? '#'.$order->id }}"
                        data-delete-type="đơn hàng">
                        <i class="fa-regular fa-trash-can me-1"></i> Xóa đơn hàng
                    </button>
                @endif
            </div>
        </div>

        {{-- Nội dung chi tiết (chỉ xem) — dùng chung với popup ở trang danh sách --}}
        @include('admin.orders.partials.detail-content')

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

                    @php
                        $isOnlineGateway = $order->paymentMethod?->isOnlineGateway() ?? false;
                        $blockedByPayment = $isOnlineGateway && $order->payment_status !== 'paid';
                        $allowedStatuses = \App\Http\Controllers\Admin\OrderController::allowedStatusOptions($order->status, $blockedByPayment);
                        $canChangeStatus = count($allowedStatuses) > 1;
                    @endphp
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Trạng thái đơn hàng <span class="text-danger">*</span></label>
                            @if($canChangeStatus)
                                <select id="status" name="status"
                                    class="form-select @error('status') is-invalid @enderror">
                                    @foreach($allowedStatuses as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('status', $order->status) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                @if($blockedByPayment)
                                    <div class="form-text text-warning" style="font-size:12px;">
                                        Đơn thanh toán online chưa được xác nhận thanh toán — chỉ có thể hủy, không thể xử lý tiếp.
                                    </div>
                                @endif
                            @else
                                <input type="hidden" name="status" value="{{ $order->status }}">
                                <div class="form-control-plaintext" style="font-size:13px;">
                                    {{ $statusLabels[$order->status] ?? $order->status }}
                                    <span class="text-muted" style="font-size:12px;">(trạng thái cuối, không thể thay đổi)</span>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label for="payment_status" class="form-label">Trạng thái thanh toán <span class="text-danger">*</span></label>
                            @if($isOnlineGateway)
                                <input type="hidden" name="payment_status" value="{{ $order->payment_status }}">
                                <div class="form-control-plaintext" style="font-size:13px;">
                                    {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
                                    <span class="text-muted" style="font-size:12px;">(đồng bộ tự động từ cổng thanh toán online, không thể sửa tay)</span>
                                </div>
                            @else
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
                            @endif
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

    @push('modals')
        @include('layouts.components.confirm.delete')
    @endpush
@endsection
