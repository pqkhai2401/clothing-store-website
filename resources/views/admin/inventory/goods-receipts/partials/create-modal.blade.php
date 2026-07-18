{{--
    Offcanvas tạo phiếu nhập kho mới.
    Variables expected:
    - $suppliers: danh sách nhà cung cấp đang hoạt động.
    - $variants: danh sách biến thể sản phẩm đã map sẵn field hiển thị.
--}}

<div class="offcanvas offcanvas-end gr-offcanvas" tabindex="-1" id="goodsReceiptOffcanvas" aria-labelledby="goodsReceiptOffcanvasLabel">
    <form method="POST" action="{{ route('admin.goods-receipts.store') }}" id="goodsReceiptAjaxForm" class="d-flex flex-column h-100">
        @csrf
        <input type="hidden" name="action" id="grModalActionInput" value="draft">

        <div class="offcanvas-header border-bottom">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h2 class="offcanvas-title mb-0" id="goodsReceiptOffcanvasLabel">Tạo phiếu nhập kho mới</h2>
                    <span class="gr-badge gr-badge--draft" id="grModalStatusBadge">Nháp</span>
                </div>
                <p class="mb-0 text-muted" style="font-size:13px;">Chọn nhà cung cấp và thêm sản phẩm cần nhập kho.</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Đóng"></button>
        </div>

        <div class="offcanvas-body flex-grow-1 overflow-auto">
            <div class="card gr-info-card mb-4">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Thông tin phiếu nhập</span>
                </div>
                <div class="card-body p-3">
                    {{-- Nguồn nhập --}}
                    <div class="edit-field mb-3" id="grModalSourceTypeFilterWrap">
                        <label class="gr-inline-label d-block mb-1">Nguồn nhập <span class="text-danger">*</span></label>
                        <input type="hidden" name="source_type" id="grModalSourceType" value="supplier">
                        <div class="hk-cat-filter gr-source-type-filter" id="grModalSourceTypeFilter">
                            <button type="button" class="hk-cat-trigger" id="grModalSourceTypeTrigger"
                                aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="grModalSourceTypeLabel">
                                    Nhập từ nhà cung cấp
                                </span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="grModalSourceTypePanel" hidden>
                                <div class="hk-cat-list" id="grModalSourceTypeList" role="listbox">
                                    <button type="button" class="hk-cat-item is-active" data-value="supplier" data-label="🏭 Nhập từ nhà cung cấp">
                                        Nhập từ nhà cung cấp
                                    </button>
                                    <button type="button" class="hk-cat-item" data-value="internal" data-label="🏠 Nhập nội bộ (kiểm kê / điều chỉnh)">
                                        Nhập nội bộ (kiểm kê / điều chỉnh)
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="source_type"></div>
                    </div>

                    {{-- Loại nhập --}}
                    <div class="edit-field mb-3" id="grModalReceiptTypeFilterWrap">
                        <label class="gr-inline-label d-block mb-1">Loại nhập <span class="text-danger">*</span></label>
                        <input type="hidden" name="receipt_type" id="grModalReceiptType" value="purchase">
                        <div class="hk-cat-filter gr-receipt-type-filter" id="grModalReceiptTypeFilter">
                            <button type="button" class="hk-cat-trigger" id="grModalReceiptTypeTrigger"
                                aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="grModalReceiptTypeLabel">
                                    Nhập hàng nhà cung cấp
                                </span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="grModalReceiptTypePanel" hidden>
                                <div class="hk-cat-list" id="grModalReceiptTypeList" role="listbox">
                                    <button type="button" class="hk-cat-item is-active" data-value="purchase" data-label="📦 Nhập hàng nhà cung cấp">
                                        Nhập hàng nhà cung cấp
                                    </button>
                                    <button type="button" class="hk-cat-item" data-value="return" data-label="🔄 Nhập trả hàng">
                                        Nhập trả hàng
                                    </button>
                                    <button type="button" class="hk-cat-item" data-value="adjustment" data-label="⚖️ Điều chỉnh kiểm kê">
                                        Điều chỉnh kiểm kê
                                    </button>
                                    <button type="button" class="hk-cat-item" data-value="initial" data-label="🗂️ Nhập tồn đầu kỳ">
                                        Nhập tồn đầu kỳ
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="receipt_type"></div>
                    </div>

                    {{-- Kho nhận hàng — hệ thống hiện chỉ vận hành 1 kho nên gán cố định, không cho chọn --}}
                    <div class="edit-field mb-3">
                        <label class="gr-inline-label d-block mb-1">Kho nhận</label>
                        @php
                            $defaultWh = $warehouses->firstWhere('is_default', true) ?? $warehouses->first();
                        @endphp
                        <input type="hidden" name="warehouse_id" id="grModalWarehouseId" value="{{ $defaultWh?->id }}">
                        <div class="gr-warehouse-static">
                            <i class="fa-solid fa-warehouse"></i>
                            {{ $defaultWh?->full_name ?? '—' }}
                        </div>
                    </div>

                    {{-- Ngày nhập kho --}}
                    <div class="edit-field mb-3">
                        <label for="grModalReceivedAt" class="gr-inline-label d-block mb-1">Ngày nhập kho <span class="text-danger">*</span></label>
                        <input type="datetime-local" id="grModalReceivedAt" name="received_at" class="form-control form-control-sm"
                            value="{{ now()->format('Y-m-d\TH:i') }}" required>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="received_at"></div>
                    </div>

                    {{-- Nhà cung cấp (ẩn khi nội bộ) --}}
                    <div class="edit-field mb-3" id="grModalSupplierField">
                        <label class="gr-inline-label d-block mb-1">Nhà cung cấp <span class="text-danger" id="grModalSupplierRequired">*</span></label>
                        <input type="hidden" name="supplier_id" id="grModalSupplierSelect" value="">
                        <div class="hk-cat-filter gr-supplier-filter" id="grModalSupplierFilter">
                            <button type="button" class="hk-cat-trigger" id="grModalSupplierTrigger"
                                aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="grModalSupplierLabel">
                                    — Chọn nhà cung cấp —
                                </span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="grModalSupplierPanel" hidden>
                                <div class="hk-cat-list" id="grModalSupplierList" role="listbox">
                                    <button type="button" class="hk-cat-item is-active" data-value="" data-label="— Chọn nhà cung cấp —">
                                        — Chọn nhà cung cấp —
                                    </button>
                                    @foreach($suppliers as $supplier)
                                        <button type="button" class="hk-cat-item" data-value="{{ $supplier->id }}" data-label="{{ $supplier->name }}">
                                            {{ $supplier->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="supplier_id"></div>
                    </div>

                    {{-- Lý do nhập (hiện khi nội bộ hoặc điều chỉnh) --}}
                    <div class="edit-field mb-3" id="grModalReasonField" style="display:none;">
                        <label for="grModalReceiptReason" class="gr-inline-label d-block mb-1">Lý do nhập</label>
                        <input type="text" id="grModalReceiptReason" name="receipt_reason" class="form-control form-control-sm"
                            placeholder="Ví dụ: Cân bằng tồn kho sau kiểm kê...">
                        <div class="invalid-feedback d-block mt-1" data-gr-error="receipt_reason"></div>
                    </div>

                    <div class="edit-field mb-0">
                        <label for="grModalNote">Ghi chú</label>
                        <textarea id="grModalNote" name="note" rows="2" class="form-control form-control-sm"
                            placeholder="Ghi chú nội bộ cho phiếu nhập kho..."></textarea>
                        <div class="invalid-feedback d-block mt-1" data-gr-error="note"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex gap-2 align-items-start">
                        <div class="gr-picker-wrap flex-grow-1">
                            <input type="text" class="gr-picker-input" id="grModalPickerInput"
                                placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..." autocomplete="off">
                            <div class="gr-picker-panel" id="grModalPickerPanel" hidden></div>
                        </div>
                        <button type="button" class="gr-add-product-btn flex-shrink-0"
                            title="Thêm sản phẩm mới"
                            data-bs-toggle="modal" data-bs-target="#qcpModal">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback d-block mt-2" data-gr-error="items"></div>

                    <div class="gr-table-wrap is-empty" id="grModalTableWrap">
                        <table class="gr-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th style="width:90px;">Tồn kho</th>
                                    <th style="width:110px;">SL nhập</th>
                                    <th style="width:140px;">Giá vốn (₫)</th>
                                    <th style="width:130px;">Thành tiền</th>
                                    <th style="width:44px;"></th>
                                </tr>
                            </thead>
                            <tbody id="grModalTableBody"></tbody>
                        </table>
                        <div class="gr-table-empty">Chưa có sản phẩm nào. Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                    </div>

                    <div class="gr-summary-bar d-flex justify-content-between align-items-center flex-wrap">
                        <div class="gr-summary-item me-4">
                            <span class="gr-summary-label">Tổng số lượng:</span>
                            <span class="gr-summary-value fw-bold text-dark" id="grModalTotalQuantity" style="font-size:16px;">0</span>
                        </div>
                        <div class="gr-summary-item">
                            <span class="gr-summary-label">Tổng giá trị phiếu nhập:</span>
                            <span class="gr-summary-value" id="grModalTotalAmount">0đ</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border-top bg-light px-4 py-3 d-flex flex-wrap justify-content-end gap-2">
            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="offcanvas">Hủy bỏ</button>
            <button type="submit" class="btn btn-outline-dark fw-semibold" data-gr-modal-action="draft">
                <i class="fa-regular fa-floppy-disk me-1"></i> Lưu nháp
            </button>
            <button type="submit" class="btn gr-btn-emerald fw-semibold" data-gr-modal-action="complete" id="grModalBtnComplete">
                <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
            </button>
        </div>
    </form>
</div>

@include('admin.inventory.goods-receipts.partials.quick-create-product-modal')

@once
@push('styles')
<style>
.gr-offcanvas {
    width: min(900px, 92vw) !important;
    border-top-left-radius: 18px;
    border-bottom-left-radius: 18px;
    overflow: hidden;
    box-shadow: -18px 0 40px rgba(15, 23, 42, 0.18);
}
.gr-info-card { border: 1.5px solid #e5e7eb; border-radius: 12px; background: #f9fafb; }
.gr-inline-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; white-space: nowrap; }

/* ── Bo góc tất cả input/select/textarea trong offcanvas tạo phiếu ── */
#goodsReceiptOffcanvas .form-select,
#goodsReceiptOffcanvas .form-select-sm,
#goodsReceiptOffcanvas .form-control,
#goodsReceiptOffcanvas .form-control-sm,
#goodsReceiptOffcanvas textarea.form-control {
    border-radius: 10px !important;
    border: 1.5px solid #d1d5db;
    font-size: 13px;
    transition: border-color .15s, box-shadow .15s;
}
#goodsReceiptOffcanvas .form-select:focus,
#goodsReceiptOffcanvas .form-select-sm:focus,
#goodsReceiptOffcanvas .form-control:focus,
#goodsReceiptOffcanvas .form-control-sm:focus {
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(23,71,97,.08);
    outline: none;
}

.gr-source-type-filter, .gr-receipt-type-filter, .gr-supplier-filter { width: 100%; position: relative; }
.gr-source-type-filter .hk-cat-trigger,
.gr-receipt-type-filter .hk-cat-trigger,
.gr-supplier-filter .hk-cat-trigger {
    width: 100%; height: 40px;
    justify-content: space-between;
    border-radius: 999px;
    border: 1.5px solid #d1d5db;
    font-size: 13px;
    color: #0f172a;
    padding: 0 14px;
    transition: border-color .15s, box-shadow .15s;
    background: #fff;
    display: flex;
    align-items: center;
}
.gr-source-type-filter .hk-cat-trigger:hover,
.gr-source-type-filter .hk-cat-trigger.is-open,
.gr-receipt-type-filter .hk-cat-trigger:hover,
.gr-receipt-type-filter .hk-cat-trigger.is-open,
.gr-supplier-filter .hk-cat-trigger:hover,
.gr-supplier-filter .hk-cat-trigger.is-open {
    border-color: #000;
    box-shadow: 0 0 0 3px rgba(23,71,97,.08);
}
.gr-source-type-filter .hk-cat-panel,
.gr-receipt-type-filter .hk-cat-panel,
.gr-supplier-filter .hk-cat-panel {
    width: 100%;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
    border: 1.5px solid #e5e7eb;
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
}
.gr-source-type-filter .hk-cat-list,
.gr-receipt-type-filter .hk-cat-list,
.gr-supplier-filter .hk-cat-list { max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; }
.gr-source-type-filter .hk-cat-item,
.gr-receipt-type-filter .hk-cat-item,
.gr-supplier-filter .hk-cat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    padding: 9px 14px;
    width: 100%;
    border: 0;
    background: transparent;
    text-align: left;
    color: #374151;
    transition: background .15s, color .15s;
}
.gr-source-type-filter .hk-cat-item:hover,
.gr-receipt-type-filter .hk-cat-item:hover,
.gr-supplier-filter .hk-cat-item:hover {
    background: #f3f4f6;
    color: #111827;
}
.gr-source-type-filter .hk-cat-item.is-active,
.gr-receipt-type-filter .hk-cat-item.is-active,
.gr-supplier-filter .hk-cat-item.is-active {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 600;
}
/* Kho nhận — hiển thị tĩnh, chỉ 1 kho nên không cần chọn */
.gr-warehouse-static {
    display: flex; align-items: center; gap: 8px;
    width: 100%; height: 40px;
    border-radius: 999px;
    border: 1.5px solid #e5e7eb;
    background: #f9fafb;
    color: #374151;
    font-size: 13px;
    padding: 0 14px;
}
.gr-warehouse-static i { color: #9ca3af; }

.gr-add-product-btn {
    width: 42px; height: 42px; flex-shrink: 0;
    border-radius: 10px;
    border: 1.5px solid #d1d5db;
    background: #fff;
    color: #374151;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all .15s;
}
.gr-add-product-btn:hover {
    background: #174761;
    border-color: #000;
    color: #fff;
    box-shadow: 0 4px 12px rgba(23,71,97,.2);
}
[data-theme="dark"] .gr-add-product-btn {
    background: var(--hk-bg-card, #1e293b);
    border-color: var(--hk-border, #334155);
    color: var(--hk-text-1, #e2e8f0);
}
[data-theme="dark"] .gr-add-product-btn:hover {
    background: var(--hk-accent, #3b82f6);
    border-color: var(--hk-accent, #3b82f6);
    color: #fff;
}

.gr-picker-wrap { position: relative; }
.gr-picker-input {
    width: 100%; height: 42px; border: 1.5px solid #d1d5db; border-radius: 8px;
    padding: 0 14px; font-size: 14px; outline: none;
}
.gr-picker-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
.gr-picker-panel {
    position: absolute; top: calc(100% + 6px); left: 0; right: 0;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    box-shadow: 0 12px 32px rgba(0,0,0,0.12); max-height: 300px; overflow-y: auto;
    z-index: 1060;
}
.gr-picker-item {
    display: flex; align-items: center; gap: 10px; padding: 9px 14px;
    cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: background .1s;
}
.gr-picker-item:last-child { border-bottom: 0; }
.gr-picker-item:hover { background: #f9fafb; }
.gr-picker-thumb { width: 38px; height: 38px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-picker-info { flex: 1; min-width: 0; }
.gr-picker-name { font-size: 13px; font-weight: 700; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.gr-picker-meta { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-picker-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; border: 1px solid rgba(0,0,0,.08); }
.gr-picker-stock { font-size: 11px; color: #9ca3af; white-space: nowrap; }
.gr-picker-empty { padding: 20px; text-align: center; color: #9ca3af; font-size: 13px; }

.gr-table-wrap { border: 1.5px solid #e5e7eb; border-radius: 10px; overflow-x: auto; margin-top: 16px; max-height: 360px; overflow-y: auto; }
.gr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 650px; }
.gr-table thead th {
    background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
    color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    position: sticky; top: 0; z-index: 1;
}
.gr-table tbody td { padding: 9px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
.gr-table tbody tr:last-child td { border-bottom: 0; }
.gr-row-product { display: flex; align-items: center; gap: 10px; min-width: 200px; }
.gr-row-thumb { width: 40px; height: 40px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
.gr-row-name { font-size: 13px; font-weight: 700; color: #111827; }
.gr-row-sub { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 6px; }
.gr-row-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; }
.gr-num-input {
    width: 100%; min-width: 85px; height: 34px; border: 1.5px solid #d1d5db; border-radius: 6px;
    padding: 0 8px; font-size: 13px; outline: none; box-sizing: border-box;
}
.gr-num-input:focus { border-color: #000; box-shadow: 0 0 0 3px rgba(23,71,97,.08); }
.gr-row-total { font-weight: 700; color: #111827; white-space: nowrap; }
.gr-row-remove {
    width: 28px; height: 28px; border-radius: 50%; border: 0; background: #f3f4f6; color: #6b7280;
    display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background .15s, color .15s;
}
.gr-row-remove:hover { background: #fee2e2; color: #dc2626; }
.gr-table-empty { padding: 28px 16px; text-align: center; color: #9ca3af; font-size: 13px; }
.gr-table-wrap.is-empty .gr-table { display: none; }
.gr-table-wrap:not(.is-empty) .gr-table-empty { display: none; }

.gr-summary-bar {
    display: flex; align-items: center; justify-content: flex-end; gap: 10px;
    margin-top: 14px; padding: 14px 16px; background: #f9fafb; border: 1.5px solid #e5e7eb; border-radius: 10px;
}
.gr-summary-label { font-size: 14px; color: #374151; font-weight: 600; }
.gr-summary-value { font-size: 20px; font-weight: 800; color: #000; }
</style>
@endpush
@endonce

@once
@push('scripts')
<script>
(function () {
    const variants = @json($variants);
    const offcanvas = document.getElementById('goodsReceiptOffcanvas');
    const form = document.getElementById('goodsReceiptAjaxForm');
    const actionInput = document.getElementById('grModalActionInput');
    const statusBadge = document.getElementById('grModalStatusBadge');
    const sourceTypeEl = document.getElementById('grModalSourceType');
    const sourceTypeFilter = document.getElementById('grModalSourceTypeFilter');
    const sourceTypeTrigger = document.getElementById('grModalSourceTypeTrigger');
    const sourceTypePanel = document.getElementById('grModalSourceTypePanel');
    const sourceTypeLabel = document.getElementById('grModalSourceTypeLabel');
    const sourceTypeList = document.getElementById('grModalSourceTypeList');
    const receiptTypeEl = document.getElementById('grModalReceiptType');
    const receiptTypeFilter = document.getElementById('grModalReceiptTypeFilter');
    const receiptTypeTrigger = document.getElementById('grModalReceiptTypeTrigger');
    const receiptTypePanel = document.getElementById('grModalReceiptTypePanel');
    const receiptTypeLabel = document.getElementById('grModalReceiptTypeLabel');
    const receiptTypeList = document.getElementById('grModalReceiptTypeList');
    const warehouseIdEl = document.getElementById('grModalWarehouseId');
    const supplierField = document.getElementById('grModalSupplierField');
    const supplierSelect = document.getElementById('grModalSupplierSelect');
    const supplierRequired = document.getElementById('grModalSupplierRequired');
    const supplierFilter = document.getElementById('grModalSupplierFilter');
    const supplierTrigger = document.getElementById('grModalSupplierTrigger');
    const supplierPanel = document.getElementById('grModalSupplierPanel');
    const supplierLabel = document.getElementById('grModalSupplierLabel');
    const supplierList = document.getElementById('grModalSupplierList');
    const reasonField = document.getElementById('grModalReasonField');
    const receiptReasonEl = document.getElementById('grModalReceiptReason');
    const receivedAtEl = document.getElementById('grModalReceivedAt');
    const pickerInput = document.getElementById('grModalPickerInput');
    const pickerPanel = document.getElementById('grModalPickerPanel');
    const tableWrap = document.getElementById('grModalTableWrap');
    const tableBody = document.getElementById('grModalTableBody');
    const totalEl = document.getElementById('grModalTotalAmount');
    const tableArea = document.querySelector('[data-admin-table-area]');
    const STATUS_LABELS = { draft: 'Nháp', complete: 'Hoàn tất nhập kho' };
    let selected = {};
    let order = [];

    if (!offcanvas || !form) return;

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function money(num) {
        return Math.round(num || 0).toLocaleString('vi-VN') + 'đ';
    }

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/80x80?text=No+Image';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function clearErrors() {
        document.querySelectorAll('[data-gr-error]').forEach(el => { el.textContent = ''; });
    }

    // ── Custom dropdowns helper ──────────────────────────────
    function closeAllCustomDropdowns() {
        if (sourceTypePanel) sourceTypePanel.hidden = true;
        sourceTypeTrigger?.classList.remove('is-open');
        sourceTypeTrigger?.setAttribute('aria-expanded', 'false');

        if (receiptTypePanel) receiptTypePanel.hidden = true;
        receiptTypeTrigger?.classList.remove('is-open');
        receiptTypeTrigger?.setAttribute('aria-expanded', 'false');

        if (supplierPanel) supplierPanel.hidden = true;
        supplierTrigger?.classList.remove('is-open');
        supplierTrigger?.setAttribute('aria-expanded', 'false');
    }

    // ── Source Type dropdown ────────────────────────────────
    sourceTypeTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = sourceTypePanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            sourceTypePanel.hidden = false;
            sourceTypeTrigger.classList.add('is-open');
            sourceTypeTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    sourceTypeList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        sourceTypeEl.value = btn.dataset.value || '';
        sourceTypeLabel.textContent = btn.dataset.label || '';
        sourceTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();

        syncReceiptTypeOptions();
        updateFieldVisibility();
        checkFormValidity();
    });

    // ── Receipt Type dropdown ────────────────────────────────
    receiptTypeTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = receiptTypePanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            receiptTypePanel.hidden = false;
            receiptTypeTrigger.classList.add('is-open');
            receiptTypeTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    receiptTypeList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn || btn.disabled) return;
        receiptTypeEl.value = btn.dataset.value || '';
        receiptTypeLabel.textContent = btn.dataset.label || '';
        receiptTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();

        updateFieldVisibility();
        checkFormValidity();
    });

    // ── Supplier dropdown ───────────────────────────────────
    supplierTrigger?.addEventListener('click', function (e) {
        e.stopPropagation();
        const shouldOpen = supplierPanel.hidden;
        closeAllCustomDropdowns();
        if (shouldOpen) {
            supplierPanel.hidden = false;
            supplierTrigger.classList.add('is-open');
            supplierTrigger.setAttribute('aria-expanded', 'true');
        }
    });

    supplierList?.addEventListener('click', function (e) {
        const btn = e.target.closest('.hk-cat-item');
        if (!btn) return;
        supplierSelect.value = btn.dataset.value || '';
        supplierLabel.textContent = btn.dataset.label || '— Chọn nhà cung cấp —';
        supplierList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
        closeAllCustomDropdowns();
        checkFormValidity();
    });

    document.addEventListener('click', function (e) {
        if (sourceTypeFilter && !sourceTypeFilter.contains(e.target) &&
            receiptTypeFilter && !receiptTypeFilter.contains(e.target) &&
            supplierFilter && !supplierFilter.contains(e.target)) {
            closeAllCustomDropdowns();
        }
    });

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalized = key.startsWith('items') ? 'items' : key;
            const target = document.querySelector(`[data-gr-error="${normalized}"]`);
            if (!target) return;
            target.textContent = Array.isArray(messages) ? messages[0] : messages;
        });
    }

    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) { alert(message); return; }
        const toast = document.createElement('div');
        toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
        toast.style.pointerEvents = 'auto';
        toast.innerHTML = `
            <div class="toast-content">
                <div class="toast-icon">
                    <svg style="flex-shrink:0;display:block;" viewBox="0 0 24 24" width="22" height="22">
                        ${type === 'error'
                            ? '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'
                            : '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>'}
                    </svg>
                </div>
                <div class="toast-message">${esc(message)}</div>
            </div>
            <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
        `;
        container.appendChild(toast);
        setTimeout(() => {
            if (document.body.contains(toast)) {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    }

    function setAction(action) {
        actionInput.value = action;
        if (!statusBadge) return;
        statusBadge.textContent = STATUS_LABELS[action] || action;
        statusBadge.classList.toggle('gr-badge--draft', action === 'draft');
        statusBadge.classList.toggle('gr-badge--completed', action === 'complete');
    }

    function syncReceiptTypeOptions() {
        const isInternal = (sourceTypeEl?.value === 'internal');
        const purchaseBtn = receiptTypeList?.querySelector('button[data-value="purchase"]');
        if (!purchaseBtn) return;
        if (isInternal) {
            purchaseBtn.disabled = true;
            purchaseBtn.style.opacity = '0.5';
            purchaseBtn.style.pointerEvents = 'none';
            if (receiptTypeEl.value === 'purchase') {
                receiptTypeEl.value = 'adjustment';
                const adjustBtn = receiptTypeList?.querySelector('button[data-value="adjustment"]');
                if (adjustBtn) {
                    receiptTypeList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === adjustBtn));
                    receiptTypeLabel.textContent = adjustBtn.dataset.label;
                }
            }
        } else {
            purchaseBtn.disabled = false;
            purchaseBtn.style.opacity = '';
            purchaseBtn.style.pointerEvents = '';
        }
    }

    function updateFieldVisibility() {
        const isInternal = (sourceTypeEl?.value === 'internal');
        const isAdjustmentOrReturn = ['adjustment', 'return', 'initial'].includes(receiptTypeEl?.value);

        if (supplierField) {
            supplierField.style.display = isInternal ? 'none' : '';
        }
        if (isInternal) {
            if (supplierSelect) {
                supplierSelect.value = '';
                supplierSelect.removeAttribute('required');
            }
            if (supplierRequired) supplierRequired.style.display = 'none';
            // Reset custom selection to default option
            if (supplierList && supplierLabel) {
                const defaultItem = supplierList.querySelector('button[data-value=""]');
                if (defaultItem) {
                    supplierList.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === defaultItem));
                    supplierLabel.textContent = defaultItem.dataset.label;
                }
            }
        } else {
            if (supplierSelect) {
                supplierSelect.setAttribute('required', '');
            }
            if (supplierRequired) supplierRequired.style.display = '';
        }

        if (reasonField) {
            reasonField.style.display = (isInternal || isAdjustmentOrReturn) ? '' : 'none';
        }
    }

    // Run initial sync and visibility check
    syncReceiptTypeOptions();
    updateFieldVisibility();

    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];
        return variants.filter(v => `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase().includes(query)).slice(0, 30);
    }

    function renderPicker(list) {
        if (list.length === 0) {
            pickerPanel.innerHTML = '<div class="gr-picker-empty">Không tìm thấy sản phẩm phù hợp.</div>';
            pickerPanel.hidden = false;
            return;
        }
        pickerPanel.innerHTML = list.map(v => `
            <div class="gr-picker-item" data-variant-id="${v.id}">
                <img class="gr-picker-thumb" src="${resolveImageUrl(v.thumbnail)}" alt="">
                <div class="gr-picker-info">
                    <div class="gr-picker-name" style="font-size: 13px;">
                        ${esc(v.product_name)} - ${esc(v.color_name)} - Size ${esc(v.size_name)} - SKU ${esc(v.sku)}
                    </div>
                </div>
                <span class="gr-picker-stock">Tồn: ${v.stock}</span>
            </div>
        `).join('');
        pickerPanel.hidden = false;
    }

    pickerInput.addEventListener('input', function () { renderPicker(filterVariants(this.value)); });
    pickerInput.addEventListener('focus', function () { if (this.value.trim()) renderPicker(filterVariants(this.value)); });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.gr-picker-wrap')) pickerPanel.hidden = true;
    });

    pickerPanel.addEventListener('click', function (e) {
        const item = e.target.closest('.gr-picker-item');
        if (!item || !item.dataset.variantId) return;
        addVariant(item.dataset.variantId);
        pickerInput.value = '';
        pickerPanel.hidden = true;
        pickerInput.focus();
    });

    pickerInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const query = this.value.trim().toLowerCase();
        if (!query) return;
        const matches = filterVariants(query);
        if (matches.length === 0) return;
        const exact = matches.find(v => String(v.sku).toLowerCase() === query);
        addVariant((exact || matches[0]).id);
        this.value = '';
        pickerPanel.hidden = true;
    });

    function addVariant(variantId) {
        variantId = String(variantId);
        const variant = variants.find(v => String(v.id) === variantId);
        if (!variant) return;

        if (selected[variantId]) {
            alert('Sản phẩm này đã có trong phiếu nhập.');
            return;
        }

        selected[variantId] = { variant, quantity: 1, cost_price: variant.cost_price || 0 };
        order.unshift(variantId);
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        order = order.filter(id => id !== variantId);
        renderTable();
    }

    function renderTable() {
        const ids = order.filter(id => selected[id]);
        tableWrap.classList.toggle('is-empty', ids.length === 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const item = selected[variantId];
            const { variant, quantity, cost_price } = item;
            const lineTotal = quantity * cost_price;
            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.innerHTML = `
                <td>
                    <div class="gr-row-product">
                        <img class="gr-row-thumb" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div>
                            <div class="gr-row-name">${esc(variant.product_name)}</div>
                            <div class="gr-row-sub">
                                <span class="gr-row-dot" style="background:${esc(variant.color_hex || '#ccc')};"></span>
                                ${esc(variant.color_name)} · ${esc(variant.size_name)} · ${esc(variant.sku)}
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${variantId}][product_variant_id]" value="${variantId}">
                </td>
                <td>${variant.stock}</td>
                <td>
                    <input type="number" min="1" step="1" class="gr-num-input" data-field="quantity"
                        name="items[${variantId}][quantity]" value="${quantity}">
                </td>
                <td>
                    <input type="number" min="0" step="1000" class="gr-num-input" data-field="cost_price"
                        name="items[${variantId}][cost_price]" value="${cost_price}">
                </td>
                <td class="gr-row-total" data-field="line-total">${money(lineTotal)}</td>
                <td>
                    <button type="button" class="gr-row-remove" data-remove-variant="${variantId}" title="Xóa">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });

        updateTotal();
    }

    tableBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr[data-variant-id]');
        if (!row) return;
        const variantId = row.dataset.variantId;
        const item = selected[variantId];
        if (!item) return;

        if (e.target.matches('[data-field="quantity"]')) {
            item.quantity = Math.max(1, parseInt(e.target.value, 10) || 1);
            e.target.value = item.quantity;
        }
        if (e.target.matches('[data-field="cost_price"]')) {
            item.cost_price = Math.max(0, parseFloat(e.target.value) || 0);
        }

        row.querySelector('[data-field="line-total"]').textContent = money(item.quantity * item.cost_price);
        updateTotal();
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        removeVariant(btn.dataset.removeVariant);
    });

    const totalQtyEl = document.getElementById('grModalTotalQuantity');
    const btnComplete = document.getElementById('grModalBtnComplete');

    function checkFormValidity() {
        if (!btnComplete) return;

        let hasError = false;

        // 1. Chưa chọn kho nhận
        if (!warehouseIdEl || !warehouseIdEl.value) hasError = true;

        // 2. Chưa chọn ngày nhập kho
        if (!receivedAtEl || !receivedAtEl.value) hasError = true;

        // 3. Nếu nguồn nhập là nhà cung cấp mà chưa chọn nhà cung cấp
        const isInternal = sourceTypeEl.value === 'internal';
        if (!isInternal && (!supplierSelect || !supplierSelect.value)) hasError = true;

        // 4. Chưa có sản phẩm nào
        const items = Object.values(selected);
        if (items.length === 0) {
            hasError = true;
        } else {
            // 5. Có sản phẩm số lượng <= 0 hoặc đơn giá nhập < 0
            const invalidItem = items.find(item => item.quantity <= 0 || item.cost_price < 0);
            if (invalidItem) hasError = true;
        }

        if (hasError) {
            btnComplete.setAttribute('disabled', 'disabled');
        } else {
            btnComplete.removeAttribute('disabled');
        }
    }

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.cost_price, 0);
        const qty = Object.values(selected).reduce((sum, item) => sum + item.quantity, 0);
        totalEl.textContent = money(total);
        if (totalQtyEl) totalQtyEl.textContent = qty.toLocaleString('vi-VN');
        checkFormValidity();
    }

    // Gắn sự kiện thay đổi để checkFormValidity
    if (receivedAtEl) receivedAtEl.addEventListener('input', checkFormValidity);

    // Chạy kiểm tra ban đầu
    checkFormValidity();

    async function refreshInboundTable(url) {
        if (!tableArea) return;
        const res = await fetch(url || '{{ route('admin.goods-receipts.list', ['tab' => 'inbound']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    document.querySelectorAll('[data-gr-modal-action]').forEach(btn => {
        btn.addEventListener('click', function () { setAction(this.dataset.grModalAction); });
    });

    offcanvas.addEventListener('shown.bs.offcanvas', function () {
        pickerInput?.focus();
    });
    offcanvas.addEventListener('hidden.bs.offcanvas', function () {
        clearErrors();
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();

        const action = actionInput.value;

        if (Object.keys(selected).length === 0) {
            showErrors({ items: ['Vui lòng thêm ít nhất một sản phẩm vào phiếu nhập kho.'] });
            return;
        }

        const isInternal = sourceTypeEl.value === 'internal';
        if (!isInternal && !supplierSelect.value) {
            showErrors({ supplier_id: ['Vui lòng chọn nhà cung cấp.'] });
            return;
        }

        if (action === 'complete') {
            const isConfirmed = confirm('Bạn có chắc chắn muốn hoàn tất nhập kho? Sau khi hoàn tất, phiếu sẽ được cộng tồn kho và không thể chỉnh sửa trực tiếp.');
            if (!isConfirmed) {
                return;
            }
        }

        const submitter = e.submitter;
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch('{{ route('admin.goods-receipts.store') }}', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            });

            const contentType = res.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                showErrors({ items: ['Phiên làm việc có thể đã hết hạn hoặc máy chủ phản hồi không hợp lệ. Vui lòng tải lại trang (F5) và thử lại.'] });
                return;
            }

            const data = await res.json().catch(() => null);
            if (!res.ok || !data) {
                showErrors((data && data.errors) || { items: [(data && data.message) || 'Không thể tạo phiếu nhập kho.'] });
                return;
            }

            selected = {};
            order = [];
            form.reset();
            if (supplierSelect) supplierSelect.value = '';
            setAction('draft');
            renderTable();
            bootstrap.Offcanvas.getOrCreateInstance(offcanvas).hide();
            await refreshInboundTable(data.table_url);
            showToast(data.message || `Tạo phiếu nhập kho "${data.code}" thành công.`);
        } catch (err) {
            showToast('Không thể kết nối tới máy chủ. Vui lòng kiểm tra kết nối mạng và thử lại.', 'error');
        } finally {
            submitter?.removeAttribute('disabled');
        }
    });

    /* ── "Thêm nhanh sản phẩm" (Quick Create) ngay trong khung tạo phiếu nhập ── */
    const csrfToken     = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const qcpModalEl    = document.getElementById('qcpModal');
    const qcpModal      = qcpModalEl ? new bootstrap.Modal(qcpModalEl) : null;
    const qcpName       = document.getElementById('qcpName');
    const qcpCategory   = document.getElementById('qcpCategory');
    const qcpBrand      = document.getElementById('qcpBrand');
    const qcpColor      = document.getElementById('qcpColor');
    const qcpSize       = document.getElementById('qcpSize');
    const qcpSku        = document.getElementById('qcpSku');
    const qcpSimilarBox = document.getElementById('qcpSimilarBox');
    const qcpError      = document.getElementById('qcpError');
    const qcpSubmitBtn  = document.getElementById('qcpSubmitBtn');

    let qcpSearchTimer = null;

    function qcpGender() {
        const checked = document.querySelector('input[name="qcpGender"]:checked');
        return checked ? checked.value : 'unisex';
    }

    function resetQcpDropdown(prefix) {
        const hidden = document.getElementById(prefix);
        const label  = document.getElementById(prefix + 'Label');
        const list   = document.getElementById(prefix + 'List');
        if (!hidden || !label || !list) return;
        const defaultItem = list.querySelector('.hk-cat-item[data-value=""]');
        hidden.value = '';
        label.textContent = defaultItem?.dataset.label || '';
        list.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === defaultItem));
    }

    function qcpResetForm() {
        if (!qcpName) return;
        qcpName.value = '';
        ['qcpCategory', 'qcpBrand', 'qcpColor', 'qcpSize'].forEach(resetQcpDropdown);
        qcpSku.value = '';
        document.getElementById('qcpGenderUnisex').checked = true;
        qcpSimilarBox.style.display = 'none';
        qcpSimilarBox.innerHTML = '';
        qcpError.style.display = 'none';
        qcpError.textContent = '';
    }

    if (qcpModalEl) {
        qcpModalEl.addEventListener('show.bs.modal', qcpResetForm);
    }

    // ── Quick-create custom dropdowns (Danh mục / Thương hiệu / Màu sắc / Kích thước) ──
    function wireQcpDropdown(prefix) {
        const filter  = document.getElementById(prefix + 'Filter');
        const trigger = document.getElementById(prefix + 'Trigger');
        const panel   = document.getElementById(prefix + 'Panel');
        const label   = document.getElementById(prefix + 'Label');
        const list    = document.getElementById(prefix + 'List');
        const hidden  = document.getElementById(prefix);
        if (!filter || !trigger || !panel || !list || !hidden) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const shouldOpen = panel.hidden;
            document.querySelectorAll('.qcp-dropdown .hk-cat-panel').forEach(p => { p.hidden = true; });
            document.querySelectorAll('.qcp-dropdown .hk-cat-trigger').forEach(t => {
                t.classList.remove('is-open');
                t.setAttribute('aria-expanded', 'false');
            });
            if (shouldOpen) {
                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        });

        list.addEventListener('click', function (e) {
            const btn = e.target.closest('.hk-cat-item');
            if (!btn) return;
            hidden.value = btn.dataset.value || '';
            label.textContent = btn.dataset.label || '';
            list.querySelectorAll('.hk-cat-item').forEach(item => item.classList.toggle('is-active', item === btn));
            panel.hidden = true;
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        });
    }

    ['qcpCategory', 'qcpBrand', 'qcpColor', 'qcpSize'].forEach(wireQcpDropdown);

    document.addEventListener('click', function (e) {
        if (e.target.closest('.qcp-dropdown')) return;
        document.querySelectorAll('.qcp-dropdown .hk-cat-panel').forEach(p => { p.hidden = true; });
        document.querySelectorAll('.qcp-dropdown .hk-cat-trigger').forEach(t => {
            t.classList.remove('is-open');
            t.setAttribute('aria-expanded', 'false');
        });
    });

    if (qcpName) {
        qcpName.addEventListener('input', function () {
            const value = this.value.trim();
            clearTimeout(qcpSearchTimer);
            if (!value) {
                qcpSimilarBox.style.display = 'none';
                return;
            }
            qcpSearchTimer = setTimeout(function () {
                fetch(`{{ route('admin.products.searchSimilar') }}?name=${encodeURIComponent(value)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(res => res.json())
                    .then(data => {
                        const products = data.products || [];
                        if (products.length === 0) {
                            qcpSimilarBox.style.display = 'none';
                            return;
                        }
                        qcpSimilarBox.innerHTML = '<div class="gr-picker-empty" style="text-align:left; padding:8px 12px; color:#92400e; background:#fef3c7; font-size:12px;">'
                            + 'Có thể đã tồn tại sản phẩm tương tự:</div>'
                            + products.map(p => `<div class="gr-picker-item" style="cursor:default;">${esc(p.name)}</div>`).join('');
                        qcpSimilarBox.style.display = 'block';
                    })
                    .catch(() => { qcpSimilarBox.style.display = 'none'; });
            }, 350);
        });
    }

    if (qcpSubmitBtn) {
        qcpSubmitBtn.addEventListener('click', function () {
            const payload = {
                name: qcpName.value.trim(),
                category_id: qcpCategory.value,
                brand_id: qcpBrand.value,
                gender: qcpGender(),
                color_id: qcpColor.value,
                size_id: qcpSize.value,
                sku: qcpSku.value.trim(),
            };

            if (!payload.name || !payload.category_id || !payload.color_id || !payload.size_id) {
                qcpError.textContent = 'Vui lòng điền đầy đủ các trường bắt buộc.';
                qcpError.style.display = 'block';
                return;
            }

            qcpSubmitBtn.setAttribute('disabled', 'disabled');

            fetch('{{ route('admin.products.quickCreate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Có lỗi xảy ra.');
                    return data;
                })
                .then(data => {
                    variants.push(data.variant);
                    addVariant(data.variant.id);
                    qcpModal?.hide();
                })
                .catch(err => {
                    qcpError.textContent = err.message;
                    qcpError.style.display = 'block';
                })
                .finally(() => {
                    qcpSubmitBtn.removeAttribute('disabled');
                });
        });
    }
})();
</script>
@endpush
@endonce
