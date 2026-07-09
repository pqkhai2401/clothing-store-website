<div class="product-table-wrap size-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table size-table align-middle" id="sizeTable">
            <thead>
                <tr>
                    <th style="width:54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:90px;">
                        <button type="button" class="product-sort-btn" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:180px; min-width:180px;">
                        <button type="button" class="product-sort-btn" data-sort-key="name">
                            Tên kích thước <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:175px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="sort_weight" data-sort-type="number">
                            Thứ tự hiển thị <span class="product-sort-icon">↑</span>
                        </button>
                    </th>
                    <th style="width:170px;">
                        <button type="button" class="product-sort-btn" data-sort-key="used_products_count" data-sort-type="number">
                            Số SP dùng <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:160px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:170px;">
                        <button type="button" class="product-sort-btn" data-sort-key="status">
                            Trạng thái <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th class="text-end pe-4" style="width:120px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sizes as $size)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $size->id }}">
                        </td>
                        <td data-sort-value="{{ $size->id }}" class="text-muted">{{ $size->id }}</td>
                        <td data-cell="name" data-sort-value="{{ $size->name }}">
                            <a href="{{ route('admin.products.list', ['size_id' => $size->id]) }}"
                                class="fw-bold attribute-name-link"
                                title="Lọc sản phẩm theo size {{ $size->name }}">
                                {{ $size->name }}
                            </a>
                        </td>
                        <td data-cell="sort_weight" data-sort-value="{{ $size->sort_weight }}">
                            <span class="size-weight-chip">{{ $size->sort_weight }}</span>
                        </td>
                        <td data-cell="used_products_count" data-sort-value="{{ $size->used_products_count }}">
                            <a href="{{ route('admin.products.list', ['size_id' => $size->id]) }}"
                                class="size-count-link"
                                title="Xem sản phẩm đang dùng size {{ $size->name }}">
                                {{ number_format($size->used_products_count) }}
                            </a>
                        </td>
                        <td data-cell="created_at" data-sort-value="{{ $size->created_at?->format('Ymd') ?? '0' }}">
                            {{ $size->created_at?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td data-cell="status" data-sort-value="{{ (int) $size->status }}">
                            <div class="hk-cat-filter size-status-dropdown" data-size-id="{{ $size->id }}"
                                data-update-url="{{ route('admin.sizes.update', $size->id) }}"
                                data-name="{{ $size->name }}" data-sort-weight="{{ $size->sort_weight }}">
                                <button type="button" class="status-badge size-status-trigger {{ (int) $size->status === 1 ? 'status-badge--active' : 'status-badge--inactive' }}"
                                    data-value="{{ (int) $size->status }}" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="size-status-trigger-label">{{ (int) $size->status === 1 ? 'Hoạt động' : 'Ẩn' }}</span>
                                    <i class="fa-solid fa-chevron-down size-status-caret"></i>
                                </button>
                                <div class="hk-cat-panel size-status-panel" hidden>
                                    <div class="hk-cat-list" role="listbox">
                                        <button type="button" class="hk-cat-item {{ (int) $size->status === 1 ? 'is-active' : '' }}" data-value="1" data-css="status-badge--active">Hoạt động</button>
                                        <button type="button" class="hk-cat-item {{ (int) $size->status === 0 ? 'is-active' : '' }}" data-value="0" data-css="status-badge--inactive">Ẩn</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <button type="button" class="row-action-btn"
                                    data-bs-toggle="modal" data-bs-target="#editSizeModal"
                                    data-edit-id="{{ $size->id }}"
                                    data-edit-name="{{ $size->name }}"
                                    data-edit-sort-weight="{{ $size->sort_weight }}"
                                    data-edit-status="{{ (int) $size->status }}"
                                    data-edit-url="{{ route('admin.sizes.update', $size->id) }}"
                                    title="Sửa">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="row-action-btn"
                                    data-delete-url="{{ route('admin.sizes.destroy', $size->id) }}"
                                    data-delete-name="{{ $size->name }}"
                                    data-delete-type="kích thước"
                                    title="Xóa">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có kích thước nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2 size-pagination-bar">
    @include('layouts.components.pagination', [
        'paginator' => $sizes,
        'itemLabel' => 'kích thước',
        'bulkDeleteUrl' => route('admin.sizes.bulkDelete'),
    ])
</div>
