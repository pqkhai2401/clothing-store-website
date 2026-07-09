<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="goodsReceiptTable">
            <thead>
                <tr>
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
                    <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goodsReceipts as $receipt)
                    <tr>
                        <td style="opacity:.55;">{{ $receipt->id }}</td>
                        <td class="fw-bold">
                            <a href="{{ route('admin.goods-receipts.show', $receipt->id) }}">{{ $receipt->code }}</a>
                        </td>
                        <td>{{ $receipt->supplier->name ?? '—' }}</td>
                        <td>{{ number_format($receipt->items_count) }}</td>
                        <td class="fw-semibold">{{ number_format($receipt->total_amount, 0, ',', '.') }}đ</td>
                        <td>
                            @if($receipt->isCompleted())
                                <span class="gr-badge gr-badge--completed">Hoàn tất</span>
                            @else
                                <span class="gr-badge gr-badge--draft">Nháp</span>
                            @endif
                        </td>
                        <td>{{ $receipt->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <a href="{{ route('admin.goods-receipts.show', $receipt->id) }}" class="dropdown-item">
                                        <i class="fa-regular fa-eye"></i> Xem chi tiết
                                    </a>
                                    @if($receipt->isDraft())
                                        <form method="POST" action="{{ route('admin.goods-receipts.complete', $receipt->id) }}" style="margin:0">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="dropdown-item"
                                                onclick="return confirm('Hoàn tất phiếu nhập kho này sẽ cộng tồn kho ngay lập tức. Tiếp tục?')">
                                                <i class="fa-solid fa-check"></i> Hoàn tất nhập kho
                                            </button>
                                        </form>
                                        <div class="dropdown-divider my-1"></div>
                                        <button type="button" class="dropdown-item text-danger"
                                            data-delete-url="{{ route('admin.goods-receipts.destroy', $receipt->id) }}"
                                            data-delete-name="{{ $receipt->code }}"
                                            data-delete-type="phiếu nhập kho">
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
    ])
</div>
