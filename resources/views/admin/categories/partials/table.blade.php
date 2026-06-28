<div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="catTable">
                        <thead>
                            <tr>
                                <th style="width:54px;">
                                    <input type="checkbox" class="form-check-input product-check" id="catCheckAll">
                                </th>
                                <th style="width:76px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên danh mục <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:190px;">Danh mục cha</th>
                                <th style="width:200px;">Slug</th>
                                <th style="width:130px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="products_count" data-sort-type="number">
                                        Số sản phẩm <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:130px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:120px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check cat-row-check" value="{{ $category->id }}">
                                    </td>
                                    <td data-sort-value="{{ $category->id }}" style="opacity:.55;">{{ $category->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $category->name }}">
                                        <div class="fw-bold text-dark">{{ $category->name }}</div>
                                        @if(is_null($category->parent_id))
                                            <span class="parent-tag mt-1">Cha</span>
                                        @else
                                            <span class="child-tag mt-1">Con</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $category->parentCategory?->name ?? 'Danh mục gốc' }}</span>
                                    </td>
                                    <td>
                                        <code class="slug-code">{{ $category->slug }}</code>
                                    </td>
                                    <td data-cell="products_count" data-sort-value="{{ $category->products_count }}">
                                        <span class="fw-semibold">{{ number_format($category->products_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $category->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $category->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="status-badge status-badge--active">Hoạt động</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.categories.destroy', $category->id) }}"
                                                    data-delete-name="{{ $category->name }}"
                                                    data-delete-type="danh mục">
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
                                        <div class="fw-semibold text-muted">Chưa có danh mục nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $categories,
                    'itemLabel'     => 'danh mục',
                    'bulkDeleteUrl' => route('admin.categories.bulkDelete'),
                ])
            </div>