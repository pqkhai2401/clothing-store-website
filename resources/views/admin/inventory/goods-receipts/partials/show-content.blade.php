{{-- Chi tiết phiếu nhập kho — cùng cấu trúc với chi tiết phiếu xuất kho (stock-issues/partials/show-content) --}}
@php
    $sourceType  = $goodsReceipt->source_type ?? \App\Models\GoodsReceipt::SOURCE_TYPE_SUPPLIER;
    $receiptType = $goodsReceipt->receipt_type ?? \App\Models\GoodsReceipt::RECEIPT_TYPE_PURCHASE;
    $importType  = match ($receiptType) {
        \App\Models\GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT => 'Điều chỉnh kiểm kê',
        \App\Models\GoodsReceipt::RECEIPT_TYPE_RETURN     => 'Nhập trả hàng',
        \App\Models\GoodsReceipt::RECEIPT_TYPE_INITIAL    => 'Nhập tồn đầu kỳ',
        default                                           => 'Nhập hàng nhà cung cấp',
    };
    $importReason = $goodsReceipt->receipt_reason
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_ADJUSTMENT ? 'Cân bằng tồn kho sau kiểm kê' : null)
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_RETURN ? 'Nhập trả hàng từ khách hàng' : null)
        ?? ($receiptType === \App\Models\GoodsReceipt::RECEIPT_TYPE_INITIAL ? 'Nhập tồn kho đầu kỳ' : null);
    $warehouseName = $goodsReceipt->warehouse?->full_name ?? $goodsReceipt->warehouse?->name ?? '—';
    $supplierName  = $goodsReceipt->supplier->name ?? '—';
    $staffName     = $goodsReceipt->creator->username ?? $goodsReceipt->creator->name ?? 'N/A';
    $totalQuantity = $goodsReceipt->items->sum('quantity');
@endphp

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
    .sid-uniform-13, .sid-uniform-13 * { font-size: 13px !important; }
    .si-detail-table tr td { padding: 6px 0; }
    .si-detail-table td { color: #1f2937 !important; }
    .si-detail-table td:first-child { color: #6b7280 !important; }
    .si-detail-table td a { color: #1f2937 !important; text-decoration: underline; }
    .si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .si-show-table thead th { background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700; color: #374151; border-bottom: 1.5px solid #e5e7eb; }
    .si-show-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; color: #1f2937 !important; }
    .si-show-product { display: flex; align-items: center; gap: 10px; }
    .si-show-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; background: #f3f4f6; }
    .si-show-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); }
    @media (max-width: 768px) {
        .sid-actions { position: static; margin: 20px 0 0; padding: 12px 0 0; }
        .sid-action-group { width: 100%; }
        .sid-btn { flex: 1 1 auto; }
    }
    </style>
@endonce

<div class="mb-3">
    <h2 class="h5 fw-bold mb-1" style="color:#000;">
        Phiếu nhập kho {{ $goodsReceipt->code }}
        @if($goodsReceipt->isCompleted())
            <span class="gr-badge gr-badge--completed">Đã nhập kho</span>
        @elseif($goodsReceipt->isAdjusted())
            <span class="gr-badge gr-badge--completed">Đã điều chỉnh</span>
        @elseif($goodsReceipt->isCancelled())
            <span class="gr-badge gr-badge--cancelled">Đã hủy</span>
        @else
            <span class="gr-badge gr-badge--draft">Nháp</span>
        @endif
    </h2>
    <p class="mb-0 text-muted" style="font-size:13px;">
        Tạo bởi {{ $staffName }} lúc {{ $goodsReceipt->created_at?->format('d/m/Y H:i') }}
    </p>
</div>

<div class="card edit-card shadow-sm mb-4 sid-uniform-13" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold">Thông tin phiếu</span></div>
    <div class="card-body p-4">
        <table class="table table-borderless mb-0 si-detail-table">
            <tr>
                <td style="width: 180px;">Loại nhập kho:</td>
                <td>{{ $importType }}</td>
            </tr>
            <tr>
                <td>Nguồn nhập:</td>
                <td>{{ $sourceType === \App\Models\GoodsReceipt::SOURCE_TYPE_INTERNAL ? 'Nội bộ' : 'Nhà cung cấp' }}</td>
            </tr>
            @if($sourceType !== \App\Models\GoodsReceipt::SOURCE_TYPE_INTERNAL)
                <tr>
                    <td>Nhà cung cấp:</td>
                    <td>{{ $supplierName }}</td>
                </tr>
            @endif
            <tr>
                <td>Kho nhận hàng:</td>
                <td>{{ $warehouseName }}</td>
            </tr>
            <tr>
                <td>Ngày nhập kho:</td>
                <td>{{ ($goodsReceipt->received_at ?? $goodsReceipt->completed_at ?? $goodsReceipt->created_at)?->format('d/m/Y H:i') ?? '—' }}</td>
            </tr>
            @if($importReason)
                <tr>
                    <td>Lý do nhập:</td>
                    <td>{{ $importReason }}</td>
                </tr>
            @endif
            @if($goodsReceipt->note)
                <tr>
                    <td>Ghi chú chi tiết:</td>
                    <td>{{ $goodsReceipt->note }}</td>
                </tr>
            @endif
            @if($goodsReceipt->isCompleted() || $goodsReceipt->isAdjusted())
                <tr>
                    <td>Xác nhận bởi:</td>
                    <td>{{ $goodsReceipt->confirmer->username ?? 'N/A' }} lúc {{ ($goodsReceipt->confirmed_at ?? $goodsReceipt->completed_at)?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
            @if($goodsReceipt->isCancelled())
                <tr>
                    <td>Hủy bởi:</td>
                    <td>{{ $goodsReceipt->canceller->username ?? 'N/A' }} lúc {{ $goodsReceipt->cancelled_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
        </table>
    </div>
</div>

<div class="card edit-card shadow-sm mb-4 sid-uniform-13" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold">Sản phẩm nhập kho</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="si-show-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th style="width:110px;">Số lượng</th>
                        <th style="width:150px;">Giá vốn (đ)</th>
                        <th style="width:160px;">Thành tiền</th>
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
                            <td class="fw-semibold">{{ number_format($item->quantity * $item->cost_price, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end" style="color:#64748b;font-weight:700;padding:12px 12px 8px;">Tổng số lượng:</td>
                        <td class="text-end" style="color:#475569;font-weight:800;padding:12px 12px 8px;">{{ number_format($totalQuantity) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="text-end" style="color:#0f172a;font-weight:800;padding:10px 12px 14px;border-top:1px solid #e5e7eb;font-size:15px !important;">Tổng tiền nhập:</td>
                        <td class="text-end" style="color:#000;font-weight:850;padding:10px 12px 14px;border-top:1px solid #e5e7eb;font-size:20px !important;">
                            {{ number_format($goodsReceipt->total_amount, 0, ',', '.') }}đ
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
        @if($goodsReceipt->logs->isEmpty())
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
                @foreach($goodsReceipt->logs->sortByDesc('created_at') as $log)
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
        <button type="button" class="sid-btn"
            data-print-url="{{ route('admin.goods-receipts.print', $goodsReceipt->id) }}"
            data-print-title="Phiếu nhập kho {{ $goodsReceipt->code }}">
            <i class="fa-solid fa-print"></i> In phiếu
        </button>
    </div>
    @if($goodsReceipt->isDraft())
        <div class="sid-action-group">
            <button type="button" class="sid-btn sid-btn--primary"
                data-goods-receipt-edit-trigger
                data-edit-url="{{ route('admin.goods-receipts.edit', $goodsReceipt->id) }}">
                <i class="fa-regular fa-pen-to-square"></i> Chỉnh sửa
            </button>

            <button type="button" class="sid-btn" disabled title="Phiếu hiện đang được lưu ở trạng thái nháp">
                <i class="fa-regular fa-floppy-disk"></i> Đã lưu nháp
            </button>

            <form method="POST" action="{{ route('admin.goods-receipts.complete', $goodsReceipt->id) }}" class="mb-0 d-inline-flex">
                @csrf @method('PATCH')
                <button type="button" class="sid-btn sid-btn--success"
                    onclick="window.showConfirm({title: 'Xác nhận', message: 'Xác nhận nhập kho phiếu {{ $goodsReceipt->code }}? Tồn kho sẽ được cập nhật ngay lập tức.', type: 'warning'}).then(ok => { if(ok) this.closest('form').submit(); })">
                    <i class="fa-solid fa-circle-check"></i> Xác nhận nhập kho
                </button>
            </form>

            <button type="button" class="sid-btn sid-btn--danger"
                data-delete-url="{{ route('admin.goods-receipts.destroy', $goodsReceipt->id) }}"
                data-delete-name="{{ $goodsReceipt->code }}"
                data-delete-type="phiếu nhập kho">
                <i class="fa-regular fa-circle-xmark"></i> Huỷ phiếu
            </button>
        </div>
    @endif
</footer>

