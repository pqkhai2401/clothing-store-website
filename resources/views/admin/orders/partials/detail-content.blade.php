@php
    $orderBadgeCss = [
        'pending'    => 'order-badge--pending',
        'processing' => 'order-badge--processing',
        'shipping'   => 'order-badge--shipping',
        'completed'  => 'order-badge--completed',
        'cancelled'  => 'order-badge--cancelled',
    ];
    // Đơn ĐÃ THU TIỀN nhưng bị hủy → còn nợ khách. Hệ thống không tự hoàn tiền, nên phải
    // chỉ rõ cho admin "hoàn cho ai, vào đâu" ngay tại đây.
    $needsRefund = $order->status === 'cancelled' && $order->payment_status === 'paid';
    $isRefunded  = $order->payment_status === 'refunded';
    $gatewayRef  = $order->payos_order_code ?: $order->momo_order_id;
@endphp

@php $cancelReq = $order->pendingCancelRequest; @endphp
@if($cancelReq)
    {{-- Khách xin hủy đơn đã xác nhận/đang giao. Duyệt = hủy đơn + hoàn kho + (nếu đã trả tiền)
         bật cờ "Cần hoàn tiền". --}}
    <div class="card border shadow-sm mb-4 ord-cancelreq-card">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
            <i class="fa-regular fa-hand text-warning"></i>
            <span class="section-title">Khách yêu cầu hủy đơn</span>
        </div>
        <div class="card-body">
            <div style="font-size:13px;">
                <div class="mb-1"><span class="text-muted">Lý do khách đưa ra:</span></div>
                <div class="ord-cancelreq-reason">{{ $cancelReq->reason }}</div>
                <div class="mt-2 text-muted" style="font-size:12px;">
                    Gửi lúc {{ $cancelReq->created_at->format('H:i d/m/Y') }}
                    @if($order->payment_status === 'paid')
                        · <b class="text-danger">Đơn đã thanh toán {{ number_format($order->total_money, 0, ',', '.') }}₫ — duyệt xong sẽ cần hoàn tiền.</b>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap mt-3">
                <input type="text" class="form-control form-control-sm" style="max-width:300px;font-size:13px;"
                    data-cancelreq-note placeholder="Ghi chú cho khách (không bắt buộc)">
                <button type="button" class="btn btn-danger btn-sm fw-semibold"
                    data-cancelreq-action="approve"
                    data-cancelreq-url="{{ route('admin.orders.processCancelRequest', $cancelReq->id) }}">
                    <i class="fa-solid fa-check me-1"></i> Duyệt hủy đơn
                </button>
                <button type="button" class="btn btn-light border btn-sm fw-semibold"
                    data-cancelreq-action="reject"
                    data-cancelreq-url="{{ route('admin.orders.processCancelRequest', $cancelReq->id) }}">
                    <i class="fa-solid fa-xmark me-1"></i> Từ chối
                </button>
            </div>
        </div>
    </div>
@endif

@if($needsRefund)
    <div class="card border shadow-sm mb-4 ord-refund-card">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-danger"></i>
            <span class="section-title">Cần hoàn tiền cho khách</span>
        </div>
        <div class="card-body">
            <div class="mb-3" style="font-size:13px;">
                Đơn đã thu <b class="text-danger">{{ number_format($order->total_money, 0, ',', '.') }}₫</b>
                nhưng đã bị hủy. Hệ thống <b>không tự chuyển tiền</b> — cần hoàn thủ công.
            </div>

            @if($gatewayRef)
                <div class="mb-2" style="font-size:13px;">
                    Mã giao dịch {{ $order->payos_order_code ? 'PayOS' : 'MoMo' }}:
                    <b style="font-family:monospace;">{{ $gatewayRef }}</b>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm fw-semibold"
                    data-refund-lookup="{{ route('admin.orders.refundInfo', $order->id) }}">
                    <i class="fa-solid fa-magnifying-glass-dollar me-1"></i> Tra cứu người đã thanh toán
                </button>
            @else
                <div class="ord-refund-note mb-3">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Đơn này <b>không có giao dịch qua cổng</b> (đánh dấu đã thanh toán thủ công hoặc thu tiền mặt)
                    → không tra cứu tự động được. Hãy liên hệ khách để lấy thông tin nhận tiền.
                </div>
            @endif

            @php
                $refundPhone = collect([$order->phone, $order->user?->phone_number])->first(fn ($p) => $p && $p !== '0');
            @endphp
            <div class="mt-3" style="font-size:13px;">
                <span class="text-muted">Liên hệ khách:</span>
                <b>{{ $order->user?->username ?? 'Khách vãng lai' }}</b>
                @if($refundPhone)
                    · <a href="tel:{{ $refundPhone }}">{{ $refundPhone }}</a>
                @endif
                @if($order->user?->email)
                    · <a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a>
                @endif
            </div>

            <div data-refund-result class="mt-3" style="display:none;"></div>

            <hr class="my-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div>
                    <label class="text-muted d-block mb-1" style="font-size:12px;">Số tiền hoàn</label>
                    <input type="number" step="1000" min="1" max="{{ (int) $order->total_money }}" class="form-control form-control-sm" style="max-width:180px;font-size:13px;"
                        data-refund-amount value="{{ (int) $order->total_money }}">
                </div>
                <div class="flex-grow-1" style="max-width:320px;">
                    <label class="text-muted d-block mb-1" style="font-size:12px;">Ghi chú (không bắt buộc)</label>
                    <input type="text" class="form-control form-control-sm" style="font-size:13px;"
                        data-refund-note placeholder="Hoàn qua CK Vietcombank, lúc 10:30...">
                </div>
                <button type="button" class="btn btn-success btn-sm fw-semibold align-self-end"
                    data-refund-done="{{ route('admin.orders.markRefunded', $order->id) }}">
                    <i class="fa-solid fa-check me-1"></i> Đánh dấu đã hoàn tiền
                </button>
            </div>
            <div class="text-muted mt-2" style="font-size:12px;">Mặc định hoàn đủ tổng tiền đơn — chỉnh lại nếu hoàn một phần (ví dụ trừ phí cổng thanh toán).</div>
        </div>
    </div>
@endif

@if($isRefunded)
    @php
        $refundAmount = $order->refund_amount ?? $order->total_money;
        $isFullRefund = (float) $refundAmount === (float) $order->total_money;
    @endphp
    <div class="card border shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-check text-success"></i>
            <span class="section-title">Đã hoàn tiền</span>
            <span class="badge {{ $isFullRefund ? 'bg-success' : 'bg-warning text-dark' }} ms-auto">
                {{ $isFullRefund ? 'Hoàn toàn bộ' : 'Hoàn một phần' }}
            </span>
        </div>
        <div class="card-body" style="font-size:13px;">
            Đã hoàn <b class="text-success">{{ number_format($refundAmount, 0, ',', '.') }}₫</b>
            @if(! $isFullRefund)
                <span class="text-muted">(trên tổng tiền đơn {{ number_format($order->total_money, 0, ',', '.') }}₫)</span>
            @endif
            @if($order->refunded_at)
                lúc {{ $order->refunded_at->format('H:i d/m/Y') }}
            @endif
        </div>
    </div>
@endif

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
                        <td class="info-label ps-0">Nguồn đơn</td>
                        <td class="info-value">
                            @if($order->source === 'admin')
                                <span class="order-badge order-badge--manual">Tạo thủ công</span>
                                @if($order->createdBy)
                                    <span class="text-muted" style="font-size:12px;">bởi {{ $order->createdBy->username }}</span>
                                @endif
                            @else
                                Khách đặt trên website
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="info-label ps-0">Trạng thái</td>
                        <td>
                            <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
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
                    @php
                        $displayPhone = collect([$order->phone, $order->user?->phone_number])
                            ->first(fn ($p) => $p && $p !== '0');
                    @endphp
                    <tr>
                        <td class="info-label ps-0">Số điện thoại</td>
                        <td class="info-value">{{ $displayPhone ?? 'Chưa cập nhật' }}</td>
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
                                {{-- M4: tên/màu/size theo snapshot lúc đặt hàng, không đổi khi sửa catalog --}}
                                <div class="fw-semibold">{{ $item->displayName() }}</div>
                            </td>
                            <td class="text-muted">{{ $item->displayColor() ?? '—' }}</td>
                            <td class="text-muted">{{ $item->displaySize() ?? '—' }}</td>
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
