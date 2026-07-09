<div class="product-table-wrap supplier-table-wrap">
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
                    <th style="width:190px;">
                        <button type="button" class="product-sort-btn" data-sort-key="name">
                            Tên nhà cung cấp <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:220px;">Liên hệ</th>
                    <th style="width:160px;">
                        <button type="button" class="product-sort-btn" data-sort-key="receipts_count" data-sort-type="number">
                            Số phiếu nhập <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:150px;">
                        <button type="button" class="product-sort-btn" data-sort-key="created_at">
                            Ngày tạo <span class="product-sort-icon">↑↓</span>
                        </button>
                    </th>
                    <th style="width:160px;">Trạng thái</th>
                    <th class="text-end pe-4" style="width:110px;">Thao tác</th>
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
                            <div class="hk-cat-filter supplier-status-dropdown" data-supplier-id="{{ $supplier->id }}"
                                data-toggle-url="{{ route('admin.suppliers.toggleStatus', $supplier->id) }}">
                                <button type="button" class="status-badge supplier-status-trigger {{ $supplier->status ? 'status-badge--active' : 'status-badge--inactive' }}"
                                    data-value="{{ $supplier->status ? 1 : 0 }}" aria-haspopup="listbox" aria-expanded="false">
                                    <span class="supplier-status-trigger-label">{{ $supplier->status ? 'Hoạt động' : 'Ngừng hợp tác' }}</span>
                                    <i class="fa-solid fa-chevron-down supplier-status-caret"></i>
                                </button>
                                <div class="hk-cat-panel supplier-status-panel" hidden>
                                    <div class="hk-cat-list" role="listbox">
                                        <button type="button" class="hk-cat-item {{ $supplier->status ? 'is-active' : '' }}" data-value="1" data-css="status-badge--active">Hoạt động</button>
                                        <button type="button" class="hk-cat-item {{ !$supplier->status ? 'is-active' : '' }}" data-value="0" data-css="status-badge--inactive">Ngừng hợp tác</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <button type="button" class="row-action-btn"
                                    data-bs-toggle="modal" data-bs-target="#editSupplierModal"
                                    data-edit-id="{{ $supplier->id }}"
                                    data-edit-name="{{ $supplier->name }}"
                                    data-edit-phone="{{ $supplier->phone ?? '' }}"
                                    data-edit-email="{{ $supplier->email ?? '' }}"
                                    data-edit-address="{{ $supplier->address ?? '' }}"
                                    data-edit-note="{{ $supplier->note ?? '' }}"
                                    data-edit-url="{{ route('admin.suppliers.update', $supplier->id) }}"
                                    title="Sửa">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="row-action-btn"
                                    data-delete-url="{{ route('admin.suppliers.destroy', $supplier->id) }}"
                                    data-delete-name="{{ $supplier->name }}"
                                    data-delete-type="nhà cung cấp"
                                    title="Xóa">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
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

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2 supplier-pagination-bar">
    @include('layouts.components.pagination', [
        'paginator'     => $suppliers,
        'itemLabel'     => 'nhà cung cấp',
        'bulkDeleteUrl' => route('admin.suppliers.bulkDelete'),
    ])
</div>
