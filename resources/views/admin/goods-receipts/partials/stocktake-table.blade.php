<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="stocktakeTable">
            <thead>
                <tr>
                    <th style="width:76px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↓</span>
                        </button>
                    </th>
                    <th style="width:170px;">Mã phiếu kiểm</th>
                    <th>Ghi chú</th>
                    <th style="width:110px;">Số SP kiểm</th>
                    <th style="width:150px;">Trạng thái</th>
                    <th style="width:140px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày lập <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">Người kiểm kê</th>
                    <th style="width:150px;">Người duyệt</th>
                    <th class="text-end pe-4" style="width:80px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocktakes as $stocktake)
                    <tr>
                        <td style="opacity:.55;">{{ $stocktake->id }}</td>
                        <td class="fw-bold">{{ $stocktake->code }}</td>
                        <td>{{ $stocktake->note ?: '—' }}</td>
                        <td>{{ number_format($stocktake->items_count ?? 0) }}</td>
                        <td>
                            @if($stocktake->isApproved())
                                <span class="gr-badge gr-badge--completed">Đã duyệt</span>
                            @elseif($stocktake->isRejected())
                                <span class="gr-badge gr-badge--out-of-stock">Đã hủy</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Chờ xử lý</span>
                            @endif
                        </td>
                        <td>{{ $stocktake->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $stocktake->creator->username ?? 'N/A' }}</td>
                        <td>
                            @if($stocktake->processor)
                                <div class="fw-semibold">{{ $stocktake->processor->username }}</div>
                                <div class="text-muted" style="font-size:12px;">{{ $stocktake->processed_at?->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="text-muted">Chưa duyệt</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button type="button"
                                class="product-more-btn d-inline-flex align-items-center justify-content-center"
                                data-stocktake-show-trigger
                                data-show-url="{{ route('admin.stocktakes.show', $stocktake->id) }}"
                                title="Xem chi tiết">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="9" class="text-center py-5">
                            <i class="fa-solid fa-clipboard-check text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có phiếu kiểm kê nào</div>
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
    ])
</div>
