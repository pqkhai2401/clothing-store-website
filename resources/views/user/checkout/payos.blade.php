@extends('layouts.app')

@section('title', 'Thanh toán VietQR | NOIR')

@section('css')
<style>
    .payos-wrap {
        max-width: 920px;
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

    /* ── Banner gợi ý ── */
    .payos-hint {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 16px 24px;
        border-bottom: 1px solid var(--border-color, #eee);
        background: #f8fafc;
        font-size: 13.5px;
        color: #374151;
    }

    .payos-hint i { font-size: 22px; color: #f59e0b; flex-shrink: 0; }
    .payos-hint strong { color: #111; }

    .payos-body {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 32px;
        padding: 28px;
    }

    @media (max-width: 680px) {
        .payos-body { grid-template-columns: 1fr; }
    }

    /* ── Cột QR ── */
    .payos-qr-col { text-align: center; }

    .payos-vietqr-img {
        width: 100%;
        max-width: 300px;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 12px;
    }

    .payos-brand {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: 0.3px;
        margin-bottom: 12px;
        line-height: 1;
    }

    .payos-brand .vq-v   { color: #e2231a; }
    .payos-brand .vq-iet { color: #14235f; }
    .payos-brand .vq-qr  { color: #e2231a; }

    .payos-brand .vq-pro {
        display: inline-flex;
        align-items: center;
        vertical-align: middle;
        margin-left: 5px;
        border-radius: 6px;
        overflow: hidden;
        font-size: 15px;
        font-weight: 800;
        line-height: 1;
    }

    .payos-brand .vq-pr { background: #f5a623; color: #fff; padding: 4px 5px 4px 7px; }
    .payos-brand .vq-o  { background: #34a853; color: #fff; padding: 4px 7px 4px 5px; }

    .payos-qr-box {
        border: 2px solid #111;
        border-radius: 14px;
        padding: 14px;
        width: 280px;
        height: 280px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    .payos-qr-box img { width: 100%; height: 100%; object-fit: contain; }

    .payos-napas {
        margin-top: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-size: 12px;
        font-weight: 700;
        color: #6b7280;
    }

    .payos-napas .napas { color: #005baa; }
    .payos-napas .divider { color: #d1d5db; font-weight: 400; }
    .payos-napas .mb { color: #d81f28; }

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

    .payos-countdown {
        margin-top: 12px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        background: #f3f4f6;
        border: 1px solid var(--border-color, #e5e7eb);
        border-radius: 999px;
        padding: 6px 14px;
    }

    .payos-countdown i { color: #6b7280; }
    .payos-countdown #payosTime { font-variant-numeric: tabular-nums; font-weight: 800; color: #111; }
    .payos-countdown.warning { background: #fffbeb; border-color: #fde68a; color: #b45309; }
    .payos-countdown.warning i, .payos-countdown.warning #payosTime { color: #b45309; }

    .payos-renew {
        margin-top: 12px;
        display: none;
        align-items: center;
        justify-content: center;
        gap: 7px;
        margin-left: auto;
        margin-right: auto;
        width: fit-content;
        font-size: 13px;
        font-weight: 700;
        color: #fff;
        background: #0d6efd;
        border: none;
        border-radius: 8px;
        padding: 9px 20px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .payos-renew.show { display: inline-flex; }
    .payos-renew:hover { background: #0b5ed7; }

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

    /* ── Cột thông tin ── */
    .payos-info-title {
        font-size: 15px;
        font-weight: 700;
        color: #111;
        margin-bottom: 4px;
    }

    .payos-order-code { font-size: 12.5px; color: #6b7280; margin-bottom: 18px; }
    .payos-order-code strong { color: #374151; }

    .payos-field { padding: 12px 0; border-bottom: 1px dashed var(--border-color, #eee); }
    .payos-field:last-of-type { border-bottom: none; }

    .payos-field-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 3px;
    }

    .payos-field-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .payos-field-value {
        font-size: 15px;
        font-weight: 700;
        color: #111;
        word-break: break-all;
    }

    .payos-field-value.amount { color: #0d6efd; font-size: 18px; }

    .payos-copy {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        font-weight: 600;
        color: #0d6efd;
        background: #eff6ff;
        border: 1px solid #dbeafe;
        border-radius: 6px;
        padding: 5px 10px;
        cursor: pointer;
        transition: all 0.15s;
        white-space: nowrap;
    }

    .payos-copy:hover { background: #dbeafe; }
    .payos-copy.copied { color: #16a34a; background: #dcfce7; border-color: #bbf7d0; }

    .payos-note {
        margin-top: 16px;
        font-size: 12.5px;
        color: #b45309;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 8px;
        padding: 10px 12px;
        line-height: 1.5;
    }

    .payos-note strong { color: #92400e; }

    /* ── Footer ── */
    .payos-foot {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 20px;
        padding: 18px 24px 26px;
        border-top: 1px solid var(--border-color, #eee);
    }

    .payos-cancel-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #6b7280;
        border: 1.5px solid #d1d5db;
        background: #fff;
        border-radius: 8px;
        padding: 10px 28px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .payos-cancel-btn:hover { border-color: #9ca3af; color: #374151; }

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

    .payos-success-overlay h2 { font-size: 22px; font-weight: 700; color: #16a34a; margin: 0; }
    .payos-success-overlay p  { font-size: 14px; color: #6b7280; margin: 0; }
</style>
@endsection

@section('content')
<div class="payos-wrap">
    <div class="payos-card">
        <div class="payos-hint">
            <i class="bi bi-lightbulb"></i>
            <div>Mở <strong>App Ngân hàng</strong> bất kỳ để quét mã <strong>VietQR</strong> hoặc chuyển khoản chính xác số tiền bên dưới.</div>
        </div>

        <div class="payos-body">
            {{-- QR --}}
            @php
                // Ảnh VietQR có sẵn khung thương hiệu (logo VIETQR PRO + napas + ngân hàng),
                // dựng từ dữ liệu PayOS trả về — giống hệt trang thanh toán PayOS.
                $vietqrUrl = ($bin && $accountNumber)
                    ? 'https://img.vietqr.io/image/'.$bin.'-'.$accountNumber.'-compact.png?'.http_build_query([
                        'amount'      => $amount,
                        'addInfo'     => $order->order_code,
                        'accountName' => $accountName,
                    ])
                    : null;

                // Mã ngắn ngân hàng trong ngoặc, vd "Ngân hàng TMCP Quân đội (MB)" -> "MB".
                $bankShort = $bankName && \Illuminate\Support\Str::contains($bankName, '(')
                    ? \Illuminate\Support\Str::of($bankName)->betweenFirst('(', ')')->value()
                    : $bankName;
            @endphp
            <div class="payos-qr-col">
                @if($vietqrUrl)
                    {{-- Ảnh VietQR thương hiệu; nếu lỗi tải -> tự hiện QR trơn dự phòng --}}
                    <img class="payos-vietqr-img" src="{{ $vietqrUrl }}" alt="Mã VietQR thanh toán"
                         onerror="this.style.display='none'; document.getElementById('payosQrFallback').style.display='block';">
                @endif

                <div id="payosQrFallback" style="{{ $vietqrUrl ? 'display:none;' : '' }}">
                    <div class="payos-brand"><span class="vq-v">V</span><span class="vq-iet">IET</span><span class="vq-qr">QR</span><span class="vq-pro"><span class="vq-pr">PR</span><span class="vq-o">O</span></span></div>

                    <div class="payos-qr-box">
                        @if($qrCode)
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&margin=0&data={{ urlencode($qrCode) }}"
                                 alt="Mã QR thanh toán VietQR">
                        @else
                            <span style="font-size:12px;color:#9ca3af;">Không tạo được mã QR</span>
                        @endif
                    </div>

                    <div class="payos-napas">
                        <span class="napas">napas 247</span>
                        @if($bankShort)
                            <span class="divider">|</span>
                            <span class="mb">{{ $bankShort }}</span>
                        @endif
                    </div>
                </div>

                <div class="payos-status" id="payosStatus">
                    <span class="payos-spinner"></span> Đang chờ thanh toán...
                </div>

                <div>
                    <span class="payos-countdown" id="payosCountdown">
                        <i class="bi bi-clock"></i> Mã hết hạn sau <span id="payosTime">--:--</span>
                    </span>
                </div>

                <button type="button" class="payos-renew" id="payosRenew" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Lấy mã mới
                </button>
            </div>

            {{-- Thông tin chuyển khoản --}}
            <div>
                <div class="payos-info-title">Quét mã QR hoặc chuyển khoản thủ công</div>
                <div class="payos-order-code">Đơn hàng <strong>{{ $order->order_code }}</strong></div>

                @if($bankName)
                    <div class="payos-field">
                        <div class="payos-field-label">Ngân hàng</div>
                        <div class="payos-field-row">
                            <span class="payos-field-value">{{ $bankName }}</span>
                        </div>
                    </div>
                @endif

                @if($accountName)
                    <div class="payos-field">
                        <div class="payos-field-label">Chủ tài khoản</div>
                        <div class="payos-field-row">
                            <span class="payos-field-value">{{ $accountName }}</span>
                        </div>
                    </div>
                @endif

                @if($accountNumber)
                    <div class="payos-field">
                        <div class="payos-field-label">Số tài khoản</div>
                        <div class="payos-field-row">
                            <span class="payos-field-value" id="payosAccount">{{ $accountNumber }}</span>
                            <button type="button" class="payos-copy" data-copy="{{ $accountNumber }}">
                                <i class="bi bi-copy"></i> Sao chép
                            </button>
                        </div>
                    </div>
                @endif

                <div class="payos-field">
                    <div class="payos-field-label">Số tiền</div>
                    <div class="payos-field-row">
                        <span class="payos-field-value amount">{{ number_format($amount, 0, ',', '.') }}đ</span>
                        <button type="button" class="payos-copy" data-copy="{{ $amount }}">
                            <i class="bi bi-copy"></i> Sao chép
                        </button>
                    </div>
                </div>

                <div class="payos-field">
                    <div class="payos-field-label">Nội dung chuyển khoản</div>
                    <div class="payos-field-row">
                        <span class="payos-field-value">{{ $order->order_code }}</span>
                        <button type="button" class="payos-copy" data-copy="{{ $order->order_code }}">
                            <i class="bi bi-copy"></i> Sao chép
                        </button>
                    </div>
                </div>

                <div class="payos-note">
                    <i class="bi bi-exclamation-circle"></i>
                    Nhập <strong>chính xác số tiền</strong> và <strong>nội dung chuyển khoản</strong> ở trên.
                    Trang này sẽ <strong>tự chuyển</strong> ngay khi nhận được thanh toán.
                </div>
            </div>
        </div>

        <div class="payos-foot">
            <a href="{{ route('orders.show', $order->id) }}" class="payos-cancel-btn">
                <i class="bi bi-clock-history"></i> Để sau · Xem đơn hàng
            </a>
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
    const statusEl    = document.getElementById('payosStatus');
    const successEl   = document.getElementById('payosSuccess');
    const countdownEl = document.getElementById('payosCountdown');
    const timeEl      = document.getElementById('payosTime');
    const renewBtn    = document.getElementById('payosRenew');
    let stopped = false;
    let remaining = {{ (int) $expiresIn }};   // giây còn lại của mã QR
    let timerId = null;

    /* ── Sao chép ── */
    document.querySelectorAll('.payos-copy').forEach(function (btn) {
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

    /* ── Poll trạng thái ── */
    function showSuccess(redirect) {
        successEl.classList.add('show');
        setTimeout(function () {
            window.location.href = redirect || @json(route('orders.show', $order->id));
        }, 1600);
    }

    /* ── Đếm ngược thời hạn mã QR ── */
    function fmt(sec) {
        const m = Math.floor(sec / 60);
        const s = sec % 60;
        return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
    }

    function tick() {
        if (stopped) return;
        remaining--;
        if (remaining <= 0) {
            timeEl.textContent = '00:00';
            expire();
            return;
        }
        timeEl.textContent = fmt(remaining);
        if (remaining <= 300) countdownEl.classList.add('warning'); // cảnh báo khi còn <= 5 phút
    }

    function startCountdown() {
        timeEl.textContent = fmt(remaining);
        timerId = setInterval(tick, 1000);
    }

    /* ── Mã hết hạn / bị hủy ── */
    function expire() {
        stopped = true;
        if (timerId) clearInterval(timerId);
        countdownEl.style.display = 'none';
        statusEl.classList.add('cancelled');
        statusEl.innerHTML = '<i class="bi bi-x-circle"></i> Mã đã hết hạn hoặc bị hủy.';
        renewBtn.classList.add('show');
    }

    function markCancelled() { expire(); }

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

    startCountdown();
    setTimeout(poll, 3000);
})();
</script>
@endpush
