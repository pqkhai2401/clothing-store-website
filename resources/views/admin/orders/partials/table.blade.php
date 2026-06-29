<div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="orderTable">
                        <thead>
                            <tr>
                                <th style="width:150px;">Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th style="width:120px;">Số điện thoại</th>
                                <th style="width:140px;">Tổng tiền</th>
                                <th style="width:110px;">Phí ship</th>
                                <th style="width:150px;">Trạng thái đơn</th>
                                <th style="width:150px;">Thanh toán</th>
                                <th style="width:120px;">Ngày đặt</th>
                                <th class="text-center" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $orderBadgeCss = [
                                    'pending'   => 'order-badge--pending',
                                    'confirmed' => 'order-badge--confirmed',
                                    'shipping'  => 'order-badge--shipping',
                                    'delivered' => 'order-badge--delivered',
                                    'cancelled' => 'order-badge--cancelled',
                                ];
                            @endphp
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <span class="order-code">{{ $order->order_code ?? '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                                        @if($order->user?->email)
                                            <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                                        @endif
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">{{ $order->phone ?? '—' }}</td>
                                    <td>
                                        <span class="fw-bold" style="color:#0F172A;">
                                            {{ number_format($order->total_money, 0, ',', '.') }}₫
                                        </span>
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">
                                        @if($order->shipping_fee > 0)
                                            {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                                        @else
                                            <span class="text-muted">Miễn phí</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="order-badge {{ $orderBadgeCss[$order->status] ?? '' }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="payment-badge payment-badge--paid">Đã thanh toán</span>
                                        @else
                                            <span class="payment-badge payment-badge--unpaid">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                    <td style="color:#64748B;font-size:13px;">
                                        {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.orders.show', $order->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-eye"></i> Xem chi tiết
                                                </a>
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