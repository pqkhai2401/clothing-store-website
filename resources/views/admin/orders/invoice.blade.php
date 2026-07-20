@php
    use App\Helpers\MoneyToWords;

    $orderBadgeLabels = $statusLabels ?? [];
    $subtotal = $order->orderItems->sum(fn ($item) => ($item->unit_price ?? 0) * $item->quantity);
    $discount = (float) ($order->discount_amount ?? 0);
    $shipping = (float) ($order->shipping_fee ?? 0);
    $total    = (float) $order->total_money;

    $shopName    = $setting->site_name ?: 'Cửa hàng';
    $shopAddress = $setting->address;
    $shopHotline = $setting->hotline;
    $shopEmail   = $setting->email;
    $logoUrl     = $setting->logo_url;

    $customerName = $order->user?->username ?? 'Khách vãng lai';
    $customerPhone = collect([$order->phone, $order->user?->phone_number])->first(fn ($p) => $p && $p !== '0') ?? '—';
    $customerAddress = $order->address
        ? (collect([$order->address->apartment_number, $order->address->ward, $order->address->district, $order->address->city])->filter()->join(', ') ?: '—')
        : '—';

    $isCancelled = $order->status === 'cancelled';
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Title = mã hóa đơn: trình duyệt dùng làm tên file mặc định khi "Lưu dưới dạng PDF". --}}
    <title>{{ $order->order_code ?: ('HoaDon-' . $order->id) }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Roboto, Arial, sans-serif;
            color: #111827;
            background: #eef2f7;
            font-size: 13px;
        }

        .invoice {
            position: relative;
            width: 800px; max-width: 100%;
            margin: 24px auto; padding: 40px;
            background: #fff; border: 1px solid #e5e7eb;
            box-shadow: 0 8px 30px rgba(15, 23, 42, 0.08);
        }

        .inv-head { display: flex; justify-content: space-between; gap: 24px; border-bottom: 2px solid #111827; padding-bottom: 16px; }
        .inv-shop { display: flex; gap: 14px; align-items: flex-start; }
        .inv-logo { width: 64px; height: 64px; object-fit: contain; border-radius: 8px; }
        .inv-shop-name { font-size: 18px; font-weight: 800; margin: 0 0 4px; }
        .inv-shop-meta { color: #4b5563; line-height: 1.5; }
        .inv-title { text-align: right; }
        .inv-title h1 { font-size: 17px; letter-spacing: .03em; margin: 0 0 6px; text-transform: uppercase; white-space: nowrap; }
        .inv-title .inv-code { font-family: "Courier New", monospace; font-weight: 700; }
        .inv-title .inv-date { color: #4b5563; margin-top: 2px; }

        .inv-parties { display: flex; gap: 32px; margin: 20px 0; }
        .inv-parties .inv-block { flex: 1; }
        .inv-block h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin: 0 0 8px; }
        .inv-block .inv-line { margin-bottom: 3px; }
        .inv-block .inv-line b { font-weight: 700; }

        table.inv-items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .inv-items th, .inv-items td { border: 1px solid #d1d5db; padding: 8px 10px; }
        .inv-items th { background: #f3f4f6; text-align: left; font-size: 12px; text-transform: uppercase; }
        .inv-items td.num, .inv-items th.num { text-align: right; }
        .inv-items td.center, .inv-items th.center { text-align: center; }

        .inv-summary { margin-top: 16px; display: flex; justify-content: flex-end; }
        .inv-summary table { border-collapse: collapse; min-width: 320px; }
        .inv-summary td { padding: 5px 10px; }
        .inv-summary td.label { color: #4b5563; }
        .inv-summary td.val { text-align: right; font-weight: 600; }
        .inv-summary tr.grand td { border-top: 2px solid #111827; font-size: 16px; font-weight: 800; padding-top: 10px; }
        .inv-summary tr.grand td.val { color: #16A34A; }

        .inv-words { margin-top: 8px; font-style: italic; color: #374151; }
        .inv-words b { font-style: normal; }

        .inv-pay { margin-top: 14px; }
        .inv-pay .paid { color: #16A34A; font-weight: 700; }
        .inv-pay .unpaid { color: #b45309; font-weight: 700; }

        .inv-sign { display: flex; justify-content: space-between; margin-top: 40px; text-align: center; }
        .inv-sign .col { width: 45%; }
        .inv-sign .role { font-weight: 700; }
        .inv-sign .hint { color: #6b7280; font-size: 12px; }
        .inv-sign .space { height: 70px; }

        .inv-thanks { text-align: center; margin-top: 28px; color: #4b5563; border-top: 1px dashed #d1d5db; padding-top: 14px; }

        /* Đóng dấu ĐÃ HỦY */
        .inv-watermark {
            position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%) rotate(-24deg);
            font-size: 96px; font-weight: 900; letter-spacing: .1em;
            color: rgba(220, 38, 38, 0.12); border: 8px solid rgba(220, 38, 38, 0.12);
            padding: 10px 40px; border-radius: 16px; pointer-events: none; white-space: nowrap;
        }

        @media print {
            body { background: #fff; }
            .invoice { width: auto; margin: 0; padding: 0 8px; border: none; box-shadow: none; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body>
    <div class="invoice">
        @if($isCancelled)
            <div class="inv-watermark">ĐÃ HỦY</div>
        @endif

        <div class="inv-head">
            <div class="inv-shop">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo" class="inv-logo">
                @endif
                <div>
                    <p class="inv-shop-name">{{ $shopName }}</p>
                    <div class="inv-shop-meta">
                        @if($shopAddress)<div>{{ $shopAddress }}</div>@endif
                        @if($shopHotline)<div>Hotline: {{ $shopHotline }}</div>@endif
                        @if($shopEmail)<div>Email: {{ $shopEmail }}</div>@endif
                    </div>
                </div>
            </div>
            <div class="inv-title">
                <h1>Hóa đơn bán hàng</h1>
                <div>Số: <span class="inv-code">{{ $order->order_code ?? ('#' . $order->id) }}</span></div>
                <div class="inv-date">Ngày {{ $order->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
            </div>
        </div>

        <div class="inv-parties">
            <div class="inv-block">
                <h3>Khách hàng</h3>
                <div class="inv-line"><b>{{ $customerName }}</b></div>
                <div class="inv-line">Điện thoại: {{ $customerPhone }}</div>
                <div class="inv-line">Địa chỉ giao: {{ $customerAddress }}</div>
            </div>
            <div class="inv-block">
                <h3>Thông tin đơn</h3>
                <div class="inv-line">Trạng thái: {{ $orderBadgeLabels[$order->status] ?? $order->status }}</div>
                <div class="inv-line">Thanh toán: {{ $order->paymentMethod?->name ?? '—' }}</div>
                @if($order->voucher)
                    <div class="inv-line">Mã giảm giá: {{ $order->voucher->code }}</div>
                @endif
            </div>
        </div>

        <table class="inv-items">
            <thead>
                <tr>
                    <th class="center" style="width:44px;">STT</th>
                    <th>Sản phẩm</th>
                    <th class="center" style="width:90px;">Màu / Size</th>
                    <th class="num" style="width:110px;">Đơn giá</th>
                    <th class="center" style="width:60px;">SL</th>
                    <th class="num" style="width:120px;">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->orderItems as $index => $item)
                    @php
                        // M4: ưu tiên thông tin snapshot lúc đặt hàng (đóng băng theo hóa đơn).
                        $attrs = collect([$item->displayColor(), $item->displaySize()])->filter()->join(' / ') ?: '—';
                    @endphp
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item->displayName() }}</td>
                        <td class="center">{{ $attrs }}</td>
                        <td class="num">{{ number_format($item->unit_price ?? 0, 0, ',', '.') }}₫</td>
                        <td class="center">{{ $item->quantity }}</td>
                        <td class="num">{{ number_format(($item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}₫</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="center" style="padding:20px;color:#6b7280;">Không có sản phẩm nào</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="inv-summary">
            <table>
                <tr>
                    <td class="label">Tạm tính</td>
                    <td class="val">{{ number_format($subtotal, 0, ',', '.') }}₫</td>
                </tr>
                @if($discount > 0)
                    <tr>
                        <td class="label">Giảm giá</td>
                        <td class="val">−{{ number_format($discount, 0, ',', '.') }}₫</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Phí vận chuyển</td>
                    <td class="val">{{ $shipping > 0 ? number_format($shipping, 0, ',', '.') . '₫' : 'Miễn phí' }}</td>
                </tr>
                <tr class="grand">
                    <td class="label">Tổng cộng</td>
                    <td class="val">{{ number_format($total, 0, ',', '.') }}₫</td>
                </tr>
            </table>
        </div>

        <div class="inv-words">Bằng chữ: <b>{{ MoneyToWords::vnd($total) }}</b></div>

        <div class="inv-pay">
            Tình trạng thanh toán:
            <span class="{{ $order->payment_status === 'paid' ? 'paid' : 'unpaid' }}">
                {{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}
            </span>
        </div>

        @if($order->note)
            <div style="margin-top:10px;color:#4b5563;">Ghi chú: {{ $order->note }}</div>
        @endif

        <div class="inv-sign">
            <div class="col">
                <div class="role">Người mua hàng</div>
                <div class="hint">(Ký, ghi rõ họ tên)</div>
                <div class="space"></div>
            </div>
            <div class="col">
                <div class="role">Người bán hàng</div>
                <div class="hint">(Ký, ghi rõ họ tên)</div>
                <div class="space"></div>
            </div>
        </div>

        <div class="inv-thanks">Cảm ơn quý khách đã mua hàng tại {{ $shopName }}!</div>
    </div>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 350);
        });
    </script>
</body>
</html>
