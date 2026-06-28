<div class="product-trash-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 product-trash-table">
            <thead>
                <tr>
                    <th style="width: 54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width: 70px;">ID</th>
                    <th>Tên màu sắc</th>
                    <th style="width: 180px;">Số biến thể SP</th>
                    <th style="width: 150px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width: 90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($colors as $color)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $color->id }}">
                        </td>
                        <td class="product-trash-id">{{ $color->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $color->name }}</div>
                        </td>
                        <td>{{ number_format($color->product_variants_count) }}</td>
                        <td class="product-trash-date">{{ $color->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <form method="POST" action="{{ route('admin.colors.restore', $color->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="dropdown-item"
                                            onclick="return confirm('Bạn có chắc chắn muốn khôi phục màu sắc này không?')">
                                            <i class="fa-solid fa-rotate-left"></i> Khôi phục
                                        </button>
                                    </form>
                                    <div class="dropdown-divider my-1"></div>
                                    <button type="button" class="dropdown-item text-danger"
                                        data-delete-url="{{ route('admin.colors.forceDelete', $color->id) }}"
                                        data-delete-name="{{ $color->name }}"
                                        data-delete-type="màu sắc (vĩnh viễn)">
                                        <i class="fa-solid fa-trash"></i> Xóa vĩnh viễn
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác màu sắc đang trống</div>
                            <div class="text-muted small mt-1">Các bản ghi bị xóa mềm sẽ xuất hiện tại đây.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
        @include('layouts.components.pagination', [
            'paginator'       => $colors,
            'itemLabel'       => 'màu sắc',
            'bulkRestoreUrl'  => route('admin.colors.bulkRestore'),
            'bulkDeleteUrl'   => route('admin.colors.bulkForceDelete'),
            'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
        ])
    </div>
</div>
