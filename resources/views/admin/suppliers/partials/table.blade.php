<div class="product-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="supplierTable">
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
                            Tên nhà cung cấp <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">Liên hệ</th>
                    <th style="width:160px;">
                        <button type="button" class="product-sort-btn" data-sort-key="receipts_count" data-sort-type="number">
                            Số phiếu nhập <span class="product-sort-icon">↑↓</span>
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
                @forelse($suppliers as $supplier)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $supplier->id }}">
                        </td>
                        <td data-sort-value="{{ $supplier->id }}" style="opacity:.55;">{{ $supplier->id }}</td>
                        <td data-cell="name" data-sort-value="{{ $supplier->name }}">
                            <span class="fw-bold">{{ $supplier->name }}</span>
                            @if($supplier->address)
                                <div class="text-muted" style="font-size:12px;">{{ $supplier->address }}</div>
                            @endif
                        </td>
                        <td>
                            @if($supplier->phone)
                                <div style="font-size:13px;"><i class="fa-solid fa-phone me-1 text-muted"></i>{{ $supplier->phone }}</div>
                            @endif
                            @if($supplier->email)
                                <div style="font-size:13px;"><i class="fa-solid fa-envelope me-1 text-muted"></i>{{ $supplier->email }}</div>
                            @endif
                            @if(!$supplier->phone && !$supplier->email)
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td data-cell="receipts_count" data-sort-value="{{ $supplier->goods_receipts_count }}">
                            <span class="fw-semibold">{{ number_format($supplier->goods_receipts_count) }}</span>
                        </td>
                        <td data-cell="created_at" data-sort-value="{{ $supplier->created_at?->format('Ymd') ?? '0' }}">
                            {{ $supplier->created_at?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td data-cell="status" data-sort-value="{{ $supplier->status ? 1 : 0 }}">
                            <span class="status-badge {{ $supplier->status ? 'status-badge--active' : 'status-badge--inactive' }}">
                                {{ $supplier->status ? 'Hoạt động' : 'Ngừng hợp tác' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="dropdown">
                                <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                    <button type="button" class="dropdown-item"
                                        data-bs-toggle="modal" data-bs-target="#editSupplierModal"
                                        data-edit-id="{{ $supplier->id }}"
                                        data-edit-name="{{ $supplier->name }}"
                                        data-edit-phone="{{ $supplier->phone ?? '' }}"
                                        data-edit-email="{{ $supplier->email ?? '' }}"
                                        data-edit-address="{{ $supplier->address ?? '' }}"
                                        data-edit-note="{{ $supplier->note ?? '' }}"
                                        data-edit-url="{{ route('admin.suppliers.update', $supplier->id) }}">
                                        <i class="fa-regular fa-pen-to-square"></i> Sửa
                                    </button>
                                    <form method="POST" action="{{ route('admin.suppliers.toggleStatus', $supplier->id) }}" style="margin:0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="dropdown-item">
                                            @if($supplier->status)
                                                <i class="fa-regular fa-eye-slash"></i> Ngừng hợp tác
                                            @else
                                                <i class="fa-regular fa-eye"></i> Kích hoạt lại
                                            @endif
                                        </button>
                                    </form>
                                    <div class="dropdown-divider my-1"></div>
                                    <button type="button" class="dropdown-item text-danger"
                                        data-delete-url="{{ route('admin.suppliers.destroy', $supplier->id) }}"
                                        data-delete-name="{{ $supplier->name }}"
                                        data-delete-type="nhà cung cấp">
                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-truck text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có nhà cung cấp nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
    @include('layouts.components.pagination', [
        'paginator'     => $suppliers,
        'itemLabel'     => 'nhà cung cấp',
        'bulkDeleteUrl' => route('admin.suppliers.bulkDelete'),
    ])
</div>
