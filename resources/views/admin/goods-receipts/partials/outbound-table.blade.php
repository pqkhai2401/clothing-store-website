<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="stockIssueTable">
            <thead>
                <tr>
                    <th class="hk-cb-th">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all" id="stockIssueCheckAll">
                    </th>
                    <th style="width:76px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↓</span>
                        </button>
                    </th>
                    <th style="width:140px;">Mã phiếu xuất</th>
                    <th style="width:130px;">Loại xuất kho</th>
                    <th style="width:130px;">Kho xuất</th>
                    <th>Lý do / Ghi chú</th>
                    <th style="width:100px;">Số lượng SP</th>
                    <th style="width:130px;">
                        <button type="button" class="product-sort-btn" data-sort-key="total_amount" data-sort-type="number">
                            Tổng GT xuất <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:130px;">Trạng thái</th>
                    <th style="width:120px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:120px;">Người tạo</th>
                    <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIssues as $issue)
                    <tr>
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $issue->id }}" data-status="{{ $issue->status }}">
                        </td>
                        <td style="opacity:.55;">{{ $issue->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('admin.stock-issues.show', $issue->id) }}"
                                data-stock-issue-show-trigger
                                data-show-url="{{ route('admin.stock-issues.show', $issue->id) }}"
                                onclick="event.preventDefault();">{{ $issue->code }}</a>
                        </td>
                        <td>
                            @php
                                $typeBadgeCss = [
                                    'sale' => 'text-bg-info',
                                    'return_supplier' => 'text-bg-warning',
                                    'adjustment' => 'text-bg-secondary',
                                    'damaged' => 'text-bg-danger',
                                    'transfer' => 'text-bg-dark',
                                ];
                            @endphp
                            <span class="badge {{ $typeBadgeCss[$issue->issue_type] ?? 'text-bg-light' }}" style="font-size:11px; font-weight:600;">
                                {{ \App\Models\StockIssue::ISSUE_TYPE_LABELS[$issue->issue_type] ?? $issue->issue_type }}
                            </span>
                        </td>
                        <td class="text-muted">{{ $issue->warehouse->name ?? '—' }}</td>
                        <td class="text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $issue->reason ?? $issue->note ?? '—' }}
                        </td>
                        <td class="fw-semibold">{{ number_format($issue->total_quantity ?? 0) }}</td>
                        <td class="fw-semibold">{{ number_format($issue->total_sale_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($issue->isCompleted())
                                <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
                            @elseif($issue->isCancelled())
                                <span class="gr-badge gr-badge--cancelled">Đã hủy</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $issue->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td>{{ $issue->creator->username ?? 'N/A' }}</td>
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
                                    <button type="button"
                                        class="product-more-btn d-inline-flex align-items-center justify-content-center text-dark"
                                        data-stock-issue-edit-trigger
                                        data-edit-url="{{ route('admin.stock-issues.edit', $issue->id) }}"
                                        title="Chỉnh sửa phiếu nháp">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                    </button>
                                @endif
                                @if($issue->isDraft() || $issue->isCancelled())
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
                        <td colspan="12" class="text-center py-5">
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
        'bulkDeleteUrl' => route('admin.stock-issues.bulkDelete'),
    ])
</div>
