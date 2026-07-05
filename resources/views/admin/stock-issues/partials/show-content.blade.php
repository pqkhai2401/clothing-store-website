{{-- Nội dung chi tiết phiếu xuất kho — dùng chung cho panel trượt (offcanvas) và trang đầy đủ (fallback truy cập trực tiếp). --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
    <div>
        <h2 class="h5 fw-bold mb-1" style="color:#174761;">
            Phiếu xuất kho {{ $stockIssue->code }}
            @if($stockIssue->isIssued())
                <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
            @else
                <span class="gr-badge gr-badge--draft">Nháp</span>
            @endif
        </h2>
        <p class="mb-0 text-muted" style="font-size:13px;">
            Tạo bởi {{ $stockIssue->creator->name ?? 'N/A' }} lúc {{ $stockIssue->created_at?->format('d/m/Y H:i') }}
        </p>
    </div>
    @if($stockIssue->isDraft())
        <form method="POST" action="{{ route('admin.stock-issues.issue', $stockIssue->id) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn gr-btn-emerald fw-bold"
                onclick="return confirm('Xuất kho phiếu này sẽ trừ tồn kho ngay lập tức. Tiếp tục?')">
                <i class="fa-solid fa-check me-1"></i> Xuất kho ngay
            </button>
        </form>
    @endif
</div>

<div class="card edit-card shadow-sm mb-4">
    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thông tin phiếu</span></div>
    <div class="card-body p-4">
        <div class="edit-field">
            <label>Lý do xuất kho</label>
            <div class="fw-bold">{{ $stockIssue->reason }}</div>
        </div>
        @if($stockIssue->note)
            <div class="edit-field">
                <label>Ghi chú</label>
                <div>{{ $stockIssue->note }}</div>
            </div>
        @endif
        @if($stockIssue->isIssued())
            <div class="edit-field mb-0">
                <label>Xuất kho lúc</label>
                <div>{{ $stockIssue->issued_at?->format('d/m/Y H:i') }}</div>
            </div>
        @endif
    </div>
</div>

<div class="card edit-card shadow-sm mb-4">
    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm xuất kho</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="si-show-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
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
                            <td>{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                            <td class="fw-semibold">{{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}đ</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold">Tổng giá trị xuất kho:</td>
                        <td class="fw-bold" style="color:#0f9d58;font-size:15px;">
                            {{ number_format($stockIssue->total_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
