<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle">
            <thead>
                <tr>
                    <th class="hk-cb-th">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:76px;">ID</th>
                    <th style="width:170px;">Mã phiếu nhập</th>
                    <th>Nhà cung cấp</th>
                    <th style="width:120px;">Số SP</th>
                    <th style="width:150px;">Tổng giá trị</th>
                    <th style="width:140px;">Trạng thái</th>
                    <th style="width:150px;">Người tạo</th>
                    <th style="width:150px;">Người xóa</th>
                    <th style="width:140px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodsReceipts as $receipt)
                    <tr>
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $receipt->id }}">
                        </td>
                        <td style="opacity:.55;">{{ $receipt->id }}</td>
                        <td class="fw-bold">{{ $receipt->code }}</td>
                        <td>{{ $receipt->supplier->name ?? 'N/A' }}</td>
                        <td>{{ number_format($receipt->items_count ?? 0) }}</td>
                        <td class="fw-semibold">{{ number_format($receipt->total_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($receipt->isCompleted())
                                <span class="gr-badge gr-badge--completed">Hoàn tất</span>
                            @elseif($receipt->isAdjusted())
                                <span class="gr-badge gr-badge--adjusted">Đã điều chỉnh</span>
                            @elseif($receipt->isCancelled())
                                <span class="gr-badge gr-badge--cancelled">Đã hủy</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $receipt->creator->username ?? 'N/A' }}</td>
                        <td>{{ $receipt->deleter->username ?? 'N/A' }}</td>
                        <td>{{ $receipt->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <form method="POST" action="{{ route('admin.goods-receipts.restore', $receipt->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="product-more-btn text-success d-inline-flex align-items-center justify-content-center"
                                        title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <button type="button"
                                    class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                    data-delete-url="{{ route('admin.goods-receipts.forceDelete', $receipt->id) }}"
                                    data-delete-name="{{ $receipt->code }}"
                                    data-delete-type="phiếu nhập kho (vĩnh viễn)"
                                    title="Xóa vĩnh viễn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="11" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác trống</div>
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
        'bulkRestoreUrl' => route('admin.goods-receipts.bulkRestore'),
        'bulkDeleteUrl' => route('admin.goods-receipts.bulkForceDelete'),
        'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
    ])
</div>

