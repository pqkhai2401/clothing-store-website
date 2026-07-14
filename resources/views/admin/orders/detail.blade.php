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
                @if(\App\Http\Controllers\Admin\OrderController::canOpenEditPanel($order))
                    <button type="button" class="btn btn-outline-primary btn-sm fw-semibold"
                        data-order-edit-trigger
                        data-edit-url="{{ route('admin.orders.editContent', $order->id) }}">
                        <i class="fa-solid fa-pen-clip me-1"></i> Sửa đơn hàng
                    </button>
                @endif
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

    </main>

    @include('admin.orders.partials.edit-offcanvas')

    @push('modals')
        @include('layouts.components.confirm.delete')
    @endpush

    @push('scripts')
    <script>
    /* ── Chạy lại các thẻ <script> bên trong một khối HTML vừa được nạp qua innerHTML ──
       (trình duyệt không tự thực thi <script> được gán qua innerHTML, phải dựng lại thủ công) ── */
    window.runInjectedScripts = window.runInjectedScripts || function (container) {
        Array.from(container.querySelectorAll('script')).forEach(function (oldScript) {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.replaceWith(newScript);
        });
    };

    /* ── Panel "Sửa đơn hàng" trượt từ phải: nạp form qua AJAX ── */
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-order-edit-trigger]');
        if (!trigger) return;
        e.preventDefault();

        const url = trigger.dataset.editUrl;
        const offcanvasEl = document.getElementById('orderEditOffcanvas');
        const bodyEl = offcanvasEl?.querySelector('[data-order-edit-body]');
        if (!url || !offcanvasEl || !bodyEl) return;

        bodyEl.innerHTML = '<div class="offcanvas-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
        bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(res => { if (!res.ok) throw new Error('request-failed'); return res.json(); })
            .then(data => {
                bodyEl.innerHTML = data.html;
                window.runInjectedScripts(bodyEl);
            })
            .catch(() => {
                bodyEl.innerHTML = '<div class="offcanvas-body text-danger p-4">Không thể tải form sửa đơn hàng. Vui lòng thử lại.</div>';
            });
    });
    </script>
    @endpush
@endsection
