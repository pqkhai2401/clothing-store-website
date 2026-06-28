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
                                    <button type="button" class="product-sort-btn" data-sort-key="stock" data-sort-type="number">
                                        Tổng tồn kho <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="status" data-sort-type="number">
                                        Trạng thái <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th class="text-end pe-4" style="width: 96px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                @php
                                    $effectivePrice = ($product->sale_price && $product->sale_price < $product->price)
                                        ? $product->sale_price
                                        : $product->price;
                                @endphp
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

                                    <td data-cell="price" data-sort-value="{{ $effectivePrice }}">
                                        @php $discounted = $product->discount > 0; @endphp
                                        @if($discounted)
                                            <div class="price-display">
                                                <span class="price-sale">{{ number_format($product->price * (100 - $product->discount) / 100, 0, ',', '.') }}₫</span>
                                                <span class="price-original">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                            </div>
                                        @else
                                            <span class="price-normal">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                        @endif
                                    </td>

                                    @php $totalStock = (int) ($product->product_variants_sum_stock ?? 0); @endphp
                                    <td data-cell="stock" data-sort-value="{{ $totalStock }}">
                                        <span class="{{ $totalStock > 0 ? 'fw-semibold text-dark' : 'text-danger fw-semibold' }}">
                                            {{ number_format($totalStock) }}
                                        </span>
                                    </td>

                                    <td data-cell="status" data-sort-value="{{ $product->status ? 1 : 0 }}">
                                        <span class="status-badge {{ $product->status ? 'status-badge--active' : 'status-badge--inactive' }}">
                                            {{ $product->status ? 'Đang bán' : 'Ẩn' }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.products.edit', $product->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <form method="POST" action="{{ route('admin.products.toggleStatus', $product->id) }}" style="margin:0">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="dropdown-item">
                                                        @if($product->status)
                                                            <i class="fa-regular fa-eye-slash"></i> Ẩn sản phẩm
                                                        @else
                                                            <i class="fa-regular fa-eye"></i> Hiện lại sản phẩm
                                                        @endif
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider my-1"></div>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.products.destroy', $product->id) }}"
                                                    data-delete-name="{{ $product->name }}"
                                                    data-delete-type="sản phẩm">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="10" class="text-center py-5">
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
