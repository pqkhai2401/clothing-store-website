@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('css')
    <style>
        .mgmt-table thead th {
            background: #ffffff; color: #111827;
            font-size: 12px; font-weight: 800; white-space: nowrap;
        }
        .mgmt-table tbody td { color: #374151; font-size: 13px; }
        .mgmt-table tbody tr:nth-child(odd) { background: #f3f3f3; }
        .status-badge {
            border-radius: 2px; font-size: 10px; font-weight: 800;
            line-height: 1; padding: 4px 7px;
        }
        .payment-badge {
            border-radius: 2px; font-size: 10px; font-weight: 700;
            line-height: 1; padding: 3px 6px; border: 1px solid;
        }
        .payment-paid   { color: #166534; border-color: #bbf7d0; background: #f0fdf4; }
        .payment-unpaid { color: #92400e; border-color: #fde68a; background: #fffbeb; }
        .order-code { font-family: monospace; font-size: 12px; font-weight: 700; color: #374151; }
    </style>
@endsection

@section('content')
    <main class="app-main container-fluid py-4">
        <x-notification />

        <div class="d-flex align-items-start justify-content-between gap-3 mb-4 flex-wrap">
            <h1 class="h4 fw-bold mb-0" style="color:#174761;">Quản lý đơn hàng</h1>
            <div class="small text-muted">Trang chủ <span class="mx-1">/</span> Đơn hàng</div>
        </div>

        <div class="card border shadow-sm">
            <div class="card-header bg-white border-bottom">
                <form method="GET" action="{{ route('admin.orders.list') }}">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4 col-lg-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                                <input type="text" name="keyword" class="form-control"
                                    value="{{ $keyword }}" placeholder="Mã đơn, SĐT, tên KH...">
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <select name="status" class="form-select" style="font-size:13px;">
                                <option value="">Tất cả trạng thái</option>
                                @foreach($statusLabels as $val => $label)
                                    <option value="{{ $val }}" {{ $statusFilter === $val ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-outline-primary fw-semibold">
                                <i class="fa-solid fa-filter me-1"></i> Lọc
                            </button>
                            @if($keyword || $statusFilter)
                                <a href="{{ route('admin.orders.list') }}" class="btn btn-outline-secondary fw-semibold ms-1">
                                    <i class="fa-solid fa-xmark me-1"></i> Xóa lọc
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mgmt-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width:60px;">ID</th>
                                <th style="width:140px;">Mã đơn hàng</th>
                                <th>Khách hàng</th>
                                <th style="width:90px;">SĐT</th>
                                <th style="width:130px;">Tổng tiền</th>
                                <th style="width:130px;">Trạng thái</th>
                                <th style="width:130px;">Thanh toán</th>
                                <th style="width:110px;">Ngày đặt</th>
                                <th class="text-center" style="width:80px;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td class="ps-3" style="opacity:.45;">{{ $order->id }}</td>
                                    <td><span class="order-code">{{ $order->order_code ?? '—' }}</span></td>
                                    <td>
                                        <div class="fw-semibold">{{ $order->user?->username ?? 'Khách vãng lai' }}</div>
                                        @if($order->user?->email)
                                            <div class="text-muted" style="font-size:11px;">{{ $order->user->email }}</div>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">{{ $order->phone ?? '—' }}</td>
                                    <td>
                                        <span class="fw-bold" style="color:#174761;">
                                            {{ number_format($order->total_money, 0, ',', '.') }}₫
                                        </span>
                                        @if($order->shipping_fee > 0)
                                            <div class="text-muted" style="font-size:11px;">
                                                Ship: {{ number_format($order->shipping_fee, 0, ',', '.') }}₫
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @php $badgeClass = $statusBadge[$order->status] ?? 'text-bg-secondary'; @endphp
                                        <span class="badge status-badge {{ $badgeClass }}">
                                            {{ $statusLabels[$order->status] ?? $order->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($order->payment_status === 'paid')
                                            <span class="payment-badge payment-paid">Đã thanh toán</span>
                                        @else
                                            <span class="payment-badge payment-unpaid">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                    <td class="text-muted" style="font-size:12px;">
                                        {{ $order->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.orders.show', $order->id) }}"
                                            class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
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

            <div class="card-footer bg-white">
                @include('layouts.components.pagination', ['paginator' => $orders, 'itemLabel' => 'đơn hàng'])
            </div>
        </div>
    </main>
@endsection
