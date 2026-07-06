<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle">
            <thead>
                <tr>
                    <th class="hk-cb-th">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:76px;">ID</th>
                    <th style="width:170px;">Mã phiếu xuất</th>
                    <th>Lý do xuất kho</th>
                    <th style="width:150px;">Trạng thái</th>
                    <th style="width:160px;">Người tạo</th>
                    <th style="width:160px;">Người xóa</th>
                    <th style="width:140px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIssues as $issue)
                    <tr>
                        <td class="hk-cb-td">
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $issue->id }}">
                        </td>
                        <td style="opacity:.55;">{{ $issue->id }}</td>
                        <td class="fw-bold">{{ $issue->code }}</td>
                        <td>{{ $issue->reason }}</td>
                        <td>
                            @if($issue->isIssued())
                                <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $issue->creator->username ?? 'N/A' }}</td>
                        <td>{{ $issue->deleter->username ?? 'N/A' }}</td>
                        <td>{{ $issue->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex align-items-center gap-1">
                                <form method="POST" action="{{ route('admin.stock-issues.restore', $issue->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="product-more-btn text-success d-inline-flex align-items-center justify-content-center"
                                        title="Khôi phục">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                                <button type="button"
                                    class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                    data-delete-url="{{ route('admin.stock-issues.forceDelete', $issue->id) }}"
                                    data-delete-name="{{ $issue->code }}"
                                    data-delete-type="phiếu xuất kho (vĩnh viễn)"
                                    title="Xóa vĩnh viễn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="9" class="text-center py-5">
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
        'paginator' => $stockIssues,
        'itemLabel' => 'phiếu xuất kho',
        'bulkRestoreUrl' => route('admin.stock-issues.bulkRestore'),
        'bulkDeleteUrl' => route('admin.stock-issues.bulkForceDelete'),
        'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
    ])
</div>
