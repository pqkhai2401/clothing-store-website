{{-- Nội dung chi tiết phiếu nhập kho — dùng chung cho panel trượt và trang chi tiết fallback. --}}
<div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
    <div>
        <h2 class="h5 fw-bold mb-1" style="color:#174761;">
            Phiếu nhập kho {{ $goodsReceipt->code }}
            @if($goodsReceipt->isCompleted())
                <span class="gr-badge gr-badge--completed">Hoàn tất</span>
            @else
                <span class="gr-badge gr-badge--draft">Nháp</span>
            @endif
        </h2>
        <p class="mb-0 text-muted" style="font-size:13px;">
            Tạo bởi {{ $goodsReceipt->creator->username ?? 'N/A' }} lúc {{ $goodsReceipt->created_at?->format('d/m/Y H:i') }}
        </p>
    </div>
    @if($goodsReceipt->isDraft())
        <form method="POST" action="{{ route('admin.goods-receipts.complete', $goodsReceipt->id) }}">
            @csrf @method('PATCH')
            <button type="submit" class="btn gr-btn-emerald fw-bold"
                onclick="return confirm('Hoàn tất phiếu nhập kho này sẽ cộng tồn kho ngay lập tức. Tiếp tục?')">
                <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
            </button>
        </form>
    @endif
</div>

<div class="card edit-card shadow-sm mb-4">
    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thông tin phiếu</span></div>
    <div class="card-body p-4">
        <div class="edit-field">
            <label>Nhà cung cấp</label>
            <div class="fw-bold">{{ $goodsReceipt->supplier->name ?? '—' }}</div>
            @if($goodsReceipt->supplier?->phone)
                <div class="text-muted" style="font-size:13px;">{{ $goodsReceipt->supplier->phone }}</div>
            @endif
        </div>
        <div class="edit-field">
            <label>Ghi chú</label>
            <div>{{ $goodsReceipt->note ?: '—' }}</div>
        </div>
        @if($goodsReceipt->isCompleted())
            <div class="edit-field mb-0">
                <label>Hoàn tất lúc</label>
                <div>{{ $goodsReceipt->completed_at?->format('d/m/Y H:i') }}</div>
            </div>
        @endif
    </div>
</div>

<div class="card edit-card shadow-sm mb-4">
    <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="gr-show-table mb-0">
                <thead>
                    <tr>
                        <th>Sản phẩm</th>
                        <th>Số lượng</th>
                        <th>Giá vốn</th>
                        <th>Thành tiền</th>
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
                                <div class="gr-show-product">
                                    <img class="gr-show-thumb" src="{{ $thumbUrl }}" alt="">
                                    <div>
                                        <div class="fw-bold">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                        <div class="text-muted" style="font-size:12px;">
                                            <span class="gr-show-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#ccc' }};"></span>
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
                        <td colspan="3" class="text-end fw-bold">Tổng giá trị phiếu nhập:</td>
                        <td class="fw-bold" style="color:#174761;font-size:15px;">
                            {{ number_format($goodsReceipt->total_amount, 0, ',', '.') }}đ
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
