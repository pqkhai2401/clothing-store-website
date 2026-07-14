@extends('layouts.app')

@section('title', 'Thanh toán MoMo | NOIR')

@section('css')
<style>
    .momo-wrap { max-width: 920px; margin: 48px auto; padding: 0 16px; }

    .momo-card {
        background: #fff;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.06);
    }

    .momo-head {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border-color, #eee);
    }

    .momo-logo {
        width: 40px; height: 40px;
        border-radius: 10px;
        background: #a50064;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 15px; letter-spacing: 0.3px;
        flex-shrink: 0;
    }

    .momo-head-title { font-size: 16px; font-weight: 700; color: #111; }

    .momo-body {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 28px;
        padding: 28px;
    }

    @media (max-width: 680px) {
        .momo-body { grid-template-columns: 1fr; }
    }

    /* ── Cột thông tin đơn ── */
    .momo-info-title { font-size: 16px; font-weight: 700; color: #111; margin-bottom: 16px; }

    .momo-field { padding: 12px 0; border-bottom: 1px dashed var(--border-color, #eee); }
    .momo-field-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
    .momo-field-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .momo-field-value { font-size: 15px; font-weight: 700; color: #111; word-break: break-all; }
    .momo-field-value.amount { color: #a50064; font-size: 22px; }

    .momo-copy {
        flex-shrink: 0;
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 12px; font-weight: 600; color: #a50064;
        background: #fdf2f8; border: 1px solid #fce7f3;
        border-radius: 6px; padding: 5px 10px; cursor: pointer;
        transition: all 0.15s; white-space: nowrap;
    }
    .momo-copy:hover { background: #fce7f3; }
    .momo-copy.copied { color: #16a34a; background: #dcfce7; border-color: #bbf7d0; }

    /* ── Cột QR (nền hồng như cổng MoMo) ── */
    .momo-qr-col {
        background: linear-gradient(180deg, #d82d8b 0%, #a50064 100%);
        border-radius: 14px;
        padding: 22px;
        text-align: center;
        color: #fff;
    }

    .momo-qr-box {
        background: #fff;
        border-radius: 14px;
        padding: 16px;
        width: 236px; height: 236px;
        margin: 0 auto;
        display: flex; align-items: center; justify-content: center;
    }
    .momo-qr-box img { width: 100%; height: 100%; object-fit: contain; }

    .momo-qr-hint { margin-top: 14px; font-size: 12.5px; line-height: 1.5; opacity: 0.95; }
    .momo-qr-hint strong { font-weight: 700; }

    .momo-status {
        margin-top: 14px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        font-size: 13px; font-weight: 600; color: #fff;
    }
    .momo-status.cancelled { color: #ffe4e6; }

    .momo-spinner {
        width: 15px; height: 15px;
        border: 2px solid rgba(255,255,255,0.5);
        border-top-color: #fff;
        border-radius: 50%;
        animation: momoSpin 0.8s linear infinite;
        flex-shrink: 0;
    }
    @keyframes momoSpin { to { transform: rotate(360deg); } }

    .momo-countdown {
        margin-top: 12px;
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 13px; font-weight: 600; color: #fff;
        background: rgba(255,255,255,0.18);
        border-radius: 999px; padding: 6px 14px;
    }
    .momo-countdown #momoTime { font-variant-numeric: tabular-nums; font-weight: 800; }
    .momo-countdown.warning { background: rgba(255,255,255,0.32); }

    .momo-renew {
        margin-top: 12px;
        display: none; align-items: center; justify-content: center; gap: 7px;
        margin-left: auto; margin-right: auto; width: fit-content;
        font-size: 13px; font-weight: 700; color: #a50064;
        background: #fff; border: none; border-radius: 8px;
        padding: 9px 20px; cursor: pointer;
    }
    .momo-renew.show { display: inline-flex; }

    .momo-deeplink {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        margin-top: 14px; width: 100%;
        font-size: 13px; font-weight: 700; color: #a50064;
        background: #fff; border-radius: 8px; padding: 10px; text-decoration: none;
    }

    .momo-foot {
        display: flex; align-items: center; justify-content: center;
        padding: 16px 24px 24px; border-top: 1px solid var(--border-color, #eee);
    }
    .momo-cancel-btn {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 13px; font-weight: 600; color: #6b7280;
        border: 1.5px solid #d1d5db; background: #fff;
        border-radius: 8px; padding: 10px 28px; text-decoration: none;
        transition: all 0.2s;
    }
    .momo-cancel-btn:hover { border-color: #9ca3af; color: #374151; }

    /* ── Success overlay ── */
    .momo-success-overlay {
        position: fixed; inset: 0;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(4px);
        z-index: 2000; display: none;
        flex-direction: column; align-items: center; justify-content: center; gap: 18px;
    }
    .momo-success-overlay.show { display: flex; }
    .momo-check {
        width: 92px; height: 92px; border-radius: 50%;
        background: #16a34a; display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: 46px;
        animation: momoPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes momoPop { 0% { transform: scale(0); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    .momo-success-overlay h2 { font-size: 22px; font-weight: 700; color: #16a34a; margin: 0; }
    .momo-success-overlay p  { font-size: 14px; color: #6b7280; margin: 0; }
</style>
@endsection

@section('content')
<div class="momo-wrap">
    <div class="momo-card">
        <div class="momo-head">
            <span class="momo-logo">momo</span>
            <span class="momo-head-title">Cổng thanh toán MoMo</span>
        </div>

        <div class="momo-body">
            {{-- Thông tin đơn --}}
            <div>
                <div class="momo-info-title">Thông tin đơn hàng</div>

                <div class="momo-field">
                    <div class="momo-field-label">Nhà cung cấp</div>
                    <div class="momo-field-row"><span class="momo-field-value">HK Store</span></div>
                </div>

                <div class="momo-field">
                    <div class="momo-field-label">Mã đơn hàng</div>
                    <div class="momo-field-row">
                        <span class="momo-field-value">{{ $order->order_code }}</span>
                        <button type="button" class="momo-copy" data-copy="{{ $order->order_code }}">
                            <i class="bi bi-copy"></i> Sao chép
                        </button>
                    </div>
                </div>

                <div class="momo-field">
                    <div class="momo-field-label">Mô tả</div>
                    <div class="momo-field-row"><span class="momo-field-value" style="font-weight:500;">Thanh toán cho đơn hàng {{ $order->order_code }}</span></div>
                </div>

                <div class="momo-field">
                    <div class="momo-field-label">Số tiền</div>
                    <div class="momo-field-row">
                        <span class="momo-field-value amount">{{ number_format($amount, 0, ',', '.') }}đ</span>
                        <button type="button" class="momo-copy" data-copy="{{ $amount }}">
                            <i class="bi bi-copy"></i> Sao chép
                        </button>
                    </div>
                </div>
            </div>

            {{-- QR --}}
            <div class="momo-qr-col">
                <div class="momo-qr-box">
                    @if($qrCodeUrl)
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=280x280&margin=0&data={{ urlencode($qrCodeUrl) }}"
                             alt="Mã QR thanh toán MoMo">
                    @else
                        <span style="font-size:12px;color:#9ca3af;">Không tạo được mã QR</span>
                    @endif
                </div>

                <div class="momo-qr-hint">
                    Dùng <strong>App MoMo</strong> hoặc ứng dụng camera hỗ trợ QR để quét mã
                </div>

                <div class="momo-status" id="momoStatus">
                    <span class="momo-spinner"></span> Đang chờ thanh toán...
                </div>

                <div>
                    <span class="momo-countdown" id="momoCountdown">
                        <i class="bi bi-clock"></i> Hết hạn sau <span id="momoTime">--:--</span>
                    </span>
                </div>

                <button type="button" class="momo-renew" id="momoRenew" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Lấy mã mới
                </button>

                @if($deeplink)
                    <a href="{{ $deeplink }}" class="momo-deeplink">
                        <i class="bi bi-phone"></i> Mở app MoMo
                    </a>
                @endif
            </div>
        </div>

        <div class="momo-foot">
            <a href="{{ route('orders.show', $order->id) }}" class="momo-cancel-btn">
                <i class="bi bi-clock-history"></i> Để sau · Xem đơn hàng
            </a>
        </div>
    </div>
</div>

{{-- Success overlay --}}
<div class="momo-success-overlay" id="momoSuccess">
    <div class="momo-check"><i class="bi bi-check-lg"></i></div>
    <h2>Thanh toán thành công!</h2>
    <p>Đang chuyển đến đơn hàng của bạn...</p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const statusUrl   = @json(route('checkout.momo.status', $order->id));
    const ordersUrl   = @json(route('orders.show', $order->id));
    const csrf        = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const statusEl    = document.getElementById('momoStatus');
    const successEl   = document.getElementById('momoSuccess');
    const countdownEl = document.getElementById('momoCountdown');
    const timeEl      = document.getElementById('momoTime');
    const renewBtn    = document.getElementById('momoRenew');
    let stopped = false;
    let remaining = {{ (int) $expiresIn }};
    let timerId = null;

    /* ── Sao chép ── */
    document.querySelectorAll('.momo-copy').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            const text = btn.getAttribute('data-copy') || '';
            try {
                await navigator.clipboard.writeText(text);
            } catch (e) {
                const ta = document.createElement('textarea');
                ta.value = text; document.body.appendChild(ta); ta.select();
                document.execCommand('copy'); document.body.removeChild(ta);
            }
            const original = btn.innerHTML;
            btn.classList.add('copied');
            btn.innerHTML = '<i class="bi bi-check-lg"></i> Đã sao chép';
            setTimeout(function () { btn.classList.remove('copied'); btn.innerHTML = original; }, 1500);
        });
    });

    /* ── Đếm ngược ── */
    function fmt(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }
    function tick() {
        if (stopped) return;
        remaining--;
        if (remaining <= 0) { timeEl.textContent = '00:00'; expire(); return; }
        timeEl.textContent = fmt(remaining);
        if (remaining <= 300) countdownEl.classList.add('warning');
    }
    function startCountdown() {
        timeEl.textContent = fmt(remaining);
        timerId = setInterval(tick, 1000);
    }
    function expire() {
        stopped = true;
        if (timerId) clearInterval(timerId);
        countdownEl.style.display = 'none';
        statusEl.classList.add('cancelled');
        statusEl.innerHTML = '<i class="bi bi-x-circle"></i> Mã đã hết hạn hoặc bị hủy.';
        renewBtn.classList.add('show');
    }

    /* ── Poll trạng thái ── */
    function showSuccess(redirect) {
        successEl.classList.add('show');
        setTimeout(function () { window.location.href = redirect || ordersUrl; }, 1600);
    }

    async function poll() {
        if (stopped) return;
        try {
            const res  = await fetch(statusUrl, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf } });
            const data = await res.json();
            if (data.status === 'paid') { stopped = true; showSuccess(data.redirect); return; }
            if (data.status === 'cancelled') { expire(); return; }
        } catch (e) { /* lỗi mạng tạm thời — thử lại lần sau */ }
        setTimeout(poll, 3000);
    }

    startCountdown();
    setTimeout(poll, 3000);
})();
</script>
@endpush
