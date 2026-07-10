<style>
.tooltip-container {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}

.tooltip-box {
    position: fixed;
    top: 0;
    left: 0;
    width: min(400px, calc(100vw - 24px));
    max-height: min(380px, calc(100vh - 32px));
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
    padding: 12px 14px;
    z-index: 3000;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.12s ease, visibility 0.12s ease;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    font-size: 14px;
    line-height: 1.4;
    text-align: left;
}

.stock-total {
    text-decoration-line: underline;
    text-decoration-style: dotted;
    text-underline-offset: 4px;
}

.tooltip-container.is-open .tooltip-box {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

.stock-tooltip-product,
.stock-tooltip-heading,
.stock-tooltip-size,
.stock-tooltip-total {
    font-weight: 700;
}

.stock-tooltip-product {
    margin-bottom: 4px;
    color: var(--stock-tip-heading);
    font-weight: 400;
    word-break: break-word;
}

.stock-tooltip-heading {
    margin-bottom: 8px;
    color: var(--stock-tip-heading);
}

.stock-tooltip-group {
    padding: 7px 0;
    border-top: 1px solid var(--stock-tip-divider);
}

.stock-tooltip-group:first-of-type {
    border-top: 0;
    padding-top: 0;
}

.stock-tooltip-size {
    margin-bottom: 4px;
    color: var(--stock-tip-heading);
}

.stock-tooltip-color {
    position: relative;
    display: flex;
    align-items: baseline;
    gap: 4px;
    padding: 2px 0 3px 18px;
}

.stock-tooltip-color::before {
    content: "•";
    position: absolute;
    left: 6px;
    top: 2px;
    color: var(--stock-tip-bullet);
}

.stock-tooltip-color-name {
    display: inline;
    color: var(--stock-tip-muted);
}

.stock-tooltip-count {
    display: inline;
    color: var(--stock-tip-count);
    font-weight: 800;
    text-align: left;
    text-decoration: none;
}

.stock-tooltip-count:hover {
    color: var(--stock-tip-link-hover);
    text-decoration: underline;
}

.stock-tooltip-total {
    display: flex;
    justify-content: flex-start;
    gap: 6px;
    margin-top: 7px;
    padding: 8px 10px;
    border: 1px solid var(--stock-tip-divider);
    border-radius: 8px;
    background: var(--stock-tip-total-bg);
    color: var(--stock-tip-heading);
    font-size: 13px;
}

html:not([data-theme="dark"]) .tooltip-box {
    --stock-tip-heading: #111827;
    --stock-tip-muted: #374151;
    --stock-tip-count: #0f172a;
    --stock-tip-divider: #e5e7eb;
    --stock-tip-bullet: #64748b;
    --stock-tip-total-bg: #eef2f7;
    --stock-tip-link-hover: #15803d;
    background: #ffffff;
    color: #111827;
    border: 1px solid #d7dde8;
}

html:not([data-theme="dark"]) .tooltip-box::-webkit-scrollbar-thumb {
    background: #cbd5e1;
}

html:not([data-theme="dark"]) .stock-tooltip-muted {
    color: #6b7280;
}

html:not([data-theme="dark"]) .stock-total {
    color: #111827 !important;
}

[data-theme="dark"] .tooltip-box {
    --stock-tip-heading: #f8fafc;
    --stock-tip-muted: #cbd5e1;
    --stock-tip-count: #ffffff;
    --stock-tip-divider: #2a3b59;
    --stock-tip-bullet: #93c5fd;
    --stock-tip-total-bg: rgba(148, 163, 184, 0.18);
    --stock-tip-link-hover: #86efac;
    background: #111827;
    color: #f8fafc;
    border: 1px solid #334155;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
}

[data-theme="dark"] .tooltip-box::-webkit-scrollbar-thumb {
    background: #475569;
}

[data-theme="dark"] .stock-tooltip-muted {
    color: #94a3b8;
}

[data-theme="dark"] .stock-total {
    color: #f8fafc !important;
}

.tooltip-box::-webkit-scrollbar {
    width: 6px;
}

.tooltip-box::-webkit-scrollbar-track {
    background: transparent;
}

.tooltip-box::-webkit-scrollbar-thumb {
    border-radius: 999px;
}
</style>

<div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="productTable">
                        <thead>
                            <tr>
                                <th style="width: 58px;">
                                    <input type="checkbox" class="form-check-input product-check hk-cb-all" id="productCheckAll">
                                </th>
                                <th style="width: 86px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th style="width: 76px;">Ảnh</th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên sản phẩm <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="category">
                                        Danh mục <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="brand">
                                        Thương hiệu <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="price" data-sort-type="number">
                                        Giá <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                 <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="cost_price" data-sort-type="number">
                                        Giá vốn<span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="stock" data-sort-type="number">
                                        Tổng tồn kho <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width: 130px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="status" data-sort-type="number">
                                        Trạng thái <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-center" style="width: 76px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="is_featured" data-sort-type="number">
                                        Nổi bật <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-end pe-4" style="width: 96px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check hk-cb-row product-row-check" value="{{ $product->id }}">
                                    </td>
                                    <td data-sort-value="{{ $product->id }}">{{ $product->id }}</td>

                                    <td>
                                        @if($product->thumbnail)
                                            <img src="{{ asset($product->thumbnail) }}" alt="{{ $product->name }}" class="product-thumb">
                                        @else
                                            <div class="product-thumb-placeholder">
                                                <i class="fa-regular fa-image"></i>
                                            </div>
                                        @endif
                                    </td>

                                    <td data-cell="name" data-sort-value="{{ $product->name }}">
                                        <div class="fw-bold text-dark">{{ $product->name }}</div>
                                    </td>

                                    <td data-cell="category" data-sort-value="{{ $product->category?->name ?? '' }}">
                                        <span class="fw-semibold">{{ $product->category?->name ?? '—' }}</span>
                                    </td>

                                    <td data-cell="brand" data-sort-value="{{ $product->brand?->name ?? '' }}">
                                        <span class="fw-semibold">{{ $product->brand?->name ?? '—' }}</span>
                                    </td>

                                    <td data-cell="price" data-sort-value="{{ $product->min_price ?? 0 }}">
                                        <div class="product-quickedit-view">
                                            @if($product->min_price !== null)
                                                @php
                                                    $minFinal = $product->discountedPrice((float) $product->min_price);
                                                    $maxFinal = $product->discountedPrice((float) $product->max_price);
                                                @endphp
                                                @if($product->hasActiveDiscount())
                                                    <div class="price-display">
                                                        @if($minFinal == $maxFinal)
                                                            <span class="price-sale">{{ number_format($minFinal, 0, ',', '.') }}₫</span>
                                                        @else
                                                            <span class="price-sale">{{ number_format($minFinal, 0, ',', '.') }}₫ - {{ number_format($maxFinal, 0, ',', '.') }}₫</span>
                                                        @endif
                                                        @if($product->min_price == $product->max_price)
                                                            <span class="price-original">{{ number_format($product->min_price, 0, ',', '.') }}₫</span>
                                                        @else
                                                            <span class="price-original">{{ number_format($product->min_price, 0, ',', '.') }}₫ - {{ number_format($product->max_price, 0, ',', '.') }}₫</span>
                                                        @endif
                                                    </div>
                                                @elseif($product->min_price == $product->max_price)
                                                    <span class="price-normal">{{ number_format($product->min_price, 0, ',', '.') }}₫</span>
                                                @else
                                                    <span class="price-normal fw-bold">{{ number_format($product->min_price, 0, ',', '.') }}₫ - {{ number_format($product->max_price, 0, ',', '.') }}₫</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Chưa có biến thể</span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Giá vốn --}}
                                    <td data-cell="cost_price" data-sort-value="{{ $product->min_cost_price ?? 0 }}">
                                        <span class="text-muted">
                                            @if($product->min_cost_price !== null)
                                                @if($product->min_cost_price == $product->max_cost_price)
                                                    {{ number_format($product->min_cost_price, 0, ',', '.') }}₫
                                                @else
                                                    {{ number_format($product->min_cost_price, 0, ',', '.') }}₫ - {{ number_format($product->max_cost_price, 0, ',', '.') }}₫
                                                @endif
                                            @else
                                                Chưa có biến thể
                                            @endif
                                        </span>
                                    </td>
                                    @php
                                        $totalStock = (int) ($product->product_variants_sum_stock ?? 0);
                                        $variantsBySize = $product->productVariants->groupBy(fn ($variant) => $variant->size?->name ?? 'No size');
                                    @endphp
                                    <td data-cell="stock" data-sort-value="{{ $totalStock }}">
                                        <div class="tooltip-container" tabindex="0" aria-label="Xem chi tiết tồn kho theo size và màu">
                                            <span class="{{ $totalStock > 0 ? 'fw-semibold text-dark' : 'text-danger fw-semibold' }} stock-total">
                                                {{ number_format($totalStock) }}
                                            </span>
                                            <i class="fa-regular fa-circle-question ms-1 text-muted"></i>

                                            <div class="tooltip-box">
                                                <h5 class="stock-tooltip-heading">
                                                    Chi tiết tồn kho theo size và màu
                                                </h5>
                                                <div class="stock-tooltip-product">
                                                    <strong>Tên sản phẩm:</strong> {{ $product->name }}
                                                </div>
                                                @forelse($variantsBySize as $sizeName => $variants)
                                                    <div class="stock-tooltip-group">
                                                        <div class="stock-tooltip-size">Size {{ $sizeName }}</div>
                                                        @foreach($variants as $variant)
                                                            <div class="stock-tooltip-color">
                                                                <span class="stock-tooltip-color-name">
                                                                    Màu: {{ $variant->color?->name ?? 'Không màu' }}
                                                                </span>
                                                                @if($variant->size_id)
                                                                    <a href="{{ route('admin.products.list', ['size_id' => $variant->size_id]) }}"
                                                                        class="stock-tooltip-count"
                                                                        title="Lọc sản phẩm theo size {{ $variant->size?->name ?? '' }}">
                                                                        {{ number_format($variant->stock) }}
                                                                    </a>
                                                                @else
                                                                    <span class="stock-tooltip-count">
                                                                        {{ number_format($variant->stock) }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @empty
                                                    <div class="stock-tooltip-muted">Chưa có biến thể</div>
                                                @endforelse

                                                <div class="stock-tooltip-total">
                                                    <span>Tổng tồn kho: </span>
                                                    <span>{{ number_format($totalStock) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-cell="status" data-sort-value="{{ $product->status ? 1 : 0 }}">
                                        <div class="form-check form-switch product-status-switch-wrap mb-0">
                                            <input type="checkbox" role="switch" class="form-check-input product-status-switch"
                                                data-toggle-status-url="{{ route('admin.products.toggleStatus', $product->id) }}"
                                                data-product-name="{{ $product->name }}"
                                                {{ $product->status ? 'checked' : '' }}>
                                            <label class="form-check-label product-status-switch-label">
                                                {{ $product->status ? 'Đang bán' : 'Ẩn' }}
                                            </label>
                                        </div>
                                    </td>

                                    <td class="text-center" data-sort-value="{{ $product->is_featured ? 1 : 0 }}">
                                        <button type="button"
                                            class="product-featured-star {{ $product->is_featured ? 'is-active' : '' }}"
                                            data-toggle-featured-url="{{ route('admin.products.toggleFeatured', $product->id) }}"
                                            data-product-name="{{ $product->name }}"
                                            title="{{ $product->is_featured ? 'Bỏ đánh dấu nổi bật' : 'Đánh dấu sản phẩm nổi bật' }}">
                                            <i class="fa-solid fa-star"></i>
                                        </button>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <button type="button"
                                                class="product-more-btn d-inline-flex align-items-center justify-content-center"
                                                data-product-edit-trigger
                                                data-edit-url="{{ route('admin.products.edit', $product->id) }}"
                                                title="Sửa">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="product-more-btn text-danger d-inline-flex align-items-center justify-content-center"
                                                data-delete-url="{{ route('admin.products.destroy', $product->id) }}"
                                                data-delete-name="{{ $product->name }}"
                                                data-delete-type="sản phẩm"
                                                title="Xóa">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="11" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px; display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có sản phẩm nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $products,
                    'itemLabel'     => 'sản phẩm',
                    'bulkDeleteUrl' => route('admin.products.bulkDelete'),
                ])
            </div>

