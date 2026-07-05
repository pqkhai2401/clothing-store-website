<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="stockIssueTable">
            <thead>
                <tr>
                    <th style="width:76px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↓</span>
                        </button>
                    </th>
                    <th style="width:170px;">Mã phiếu xuất</th>
                    <th>Lý do xuất kho</th>
                    <th style="width:110px;">Số lượng SP</th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn" data-sort-key="total_amount" data-sort-type="number">
                            Tổng giá trị xuất <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">Trạng thái</th>
                    <th style="width:140px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIssues as $issue)
                    <tr>
                        <td style="opacity:.55;">{{ $issue->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('admin.stock-issues.show', $issue->id) }}"
                                data-stock-issue-show-trigger
                                data-show-url="{{ route('admin.stock-issues.show', $issue->id) }}"
                                onclick="event.preventDefault();">{{ $issue->code }}</a>
                        </td>
                        <td>{{ $issue->reason }}</td>
                        <td>{{ number_format($issue->items_quantity_sum ?? 0) }}</td>
                        <td class="fw-semibold">{{ number_format($issue->total_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($issue->isIssued())
                                <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
                            @else
                                <div class="hk-cat-filter gr-row-status-filter" id="rowStatusFilter{{ $issue->id }}" style="width:auto;flex:none;">
                                    <button type="button" class="hk-cat-trigger" style="min-height:30px;width:auto;white-space:nowrap;padding:0 10px;font-size:11px;">
                                        <span class="hk-cat-trigger-label">Nháp</span>
                                        <i class="fa-solid fa-chevron-down hk-cat-arrow" style="font-size:9px;"></i>
                                    </button>
                                    <div class="hk-cat-panel" hidden style="width:160px;">
                                        <div class="hk-cat-list">
                                            <button type="button" class="hk-cat-item is-active" data-value="draft">Nháp</button>
                                            <button type="button" class="hk-cat-item"
                                                data-value="issue"
                                                data-issue-url="{{ route('admin.stock-issues.issue', $issue->id) }}"
                                                data-issue-code="{{ $issue->code }}">
                                                Đã xuất kho
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>{{ $issue->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <button type="button"
                                    class="product-more-btn d-inline-flex align-items-center justify-content-center"
                                    data-stock-issue-show-trigger
                                    data-show-url="{{ route('admin.stock-issues.show', $issue->id) }}"
                                    title="Xem chi tiết">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                                @if($issue->isDraft())
                                    <button type="button" class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                        data-delete-url="{{ route('admin.stock-issues.destroy', $issue->id) }}"
                                        data-delete-name="{{ $issue->code }}"
                                        data-delete-type="phiếu xuất kho"
                                        title="Xóa">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-truck-ramp-box text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có phiếu xuất kho nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator' => $stockIssues,
        'itemLabel' => 'phiếu xuất kho',
    ])
</div>
