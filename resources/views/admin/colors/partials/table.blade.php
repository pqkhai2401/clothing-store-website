<div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="colorTable">
                        <thead>
                            <tr>
                                <th style="width:54px;">
                                    <input type="checkbox" class="form-check-input product-check" id="colorCheckAll">
                                </th>
                                <th style="width:76px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên màu sắc <span class="product-sort-icon">↑↓</span>
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
                            @forelse($colors as $color)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check color-row-check" value="{{ $color->id }}">
                                    </td>
                                    <td data-sort-value="{{ $color->id }}" style="opacity:.55;">{{ $color->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $color->name }}">
                                        <div class="fw-bold text-dark">{{ $color->name }}</div>
                                    </td>
                                    <td data-cell="variants_count" data-sort-value="{{ $color->product_variants_count }}">
                                        <span class="fw-semibold">{{ number_format($color->product_variants_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $color->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $color->created_at?->format('d/m/Y') ?? '—' }}
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
                                                <a href="{{ route('admin.colors.edit', $color->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.colors.destroy', $color->id) }}"
                                                    data-delete-name="{{ $color->name }}"
                                                    data-delete-type="màu sắc">
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
                                        <div class="fw-semibold text-muted">Chưa có màu sắc nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $colors,
                    'itemLabel'     => 'màu sắc',
                    'bulkDeleteUrl' => route('admin.colors.bulkDelete'),
                ])
            </div>