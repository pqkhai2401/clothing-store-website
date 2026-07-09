<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="goodsReceiptTable">
            <thead>
                <tr>
                    <th class="hk-cb-th">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all" id="goodsReceiptCheckAll">
                    </th>
                    <th style="width:76px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↓</span>
                        </button>
                    </th>
                    <th style="width:160px;">Mã phiếu</th>
                    <th>Nhà cung cấp</th>
                    <th style="width:110px;">Số SP</th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn" data-sort-key="total_amount" data-sort-type="number">
                            Tổng giá trị <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:120px;">Trạng thái</th>
                    <th style="width:140px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">Người tạo</th>
                    <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodsReceipts as $receipt)
                    <tr>
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $receipt->id }}" data-status="{{ $receipt->status }}">
                        </td>
                        <td style="opacity:.55;">{{ $receipt->id }}</td>
                        <td class="fw-bold">{{ $receipt->code }}</td>
                        <td>{{ $receipt->supplier->name ?? '—' }}</td>
                        <td>{{ number_format($receipt->items_count) }}</td>
                        <td class="fw-semibold">{{ number_format($receipt->total_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($receipt->isCompleted())
                                <span class="gr-badge gr-badge--completed">Hoàn tất</span>
                            @elseif($receipt->isAdjusted())
                                <span class="gr-badge gr-badge--out-of-stock">Đã điều chỉnh</span>
                            @elseif($receipt->status === 'cancelled')
                                <span class="gr-badge gr-badge--cancelled">Đã hủy</span>
                            @else
                                <button type="button" class="gr-row-status-trigger"
                                    data-row-status-trigger
                                    data-status-action="complete"
                                    data-status-url="{{ route('admin.goods-receipts.complete', $receipt->id) }}"
                                    data-status-code="{{ $receipt->code }}"
                                    data-status-confirm="Hoàn tất phiếu nhập kho &quot;{{ $receipt->code }}&quot; sẽ cộng tồn kho ngay lập tức. Tiếp tục?">
                                    <span>Nháp</span>
                                    <i class="fa-solid fa-chevron-down" style="font-size:9px;"></i>
                                </button>
                            @endif
                        </td>
                        <td>{{ $receipt->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $receipt->creator->username ?? 'N/A' }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button"
                                    class="product-more-btn d-inline-flex align-items-center justify-content-center"
                                    data-goods-receipt-show-trigger
                                    data-show-url="{{ route('admin.goods-receipts.show', $receipt->id) }}"
                                    title="Xem chi tiết">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @if($receipt->isDraft())
                                    <button type="button"
                                        class="product-more-btn d-inline-flex align-items-center justify-content-center text-dark"
                                        data-goods-receipt-edit-trigger
                                        data-edit-url="{{ route('admin.goods-receipts.edit', $receipt->id) }}"
                                        title="Chỉnh sửa phiếu nháp">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                @endif
                                @if($receipt->isDraft())
                                    <button type="button" class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                        data-delete-url="{{ route('admin.goods-receipts.destroy', $receipt->id) }}"
                                        data-delete-name="{{ $receipt->code }}"
                                        data-delete-type="phiếu nhập kho"
                                        title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="10" class="text-center py-5">
                            <i class="fa-solid fa-box-open text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có phiếu nhập kho nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator' => $goodsReceipts,
        'itemLabel' => 'phiếu nhập kho',
        'bulkDeleteUrl' => route('admin.goods-receipts.bulkDelete'),
    ])
</div>
