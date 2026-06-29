<div class="product-trash-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 product-trash-table">
            <thead>
                <tr>
                    <th style="width: 54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th class="ps-4" style="width: 70px;">ID</th>
                    <th style="width: 70px;">Ảnh</th>
                    <th>Tên sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Ngày xóa</th>
                    <th class="text-end pe-4">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $product->id }}">
                        </td>
                        <td class="ps-4 product-trash-id">{{ $product->id }}</td>
                        <td>
                            @if($product->thumbnail)
                                <img src="{{ asset($product->thumbnail) }}" class="product-trash-thumb" alt="{{ $product->name }}">
                            @else
                                <div class="product-trash-thumb-placeholder">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $product->name }}</div>
                            @if($product->brand)
                                <div class="product-trash-brand">{{ $product->brand->name }}</div>
                            @endif
                        </td>
                        <td class="product-trash-cat">{{ $product->category?->name ?? '—' }}</td>
                        <td>
                            @if($product->discount > 0)
                                <div class="trash-price-sale">
                                    {{ number_format($product->price * (100 - $product->discount) / 100, 0, ',', '.') }}₫
                                </div>
                                <div class="trash-price-original">
                                    {{ number_format($product->price, 0, ',', '.') }}₫
                                </div>
                            @else
                                <span class="trash-price-normal">
                                    {{ number_format($product->price, 0, ',', '.') }}₫
                                </span>
                            @endif
                        </td>
                        <td class="product-trash-date">
                            {{ $product->deleted_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-2">
                                <form method="POST" action="{{ route('admin.products.restore', $product->id) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold"
                                    data-delete-url="{{ route('admin.products.forceDelete', $product->id) }}"
                                    data-delete-name="{{ $product->name }}"
                                    data-delete-type="sản phẩm (vĩnh viễn)">
                                    <i class="fa-solid fa-trash me-1"></i> Xóa vĩnh viễn
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác trống</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
        @include('layouts.components.pagination', [
            'paginator'        => $products,
            'itemLabel'        => 'sản phẩm',
            'bulkRestoreUrl'   => route('admin.products.bulkRestore'),
            'bulkDeleteUrl'    => route('admin.products.bulkForceDelete'),
            'bulkDeleteLabel'  => 'Xóa vĩnh viễn đã chọn',
        ])
    </div>
</div>
