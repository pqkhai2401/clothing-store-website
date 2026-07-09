{{-- Nội dung chi tiết phiếu nhập kho — dùng cho modal AJAX và trang fallback. --}}
@php
    $statusLabel = match ($goodsReceipt->status) {
        \App\Models\GoodsReceipt::STATUS_COMPLETED => 'Đã nhập kho',
        \App\Models\GoodsReceipt::STATUS_ADJUSTED => 'Đã điều chỉnh',
        'cancelled' => 'Đã hủy',
        default => 'Nháp',
    };

    $statusClass = match ($goodsReceipt->status) {
        \App\Models\GoodsReceipt::STATUS_COMPLETED => 'grd-status--done',
        \App\Models\GoodsReceipt::STATUS_ADJUSTED => 'grd-status--adjusted',
        'cancelled' => 'grd-status--cancelled',
        default => 'grd-status--draft',
    };

    $totalQuantity = $goodsReceipt->items->sum('quantity');
    $supplierName = $goodsReceipt->supplier->name ?? '—';
    $sourceType = $goodsReceipt->source_type ?? \App\Models\GoodsReceipt::SOURCE_TYPE_SUPPLIER;
    $receiptType = $goodsReceipt->receipt_type ?? \App\Models\GoodsReceipt::RECEIPT_TYPE_PURCHASE;
    $importType = match ($receiptType) {
        \App\Models\GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT => '⚖️ Điều chỉnh kiểm kê',
        \App\Models\GoodsReceipt::RECEIPT_TYPE_RETURN     => '🔄 Nhập trả hàng',
        \App\Models\GoodsReceipt::RECEIPT_TYPE_INITIAL    => '🗂️ Nhập tồn đầu kỳ',
        default => '📦 Nhập hàng nhà cung cấp',
    };
    // Ưu tiên đọc receipt_reason (trường riêng cho lý do nhập từ DB mới)
    // Nếu chưa có thì fallback về note, rồi mới dùng text mặc định
    $importReason = $goodsReceipt->receipt_reason
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT ? 'Cân bằng tồn kho sau kiểm kê' : null)
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_RETURN ? 'Nhập trả hàng từ khách hàng' : null)
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_INITIAL ? 'Nhập tồn kho đầu kỳ' : null)
        ?? '—';
    $warehouseName = $goodsReceipt->warehouse?->full_name
        ?? ($goodsReceipt->warehouse ? $goodsReceipt->warehouse->name : '—');
    $staffName = $goodsReceipt->creator->username ?? $goodsReceipt->creator->name ?? 'N/A';
    $relatedCode = null;

    if ($goodsReceipt->adjustmentStockIssue) {
        $relatedCode = $goodsReceipt->adjustmentStockIssue->code;
    } elseif (preg_match('/PKK\d+/', (string) $goodsReceipt->note, $matches)) {
        $relatedCode = $matches[0];
    }

    $actionLabels = [
        'created' => 'Tạo phiếu',
        'updated' => 'Cập nhật phiếu',
        'confirmed' => 'Xác nhận nhập kho',
        'cancelled' => 'Hủy phiếu',
    ];
    $actionTones = [
        'created' => 'muted',
        'updated' => 'warning',
        'confirmed' => 'success',
        'cancelled' => 'danger',
    ];
    $logs = $goodsReceipt->logs->isNotEmpty()
        ? $goodsReceipt->logs->map(fn ($log) => [
            'label' => $actionLabels[$log->action] ?? $log->action,
            'user' => $log->user->username ?? $log->user->name ?? 'N/A',
            'time' => $log->created_at,
            'note' => $log->message ?: '—',
            'tone' => $actionTones[$log->action] ?? 'muted',
        ])
        : collect([[
            'label' => 'Tạo phiếu',
            'user' => $staffName,
            'time' => $goodsReceipt->created_at,
            'note' => 'Khởi tạo phiếu nhập kho.',
            'tone' => 'muted',
        ]]);
@endphp

@once
    <style>
        .grd-detail {
            color: #0f172a;
            font-size: 13px;
        }
        .grd-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 14px;
        }
        .grd-breadcrumb span:last-child { color: #0f172a; }
        .grd-header {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .06);
            padding: 18px 20px;
            margin-bottom: 16px;
        }
        .grd-header-main {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .grd-title {
            margin: 0;
            color: #020617;
            font-size: 22px;
            font-weight: 850;
            letter-spacing: 0;
            line-height: 1.2;
        }
        .grd-code-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }
        .grd-code {
            color: #1e3a5f;
            font-size: 17px;
            font-weight: 850;
        }
        .grd-status {
            display: inline-flex;
            align-items: center;
            min-height: 24px;
            padding: 0 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .03em;
        }
        .grd-status--draft { background: #fef3c7; color: #92400e; }
        .grd-status--done { background: #dcfce7; color: #166534; }
        .grd-status--adjusted { background: #ffedd5; color: #9a3412; }
        .grd-status--cancelled { background: #fee2e2; color: #991b1b; }
        .grd-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 12.5px;
            margin-top: 10px;
        }
        .grd-meta strong { color: #334155; }
        .grd-close-btn {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            color: #64748b;
        }
        .grd-close-btn:hover { background: #f8fafc; color: #0f172a; }
        .grd-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 320px;
            gap: 16px;
            align-items: start;
        }
        .grd-stack {
            display: flex;
            flex-direction: column;
            gap: 16px;
            min-width: 0;
        }
        .grd-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .055);
            overflow: hidden;
        }
        .grd-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        .grd-card-title {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            color: #020617;
            font-size: 15px;
            font-weight: 850;
        }
        .grd-card-title i { color: #0f766e; }
        .grd-info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px 22px;
            padding: 18px;
        }
        .grd-field {
            min-width: 0;
            border-left: 2px solid #cbd5e1;
            padding-left: 12px;
        }
        .grd-field-label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 6px;
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .grd-field-value {
            color: #0f172a;
            font-size: 13.5px;
            font-weight: 750;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .grd-field-value--muted {
            color: #475569;
            font-weight: 600;
            font-style: italic;
        }
        .grd-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
        }
        .grd-table th {
            padding: 13px 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            color: #475569;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .04em;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .grd-table td {
            padding: 15px 16px;
            border-bottom: 1px solid #eef2f7;
            vertical-align: middle;
        }
        .grd-product {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 280px;
        }
        .grd-thumb {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
        }
        .grd-product-name {
            color: #111827;
            font-size: 14.5px;
            font-weight: 850;
            line-height: 1.25;
        }
        .grd-variant {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            color: #64748b;
            font-size: 12px;
            line-height: 1.3;
        }
        .grd-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            border: 1px solid rgba(15, 23, 42, .14);
            flex: 0 0 auto;
        }
        .grd-num { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .grd-center { text-align: center; }
        .grd-summary {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 28px;
            flex-wrap: wrap;
            padding: 16px 18px;
            border-top: 1px solid #e5e7eb;
            background: #f8fafc;
        }
        .grd-summary-item {
            display: flex;
            align-items: baseline;
            gap: 8px;
        }
        .grd-summary-label { color: #64748b; font-size: 13px; font-weight: 650; }
        .grd-summary-value { color: #0f172a; font-size: 16px; font-weight: 850; }
        .grd-summary-value--money { color: #1e3a5f; font-size: 22px; }
        .grd-log {
            position: relative;
            padding: 18px;
        }
        .grd-log-item {
            position: relative;
            display: grid;
            grid-template-columns: 22px minmax(0, 1fr);
            gap: 10px;
            padding-bottom: 18px;
        }
        .grd-log-item:last-child { padding-bottom: 0; }
        .grd-log-item:not(:last-child)::before {
            content: "";
            position: absolute;
            left: 10px;
            top: 20px;
            bottom: -2px;
            width: 1px;
            background: #e2e8f0;
        }
        .grd-log-dot {
            position: relative;
            z-index: 1;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
            background: #e2e8f0;
            color: #475569;
            font-size: 8px;
        }
        .grd-log-dot--success { background: #ccfbf1; color: #0f766e; }
        .grd-log-dot--warning { background: #fef3c7; color: #92400e; }
        .grd-log-dot--danger { background: #fee2e2; color: #991b1b; }
        .grd-log-title { color: #0f172a; font-size: 13px; font-weight: 850; line-height: 1.25; }
        .grd-log-meta { color: #64748b; font-size: 11.5px; margin-top: 4px; line-height: 1.4; }
        .grd-log-note { color: #475569; font-size: 12px; margin-top: 5px; line-height: 1.45; }
        .grd-actions {
            position: sticky;
            bottom: -20px;
            z-index: 3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin: 16px -20px -20px;
            padding: 12px 20px;
            border-top: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, .96);
            backdrop-filter: blur(8px);
        }
        .grd-action-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .grd-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 36px;
            padding: 0 13px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #334155;
            font-size: 12.5px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }
        .grd-btn:hover { background: #f8fafc; color: #0f172a; }
        .grd-btn:disabled {
            opacity: .65;
            cursor: not-allowed;
            background: #f8fafc;
            color: #64748b;
        }
        .grd-btn--primary { border-color: #1e3a5f; background: #1e3a5f; color: #fff; }
        .grd-btn--primary:hover { background: #132f4d; color: #fff; }
        .grd-btn--success { border-color: #059669; background: #059669; color: #fff; }
        .grd-btn--success:hover { background: #047857; color: #fff; }
        .grd-btn--danger { border-color: #fecaca; background: #fff; color: #dc2626; }
        .grd-btn--danger:hover { background: #fef2f2; color: #b91c1c; }
        .grd-btn--muted { background: #f8fafc; }
        @media (max-width: 992px) {
            .grd-layout { grid-template-columns: 1fr; }
        }
        @media (max-width: 768px) {
            .grd-info-grid { grid-template-columns: 1fr; }
            .grd-header { padding: 16px; }
            .grd-title { font-size: 19px; }
            .grd-actions { position: static; margin: 16px 0 0; padding: 12px 0 0; }
            .grd-action-group { width: 100%; }
            .grd-btn { flex: 1 1 auto; }
        }
    </style>
@endonce

<div class="grd-detail">
    <div class="grd-breadcrumb">
        <span>Quản lý kho</span>
        <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
        <span>Phiếu nhập kho</span>
        <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
        <span>Chi tiết phiếu nhập</span>
    </div>

    <header class="grd-header">
        <div class="grd-header-main">
            <div>
                <h2 class="grd-title">Chi tiết phiếu nhập kho</h2>
                <div class="grd-code-line">
                    <span class="grd-code">{{ $goodsReceipt->code }}</span>
                    <span class="grd-status {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <div class="grd-meta">
                    <span><i class="fa-regular fa-user me-1"></i> Người tạo: <strong>{{ $staffName }}</strong></span>
                    <span><i class="fa-regular fa-clock me-1"></i> Thời gian tạo phiếu: <strong>{{ $goodsReceipt->created_at?->format('d/m/Y H:i') ?? '—' }}</strong></span>
                    @if($goodsReceipt->confirmed_at)
                        <span><i class="fa-regular fa-circle-check me-1"></i> Thời gian xác nhận: <strong>{{ $goodsReceipt->confirmed_at?->format('d/m/Y H:i') }}</strong></span>
                    @endif
                </div>
            </div>
            <button type="button" class="grd-close-btn" data-bs-dismiss="modal" aria-label="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </header>

    <div class="grd-layout">
        <div class="grd-stack">
            <section class="grd-card">
                <div class="grd-card-head">
                    <h3 class="grd-card-title"><i class="fa-regular fa-circle-info"></i> Thông tin phiếu</h3>
                </div>
                <div class="grd-info-grid">
                    <div class="grd-field">
                        {{-- Nguồn nhập: chỉ hiển thị 1 khối duy nhất, không trùng lặp --}}
                        @if($sourceType === \App\Models\GoodsReceipt::SOURCE_TYPE_INTERNAL)
                            <div class="grd-field-label"><i class="fa-solid fa-building-user"></i> Nguồn nhập</div>
                            <div class="grd-field-value">Nội bộ</div>
                        @else
                            <div class="grd-field-label"><i class="fa-solid fa-truck-ramp-box"></i> Nhà cung cấp</div>
                            <div class="grd-field-value">{{ $supplierName }}</div>
                        @endif
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-solid fa-layer-group"></i> Loại nhập</div>
                        <div class="grd-field-value">{{ $importType }}</div>
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-regular fa-file-lines"></i> Lý do nhập</div>
                        <div class="grd-field-value">{{ $importReason }}</div>
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-solid fa-location-dot"></i> Kho nhận</div>
                        <div class="grd-field-value">{{ $warehouseName }}</div>
                    </div>
                    <div class="grd-field">
                        {{-- Nhãn ngày thay đổi theo trạng thái phiếu --}}
                        @if($goodsReceipt->isCompleted() || $goodsReceipt->isAdjusted())
                            <div class="grd-field-label"><i class="fa-regular fa-calendar-check"></i> Ngày xác nhận nhập kho</div>
                            <div class="grd-field-value">{{ ($goodsReceipt->confirmed_at ?? $goodsReceipt->completed_at)?->format('d/m/Y H:i') ?? '—' }}</div>
                        @elseif($goodsReceipt->isCancelled())
                            <div class="grd-field-label"><i class="fa-regular fa-calendar-xmark"></i> Ngày hủy phiếu</div>
                            <div class="grd-field-value">{{ $goodsReceipt->cancelled_at?->format('d/m/Y H:i') ?? '—' }}</div>
                        @else
                            <div class="grd-field-label"><i class="fa-regular fa-calendar"></i> Ngày tạo phiếu nháp</div>
                            <div class="grd-field-value">{{ $goodsReceipt->created_at?->format('d/m/Y H:i') ?? '—' }}</div>
                        @endif
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-regular fa-id-badge"></i> Nhân viên phụ trách</div>
                        <div class="grd-field-value">{{ $staffName }}</div>
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-solid fa-link"></i> Mã phiếu liên quan</div>
                        <div class="grd-field-value">{{ $relatedCode ?: '—' }}</div>
                    </div>
                    <div class="grd-field">
                        <div class="grd-field-label"><i class="fa-regular fa-note-sticky"></i> Ghi chú</div>
                        <div class="grd-field-value grd-field-value--muted">{{ $goodsReceipt->note ?: 'Không có ghi chú.' }}</div>
                    </div>
                </div>
            </section>

            <section class="grd-card">
                <div class="grd-card-head">
                    <h3 class="grd-card-title"><i class="fa-solid fa-bag-shopping"></i> Sản phẩm nhập kho</h3>
                    <span class="text-muted" style="font-size:12px;font-weight:700;">Tổng số dòng: {{ $goodsReceipt->items->count() }}</span>
                </div>
                <div class="table-responsive">
                    <table class="grd-table">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Sản phẩm</th>
                                <th class="grd-center" style="width:110px;">Số lượng</th>
                                <th class="grd-num" style="width:140px;">Đơn giá nhập</th>
                                <th class="grd-num" style="width:150px;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($goodsReceipt->items as $item)
                                @php
                                    $variant = $item->productVariant;
                                    $thumb = $variant?->product?->thumbnail;
                                    $thumbUrl = $thumb
                                        ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                                        : 'https://placehold.co/80x80?text=No+Image';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="grd-product">
                                            <img class="grd-thumb" src="{{ $thumbUrl }}" alt="">
                                            <div>
                                                <div class="grd-product-name">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                                <div class="grd-variant">
                                                    <span class="grd-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#cbd5e1' }};"></span>
                                                    <span>Màu: {{ $variant?->color?->name ?? '—' }} | Size: {{ $variant?->size?->name ?? '—' }} | SKU: {{ $variant?->sku ?? '—' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="grd-center fw-bold">{{ number_format($item->quantity) }}</td>
                                    <td class="grd-num">{{ number_format($item->cost_price, 0, ',', '.') }}đ</td>
                                    <td class="grd-num fw-bold">{{ number_format($item->quantity * $item->cost_price, 0, ',', '.') }}đ</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="grd-summary">
                    <div class="grd-summary-item">
                        <span class="grd-summary-label">Tổng số lượng:</span>
                        <span class="grd-summary-value">{{ number_format($totalQuantity) }}</span>
                    </div>
                    <div class="grd-summary-item">
                        <span class="grd-summary-label">Tổng tiền nhập:</span>
                        <span class="grd-summary-value grd-summary-value--money">{{ number_format($goodsReceipt->total_amount, 0, ',', '.') }}đ</span>
                    </div>
                </div>
            </section>
        </div>

        <aside class="grd-card">
            <div class="grd-card-head">
                <h3 class="grd-card-title"><i class="fa-solid fa-clock-rotate-left"></i> Nhật ký thao tác</h3>
            </div>
            <div class="grd-log">
                @foreach($logs->sortByDesc('time')->values() as $log)
                    <div class="grd-log-item">
                        <span class="grd-log-dot grd-log-dot--{{ $log['tone'] === 'muted' ? 'muted' : $log['tone'] }}">
                            <i class="fa-solid fa-circle"></i>
                        </span>
                        <div>
                            <div class="grd-log-title">{{ $log['label'] }}</div>
                            <div class="grd-log-meta">
                                Bởi: <strong>{{ $log['user'] }}</strong><br>
                                {{ $log['time'] ? $log['time']->format('d/m/Y H:i') : '—' }}
                            </div>
                            <div class="grd-log-note">{{ $log['note'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>

    <footer class="grd-actions">
        <div class="grd-action-group">
            <button type="button" class="grd-btn grd-btn--muted" data-bs-dismiss="modal">
                <i class="fa-solid fa-arrow-left"></i> Quay lại
            </button>
            @if(! $goodsReceipt->isCancelled())
                <button type="button" class="grd-btn" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> In phiếu
                </button>
            @endif
        </div>
        <div class="grd-action-group">
            @if($goodsReceipt->isDraft())
                <button type="button" class="grd-btn grd-btn--primary" data-goods-receipt-edit-trigger data-edit-url="{{ route('admin.goods-receipts.edit', $goodsReceipt->id) }}">
                    <i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa
                </button>
            @endif

            @if($goodsReceipt->isDraft())
                <button type="button" class="grd-btn" disabled title="Phiếu hiện đang được lưu ở trạng thái nháp">
                    <i class="fa-regular fa-floppy-disk"></i> Đã lưu nháp
                </button>
                <form method="POST" action="{{ route('admin.goods-receipts.complete', $goodsReceipt->id) }}" class="mb-0 d-inline-flex">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="grd-btn grd-btn--success"
                        onclick="return confirm('Xác nhận nhập kho phiếu {{ $goodsReceipt->code }}? Tồn kho sẽ được cập nhật ngay lập tức.')">
                        <i class="fa-solid fa-circle-check"></i> Xác nhận nhập kho
                    </button>
                </form>
                <button type="button"
                    class="grd-btn grd-btn--danger"
                    data-delete-url="{{ route('admin.goods-receipts.destroy', $goodsReceipt->id) }}"
                    data-delete-name="{{ $goodsReceipt->code }}"
                    data-delete-type="phiếu nhập kho">
                    <i class="fa-regular fa-circle-xmark"></i> Huỷ phiếu
                </button>
            @endif
        </div>
    </footer>
</div>
