<style>
.category-count-tooltip {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}

.category-count-total {
    text-decoration-line: underline;
    text-decoration-style: dotted;
    text-underline-offset: 4px;
}

.category-count-box {
    position: fixed;
    top: 0;
    left: 0;
    width: min(340px, calc(100vw - 24px));
    max-height: min(320px, calc(100vh - 32px));
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    border-radius: 10px;
    padding: 10px 14px;
    z-index: 3000;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: opacity 0.12s ease, visibility 0.12s ease;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.18);
    font-size: 13px;
    line-height: 1.32;
    text-align: left;
}

.category-count-tooltip.is-open .category-count-box {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
}

.category-count-heading,
.category-count-row strong,
.category-count-summary {
    font-weight: 700;
}

.category-count-heading {
    margin: 0 0 6px;
    color: var(--cat-count-heading);
    font-size: 20px;
    line-height: 1.25;
}

.category-count-name {
    margin-bottom: 6px;
    color: var(--cat-count-muted);
    /* font-weight: ; */
    word-break: break-word;
}

.category-count-row {
    display: flex;
    align-items: baseline;
    gap: 4px;
    padding: 4px 0;
}

.category-count-row span {
    color: var(--cat-count-muted);
}

.category-count-row strong {
    color: var(--cat-count-value);
}

.category-count-summary {
    display: flex;
    justify-content: flex-start;
    gap: 6px;
    margin-top: 6px;
    padding: 7px 10px;
    border: 1px solid var(--cat-count-divider);
    border-radius: 8px;
    background: var(--cat-count-summary-bg);
    color: var(--cat-count-heading);
    font-size: 14px;
}

html:not([data-theme="dark"]) .category-count-box {
    --cat-count-heading: #111827;
    --cat-count-muted: #000000;
    --cat-count-value: #0f172a;
    --cat-count-divider: #e5e7eb;
    --cat-count-summary-bg: #eef2f7;
    background: #ffffff;
    color: #111827;
    border: 1px solid #d7dde8;
}

html:not([data-theme="dark"]) .category-count-total {
    color: #111827 !important;
}

[data-theme="dark"] .category-count-box {
    --cat-count-heading: #f8fafc;
    --cat-count-muted: #cbd5e1;
    --cat-count-value: #ffffff;
    --cat-count-divider: #2a3b59;
    --cat-count-summary-bg: rgba(148, 163, 184, 0.18);
    background: #111827;
    color: #f8fafc;
    border: 1px solid #334155;
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45);
}

[data-theme="dark"] .category-count-total {
    color: #f8fafc !important;
}
</style>

<div class="product-table-wrap category-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="catTable">
                        <thead>
                            <tr>
                                <th style="width:54px;">
                                    <input type="checkbox" class="form-check-input product-check hk-cb-all">
                                </th>
                                <th style="width:76px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th style="width:180px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên danh mục <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:130px;">Danh mục cha</th>
                                <th style="width:130px;">Slug</th>
                                <th style="width:140px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="products_count" data-sort-type="number">
                                        Số sản phẩm <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:140px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:160px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $category->id }}">
                                    </td>
                                    <td data-sort-value="{{ $category->id }}" style="opacity:.55;">{{ $category->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $category->name }}">
                                        @if(is_null($category->parent_id))
                                            <a href="{{ route('admin.products.list', ['parent_category_id' => $category->id]) }}"
                                               class="fw-bold attribute-name-link"
                                               title="Xem tất cả sản phẩm thuộc danh mục {{ $category->name }} và danh mục con">
                                                {{ $category->name }}
                                            </a>
                                            <span class="parent-tag mt-1">Cha</span>
                                        @else
                                            <a href="{{ route('admin.products.list', ['category_id' => $category->id]) }}"
                                               class="fw-bold attribute-name-link"
                                               title="Xem sản phẩm trong danh mục {{ $category->name }}">
                                                {{ $category->name }}
                                            </a>
                                            <span class="child-tag mt-1">Con</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $category->parentCategory?->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <code class="slug-code">{{ $category->slug }}</code>
                                    </td>
                                    @php
                                        $productsCount = (int) $category->products_count;
                                        $activeProductsCount = (int) ($category->active_products_count ?? 0);
                                        $inactiveProductsCount = max($productsCount - $activeProductsCount, 0);
                                        $childrenCount = (int) ($category->children_categories_count ?? 0);
                                    @endphp
                                    <td data-cell="products_count" data-sort-value="{{ $productsCount }}">
                                        <div class="category-count-tooltip" tabindex="0" aria-label="Xem chi tiết số sản phẩm trong danh mục">
                                            <span class="fw-semibold category-count-total">{{ number_format($productsCount) }}</span>
                                            <i class="fa-regular fa-circle-question ms-1 text-muted"></i>

                                            <div class="category-count-box">
                                                <h5 class="category-count-heading">Chi tiết số sản phẩm</h5>
                                                <div class="category-count-name"> <strong>Tên danh mục: </strong>{{ $category->name }}</div>

                                                <div class="category-count-row">
                                                    <span>Sản phẩm đang bán:</span>
                                                    <strong>{{ number_format($activeProductsCount) }}</strong>
                                                </div>
                                                <div class="category-count-row">
                                                    <span>Sản phẩm ẩn:</span>
                                                    <strong>{{ number_format($inactiveProductsCount) }}</strong>
                                                </div>
                                                <div class="category-count-row">
                                                    <span>Danh mục con:</span>
                                                    <strong>{{ number_format($childrenCount) }}</strong>
                                                </div>

                                                <div class="category-count-summary">
                                                    <span>Tổng sản phẩm:</span>
                                                    <span>{{ number_format($productsCount) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $category->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $category->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td data-cell="status" data-sort-value="{{ $category->status ? 1 : 0 }}">
                                        <div class="hk-cat-filter category-status-dropdown" data-category-id="{{ $category->id }}"
                                            data-toggle-url="{{ route('admin.categories.toggleStatus', $category->id) }}">
                                            <button type="button" class="status-badge category-status-trigger {{ $category->status ? 'status-badge--active' : 'status-badge--inactive' }}"
                                                data-value="{{ $category->status ? 1 : 0 }}" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="category-status-trigger-label">{{ $category->status ? 'Hoạt động' : 'Ngưng hoạt động' }}</span>
                                                <i class="fa-solid fa-chevron-down category-status-caret"></i>
                                            </button>
                                            <div class="hk-cat-panel category-status-panel" hidden>
                                                <div class="hk-cat-list" role="listbox">
                                                    <button type="button" class="hk-cat-item {{ $category->status ? 'is-active' : '' }}" data-value="1" data-css="status-badge--active">Hoạt động</button>
                                                    <button type="button" class="hk-cat-item {{ !$category->status ? 'is-active' : '' }}" data-value="0" data-css="status-badge--inactive">Ngưng hoạt động</button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <a href="{{ route('admin.categories.edit', $category->id) }}" class="row-action-btn" title="Sửa">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>
                                            <button type="button" class="row-action-btn"
                                                data-delete-url="{{ route('admin.categories.destroy', $category->id) }}"
                                                data-delete-name="{{ $category->name }}"
                                                data-delete-type="danh mục"
                                                title="Xóa">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có danh mục nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2 category-pagination-bar">
                @include('layouts.components.pagination', [
                    'paginator'     => $categories,
                    'itemLabel'     => 'danh mục',
                    'bulkDeleteUrl' => route('admin.categories.bulkDelete'),
                ])
            </div>
