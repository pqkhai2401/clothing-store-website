@extends('layouts.app')

@section('title', 'Thanh toán PayOS | NOIR')

@section('css')
<style>
    .payos-wrap {
        max-width: 860px;
        margin: 48px auto;
        padding: 0 16px;
    }

    .payos-card {
        background: #fff;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
    }

    .payos-head {
        text-align: center;
        padding: 26px 24px 20px;
        border-bottom: 1px solid var(--border-color, #eee);
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
    }

    .payos-head .payos-brand {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-weight: 800;
        letter-spacing: 0.5px;
        color: #0d6efd;
        font-size: 15px;
        margin-bottom: 8px;
    }

    .payos-head h1 {
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 4px;
        color: #111;
    }

    .payos-head p {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }

    .payos-body {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 28px;
        padding: 28px;
    }

    @media (max-width: 640px) {
        .payos-body { grid-template-columns: 1fr; text-align: center; }
    }

    /* ── QR column ── */
    .payos-qr-box {
        border: 2px solid #111;
        border-radius: 14px;
        padding: 14px;
        width: 268px;
        height: 268px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .payos-qr-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .payos-status {
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #b45309;
    }

    .payos-status.cancelled { color: #dc2626; }

    .payos-spinner {
        width: 15px;
        height: 15px;
        border: 2px solid #fcd34d;
        border-top-color: #b45309;
        border-radius: 50%;
        animation: payosSpin 0.8s linear infinite;
        flex-shrink: 0;
    }

    @keyframes payosSpin { to { transform: rotate(360deg); } }

    /* ── Info column ── */
    .payos-amount-label {
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .payos-amount {
        font-size: 30px;
        font-weight: 800;
        color: #0d6efd;
        margin-bottom: 18px;
        line-height: 1.1;
    }

    .payos-steps {
        list-style: none;
        padding: 0;
        margin: 0 0 18px;
        text-align: left;
    }

    .payos-steps li {
        position: relative;
        padding: 6px 0 6px 30px;
        font-size: 13px;
        color: #374151;
        counter-increment: payos-step;
    }

    .payos-steps li::before {
        content: counter(payos-step);
        position: absolute;
        left: 0;
        top: 5px;
        width: 20px;
        height: 20px;
        background: #111;
        color: #fff;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .payos-steps { counter-reset: payos-step; }

    .payos-bank {
        background: #f8fafc;
        border: 1px solid var(--border-color, #eee);
        border-radius: 10px;
        padding: 12px 14px;
        font-size: 12.5px;
        color: #374151;
        margin-bottom: 18px;
        text-align: left;
    }

    .payos-bank div { padding: 2px 0; }
    .payos-bank strong { color: #111; }

    .payos-open-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        height: 46px;
        border-radius: 10px;
        background: #0d6efd;
        color: #fff !important;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        transition: background 0.2s;
        margin-bottom: 10px;
    }

    .payos-open-btn:hover { background: #0b5ed7; }

    .payos-cancel-link {
        display: block;
        text-align: center;
        font-size: 12.5px;
        color: #6b7280;
        text-decoration: none;
    }

    .payos-cancel-link:hover { color: #111; text-decoration: underline; }

    /* ── Success overlay ── */
    .payos-success-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(4px);
        z-index: 2000;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 18px;
    }

    .payos-success-overlay.show { display: flex; }

    .payos-check {
        width: 92px;
        height: 92px;
        border-radius: 50%;
        background: #16a34a;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 46px;
        animation: payosPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes payosPop {
        0%   { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }

    .payos-success-overlay h2 {
        font-size: 22px;
        font-weight: 700;
        color: #16a34a;
        margin: 0;
    }

    .payos-success-overlay p {
        font-size: 14px;
        color: #6b7280;
        margin: 0;
    }
</style>
@endsection

@section('content')
<div class="payos-wrap">
    <div class="payos-card">
        <div class="payos-head">
            <span class="payos-brand"><i class="bi bi-qr-code"></i> PayOS</span>
            <h1>Quét mã QR để thanh toán</h1>
            <p>Đơn hàng <strong>{{ $order->order_code }}</strong></p>
        </div>

        <div class="payos-body">
            {{-- QR --}}
            <div>
                <div class="payos-qr-box">
                    @if($qrCode)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=0&data={{ urlencode($qrCode) }}"
                             alt="Mã QR thanh toán PayOS">
                    @else
                        <span style="font-size:12px;color:#9ca3af;">Không tạo được mã QR</span>
                    @endif
                </div>
                <div class="payos-status" id="payosStatus">
                    <span class="payos-spinner"></span> Đang chờ thanh toán...
                </div>
            </div>

            {{-- Info --}}
            <div>
                <div class="payos-amount-label">Số tiền cần thanh toán</div>
                <div class="payos-amount">{{ number_format($amount, 0, ',', '.') }}đ</div>

                <ol class="payos-steps">
                    <li>Mở app ngân hàng hoặc ví điện tử có hỗ trợ QR</li>
                    <li>Quét mã QR bên cạnh và kiểm tra số tiền</li>
                    <li>Xác nhận — trang này sẽ <strong>tự chuyển</strong> khi nhận được thanh toán</li>
                </ol>

                @if($accountNumber)
                    <div class="payos-bank">
                        <div>Chủ tài khoản: <strong>{{ $accountName ?? '—' }}</strong></div>
                        <div>Số tài khoản: <strong>{{ $accountNumber }}</strong></div>
                        <div>Nội dung CK: <strong>{{ $order->order_code }}</strong></div>
                    </div>
                @endif

                @if($checkoutUrl)
                    <a href="{{ $checkoutUrl }}" target="_blank" rel="noopener" class="payos-open-btn">
                        <i class="bi bi-box-arrow-up-right"></i> Mở trang thanh toán PayOS
                    </a>
                @endif

                <a href="{{ route('orders.show', $order->id) }}" class="payos-cancel-link">
                    Để sau &middot; Xem đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Success overlay --}}
<div class="payos-success-overlay" id="payosSuccess">
    <div class="payos-check"><i class="bi bi-check-lg"></i></div>
    <h2>Thanh toán thành công!</h2>
    <p>Đang chuyển đến đơn hàng của bạn...</p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const statusUrl = @json(route('checkout.payos.status', $order->id));
    const csrf      = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const statusEl  = document.getElementById('payosStatus');
    const successEl = document.getElementById('payosSuccess');
    let stopped = false;

    function showSuccess(redirect) {
        successEl.classList.add('show');
        setTimeout(function () {
            window.location.href = redirect || @json(route('orders.show', $order->id));
        }, 1600);
    }

    function markCancelled() {
        stopped = true;
        statusEl.classList.add('cancelled');
        statusEl.innerHTML = '<i class="bi bi-x-circle"></i> Mã đã hết hạn hoặc bị hủy. Tải lại trang để lấy mã mới.';
    }

    async function poll() {
        if (stopped) return;
        try {
            const res  = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            });
            const data = await res.json();

            if (data.status === 'paid') {
                stopped = true;
                showSuccess(data.redirect);
                return;
            }
            if (data.status === 'cancelled') {
                markCancelled();
                return;
            }
        } catch (e) {
            /* lỗi mạng tạm thời — thử lại lần sau */
        }
        setTimeout(poll, 3000);
    }

    setTimeout(poll, 3000);
})();
</script>
@endpush
