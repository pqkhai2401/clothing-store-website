{{-- Nội dung chi tiết phiếu xuất kho — dùng chung cho panel trượt (offcanvas) và trang đầy đủ. --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
    <div>
        <h2 class="h5 fw-bold mb-1" style="color:#174761;">
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
    <div class="d-flex gap-2">
        @if($stockIssue->isDraft())
            {{-- Nút Chỉnh Sửa --}}
            <button type="button" class="btn btn-outline-dark btn-sm fw-bold px-3" style="border-radius:999px;"
                data-stock-issue-edit-trigger
                data-edit-url="{{ route('admin.stock-issues.edit', $stockIssue->id) }}">
                <i class="fa-regular fa-pen-to-square me-1"></i> Chỉnh sửa
            </button>

            {{-- Nút Hủy Phiếu --}}
            <form method="POST" action="{{ route('admin.stock-issues.cancel', $stockIssue->id) }}" class="d-inline"
                onsubmit="return confirm('Bạn có chắc chắn muốn hủy phiếu xuất kho này? Trạng thái sẽ không thể thay đổi sau khi hủy.')">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline-danger btn-sm fw-bold px-3" style="border-radius:999px;">
                    <i class="fa-solid fa-ban me-1"></i> Hủy phiếu
                </button>
            </form>

            {{-- Nút Hoàn Tất --}}
            <form method="POST" action="{{ route('admin.stock-issues.issue', $stockIssue->id) }}" class="d-inline"
                onsubmit="return confirm('Xác nhận hoàn tất xuất kho? Tồn kho thực tế sẽ bị trừ lập tức.')">
                @csrf @method('PATCH')
                <button type="submit" class="btn gr-btn-emerald btn-sm fw-bold px-3 text-white" style="border-radius:999px;">
                    <i class="fa-solid fa-check me-1"></i> Hoàn tất xuất kho
                </button>
            </form>
        @endif
    </div>
</div>

<div class="card edit-card shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold" style="font-size:14px;">Thông tin phiếu</span></div>
    <div class="card-body p-4">
        <table class="table table-borderless mb-0 si-detail-table" style="font-size:13px;">
            <tr>
                <td class="text-muted" style="width: 180px;">Loại xuất kho:</td>
                <td class="fw-bold">
                    {{ \App\Models\StockIssue::ISSUE_TYPE_LABELS[$stockIssue->issue_type] ?? $stockIssue->issue_type }}
                </td>
            </tr>
            <tr>
                <td class="text-muted">Kho xuất hàng:</td>
                <td>{{ $stockIssue->warehouse->name ?? '—' }}</td>
            </tr>
            @if($stockIssue->order)
                <tr>
                    <td class="text-muted">Đơn hàng liên quan:</td>
                    <td>
                        <a href="{{ route('admin.orders.detail', $stockIssue->order_id) }}" class="fw-bold" style="color: #000;">
                            {{ $stockIssue->order->order_code ?? 'Đơn hàng #'.$stockIssue->order_id }}
                        </a>
                    </td>
                </tr>
            @endif
            <tr>
                <td class="text-muted">Ngày xuất kho:</td>
                <td>{{ $stockIssue->issued_at ? $stockIssue->issued_at->format('d/m/Y H:i') : '—' }}</td>
            </tr>
            @if($stockIssue->reason)
                <tr>
                    <td class="text-muted">Lý do xuất:</td>
                    <td>{{ $stockIssue->reason }}</td>
                </tr>
            @endif
            @if($stockIssue->note)
                <tr>
                    <td class="text-muted">Ghi chú chi tiết:</td>
                    <td>{{ $stockIssue->note }}</td>
                </tr>
            @endif
            @if($stockIssue->isCompleted())
                <tr>
                    <td class="text-muted">Xác nhận bởi:</td>
                    <td>{{ $stockIssue->confirmer->username ?? 'N/A' }} lúc {{ $stockIssue->confirmed_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
            @if($stockIssue->isCancelled())
                <tr>
                    <td class="text-muted">Hủy bởi:</td>
                    <td>{{ $stockIssue->canceller->username ?? 'N/A' }} lúc {{ $stockIssue->cancelled_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @endif
        </table>
    </div>
</div>

<div class="card edit-card shadow-sm mb-4" style="border-radius:12px;">
    <div class="card-header bg-white"><span class="fw-bold" style="font-size:14px;">Sản phẩm xuất kho</span></div>
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
                        <th style="width:140px;">Thành tiền bán</th>
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
                                        <div class="fw-bold">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                        <div class="text-muted" style="font-size:12px;">
                                            <span class="si-show-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#ccc' }};"></span>
                                            {{ $variant?->color?->name }} · {{ $variant?->size?->name }} · {{ $variant?->sku }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($item->quantity) }}</td>
                            <td class="text-muted">{{ number_format($item->cost_price, 0, ',', '.') }}đ</td>
                            <td>{{ number_format($item->sale_price, 0, ',', '.') }}đ</td>
                            <td class="text-mutedfw-semibold">{{ number_format($item->total_cost, 0, ',', '.') }}đ</td>
                            <td class="fw-bold" style="color: #000;">{{ number_format($item->total_sale, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Tổng cộng vốn:</td>
                        <td class="fw-bold text-muted" colspan="2">
                            {{ number_format($stockIssue->total_cost_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="4" class="text-end fw-bold">Tổng cộng bán:</td>
                        <td class="fw-bold text-success" colspan="2" style="font-size:16px;">
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

<style>
.si-detail-table tr td { padding: 6px 0; }
.si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.si-show-table thead th { background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700; color: #374151; border-bottom: 1.5px solid #e5e7eb; }
.si-show-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; }
.si-show-product { display: flex; align-items: center; gap: 10px; }
.si-show-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; background: #f3f4f6; }
.si-show-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); }
</style>
