@php
    $currentSort = $sort ?? 'created_at';
    $currentDir  = $direction ?? 'desc';

    $sortIcon = function (string $key) use ($currentSort, $currentDir): string {
        if ($currentSort !== $key) return '↑↓';
        return $currentDir === 'asc' ? '↑' : '↓';
    };

    $isActive = fn (string $key) => $currentSort === $key ? 'is-active' : '';

    $orderBadgeCss = [
        'pending'    => 'order-badge--pending',
        'processing' => 'order-badge--processing',
        'shipping'   => 'order-badge--shipping',
        'completed'  => 'order-badge--completed',
        'cancelled'  => 'order-badge--cancelled',
    ];
@endphp

<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="orderTable">
            <thead>
                <tr>
                    <th style="width:160px;">
                        Mã đơn hàng
                    </th>
                    <th>Khách hàng</th>
                    <th style="width:130px;">Số điện thoại</th>
                    <th style="width:145px;">
                        <button type="button" class="product-sort-btn {{ $isActive('total') }}" data-sort-key="total" data-sort-type="number">
                            Tổng tiền <span class="product-sort-icon">{{ $sortIcon('total') }}</span>
                        </button>
                    </th>
                    <th style="width:115px;">
                        <button type="button" class="product-sort-btn {{ $isActive('fee') }}" data-sort-key="fee" data-sort-type="number">
                            Phí ship <span class="product-sort-icon">{{ $sortIcon('fee') }}</span>
                        </button>
                    </th>
                    <th style="width:155px;">
                        <button type="button" class="product-sort-btn {{ $isActive('status') }}" data-sort-key="status">
                            Trạng thái đơn <span class="product-sort-icon">{{ $sortIcon('status') }}</span>
                        </button>
                    </th>
                    <th style="width:155px;">
                        <button type="button" class="product-sort-btn {{ $isActive('payment') }}" data-sort-key="payment">
                            Thanh toán <span class="product-sort-icon">{{ $sortIcon('payment') }}</span>
                        </button>
                    </th>
                    <th style="width:130px;">
                        <button type="button" class="product-sort-btn {{ $isActive('created_at') }}" data-sort-key="created_at">
                            Ngày đặt <span class="product-sort-icon">{{ $sortIcon('created_at') }}</span>
                        </button>
                    </th>
                    <th class="text-center" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td data-sort-value="{{ $order->order_code ?? '' }}">
                            <span class="order-code">{{ $order->order_code ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                            @if($order->user?->email)
                                <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                            @endif
                        </td>
                        <td style="color:#64748B;font-size:13px;" data-sort-value="{{ $order->phone ?? '' }}">
                            {{ $order->phone ?? '—' }}
                        </td>
                        <td data-sort-value="{{ $order->total_money }}">
                            <span class="fw-bold" style="color:#0F172A;">
                                {{ number_format($order->total_money, 0, ',', '.') }}₫
                            </span>
                        </td>
                        <td style="color:#64748B;font-size:13px;" data-sort-value="{{ $order->shipping_fee }}">
                            @if($order->shipping_fee > 0)
                                {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                            @else
                                <span class="text-muted">Miễn phí</span>
                            @endif
                        </td>
                        <td data-sort-value="{{ $order->status }}">
                            <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }}">
                                {{ $statusLabels[$order->status] ?? $order->status }}
                            </span>
                        </td>
                        <td data-sort-value="{{ $order->payment_status }}">
                            @if($order->payment_status === 'paid')
                                <span class="payment-badge payment-badge--paid">Đã thanh toán</span>
                            @else
                                <span class="payment-badge payment-badge--unpaid">Chưa thanh toán</span>
                            @endif
                        </td>
                        <td style="color:#64748B;font-size:13px;" data-sort-value="{{ $order->created_at?->timestamp }}">
                            {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <a href="{{ route('admin.orders.detail', $order->id) }}" class="dropdown-item">
                                        <i class="fa-regular fa-eye"></i> Xem chi tiết
                                    </a>
                                    <button type="button" class="dropdown-item"
                                        data-update-order="{{ $order->id }}"
                                        data-status="{{ $order->status }}"
                                        data-payment="{{ $order->payment_status }}"
                                        data-code="{{ $order->order_code ?? '#'.$order->id }}">
                                        <i class="fa-regular fa-pen-to-square"></i> Cập nhật
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="9" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có đơn hàng nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator' => $orders,
        'itemLabel' => 'đơn hàng',
    ])
</div>
