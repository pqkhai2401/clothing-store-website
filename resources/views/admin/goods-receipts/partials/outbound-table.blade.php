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
                            <a href="{{ route('admin.stock-issues.show', $issue->id) }}">{{ $issue->code }}</a>
                        </td>
                        <td>{{ $issue->reason }}</td>
                        <td>{{ number_format($issue->items_quantity_sum ?? 0) }}</td>
                        <td class="fw-semibold">{{ number_format($issue->total_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($issue->isIssued())
                                <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $issue->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <a href="{{ route('admin.stock-issues.show', $issue->id) }}" class="dropdown-item">
                                        <i class="fa-regular fa-eye"></i> Xem chi tiết
                                    </a>
                                    @if($issue->isDraft())
                                        <form method="POST" action="{{ route('admin.stock-issues.issue', $issue->id) }}" style="margin:0">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="dropdown-item"
                                                onclick="return confirm('Xuất kho phiếu này sẽ trừ tồn kho ngay lập tức. Tiếp tục?')">
                                                <i class="fa-solid fa-check"></i> Xuất kho ngay
                                            </button>
                                        </form>
                                        <div class="dropdown-divider my-1"></div>
                                        <button type="button" class="dropdown-item text-danger"
                                            data-delete-url="{{ route('admin.stock-issues.destroy', $issue->id) }}"
                                            data-delete-name="{{ $issue->code }}"
                                            data-delete-type="phiếu xuất kho">
                                            <i class="fa-regular fa-trash-can"></i> Xóa
                                        </button>
                                    @endif
                                </div>
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
