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
        <button type="button" class="sid-btn"
            data-print-url="{{ route('admin.stock-issues.print', $stockIssue->id) }}"
            data-print-title="Phiếu xuất kho {{ $stockIssue->code }}">
            <i class="fa-solid fa-print"></i> In phiếu
        </button>
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

