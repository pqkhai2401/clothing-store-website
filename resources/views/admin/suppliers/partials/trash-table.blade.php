<div class="product-trash-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 product-trash-table">
            <thead>
                <tr>
                    <th style="width: 54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width: 70px;">ID</th>
                    <th>Tên nhà cung cấp</th>
                    <th style="width: 150px;">Liên hệ</th>
                    <th style="width: 150px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width: 90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $supplier->id }}">
                        </td>
                        <td class="product-trash-id">{{ $supplier->id }}</td>
                        <td>
                            <div class="fw-bold">{{ $supplier->name }}</div>
                        </td>
                        <td>{{ $supplier->phone ?? $supplier->email ?? '—' }}</td>
                        <td class="product-trash-date">{{ $supplier->deleted_at?->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <form method="POST" action="{{ route('admin.suppliers.restore', $supplier->id) }}">
                                        @csrf @method('PATCH')
                                        <button type="button" class="dropdown-item"
                                            onclick="window.showConfirm({title: 'Xác nhận khôi phục', message: 'Bạn có chắc chắn muốn khôi phục nhà cung cấp này không?', type: 'warning'}).then(ok => { if(ok) this.closest('form').submit(); })">
                                            <i class="fa-solid fa-rotate-left"></i> Khôi phục
                                        </button>
                                    </form>
                                    <div class="dropdown-divider my-1"></div>
                                    <button type="button" class="dropdown-item text-danger"
                                        data-delete-url="{{ route('admin.suppliers.forceDelete', $supplier->id) }}"
                                        data-delete-name="{{ $supplier->name }}"
                                        data-delete-type="nhà cung cấp (vĩnh viễn)">
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
                            <div class="fw-semibold text-muted">Thùng rác nhà cung cấp đang trống</div>
                            <div class="text-muted small mt-1">Các bản ghi bị xóa mềm sẽ xuất hiện tại đây.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-top px-4 py-2" style="background: var(--hk-bg-card, #fff); border-color: #E2E8F0 !important;">
        @include('layouts.components.pagination', [
            'paginator'       => $suppliers,
            'itemLabel'       => 'nhà cung cấp',
            'bulkRestoreUrl'  => route('admin.suppliers.bulkRestore'),
            'bulkDeleteUrl'   => route('admin.suppliers.bulkForceDelete'),
            'bulkDeleteLabel' => 'Xóa vĩnh viễn đã chọn',
        ])
    </div>
</div>
