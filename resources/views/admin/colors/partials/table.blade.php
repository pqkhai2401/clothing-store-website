<div class="product-table-wrap">
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
                                <th style="width:60px;">Màu thực tế</th>
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
                                    <td>
                                        <span class="status-badge status-badge--active">Hoạt động</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <button type="button" class="dropdown-item"
                                                    data-bs-toggle="modal" data-bs-target="#editColorModal"
                                                    data-edit-id="{{ $color->id }}"
                                                    data-edit-name="{{ $color->name }}"
                                                    data-edit-hex="{{ $color->hex_code ?? '' }}"
                                                    data-edit-url="{{ route('admin.colors.update', $color->id) }}">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </button>
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

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $colors,
                    'itemLabel'     => 'màu sắc',
                    'bulkDeleteUrl' => route('admin.colors.bulkDelete'),
                ])
            </div>
