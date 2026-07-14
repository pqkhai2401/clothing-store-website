<div class="product-trash-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 product-trash-table">
            <thead>
                <tr>
                    <th style="width: 54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:160px;">Mã đơn hàng</th>
                    <th>Khách hàng</th>
                    <th style="width:110px;">Số điện thoại</th>
                    <th style="width:120px;">Tổng tiền</th>
                    <th style="width:150px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width: 90px;">Thao tác</th>
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
                        <td>
                            <span class="order-code">{{ $order->order_code ?? '—' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                            @if($order->user?->email)
                                <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                            @endif
                        </td>
                        <td style="color:#64748B;font-size:13px;">
                            {{ $orderPhone ?? '—' }}
                        </td>
                        <td class="fw-bold" style="color:#0F172A;">
                            {{ number_format($order->total_money, 0, ',', '.') }}₫
                        </td>
                        <td class="product-trash-date">{{ $order->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <form method="POST" action="{{ route('admin.orders.restore', $order->id) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="product-more-btn" title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <button type="button" class="product-more-btn text-danger"
                                    data-delete-url="{{ route('admin.orders.forceDelete', $order->id) }}"
                                    data-delete-name="{{ $order->order_code ?? '#'.$order->id }}"
                                    data-delete-type="đơn hàng (vĩnh viễn)"
                                    title="Xóa vĩnh viễn">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác đơn hàng đang trống</div>
                            <div class="text-muted small mt-1">Các đơn hàng bị xóa mềm sẽ xuất hiện tại đây.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
        @include('layouts.components.pagination', [
            'paginator'       => $orders,
            'itemLabel'       => 'đơn hàng',
            'bulkRestoreUrl'  => route('admin.orders.bulkRestore'),
            'bulkDeleteUrl'   => route('admin.orders.bulkForceDelete'),
            'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
        ])
    </div>
</div>
