@php
    $lowStockThreshold = \App\Http\Controllers\Admin\GoodsReceiptController::LOW_STOCK_THRESHOLD;
@endphp

<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="inventoryOverviewTable">
            <thead>
                <tr>
                    <th style="width:150px;">SKU</th>
                    <th style="width:340px;">Sản phẩm</th>
                    <th style="width:150px;">Biến thể</th>
                    <th style="width:120px;">
                        <button type="button" class="product-sort-btn" data-sort-key="cost_price" data-sort-type="number">
                            Giá vốn <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:100px;">
                        <button type="button" class="product-sort-btn" data-sort-key="stock" data-sort-type="number">
                            Tồn kho <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:130px;">Trạng thái</th>
                    <th class="text-end pe-3" style="width:80px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($variants as $variant)
                    @php
                        $thumb = $variant->product?->thumbnail;
                        $thumbUrl = $thumb
                            ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                            : 'https://placehold.co/80x80?text=No+Image';

                        if ($variant->stock <= 0) {
                            $statusLabel = 'Hết hàng'; $statusClass = 'gr-badge--out-of-stock';
                        } elseif ($variant->stock <= $lowStockThreshold) {
                            $statusLabel = 'Sắp hết hàng'; $statusClass = 'gr-badge--low-stock';
                        } else {
                            $statusLabel = 'Còn hàng'; $statusClass = 'gr-badge--in-stock';
                        }
                    @endphp
                    <tr>
                        <td class="fw-semibold" style="opacity:.8;">{{ $variant->sku }}</td>
                        <td>
                            <div class="gr-ov-product">
                                <img class="gr-ov-thumb" src="{{ $thumbUrl }}" alt="">
                                <span class="gr-ov-name fw-bold">{{ $variant->product?->name ?? 'Sản phẩm đã xóa' }}</span>
                            </div>
                        </td>
                        <td class="gr-ov-variant">
                            Màu: <strong>{{ $variant->color?->name ?? '—' }}<br></strong>
                            Size: <strong>{{ $variant->size?->name ?? '—' }}</strong>
                        </td>
                        <td>{{ number_format($variant->cost_price, 0, ',', '.') }}đ</td>
                        <td class="{{ $variant->stock <= 0 ? 'text-danger fw-bold' : '' }}">
                            {{ number_format($variant->stock) }}
                        </td>
                        <td>
                            <span class="gr-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <button type="button"
                                class="product-more-btn"
                                data-stock-card-trigger
                                data-stock-card-url="{{ route('admin.goods-receipts.stockCard', $variant->id) }}"
                                title="Xem thẻ kho">
                                <i class="fa-regular fa-rectangle-list"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-boxes-stacked text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Không có sản phẩm nào trong kho</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator' => $variants,
        'itemLabel' => 'sản phẩm',
    ])
</div>
