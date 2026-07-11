@once
    <style>
    .sid-actions {
        position: sticky;
        bottom: -20px;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 20px -20px -20px;
        padding: 12px 20px;
        border-top: 1px solid #e2e8f0;
        background: rgba(255, 255, 255, .96);
        backdrop-filter: blur(8px);
    }
    .sid-action-group { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .sid-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 7px;
        min-height: 36px; padding: 0 13px; border-radius: 8px; border: 1px solid #d1d5db;
        background: #fff; color: #334155; font-size: 12.5px; font-weight: 800;
        text-decoration: none; cursor: pointer;
    }
    .sid-btn:hover { background: #f8fafc; color: #0f172a; }
    .sid-btn:disabled { opacity: .65; cursor: not-allowed; background: #f8fafc; color: #64748b; }
    .sid-btn--primary { border-color: #000; background: #000; color: #fff; }
    .sid-btn--primary:hover { background: #1f1f1f; color: #fff; }
    .sid-btn--success { border-color: #059669; background: #059669; color: #fff; }
    .sid-btn--success:hover { background: #047857; color: #fff; }
    .sid-btn--danger { border-color: #fecaca; background: #fff; color: #dc2626; }
    .sid-btn--danger:hover { background: #fef2f2; color: #b91c1c; }
    .sid-btn--muted { background: #f8fafc; }
    .sid-uniform-13, .sid-uniform-13 * {
        font-size: 13px !important;
    }
    .si-detail-table td,
    .si-show-table tbody td {
        color: #1f2937 !important;
    }
    .si-detail-table td:first-child { color: #6b7280 !important; }
    .si-detail-table td a { color: #1f2937 !important; text-decoration: underline; }
    @media (max-width: 768px) {
        .sid-actions { position: static; margin: 20px 0 0; padding: 12px 0 0; }
        .sid-action-group { width: 100%; }
        .sid-btn { flex: 1 1 auto; }
    }
    </style>
@endonce

<div class="mb-3">
    <h2 class="h5 fw-bold mb-1" style="color:#000;">
        Phiếu xuất kho {{ $stockIssue->code }}
        @if($stockIssue->isCompleted())
            <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
        @elseif($stockIssue->isCancelled())
            <span class="gr-badge gr-badge--cancelled">Đã hủy</span>
        @else
            <span class="gr-badge gr-badge--draft">Nháp</span>
        @endif
    </h2>
    <p class="mb-0 text-muted" style="font-size:13px;">
        Tạo bởi {{ $stockIssue->creator->username ?? 'N/A' }} lúc {{ $stockIssue->created_at?->format('d/m/Y H:i') }}
    </p>
</div>

<div class="card edit-card shadow-sm mb-4 sid-uniform-13" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold">Thông tin phiếu</span></div>
    <div class="card-body p-4">
        <table class="table table-borderless mb-0 si-detail-table">
            <tr>
                <td style="width: 180px;">Loại xuất kho:</td>
                <td>
                    {{ \App\Models\StockIssue::ISSUE_TYPE_LABELS[$stockIssue->issue_type] ?? $stockIssue->issue_type }}
                </td>
            </tr>
            <tr>
                <td>Kho xuất hàng:</td>
                <td>{{ $stockIssue->warehouse->name ?? '—' }}</td>
            </tr>
            @if($stockIssue->order)
                <tr>
                    <td>Đơn hàng liên quan:</td>
                    <td>
                        <a href="{{ route('admin.orders.detail', $stockIssue->order_id) }}" style="color: #000;font-weight:800;">
                            {{ $stockIssue->order->order_code ?? 'Đơn hàng #'.$stockIssue->order_id }}
                        </a>
                    </td>
                </tr>
            @endif
            <tr>
                <td>Ngày xuất kho:</td>
                <td>{{ $stockIssue->issued_at ? $stockIssue->issued_at->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            @if($stockIssue->reason)
                <tr>
                    <td>Lý do xuất:</td>
                    <td>{{ $stockIssue->reason }}</td>
                </tr>
            @endif
            @if($stockIssue->note)
                <tr>
                    <td>Ghi chú chi tiết:</td>
                    <td>{{ $stockIssue->note }}</td>
                </tr>
            @endif
            @if($stockIssue->isCompleted())
                <tr>
                    <td>Xác nhận bởi:</td>
                    <td>{{ $stockIssue->confirmer->username ?? 'N/A' }} lúc {{ $stockIssue->confirmed_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
            @if($stockIssue->isCancelled())
                <tr>
                    <td>Hủy bởi:</td>
                    <td>{{ $stockIssue->canceller->username ?? 'N/A' }} lúc {{ $stockIssue->cancelled_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
        </table>
    </div>
</div>

<div class="card edit-card shadow-sm mb-4 sid-uniform-13" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold">Sản phẩm xuất kho</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="si-show-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="width:100px;">Số lượng</th>
                        <th style="width:140px;">Giá vốn (đ)</th>
                        <th style="width:140px;">Giá bán (đ)</th>
                        <th style="width:140px;">Thành tiền vốn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stockIssue->items as $item)
                        @php
                            $variant = $item->productVariant;
                            $thumb = $variant?->product?->thumbnail;
                            $thumbUrl = $thumb
                                ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                                : 'https://placehold.co/80x80?text=No+Image';
                        @endphp
                        <tr>
                            <td>
                                <div class="si-show-product">
                                    <img class="si-show-thumb" src="{{ $thumbUrl }}" alt="">
                                    <div>
                                        <div style="font-weight:800;color:#111827;line-height:1.25;">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                        <div style="color:#6b7280;margin-top:3px;">
                                            <span class="si-show-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#ccc' }};"></span>
                                            {{ $variant?->color?->name }} · {{ $variant?->size?->name }} · {{ $variant?->sku }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td>{{ number_format($item->cost_price, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->sale_price, 0, ',', '.') }}đ</td>
                            <td class="fw-semibold">{{ number_format($item->total_cost, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-end" style="color:#64748b;font-weight:700;padding:12px 12px 8px;">Tổng cộng vốn:</td>
                        <td class="text-end" style="color:#475569;font-weight:800;padding:12px 12px 8px;">
                            {{ number_format($stockIssue->total_cost_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end" style="color:#0f172a;font-weight:800;padding:10px 12px 14px;border-top:1px solid #e5e7eb;font-size:15px !important;">Tổng cộng bán:</td>
                        <td class="text-end" style="color:#000;font-weight:850;padding:10px 12px 14px;border-top:1px solid #e5e7eb;font-size:20px !important;">
                            {{ number_format($stockIssue->total_sale_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

{{-- Nhật ký thao tác (Logs) --}}
<div class="card edit-card shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold" style="font-size:14px;">Lịch sử thao tác</span></div>
    <div class="card-body p-3">
        @if($stockIssue->logs->isEmpty())
            <div class="text-muted text-center py-3" style="font-size:13px;">Chưa có nhật ký hoạt động nào.</div>
        @else
            <div class="timeline-logs" style="font-size:13px;">
                @php
                    $actionLabels = [
                        'created' => 'Khởi tạo',
                        'draft_saved' => 'Lưu nháp',
                        'updated' => 'Cập nhật',
                        'confirmed' => 'Hoàn tất',
                        'cancelled' => 'Hủy bỏ',
                    ];
                    $actionBadges = [
                        'created' => 'bg-secondary',
                        'draft_saved' => 'bg-warning text-dark',
                        'updated' => 'bg-info text-dark',
                        'confirmed' => 'bg-success',
                        'cancelled' => 'bg-danger',
                    ];
                @endphp
                @foreach($stockIssue->logs->sortByDesc('created_at') as $log)
                    <div class="d-flex justify-content-between border-bottom py-2 align-items-center">
                        <div>
                            <span class="badge {{ $actionBadges[$log->action] ?? 'bg-light text-dark' }} me-2">
                                {{ $actionLabels[$log->action] ?? $log->action }}
                            </span>
                            <span class="text-dark">{{ $log->message }}</span>
                            <span class="text-muted ms-2" style="font-size:12px;">(bởi {{ $log->user->username ?? 'Hệ thống' }})</span>
                        </div>
                        <div class="text-muted" style="font-size:12px;">{{ $log->created_at?->format('d/m/Y H:i:s') }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<footer class="sid-actions">
    <div class="sid-action-group">
        <button type="button" class="sid-btn sid-btn--muted" data-bs-dismiss="modal">
            <i class="fa-solid fa-arrow-left"></i> Quay lại
        </button>
        @if(! $stockIssue->isCancelled())
            <button type="button" class="sid-btn" onclick="document.getElementById('stockIssuePrintModal').classList.add('show')">
                <i class="fa-solid fa-print"></i> In phiếu
            </button>
        @endif
    </div>
    @if($stockIssue->isDraft())
        <div class="sid-action-group">
            <button type="button" class="sid-btn sid-btn--primary"
                data-stock-issue-edit-trigger
                data-edit-url="{{ route('admin.stock-issues.edit', $stockIssue->id) }}">
                <i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa
            </button>

            <button type="button" class="sid-btn" disabled title="Phiếu hiện đang được lưu ở trạng thái nháp">
                <i class="fa-regular fa-floppy-disk"></i> Đã lưu nháp
            </button>

            <form method="POST" action="{{ route('admin.stock-issues.issue', $stockIssue->id) }}" class="mb-0 d-inline-flex">
                @csrf @method('PATCH')
                <button type="submit" class="sid-btn sid-btn--success"
                    onclick="return confirm('Xác nhận hoàn tất xuất kho? Tồn kho thực tế sẽ bị trừ lập tức.')">
                    <i class="fa-solid fa-circle-check"></i> Hoàn tất xuất kho
                </button>
            </form>

            <form method="POST" action="{{ route('admin.stock-issues.cancel', $stockIssue->id) }}" class="mb-0 d-inline-flex">
                @csrf @method('PATCH')
                <button type="submit" class="sid-btn sid-btn--danger"
                    onclick="return confirm('Bạn có chắc chắn muốn hủy phiếu xuất kho này? Trạng thái sẽ không thể thay đổi sau khi hủy.')">
                    <i class="fa-regular fa-circle-xmark"></i> Huỷ phiếu
                </button>
            </form>
        </div>
    @endif
</footer>

<style>
.si-detail-table tr td { padding: 6px 0; }
.si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.si-show-table thead th { background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700; color: #374151; border-bottom: 1.5px solid #e5e7eb; }
.si-show-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; }
.si-show-product { display: flex; align-items: center; gap: 10px; }
.si-show-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; background: #f3f4f6; }
.si-show-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); }
</style>

{{-- Modal xem trước / in phiếu xuất kho --}}
@php
    if (! function_exists('siNumberToVietnameseWords')) {
        function siNumberToVietnameseWords(int $number): string
        {
            if ($number === 0) {
                return 'Không đồng';
            }

            $digits = ['không','một','hai','ba','bốn','năm','sáu','bảy','tám','chín'];
            $unitLabels = ['', 'nghìn', 'triệu', 'tỷ'];

            $readThree = function (int $n) use ($digits): string {
                $hundred = intdiv($n, 100);
                $remainder = $n % 100;
                $ten = intdiv($remainder, 10);
                $unit = $remainder % 10;
                $str = '';

                if ($hundred > 0) {
                    $str .= $digits[$hundred].' trăm ';
                    if ($remainder > 0 && $ten === 0) {
                        $str .= 'lẻ ';
                    }
                }

                if ($ten > 1) {
                    $str .= $digits[$ten].' mươi ';
                    if ($unit === 1) {
                        $str .= 'mốt ';
                    } elseif ($unit === 5) {
                        $str .= 'lăm ';
                    } elseif ($unit > 0) {
                        $str .= $digits[$unit].' ';
                    }
                } elseif ($ten === 1) {
                    $str .= 'mười ';
                    if ($unit === 1) {
                        $str .= 'một ';
                    } elseif ($unit === 5) {
                        $str .= 'lăm ';
                    } elseif ($unit > 0) {
                        $str .= $digits[$unit].' ';
                    }
                } elseif ($unit > 0) {
                    $str .= $digits[$unit].' ';
                }

                return trim($str);
            };

            $groups = [];
            $remaining = $number;
            while ($remaining > 0) {
                $groups[] = $remaining % 1000;
                $remaining = intdiv($remaining, 1000);
            }
            $groups = array_reverse($groups);
            $groupCount = count($groups);

            $parts = [];
            foreach ($groups as $i => $group) {
                if ($group === 0) {
                    continue;
                }
                $unitIndex = $groupCount - $i - 1;
                $parts[] = trim($readThree($group).' '.($unitLabels[$unitIndex] ?? ''));
            }

            $result = trim(preg_replace('/\s+/', ' ', implode(' ', $parts)));

            return ucfirst($result).' đồng chẵn.';
        }
    }

    $printCode = $stockIssue->code;
    $printDate = ($stockIssue->issued_at ?? $stockIssue->created_at)?->format('d/m/Y') ?? '—';
    $printCreator = $stockIssue->creator->username ?? $stockIssue->creator->name ?? 'N/A';
    $printWarehouse = $stockIssue->warehouse?->full_name ?? $stockIssue->warehouse?->name ?? '—';
    $printNote = $stockIssue->note ?: ($stockIssue->reason ?: 'Không có ghi chú.');
    $printTotal = (int) $stockIssue->total_sale_amount;
@endphp

<div class="sid-print-overlay" id="stockIssuePrintModal">
    <div class="sid-print-box" style="max-width:840px;">
        <div class="sid-print-modal">
            <div class="sid-print-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <span class="sid-print-toolbar-icon"><i class="fa-regular fa-file-lines"></i></span>
                    <div>
                        <div class="fw-bold" style="font-size:14px;color:#0f172a;">Xem trước phiếu xuất</div>
                        <div style="font-size:12px;color:#64748b;">Phiếu xuất kho: {{ $printCode }}</div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="sid-btn sid-btn--muted" onclick="document.getElementById('stockIssuePrintModal').classList.remove('show')">
                        <i class="fa-solid fa-xmark"></i> Đóng
                    </button>
                    <button type="button" class="sid-btn sid-btn--success" onclick="window.print()">
                        <i class="fa-solid fa-print"></i> In phiếu (Print)
                    </button>
                </div>
            </div>
            <div class="sid-print-canvas">
                <div class="sid-print-sheet" id="stockIssuePrintSheet">
                    <div class="sid-print-head">
                        <div>
                            <div class="d-flex align-items-center gap-2">
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
                        <div class="text-end">
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
                        <div class="sid-print-in-words">Bằng chữ: {{ siNumberToVietnameseWords($printTotal) }}</div>
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
                        <span>{{ $siteSettings->site_name }} Hệ thống quản lý kho v2.4.0</span>
                        <span>Trang 1 / 1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sid-print-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: rgba(15, 23, 42, .6);
    align-items: center;
    justify-content: center;
    padding: 24px;
    overflow-y: auto;
}
.sid-print-overlay.show { display: flex; }
.sid-print-box { width: 100%; margin: auto; }
.sid-print-modal { border-radius: 14px; overflow: hidden; background: #fff; box-shadow: 0 20px 60px rgba(0,0,0,.35); }
.sid-print-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; background: #fff; border-bottom: 1px solid #e5e7eb; padding: 14px 18px; }
.sid-print-toolbar-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 32px; height: 32px; border-radius: 8px; background: #059669; color: #fff; font-size: 13px;
}
.sid-print-canvas { background: #eef2f7; padding: 28px; max-height: calc(100vh - 160px); overflow-y: auto; }
.sid-print-sheet {
    background: #fff; max-width: 720px; margin: 0 auto; padding: 34px 38px;
    border-radius: 10px; box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
    font-size: 12.5px; color: #1f2937;
}
.sid-print-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.sid-print-logo {
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px; border-radius: 6px; background: #059669; color: #fff; font-weight: 800; font-size: 12px;
}
.sid-print-brand { font-weight: 850; font-size: 15px; color: #0f172a; }
.sid-print-addr { margin-top: 8px; line-height: 1.7; color: #374151; }
.sid-print-title { font-weight: 850; font-size: 15px; color: #0f172a; letter-spacing: .02em; }
.sid-print-meta { margin-top: 6px; color: #64748b; }
.sid-print-meta strong { color: #0f172a; }
.sid-print-divider { border: none; border-top: 2px solid #059669; margin: 18px 0; }
.sid-print-info { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; margin-bottom: 18px; }
.sid-print-label { font-weight: 850; color: #059669; letter-spacing: .04em; text-transform: uppercase; font-size: 11px; margin-bottom: 8px; }
.sid-print-line { margin-bottom: 4px; }
.sid-print-note { font-style: italic; color: #4b5563; line-height: 1.6; }
.sid-print-table { width: 100%; border-collapse: collapse; margin-top: 6px; }
.sid-print-table thead th {
    text-align: left; text-transform: uppercase; font-size: 11px; letter-spacing: .03em;
    color: #374151; padding: 10px 6px; border-bottom: 1px solid #e5e7eb; border-top: 1px solid #e5e7eb;
}
.sid-print-table tbody td { padding: 12px 6px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.sid-print-product-name { font-weight: 800; color: #0f172a; }
.sid-print-product-sku { color: #6b7280; margin-top: 2px; font-size: 11.5px; }
.sid-print-line-total { font-weight: 800; color: #0f172a; }
.sid-print-totals { margin-top: 18px; margin-left: auto; width: 260px; }
.sid-print-totals-row { display: flex; justify-content: space-between; padding: 4px 0; color: #4b5563; }
.sid-print-grand { border-top: 1px solid #e5e7eb; margin-top: 6px; padding-top: 10px; font-weight: 850; color: #0f172a; font-size: 14px; }
.sid-print-grand span:last-child { color: #059669; }
.sid-print-in-words { text-align: right; font-style: italic; color: #6b7280; margin-top: 6px; font-size: 11.5px; }
.sid-print-signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center; margin-top: 46px; }
.sid-print-sign-title { font-weight: 850; color: #0f172a; text-transform: uppercase; font-size: 11.5px; }
.sid-print-sign-sub { color: #6b7280; font-size: 11px; margin-top: 2px; }
.sid-print-sign-name { margin-top: 48px; font-weight: 700; }
.sid-print-footer {
    display: flex; justify-content: space-between; margin-top: 34px; padding-top: 12px;
    border-top: 1px solid #e5e7eb; color: #9ca3af; font-size: 11px;
}
@media print {
    body * { visibility: hidden; }
    #stockIssuePrintSheet, #stockIssuePrintSheet * { visibility: visible; }
    #stockIssuePrintSheet {
        position: absolute; top: 0; left: 0; width: 100%; max-width: 100%;
        margin: 0; box-shadow: none; border-radius: 0; padding: 10mm;
    }
    @page { size: A4; margin: 0; }
}
</style>
