{{--
    Nội dung chi tiết phiếu kiểm kê — nạp qua AJAX vào #stocktakeDetailModalContent.
    Variable expected: $stocktake (đã eager-load creator, processor, items.productVariant...).
--}}
@php
    $negItems = $stocktake->items->filter(fn ($i) => $i->diff() < 0);
    $posItems = $stocktake->items->filter(fn ($i) => $i->diff() > 0);
    $negTotalQty = $negItems->sum(fn ($i) => $i->diff());
    $posTotalQty = $posItems->sum(fn ($i) => $i->diff());
    $negTotalValue = $negItems->sum(fn ($i) => $i->diffValue());
    $posTotalValue = $posItems->sum(fn ($i) => $i->diffValue());
    $netValue = $negTotalValue + $posTotalValue;
@endphp

<div class="modal-header border-bottom d-flex align-items-start gap-3">
    <div class="flex-grow-1">
        <h1 class="modal-title fw-bold mb-1" style="font-size:20px;color:#111827;">
            Chi tiết phiếu kiểm kê: #{{ $stocktake->code }}
        </h1>
        <p class="mb-0 text-muted" style="font-size:13px;">
            Xem đối chiếu số liệu tồn kho hệ thống và thực tế trước khi bấm duyệt cân bằng.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2 ms-auto">
        @if($stocktake->isApproved())
            <span class="gr-badge gr-badge--completed">Đã duyệt</span>
        @elseif($stocktake->isRejected())
            <span class="gr-badge gr-badge--out-of-stock">Đã hủy</span>
        @else
            <span class="gr-badge gr-badge--draft">Chờ xử lý</span>
        @endif
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
    </div>
</div>

<div class="modal-body">
    <div class="card stk-info-card mb-4">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <span class="stk-inline-label d-block mb-1">Người kiểm kê</span>
                    <div class="fw-bold" style="font-size:14px;">{{ $stocktake->creator->username ?? 'N/A' }}</div>
                </div>
                <div class="col-md-3">
                    <span class="stk-inline-label d-block mb-1">Ngày lập phiếu</span>
                    <div class="fw-bold" style="font-size:14px;">{{ $stocktake->created_at?->format('d/m/Y H:i') }}</div>
                </div>
                <div class="col-md-3">
                    <span class="stk-inline-label d-block mb-1">Người duyệt</span>
                    @if($stocktake->processor)
                        <div class="fw-bold" style="font-size:14px;">{{ $stocktake->processor->username }}</div>
                        <div class="text-muted" style="font-size:12px;">{{ $stocktake->processed_at?->format('d/m/Y H:i') }}</div>
                    @else
                        <div class="fw-semibold text-muted" style="font-size:14px;">Chưa duyệt</div>
                    @endif
                </div>
                <div class="col-md-3">
                    <span class="stk-inline-label d-block mb-1">Ghi chú</span>
                    <div class="fw-semibold" style="font-size:14px;">{{ $stocktake->note ?: '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="gr-table-wrap mb-4">
        <table class="gr-table">
            <thead>
                <tr>
                    <th>Sản phẩm / SKU</th>
                    <th style="width:80px;">Đơn vị</th>
                    <th style="width:110px;">Tồn hệ thống</th>
                    <th style="width:110px;">Tồn thực tế</th>
                    <th style="width:100px;">Chênh lệch</th>
                    <th style="width:130px;">Thành tiền lệch</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stocktake->items as $item)
                    @php
                        $variant = $item->productVariant;
                        $thumb = $variant?->product?->thumbnail;
                        $thumbUrl = $thumb
                            ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                            : 'https://placehold.co/80x80?text=No+Image';
                        $diff = $item->diff();
                        $diffValue = $item->diffValue();
                    @endphp
                    <tr>
                        <td>
                            <div class="gr-row-product">
                                <img class="gr-row-thumb" src="{{ $thumbUrl }}" alt="">
                                <div>
                                    <div class="gr-row-name">
                                        {{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}
                                        @if($variant?->color?->name || $variant?->size?->name)
                                            ({{ $variant?->color?->name }}{{ $variant?->color?->name && $variant?->size?->name ? ' - ' : '' }}{{ $variant?->size?->name }})
                                        @endif
                                    </div>
                                    <div class="gr-row-sub">SKU: {{ $variant?->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>cái</td>
                        <td class="stk-system-stock">{{ $item->system_stock }}</td>
                        <td class="fw-bold">{{ $item->actual_stock }}</td>
                        <td>
                            @if($diff < 0)
                                <span class="stk-diff-badge stk-diff-badge--neg">{{ $diff }}</span>
                            @elseif($diff > 0)
                                <span class="stk-diff-badge stk-diff-badge--pos">+{{ $diff }}</span>
                            @else
                                <span class="stk-diff-badge stk-diff-badge--zero">0</span>
                            @endif
                        </td>
                        <td class="fw-bold {{ $diff < 0 ? 'text-danger' : ($diff > 0 ? '' : '') }}" style="{{ $diff > 0 ? 'color:#16a34a;' : '' }}">
                            {{ $diff > 0 ? '+' : '' }}{{ number_format($diffValue, 0, ',', '.') }}đ
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="stkd-summary-bar">
        <div class="stkd-summary-item">
            <span class="stkd-summary-item-label">Tổng lệch âm:</span>
            <span class="stkd-summary-item-value text-danger">{{ $negTotalQty }} cái ({{ number_format($negTotalValue, 0, ',', '.') }}đ)</span>
        </div>
        <div class="stkd-summary-item">
            <span class="stkd-summary-item-label">Tổng lệch dương:</span>
            <span class="stkd-summary-item-value" style="color:#16a34a;">+{{ $posTotalQty }} cái (+{{ number_format($posTotalValue, 0, ',', '.') }}đ)</span>
        </div>
        <div class="stkd-summary-item stkd-summary-item--net">
            <span class="stkd-summary-item-label">Tổng chênh lệch thực tế:</span>
            <span class="stkd-summary-net-value">{{ $netValue >= 0 ? '+' : '' }}{{ number_format($netValue, 0, ',', '.') }}đ</span>
        </div>
    </div>

    @if($stocktake->isApproved())
        <div class="alert alert-success mt-3 mb-0" style="font-size:13px;">
            Đã duyệt cân bằng kho bởi {{ $stocktake->processor->username ?? 'N/A' }} lúc {{ $stocktake->processed_at?->format('d/m/Y H:i') }}.
            @if($stocktake->stock_issue_id)
                Đã tự động sinh <a href="{{ route('admin.stock-issues.show', $stocktake->stock_issue_id) }}" target="_blank">phiếu xuất kho</a>.
            @endif
            @if($stocktake->goods_receipt_id)
                Đã tự động sinh <a href="{{ route('admin.goods-receipts.show', $stocktake->goods_receipt_id) }}" target="_blank">phiếu nhập kho</a>.
            @endif
        </div>
    @elseif($stocktake->isRejected())
        <div class="alert alert-danger mt-3 mb-0" style="font-size:13px;">
            Đã hủy bỏ bởi {{ $stocktake->processor->username ?? 'N/A' }} lúc {{ $stocktake->processed_at?->format('d/m/Y H:i') }}.
        </div>
    @endif
</div>

<div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
    <button type="button" class="btn btn-light border fw-semibold stk-footer-btn" data-bs-dismiss="modal">
        Đóng
    </button>
    @if($stocktake->isPending())
        <button type="button" class="btn btn-outline-danger fw-semibold stk-footer-btn"
            data-stocktake-reject-trigger
            data-reject-url="{{ route('admin.stocktakes.reject', $stocktake->id) }}"
            data-code="{{ $stocktake->code }}">
            Hủy bỏ phiếu kiểm
        </button>
        <button type="button" class="btn gr-btn-emerald fw-semibold stk-footer-btn"
            data-stocktake-approve-trigger
            data-approve-url="{{ route('admin.stocktakes.approve', $stocktake->id) }}"
            data-code="{{ $stocktake->code }}">
            <i class="fa-solid fa-check me-1"></i> Xác nhận cân bằng kho
        </button>
    @endif
</div>
