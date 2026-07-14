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
                    <th style="width: 44px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:160px;">
                        Mã đơn hàng
                    </th>
                    <th style="width:230px;">Khách hàng</th>
                    <th style="width:110px;">Số điện thoại</th>
                    <th style="width:110px;">
                        <button type="button" class="product-sort-btn {{ $isActive('total') }}" data-sort-key="total" data-sort-type="number">
                            Tổng tiền <span class="product-sort-icon">{{ $sortIcon('total') }}</span>
                        </button>
                    </th>
                    <th style="width:90px;">
                        <button type="button" class="product-sort-btn {{ $isActive('fee') }}" data-sort-key="fee" data-sort-type="number">
                            Phí ship <span class="product-sort-icon">{{ $sortIcon('fee') }}</span>
                        </button>
                    </th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn {{ $isActive('status') }}" data-sort-key="status">
                            Trạng thái đơn <span class="product-sort-icon">{{ $sortIcon('status') }}</span>
                        </button>
                    </th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn {{ $isActive('payment') }}" data-sort-key="payment">
                            Thanh toán <span class="product-sort-icon">{{ $sortIcon('payment') }}</span>
                        </button>
                    </th>
                    <th style="width:110px;">
                        <button type="button" class="product-sort-btn {{ $isActive('created_at') }}" data-sort-key="created_at">
                            Ngày đặt <span class="product-sort-icon">{{ $sortIcon('created_at') }}</span>
                        </button>
                    </th>
                    <th class="text-center" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $orderPhone = ($order->phone && $order->phone !== '0') ? $order->phone : null;
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $order->id }}">
                        </td>
                        <td data-sort-value="{{ $order->order_code ?? '' }}">
                            <span class="order-code">{{ $order->order_code ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                            @if($order->user?->email)
                                <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                            @endif
                        </td>
                        <td style="color:#64748B;font-size:13px;" data-sort-value="{{ $orderPhone ?? '' }}">
                            @if($orderPhone)
                                {{ $orderPhone }}
                            @else
                                <span class="text-muted fst-italic">Chưa cập nhật</span>
                            @endif
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
                            <span class="payment-badge {{ $order->payment_status === 'paid' ? 'payment-badge--paid' : 'payment-badge--unpaid' }}">
                                {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
                            </span>
                        </td>
                        <td style="color:#64748B;font-size:13px;" data-sort-value="{{ $order->created_at?->timestamp }}">
                            {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('admin.orders.detail', $order->id) }}" class="order-row-action-btn"
                                    data-order-detail="{{ $order->id }}" title="Xem chi tiết">
                                    <i class="fa-regular fa-eye"></i>
                                </a>
                                @if(\App\Http\Controllers\Admin\OrderController::canOpenEditPanel($order))
                                    <button type="button" class="order-row-action-btn"
                                        data-order-edit-trigger
                                        data-edit-url="{{ route('admin.orders.editContent', $order->id) }}"
                                        title="Sửa đơn hàng">
                                        <i class="fa-solid fa-pen-clip"></i>
                                    </button>
                                @endif
                                @if($order->status === 'pending')
                                    <button type="button" class="order-row-action-btn text-danger"
                                        data-delete-url="{{ route('admin.orders.destroy', $order->id) }}"
                                        data-delete-name="{{ $order->order_code ?? '#'.$order->id }}"
                                        data-delete-type="đơn hàng"
                                        title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="10" class="text-center py-5">
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
        'bulkCustomActionsUrl'   => route('admin.orders.bulkUpdateStatus'),
        'bulkCustomActionsField' => 'status',
        'bulkCustomActions' => [
            ['value' => 'processing', 'label' => 'Duyệt hàng loạt', 'class' => 'hk-pg-sel-status--active'],
            ['value' => 'cancelled',  'label' => 'Hủy hàng loạt',   'class' => 'hk-pg-sel-status--inactive'],
        ],
    ])
</div>
