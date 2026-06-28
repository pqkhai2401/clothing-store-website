<div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="sizeTable">
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
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên kích thước <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:200px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="variants_count" data-sort-type="number">
                                        Số biến thể SP <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:140px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:120px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sizes as $size)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $size->id }}">
                                    </td>
                                    <td data-sort-value="{{ $size->id }}" style="opacity:.55;">{{ $size->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $size->name }}">
                                        <div class="fw-bold text-dark">{{ $size->name }}</div>
                                    </td>
                                    <td data-cell="variants_count" data-sort-value="{{ $size->product_variants_count }}">
                                        <span class="fw-semibold">{{ number_format($size->product_variants_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $size->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $size->created_at?->format('d/m/Y') ?? '—' }}
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
                                                <a href="{{ route('admin.sizes.edit', $size->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
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
                                    <td colspan="7" class="text-center py-5">
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
                    'paginator'     => $sizes,
                    'itemLabel'     => 'kích thước',
                    'bulkDeleteUrl' => route('admin.sizes.bulkDelete'),
                ])
            </div>