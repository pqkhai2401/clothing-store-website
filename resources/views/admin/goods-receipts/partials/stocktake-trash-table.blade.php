<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle">
            <thead>
                <tr>
                    <th class="hk-cb-th">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:76px;">ID</th>
                    <th style="width:170px;">Mã phiếu kiểm</th>
                    <th>Ghi chú</th>
                    <th style="width:120px;">Số SP kiểm</th>
                    <th style="width:140px;">Trạng thái</th>
                    <th style="width:150px;">Người kiểm kê</th>
                    <th style="width:150px;">Người xóa</th>
                    <th style="width:140px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocktakes as $stocktake)
                    <tr>
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $stocktake->id }}">
                        </td>
                        <td style="opacity:.55;">{{ $stocktake->id }}</td>
                        <td class="fw-bold">{{ $stocktake->code }}</td>
                        <td>{{ $stocktake->note ?: '—' }}</td>
                        <td>{{ number_format($stocktake->items_count ?? 0) }}</td>
                        <td>
                            @if($stocktake->isApproved())
                                <span class="gr-badge gr-badge--completed">Đã duyệt</span>
                            @elseif($stocktake->isRejected())
                                <span class="gr-badge gr-badge--rejected">Đã hủy</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Chờ xử lý</span>
                            @endif
                        </td>
                        <td>{{ $stocktake->creator->username ?? 'N/A' }}</td>
                        <td>{{ $stocktake->deleter->username ?? 'N/A' }}</td>
                        <td>{{ $stocktake->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <form method="POST" action="{{ route('admin.stocktakes.restore', $stocktake->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="product-more-btn text-success d-inline-flex align-items-center justify-content-center"
                                        title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <button type="button"
                                    class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                    data-delete-url="{{ route('admin.stocktakes.forceDelete', $stocktake->id) }}"
                                    data-delete-name="{{ $stocktake->code }}"
                                    data-delete-type="phiếu kiểm kê (vĩnh viễn)"
                                    title="Xóa vĩnh viễn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="10" class="text-center py-5">
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
        'paginator' => $stocktakes,
        'itemLabel' => 'phiếu kiểm kê',
        'bulkRestoreUrl' => route('admin.stocktakes.bulkRestore'),
        'bulkDeleteUrl' => route('admin.stocktakes.bulkForceDelete'),
        'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
    ])
</div>

