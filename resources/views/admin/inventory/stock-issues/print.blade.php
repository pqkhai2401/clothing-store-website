@php
    use App\Helpers\MoneyToWords;

    $printCode = $stockIssue->code;
    $printDate = ($stockIssue->issued_at ?? $stockIssue->created_at)?->format('d/m/Y') ?? '—';
    $printCreator = $stockIssue->creator->username ?? $stockIssue->creator->name ?? 'N/A';
    $printWarehouse = $stockIssue->warehouse?->full_name ?? $stockIssue->warehouse?->name ?? '—';
    $printNote = $stockIssue->note ?: ($stockIssue->reason ?: 'Không có ghi chú.');
    $printTotal = (int) $stockIssue->total_sale_amount;
    $isCancelled = $stockIssue->isCancelled();
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Title = mã phiếu: trình duyệt dùng làm tên file mặc định khi "Lưu dưới dạng PDF". --}}
    <title>{{ $printCode }}</title>
    @include('admin.partials.print-sheet-styles')
</head>
<body>
    <div class="pp-wrap">
        <div class="sid-print-sheet" id="printSheet">
            @if($isCancelled)
                <div class="sid-print-watermark">ĐÃ HỦY</div>
            @endif

            <div class="sid-print-head">
                <div>
                    <div style="display:flex;align-items:center;gap:8px;">
                        @if($siteSettings->logo_url)
                            <img src="{{ $siteSettings->logo_url }}" alt="{{ $siteSettings->site_name }}" style="height:30px;width:auto;object-fit:contain;">
                        @else
                            <span class="sid-print-logo">{{ \Illuminate\Support\Str::of($siteSettings->site_name)->trim()->substr(0, 2)->upper() }}</span>
                        @endif
                        <span class="sid-print-brand">{{ \Illuminate\Support\Str::upper($siteSettings->site_name) }}</span>
                    </div>
                    <div class="sid-print-addr">
                        {{ $siteSettings->address ?: '—' }}<br>
                        Hotline: {{ $siteSettings->hotline ?: '—' }}<br>
                        Email: {{ $siteSettings->email ?: '—' }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div class="sid-print-title">PHIẾU XUẤT KHO</div>
                    <div class="sid-print-meta">Mã phiếu: <strong>{{ $printCode }}</strong></div>
                    <div class="sid-print-meta">Ngày xuất: <strong>{{ $printDate }}</strong></div>
                    <div class="sid-print-meta">Người lập: <strong>{{ $printCreator }}</strong></div>
                </div>
            </div>

            <hr class="sid-print-divider">

            <div class="sid-print-info">
                <div>
                    <div class="sid-print-label">Thông tin đơn hàng</div>
                    @if($stockIssue->order)
                        <div class="sid-print-line">Đơn hàng gốc: <strong>{{ $stockIssue->order->order_code ?? '#'.$stockIssue->order_id }}</strong></div>
                    @endif
                    <div class="sid-print-line">Kho xuất: <strong>{{ $printWarehouse }}</strong></div>
                </div>
                <div>
                    <div class="sid-print-label">Ghi chú</div>
                    <div class="sid-print-note">{{ $printNote }}</div>
                </div>
            </div>

            <table class="sid-print-table">
                <thead>
                    <tr>
                        <th style="width:40px;">STT</th>
                        <th>Tên sản phẩm / Mã SKU</th>
                        <th class="text-center" style="width:50px;">SL</th>
                        <th class="text-end" style="width:110px;">Đơn giá</th>
                        <th class="text-end" style="width:120px;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockIssue->items as $index => $item)
                        @php $variant = $item->productVariant; @endphp
                        <tr>
                            <td>{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="sid-print-product-name">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                <div class="sid-print-product-sku">{{ $variant?->color?->name }} - {{ $variant?->size?->name }} - {{ $variant?->sku }}</div>
                            </td>
                            <td class="text-center">{{ number_format($item->quantity) }}</td>
                            <td class="text-end">{{ number_format($item->sale_price, 0, ',', '.') }}đ</td>
                            <td class="text-end sid-print-line-total">{{ number_format($item->total_sale, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="sid-print-totals">
                <div class="sid-print-totals-row">
                    <span>Tổng tiền hàng:</span>
                    <span>{{ number_format($printTotal, 0, ',', '.') }}đ</span>
                </div>
                <div class="sid-print-totals-row">
                    <span>Chiết khấu:</span>
                    <span>0đ</span>
                </div>
                <div class="sid-print-totals-row sid-print-grand">
                    <span>TỔNG THANH TOÁN:</span>
                    <span>{{ number_format($printTotal, 0, ',', '.') }}đ</span>
                </div>
                <div class="sid-print-in-words">Bằng chữ: {{ MoneyToWords::vnd($printTotal) }}</div>
            </div>

            <div class="sid-print-signs">
                <div>
                    <div class="sid-print-sign-title">Người lập phiếu</div>
                    <div class="sid-print-sign-sub">(Ký và ghi rõ họ tên)</div>
                    <div class="sid-print-sign-name">{{ $printCreator }}</div>
                </div>
                <div>
                    <div class="sid-print-sign-title">Người nhận hàng</div>
                    <div class="sid-print-sign-sub">(Ký và ghi rõ họ tên)</div>
                </div>
                <div>
                    <div class="sid-print-sign-title">Thủ kho</div>
                    <div class="sid-print-sign-sub">(Ký và ghi rõ họ tên)</div>
                </div>
            </div>

            <div class="sid-print-footer">
                <span>{{ $siteSettings->site_name }} — Hệ thống quản lý kho</span>
                <span>Trang 1 / 1</span>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });
    </script>
</body>
</html>
