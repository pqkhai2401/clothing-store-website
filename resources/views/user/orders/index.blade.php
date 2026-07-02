@extends('layouts.app')

@section('title', 'Đơn hàng của tôi | NOIR')

@section('css')
<style>
    .orders-wrap {
        max-width: 860px;
        margin: 0 auto;
        padding: 40px 16px 60px;
    }

    .orders-page-title {
        font-family: var(--font-serif);
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ── Status tabs ── */
    .status-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 24px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .status-tabs::-webkit-scrollbar { display: none; }

    .status-tab {
        flex-shrink: 0;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 500;
        color: var(--muted-text);
        text-decoration: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        white-space: nowrap;
        transition: color 0.15s, border-color 0.15s;
    }

    .status-tab:hover {
        color: var(--primary-color);
    }

    .status-tab.active {
        color: var(--primary-color);
        border-bottom-color: var(--primary-color);
        font-weight: 700;
    }

    /* ── Order card ── */
    .order-card {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
        margin-bottom: 16px;
        overflow: hidden;
    }

    .order-card-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 18px;
        background: #f8f8f8;
        border-bottom: 1px solid #e5e7eb;
        font-size: 13px;
        color: #6b7280;
    }

    .order-card-head .order-code {
        font-weight: 600;
        color: #111;
    }

    .order-card-head .order-date { flex: 1; }

    .order-status-badge {
        font-size: 11.5px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        white-space: nowrap;
    }

    .status--pending    { background: #fef9c3; color: #92400e; }
    .status--processing { background: #dbeafe; color: #1e40af; }
    .status--shipping   { background: #2563eb; color: #fff; }
    .status--completed  { background: #dcfce7; color: #166534; }
    .status--cancelled  { background: #fee2e2; color: #991b1b; }

    /* ── Items ── */
    .order-item-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-bottom: 1px solid #f0f0f0;
    }

    .order-item-row:last-of-type { border-bottom: none; }

    .order-item-img {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 4px;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .order-item-info { flex: 1; min-width: 0; }

    .order-item-name {
        font-size: 14px;
        font-weight: 600;
        color: #111;
        margin-bottom: 4px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .order-item-meta {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .order-item-qty {
        font-size: 12px;
        color: #6b7280;
    }

    .order-item-qty span { color: #2563eb; font-weight: 600; }

    .order-item-price {
        font-size: 14px;
        font-weight: 700;
        color: #e53e3e;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .order-more {
        padding: 8px 18px 12px;
        font-size: 12px;
        color: #9ca3af;
    }

    /* ── Footer ── */
    .order-card-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 18px;
        border-top: 1px solid #e5e7eb;
        background: #fafafa;
    }

    .order-total {
        font-size: 16px;
        font-weight: 700;
        color: #e53e3e;
    }

    .btn-view-order {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #d97706;
        border: 1.5px solid #d97706;
        background: transparent;
        padding: 8px 20px;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-view-order:hover {
        background: #d97706;
        color: #fff;
    }

    /* ── Empty state ── */
    .orders-empty {
        text-align: center;
        padding: 70px 20px;
        border: 1px dashed #e5e7eb;
        border-radius: 8px;
    }

    .orders-empty-icon {
        font-size: 52px;
        color: #d1d5db;
        margin-bottom: 16px;
    }

    .orders-empty-title {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }

    .orders-empty-sub {
        font-size: 13px;
        color: #9ca3af;
        margin-bottom: 24px;
    }

    .btn-shop-now {
        display: inline-block;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 11px 30px;
        background: var(--primary-color);
        color: #fff;
        text-decoration: none;
        border-radius: 4px;
        transition: background 0.2s;
    }

    .btn-shop-now:hover { background: #333; color: #fff; }

    /* ── Order detail modal ── */
    #orderDetailOverlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.5);
        z-index: 1050;
        align-items: flex-start;
        justify-content: center;
        padding: 40px 16px;
        overflow-y: auto;
    }

    #orderDetailOverlay.open { display: flex; }

    .od-modal {
        background: #fff;
        border-radius: 12px;
        width: 100%;
        max-width: 780px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.18);
        animation: odSlideIn 0.25s cubic-bezier(0.34,1.28,0.64,1) forwards;
        margin: auto;
    }

    @keyframes odSlideIn {
        from { opacity:0; transform: translateY(20px) scale(0.98); }
        to   { opacity:1; transform: translateY(0) scale(1); }
    }

    .od-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    .od-title {
        font-size: 18px;
        font-weight: 700;
        flex: 1;
    }

    .od-close {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: none;
        background: #f3f4f6;
        color: #374151;
        font-size: 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s, color 0.2s, transform 0.2s;
        flex-shrink: 0;
    }

    .od-close:hover { background: #111; color: #fff; transform: rotate(90deg); }

    .od-body { padding: 20px 24px; }

    .od-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    @media(max-width:600px) { .od-info-grid { grid-template-columns: 1fr; } }

    .od-info-block-title {
        font-size: 13px;
        font-weight: 700;
        color: #2563eb;
        margin-bottom: 10px;
    }

    .od-info-row {
        font-size: 13px;
        color: #374151;
        margin-bottom: 5px;
        line-height: 1.5;
    }

    .od-info-row b { color: #111; }

    /* Product table */
    .od-table-wrap { overflow-x: auto; margin-bottom: 16px; }

    .od-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .od-table thead tr {
        background: #f3f4f6;
    }

    .od-table th {
        padding: 10px 12px;
        font-weight: 600;
        color: #6b7280;
        text-align: left;
        font-size: 12px;
        border-bottom: 1px solid #e5e7eb;
    }

    .od-table th:last-child,
    .od-table td:last-child { text-align: right; }

    .od-table th:nth-child(3),
    .od-table td:nth-child(3) { text-align: center; }

    .od-table td {
        padding: 12px 12px;
        border-bottom: 1px solid #f0f0f0;
        vertical-align: middle;
    }

    .od-table tbody tr:last-child td { border-bottom: none; }

    .od-prod-cell { display: flex; align-items: center; gap: 10px; }

    .od-prod-img {
        width: 52px;
        height: 52px;
        object-fit: cover;
        border-radius: 4px;
        background: #f3f4f6;
        flex-shrink: 0;
    }

    .od-prod-name { font-weight: 600; color: #111; line-height: 1.4; }
    .od-prod-meta { font-size: 11px; color: #9ca3af; margin-top: 2px; }

    /* Summary */
    .od-summary {
        margin-left: auto;
        width: 280px;
        max-width: 100%;
    }

    .od-summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        padding: 7px 0;
        border-bottom: 1px dashed #e5e7eb;
        color: #6b7280;
    }

    .od-summary-row:last-of-type { border-bottom: none; }
    .od-summary-row .val { color: #111; font-weight: 500; }
    .od-summary-row .val.discount { color: #16a34a; }

    .od-summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0 0;
        border-top: 2px solid #111;
        margin-top: 4px;
    }

    .od-summary-total .lbl { font-size: 15px; font-weight: 700; }
    .od-summary-total .val { font-size: 18px; font-weight: 700; color: #111; }

    .od-footer {
        padding: 16px 24px 20px;
        border-top: 1px solid #e5e7eb;
        display: flex;
        justify-content: flex-end;
    }

    .od-btn-close {
        font-size: 13px;
        font-weight: 600;
        padding: 9px 28px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: #f9fafb;
        cursor: pointer;
        transition: background 0.2s;
    }

    .od-btn-close:hover { background: #e5e7eb; }

    .btn-cancel-order {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #dc2626;
        border: 1.5px solid #dc2626;
        background: transparent;
        padding: 8px 20px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-cancel-order:hover { background: #dc2626; color: #fff; }

    .od-btn-cancel {
        font-size: 13px;
        font-weight: 600;
        padding: 9px 24px;
        border-radius: 6px;
        border: 1.5px solid #dc2626;
        background: transparent;
        color: #dc2626;
        cursor: pointer;
        transition: all 0.2s;
    }

    .od-btn-cancel:hover { background: #dc2626; color: #fff; }

    /* loading skeleton */
    .od-skeleton-line {
        height: 14px;
        border-radius: 6px;
        background: linear-gradient(90deg,#f0f0f0 25%,#e8e8e8 50%,#f0f0f0 75%);
        background-size: 200% 100%;
        animation: shimmer 1.4s infinite;
        margin-bottom: 10px;
    }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>
@endsection

@section('content')
<div class="orders-wrap">

    <div class="orders-page-title">
        <i class="bi bi-bag-check"></i> Đơn mua
    </div>

    {{-- Status tabs --}}
    <div class="status-tabs">
        @foreach($statusTabs as $key => $label)
            <a href="{{ route('orders.index', $key !== '' ? ['status' => $key] : []) }}"
               class="status-tab {{ $activeTab === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if($orders->isEmpty())
        <div class="orders-empty">
            <div class="orders-empty-icon"><i class="bi bi-bag-x"></i></div>
            <div class="orders-empty-title">Chưa có đơn hàng nào</div>
            <div class="orders-empty-sub">Hãy khám phá và đặt hàng ngay hôm nay!</div>
            <a href="{{ route('products.index') }}" class="btn-shop-now">Mua sắm ngay</a>
        </div>
    @else
        @foreach($orders as $order)
            @php
                $statusMap = [
                    'pending'    => ['label' => 'Chờ xử lý',   'class' => 'status--pending'],
                    'processing' => ['label' => 'Đã xác nhận', 'class' => 'status--processing'],
                    'shipping'   => ['label' => 'Đang giao',   'class' => 'status--shipping'],
                    'completed'  => ['label' => 'Đã giao',     'class' => 'status--completed'],
                    'cancelled'  => ['label' => 'Đã huỷ',      'class' => 'status--cancelled'],
                ];
                $st           = $statusMap[$order->status] ?? ['label' => $order->status, 'class' => ''];
                $previewItems = $order->orderItems->take(2);
                $extraCount   = $order->orderItems->count() - $previewItems->count();
            @endphp

            <div class="order-card">
                {{-- Head --}}
                <div class="order-card-head">
                    <span class="order-code">Mã đơn hàng: #{{ $order->id }}</span>
                    <span class="order-date">{{ $order->created_at->format('H:i d/m/Y') }}</span>
                    <span class="order-status-badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                </div>

                {{-- Items --}}
                @foreach($previewItems as $item)
                    @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;
                        $image   = $variant?->image ?: $product?->thumbnail;
                    @endphp
                    <div class="order-item-row">
                        @if($image)
                            <img src="{{ asset($image) }}" alt="{{ $product?->name }}" class="order-item-img">
                        @else
                            <div class="order-item-img d-flex align-items-center justify-content-center">
                                <i class="bi bi-image" style="font-size:22px;color:#d1d5db;"></i>
                            </div>
                        @endif
                        <div class="order-item-info">
                            <div class="order-item-name">{{ $product?->name ?? 'Sản phẩm đã xoá' }}</div>
                            <div class="order-item-meta">
                                @if($variant?->color) {{ $variant->color->name }} @endif
                                @if($variant?->color && $variant?->size) · @endif
                                @if($variant?->size) {{ $variant->size->name }} @endif
                            </div>
                            <div class="order-item-qty">Số lượng: <span>{{ $item->quantity }}</span></div>
                        </div>
                        <div class="order-item-price">
                            {{ number_format($item->unit_price * $item->quantity, 0, ',', '.') }} đ
                        </div>
                    </div>
                @endforeach

                @if($extraCount > 0)
                    <div class="order-more">+ {{ $extraCount }} sản phẩm khác trong đơn</div>
                @endif

                {{-- Footer --}}
                <div class="order-card-foot" data-card-id="{{ $order->id }}">
                    <div class="order-total">
                        {{ number_format($order->total_money, 0, ',', '.') }} đ
                    </div>
                    <div style="display:flex; gap:8px; align-items:center;">
                        @if($order->status === 'pending' && $order->payment_status === 'unpaid')
                            <button type="button" class="btn-cancel-order"
                                    data-cancel-order="{{ $order->id }}">
                                <i class="bi bi-x-circle"></i> Hủy đơn
                            </button>
                        @elseif($order->status !== 'cancelled')
                            <span style="font-size:12px; color:#9ca3af;">Không thể hủy đơn này</span>
                        @endif
                        <button type="button" class="btn-view-order" data-order-id="{{ $order->id }}">
                            Xem chi tiết
                        </button>
                    </div>
                </div>
            </div>
        @endforeach

        @if($orders->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    @endif

</div>

{{-- Order detail modal --}}
<div id="orderDetailOverlay">
    <div class="od-modal" id="odModal">
        <div class="od-header">
            <div class="od-title" id="odTitle">Chi tiết đơn hàng</div>
            <button class="od-close" id="odClose" title="Đóng"><i class="bi bi-x-lg"></i></button>
        </div>
        <div class="od-body" id="odBody">
            <div style="padding:30px 0;">
                <div class="od-skeleton-line" style="width:60%;"></div>
                <div class="od-skeleton-line" style="width:40%;"></div>
                <div class="od-skeleton-line" style="width:80%; margin-top:20px;"></div>
                <div class="od-skeleton-line" style="width:70%;"></div>
            </div>
        </div>
        <div class="od-footer" style="justify-content:space-between;">
            <button class="od-btn-cancel" id="odBtnCancel" style="display:none;">
                <i class="bi bi-x-circle"></i> Hủy đơn hàng
            </button>
            <button class="od-btn-close" id="odBtnClose">Đóng</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const overlay     = document.getElementById('orderDetailOverlay');
    const body        = document.getElementById('odBody');
    const title       = document.getElementById('odTitle');
    const odCancelBtn = document.getElementById('odBtnCancel');
    const csrf        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let currentOrderId = null;

    const statusMap = {
        pending:    { label: 'Chờ xử lý',   cls: 'status--pending' },
        processing: { label: 'Đã xác nhận', cls: 'status--processing' },
        shipping:   { label: 'Đang giao',   cls: 'status--shipping' },
        completed:  { label: 'Đã giao',     cls: 'status--completed' },
        cancelled:  { label: 'Đã huỷ',      cls: 'status--cancelled' },
    };

    /* ── Modal open / close ── */
    function openModal() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        currentOrderId = null;
        odCancelBtn.style.display = 'none';
    }

    document.getElementById('odClose').addEventListener('click', closeModal);
    document.getElementById('odBtnClose').addEventListener('click', closeModal);
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    /* ── Helpers ── */
    function fmt(n) { return Number(n).toLocaleString('vi-VN') + ' đ'; }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    /* ── Render modal content ── */
    function renderModal(d) {
        currentOrderId = d.id;
        const st = statusMap[d.status] ?? { label: d.status, cls: '' };

        title.innerHTML = `Chi tiết đơn hàng #${d.id}&nbsp;
            <span class="order-status-badge ${st.cls}" style="font-size:11px;padding:4px 12px;border-radius:20px;">${esc(st.label)}</span>`;

        odCancelBtn.style.display = d.can_cancel ? '' : 'none';

        const itemRows = d.items.map(item => {
            const imgTag = item.image
                ? `<img src="${esc(item.image)}" class="od-prod-img" alt="">`
                : `<div class="od-prod-img" style="display:flex;align-items:center;justify-content:center;"><i class="bi bi-image" style="color:#d1d5db;font-size:18px;"></i></div>`;
            const meta = [item.color, item.size].filter(Boolean).join(' · ');
            return `<tr>
                <td>
                    <div class="od-prod-cell">
                        ${imgTag}
                        <div>
                            <div class="od-prod-name">${esc(item.name)}</div>
                            ${meta ? `<div class="od-prod-meta">${esc(meta)}</div>` : ''}
                        </div>
                    </div>
                </td>
                <td>${fmt(item.unit_price)}</td>
                <td>x${item.quantity}</td>
                <td>${fmt(item.subtotal)}</td>
            </tr>`;
        }).join('');

        const discount = d.subtotal - (d.total_money - d.shipping_fee);
        const discountRow = discount > 0
            ? `<div class="od-summary-row"><span>Giảm giá:</span><span class="val discount">-${fmt(discount)}</span></div>`
            : '';

        const payStatusColor = d.payment_status === 'paid' ? '#16a34a' : '#d97706';
        const payStatusLabel = d.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán';

        body.innerHTML = `
            <div class="od-info-grid">
                <div>
                    <div class="od-info-block-title">Thông tin nhận hàng</div>
                    <div class="od-info-row"><b>{{ auth()->user()->username }}</b></div>
                    <div class="od-info-row">${esc(d.phone)}</div>
                    <div class="od-info-row">${esc(d.address ?? '—')}</div>
                    ${d.note ? `<div class="od-info-row" style="margin-top:8px; color:#6b7280;">Ghi chú: ${esc(d.note)}</div>` : ''}
                </div>
                <div>
                    <div class="od-info-block-title">Thông tin đơn hàng</div>
                    <div class="od-info-row">Ngày đặt: &nbsp;<b>${esc(d.created_at)}</b></div>
                    <div class="od-info-row">Thanh toán: &nbsp;<b>${esc(d.payment_method ?? '—')}</b></div>
                    <div class="od-info-row">Trạng thái thanh toán: &nbsp;<b style="color:${payStatusColor};">${payStatusLabel}</b></div>
                </div>
            </div>

            <div style="font-size:14px; font-weight:700; margin-bottom:10px;">Sản phẩm</div>
            <div class="od-table-wrap">
                <table class="od-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Đơn giá</th>
                            <th>Số lượng</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>${itemRows}</tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                <div class="od-summary">
                    <div class="od-summary-row"><span>Tạm tính:</span><span class="val">${fmt(d.subtotal)}</span></div>
                    <div class="od-summary-row"><span>Phí vận chuyển:</span><span class="val">${d.shipping_fee > 0 ? fmt(d.shipping_fee) : '0 đ'}</span></div>
                    ${discountRow}
                    <div class="od-summary-total">
                        <span class="lbl">Tổng cộng:</span>
                        <span class="val">${fmt(d.total_money)}</span>
                    </div>
                </div>
            </div>`;
    }

    function showSkeleton() {
        odCancelBtn.style.display = 'none';
        body.innerHTML = `<div style="padding:20px 0;">
            <div class="od-skeleton-line" style="width:55%;"></div>
            <div class="od-skeleton-line" style="width:38%;"></div>
            <div class="od-skeleton-line" style="width:80%; margin-top:20px;"></div>
            <div class="od-skeleton-line" style="width:65%;"></div>
            <div class="od-skeleton-line" style="width:72%; margin-top:20px;"></div>
        </div>`;
    }

    /* ── Cancel logic ── */
    async function doCancel(orderId) {
        if (!confirm('Bạn có chắc muốn hủy đơn hàng này? Thao tác không thể hoàn tác.')) return;

        try {
            const res  = await fetch(`/orders/${orderId}/cancel`, {
                method: 'PATCH',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();

            if (!res.ok) {
                alert(data.message || 'Không thể hủy đơn hàng.');
                return;
            }

            /* Update card on the page */
            const cardFoot = document.querySelector(`.order-card-foot[data-card-id="${orderId}"]`);
            if (cardFoot) {
                const card = cardFoot.closest('.order-card');
                const badge = card?.querySelector('.order-status-badge');
                if (badge) {
                    badge.className  = 'order-status-badge status--cancelled';
                    badge.textContent = 'Đã huỷ';
                }
                /* Remove cancel button, leave the gray text slot empty */
                const cancelBtn = cardFoot.querySelector('[data-cancel-order]');
                if (cancelBtn) cancelBtn.remove();
                const grayText = cardFoot.querySelector('span[style*="color:#9ca3af"]');
                if (grayText) grayText.remove();
            }

            /* Update modal if still open for this order */
            if (overlay.classList.contains('open') && currentOrderId == orderId) {
                odCancelBtn.style.display = 'none';
                const modalBadge = title.querySelector('.order-status-badge');
                if (modalBadge) {
                    modalBadge.className  = 'order-status-badge status--cancelled';
                    modalBadge.textContent = 'Đã huỷ';
                }
            }

        } catch {
            alert('Không thể kết nối. Vui lòng thử lại.');
        }
    }

    /* ── Card cancel buttons ── */
    document.querySelectorAll('[data-cancel-order]').forEach(btn => {
        btn.addEventListener('click', function () {
            doCancel(this.dataset.cancelOrder);
        });
    });

    /* ── Modal cancel button ── */
    odCancelBtn.addEventListener('click', function () {
        if (currentOrderId) doCancel(currentOrderId);
    });

    /* ── Open detail modal ── */
    document.querySelectorAll('[data-order-id]').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.orderId;
            title.textContent = 'Chi tiết đơn hàng';
            showSkeleton();
            openModal();

            try {
                const res  = await fetch(`/orders/${id}/detail`, {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                const data = await res.json();
                renderModal(data);
            } catch {
                body.innerHTML = `<div style="text-align:center;padding:40px;color:#ef4444;">Không thể tải dữ liệu. Vui lòng thử lại.</div>`;
            }
        });
    });
})();
</script>
@endpush
