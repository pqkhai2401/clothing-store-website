@extends('layouts.app')

@section('title', 'Đơn hàng ' . $order->order_code . ' | NOIR')

@section('css')
<style>
    .order-detail-header {
        padding: 48px 0 32px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 40px;
    }

    .order-detail-header .page-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 3px;
        color: var(--muted-text);
        margin-bottom: 10px;
    }

    .order-detail-header h1 {
        font-family: var(--font-serif);
        font-size: clamp(22px, 3.5vw, 34px);
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin: 0 0 12px;
        line-height: 1.2;
    }

    .order-status-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        padding: 5px 14px;
    }

    .status--pending    { background: #fef9c3; color: #92400e; }
    .status--processing { background: #dbeafe; color: #1e40af; }
    .status--shipping   { background: #e0e7ff; color: #3730a3; }
    .status--completed  { background: #dcfce7; color: #166534; }
    .status--cancelled  { background: #fee2e2; color: #991b1b; }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: var(--muted-text);
        text-decoration: none;
        transition: color 0.2s;
    }

    .btn-back:hover { color: var(--primary-color); }

    /* ── Info blocks ── */
    .info-block {
        border: 1px solid var(--border-color);
        background: #fff;
        margin-bottom: 20px;
    }

    .info-block-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted-text);
        padding: 14px 20px;
        border-bottom: 1px solid var(--border-color);
        background: #fafafa;
    }

    .info-block-body {
        padding: 18px 20px;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 8px 0;
        font-size: 13px;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child { border-bottom: none; }

    .info-row-label { color: var(--muted-text); flex-shrink: 0; }
    .info-row-value { font-weight: 600; text-align: right; }

    /* ── Product table ── */
    .order-items-block {
        border: 1px solid var(--border-color);
        background: #fff;
        margin-bottom: 20px;
    }

    .order-items-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted-text);
        padding: 14px 20px;
        border-bottom: 1px solid var(--border-color);
        background: #fafafa;
    }

    .order-item-row {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .order-item-row:last-child { border-bottom: none; }

    .order-item-img {
        width: 64px;
        height: 84px;
        object-fit: cover;
        background: var(--hover-bg);
        flex-shrink: 0;
    }

    .order-item-info { flex: 1; min-width: 0; }

    .order-item-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 4px;
        line-height: 1.4;
        text-decoration: none;
    }

    .order-item-name:hover { text-decoration: underline; }

    .order-item-meta {
        font-size: 11px;
        color: var(--muted-text);
        margin-bottom: 3px;
    }

    .order-item-qty {
        display: inline-block;
        font-size: 11px;
        background: #f3f4f6;
        padding: 2px 10px;
        border-radius: 20px;
        color: #374151;
        margin-top: 4px;
    }

    .order-item-price {
        text-align: right;
        flex-shrink: 0;
    }

    .order-item-price .unit { font-size: 11px; color: var(--muted-text); }
    .order-item-price .total { font-size: 14px; font-weight: 700; }

    /* ── Summary ── */
    .order-summary-block {
        border: 1px solid var(--border-color);
        background: #fff;
    }

    .order-summary-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--muted-text);
        padding: 14px 20px;
        border-bottom: 1px solid var(--border-color);
        background: #fafafa;
    }

    .order-summary-body { padding: 6px 20px 20px; }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        font-size: 13px;
        border-bottom: 1px dashed var(--border-color);
    }

    .summary-row:last-of-type { border-bottom: none; }
    .summary-row-label { color: var(--muted-text); }
    .summary-row-value { font-weight: 600; }

    .summary-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0 0;
        border-top: 2px solid var(--primary-color);
        margin-top: 4px;
    }

    .summary-total-label { font-size: 14px; font-weight: 700; }
    .summary-total-value { font-size: 20px; font-weight: 700; color: var(--primary-color); }

    /* ── Payment badge ── */
    .payment-status-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 3px 10px;
    }

    .pay--paid   { background: #dcfce7; color: #166534; }
    .pay--unpaid { background: #fef9c3; color: #92400e; }
</style>
@endsection

@section('content')
<div class="container-fluid px-lg-5">

    <div class="order-detail-header">
        <a href="{{ route('orders.index') }}" class="btn-back mb-3 d-inline-flex">
            <i class="bi bi-arrow-left"></i> Đơn hàng của tôi
        </a>
        <div class="page-label">Chi tiết đơn hàng</div>
        <h1>{{ $order->order_code }}</h1>
        @php
            $statusMap = [
                'pending'    => ['label' => 'Chờ xác nhận', 'class' => 'status--pending'],
                'processing' => ['label' => 'Đang xử lý',   'class' => 'status--processing'],
                'shipping'   => ['label' => 'Đang giao',    'class' => 'status--shipping'],
                'completed'  => ['label' => 'Hoàn thành',   'class' => 'status--completed'],
                'cancelled'  => ['label' => 'Đã huỷ',       'class' => 'status--cancelled'],
            ];
            $st = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => ''];
        @endphp
        <span class="order-status-badge {{ $st['class'] }}">{{ $st['label'] }}</span>
    </div>

    <div class="row g-4">

        {{-- LEFT: Items + Summary --}}
        <div class="col-lg-8">

            {{-- Product list --}}
            <div class="order-items-block">
                <div class="order-items-title">
                    Sản phẩm &nbsp;({{ $order->orderItems->count() }} sản phẩm)
                </div>

                @foreach($order->orderItems as $item)
                    @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;
                        $image   = $variant?->image ?: $product?->thumbnail;
                        $slug    = $product?->slug;
                    @endphp
                    <div class="order-item-row">
                        @if($image)
                            <img src="{{ asset($image) }}" alt="{{ $product?->name }}" class="order-item-img">
                        @else
                            <div class="order-item-img d-flex align-items-center justify-content-center" style="background:#f3f4f6;">
                                <i class="bi bi-image" style="font-size:22px;color:#d1d5db;"></i>
                            </div>
                        @endif

                        <div class="order-item-info">
                            @if($slug)
                                <a href="{{ route('products.show', $slug) }}" class="order-item-name">{{ $product->name }}</a>
                            @else
                                <div class="order-item-name" style="cursor:default;">{{ $product?->name ?? 'Sản phẩm đã xoá' }}</div>
                            @endif
                            <div class="order-item-meta">
                                @if($variant?->color) {{ $variant->color->name }} @endif
                                @if($variant?->color && $variant?->size) · @endif
                                @if($variant?->size) {{ $variant->size->name }} @endif
                            </div>
                            <span class="order-item-qty">x{{ $item->quantity }}</span>
                        </div>

                        <div class="order-item-price">
                            <div class="unit">{{ number_format($item->unit_price, 0, ',', '.') }}đ / cái</div>
                            <div class="total">{{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }}đ</div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Payment summary --}}
            <div class="order-summary-block">
                <div class="order-summary-title">Chi tiết thanh toán</div>
                <div class="order-summary-body">
                    @php $subtotal = $order->orderItems->sum(fn($i) => $i->unit_price * $i->quantity); @endphp
                    <div class="summary-row">
                        <span class="summary-row-label">Tạm tính</span>
                        <span class="summary-row-value">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-row-label">Phí giao hàng</span>
                        <span class="summary-row-value">
                            @if($order->shipping_fee > 0)
                                {{ number_format($order->shipping_fee, 0, ',', '.') }}đ
                            @else
                                <span style="color:#16a34a; font-weight:700;">Miễn phí</span>
                            @endif
                        </span>
                    </div>
                    <div class="summary-total-row">
                        <span class="summary-total-label">Tổng cộng</span>
                        <span class="summary-total-value">{{ number_format($order->total_money, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </div>

        </div>

        {{-- RIGHT: Order info --}}
        <div class="col-lg-4">

            {{-- Order info --}}
            <div class="info-block">
                <div class="info-block-title">Thông tin đơn hàng</div>
                <div class="info-block-body">
                    <div class="info-row">
                        <span class="info-row-label">Mã đơn</span>
                        <span class="info-row-value">{{ $order->order_code }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Ngày đặt</span>
                        <span class="info-row-value">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Trạng thái</span>
                        <span class="order-status-badge {{ $st['class'] }}" style="font-size:10px; padding:3px 10px;">{{ $st['label'] }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Thanh toán</span>
                        <span class="payment-status-badge {{ $order->payment_status === 'paid' ? 'pay--paid' : 'pay--unpaid' }}">
                            {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-row-label">Phương thức</span>
                        <span class="info-row-value">{{ $order->paymentMethod?->name ?? '—' }}</span>
                    </div>
                    @if($order->note)
                    <div class="info-row">
                        <span class="info-row-label">Ghi chú</span>
                        <span class="info-row-value" style="max-width:200px;">{{ $order->note }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($order->status === 'pending' && $order->payment_status === 'unpaid' && $order->paymentMethod?->isPayos())
                <a href="{{ route('checkout.payos.show', $order->id) }}"
                   style="display:flex; align-items:center; justify-content:center; gap:8px;
                          width:100%; height:46px; margin-bottom:20px; border-radius:8px;
                          background:#0d6efd; color:#fff; font-size:14px; font-weight:700;
                          text-decoration:none; transition:background .2s;"
                   onmouseover="this.style.background='#0b5ed7'" onmouseout="this.style.background='#0d6efd'">
                    <i class="bi bi-qr-code"></i> Tiếp tục thanh toán
                </a>
            @endif

            {{-- Shipping address --}}
            <div class="info-block">
                <div class="info-block-title">Địa chỉ giao hàng</div>
                <div class="info-block-body" style="font-size:13px; line-height:1.7;">
                    <div style="font-weight:600; margin-bottom:4px;">{{ auth()->user()->username }}</div>
                    <div style="color:var(--muted-text);">{{ $order->phone }}</div>
                    @if($order->address)
                        <div style="margin-top:8px;">
                            {{ collect([
                                $order->address->apartment_number,
                                $order->address->ward,
                                $order->address->district,
                                $order->address->city,
                            ])->filter()->join(', ') }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
