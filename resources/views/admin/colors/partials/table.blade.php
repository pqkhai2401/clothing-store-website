<div class="product-table-wrap color-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="colorTable">
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
                                <th style="width:70px;">Màu thực tế</th>
                                <th style="width:290px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên màu sắc <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:190px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="variants_count" data-sort-type="number">
                                        Số biến thể SP <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:160px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:160px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:110px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($colors as $color)
                                @php
                                    $displayHexCode = $color->display_hex_code;
                                @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $color->id }}">
                                    </td>
                                    <td data-sort-value="{{ $color->id }}" style="opacity:.55;">{{ $color->id }}</td>
                                    <td class="text-center">
                                        @if($displayHexCode)
                                            <span class="color-swatch"
                                                style="background-color: {{ $displayHexCode }};"
                                                title="{{ $displayHexCode }}">
                                            </span>
                                        @else
                                            <span class="color-swatch color-swatch--empty" title="Chưa có mã màu"></span>
                                        @endif
                                    </td>
                                    <td data-cell="name" data-sort-value="{{ $color->name }}">
                                        <a href="{{ route('admin.products.list', ['color_id' => $color->id]) }}"
                                            class="fw-bold attribute-name-link"
                                            title="Lọc sản phẩm theo màu {{ $color->name }}">
                                            {{ $color->name }}
                                        </a>
                                        @if($displayHexCode)
                                            <code class="ms-1 text-muted" style="font-size:11px;">{{ $displayHexCode }}</code>
                                        @endif
                                    </td>
                                    <td data-cell="variants_count" data-sort-value="{{ $color->product_variants_count }}">
                                        <span class="fw-semibold">{{ number_format($color->product_variants_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $color->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $color->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td data-cell="status" data-sort-value="{{ $color->status ? 1 : 0 }}">
                                        <div class="hk-cat-filter color-status-dropdown" data-color-id="{{ $color->id }}"
                                            data-toggle-url="{{ route('admin.colors.toggleStatus', $color->id) }}">
                                            <button type="button" class="status-badge color-status-trigger {{ $color->status ? 'status-badge--active' : 'status-badge--inactive' }}"
                                                data-value="{{ $color->status ? 1 : 0 }}" aria-haspopup="listbox" aria-expanded="false">
                                                <span class="color-status-trigger-label">{{ $color->status ? 'Hoạt động' : 'Ngưng hoạt động' }}</span>
                                                <i class="fa-solid fa-chevron-down color-status-caret"></i>
                                            </button>
                                            <div class="hk-cat-panel color-status-panel" hidden>
                                                <div class="hk-cat-list" role="listbox">
                                                    <button type="button" class="hk-cat-item {{ $color->status ? 'is-active' : '' }}" data-value="1" data-css="status-badge--active">Hoạt động</button>
                                                    <button type="button" class="hk-cat-item {{ !$color->status ? 'is-active' : '' }}" data-value="0" data-css="status-badge--inactive">Ngưng hoạt động</button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-1">
                                            <button type="button" class="row-action-btn"
                                                data-bs-toggle="modal" data-bs-target="#editColorModal"
                                                data-edit-id="{{ $color->id }}"
                                                data-edit-name="{{ $color->name }}"
                                                data-edit-hex="{{ $color->hex_code ?? '' }}"
                                                data-edit-url="{{ route('admin.colors.update', $color->id) }}"
                                                title="Sửa">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="row-action-btn"
                                                data-delete-url="{{ route('admin.colors.destroy', $color->id) }}"
                                                data-delete-name="{{ $color->name }}"
                                                data-delete-type="màu sắc"
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
                                        <div class="fw-semibold text-muted">Chưa có màu sắc nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2 color-pagination-bar">
                @include('layouts.components.pagination', [
                    'paginator'     => $colors,
                    'itemLabel'     => 'màu sắc',
                    'bulkDeleteUrl' => route('admin.colors.bulkDelete'),
                ])
            </div>
