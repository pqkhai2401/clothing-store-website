<div class="product-table-wrap size-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table size-table align-middle" id="sizeTable">
            <thead>
                <tr>
                    <th style="width:54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:76px;">
                        <button type="button" class="product-sort-btn" data-sort-key="id" data-sort-type="number">
                            ID <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:130px; min-width:130px;">
                        <button type="button" class="product-sort-btn" data-sort-key="name">
                            Tên kích thước <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:330px; min-width:330px;">
                        <button type="button" class="product-sort-btn" data-sort-key="category_group">
                            Nhóm danh mục <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn is-active" data-sort-key="sort_weight" data-sort-type="number">
                            Thứ tự hiển thị <span class="product-sort-icon">↑</span>
                        </button>
                    </th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn" data-sort-key="used_products_count" data-sort-type="number">
                            Số SP dùng <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:130px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:120px;">
                        <button type="button" class="product-sort-btn" data-sort-key="status">
                            Trạng thái <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sizes as $size)
                    @php
                        $groupLabel = isset($categoryGroupLabel)
                            ? $categoryGroupLabel($size->category_group)
                            : ($categoryGroups[$size->category_group] ?? $size->category_group);
                    @endphp
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
                        <td data-cell="category_group" data-sort-value="{{ $size->category_group }}">
                            <span class="size-group-badge">{{ $groupLabel }}</span>
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
                            @if((int) $size->status === 1)
                                <span class="status-badge status-badge--active">Hoạt động</span>
                            @else
                                <span class="status-badge status-badge--inactive">Ẩn</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <button type="button" class="dropdown-item"
                                        data-bs-toggle="modal" data-bs-target="#editSizeModal"
                                        data-edit-id="{{ $size->id }}"
                                        data-edit-name="{{ $size->name }}"
                                        data-edit-category-group="{{ $size->category_group }}"
                                        data-edit-sort-weight="{{ $size->sort_weight }}"
                                        data-edit-status="{{ (int) $size->status }}"
                                        data-edit-url="{{ route('admin.sizes.update', $size->id) }}">
                                        <i class="fa-regular fa-pen-to-square"></i> Sửa
                                    </button>
                                    <button type="button" class="dropdown-item text-danger"
                                        data-delete-url="{{ route('admin.sizes.destroy', $size->id) }}"
                                        data-delete-name="{{ $size->name }}"
                                        data-delete-type="kích thước">
                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="9" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có kích thước nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator' => $sizes,
        'itemLabel' => 'kích thước',
        'bulkDeleteUrl' => route('admin.sizes.bulkDelete'),
    ])
</div>
