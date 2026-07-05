<div id="stockIssueModal" class="fixed inset-0 z-[1200] hidden justify-end bg-black/40 backdrop-blur-[2px]">
    <form method="POST"
        action="{{ route('admin.stock-issues.store') }}"
        id="stockIssueAjaxForm"
        class="flex h-screen w-full max-w-5xl flex-col overflow-hidden bg-white shadow-2xl ring-1 ring-black/5 lg:w-2/3">
        @csrf
        <input type="hidden" name="action" id="siModalActionInput" value="draft">

        <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 px-5 py-3">
            <div>
                <h2 class="text-lg font-bold tracking-[-0.01em] text-slate-950">Tạo phiếu xuất kho mới</h2>
                <p class="mt-0.5 text-xs font-medium text-slate-500">Chọn sản phẩm cần xuất và điền lý do tương ứng.</p>
            </div>
            <button type="button"
                id="closeStockIssueModalBtn"
                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-xl font-semibold leading-none text-slate-500 transition hover:bg-slate-200 hover:text-slate-900">
                ×
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-5 py-4">
            <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-950">Thông tin chung</h3>
                        <p class="mt-1 text-xs text-slate-500">Thiết lập lý do và ghi chú cho phiếu xuất kho.</p>
                    </div>
                    <span class="inline-flex flex-none rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.08em] text-amber-700 ring-1 ring-amber-200">
                        Nháp
                    </span>
                </div>

                <div>
                    <label for="siModalReason" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600">
                        Lý do xuất kho <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                        id="siModalReason"
                        name="reason"
                        list="siModalReasonSuggestions"
                        value="{{ old('reason') }}"
                        placeholder="Ví dụ: Xuất hủy - Hàng lỗi rách, hỏng"
                        required
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-900 outline-none transition placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                    <datalist id="siModalReasonSuggestions">
                        <option value="Xuất hủy - Hàng lỗi rách, hỏng">
                        <option value="Xuất trả NCC - Hàng lỗi">
                        <option value="Xuất mẫu trưng bày">
                        <option value="Xuất bán buôn">
                    </datalist>
                    <div class="mt-2 hidden text-xs font-semibold text-red-600" data-si-error="reason"></div>
                </div>

                <div class="mt-4">
                    <label for="siModalNote" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600">Ghi chú (không bắt buộc)</label>
                    <textarea id="siModalNote"
                        name="note"
                        rows="2"
                        placeholder="Thêm ghi chú nội bộ cho phiếu xuất này..."
                        class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100">{{ old('note') }}</textarea>
                </div>
            </section>

            <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="mb-3">
                    <h3 class="text-sm font-bold text-slate-950">Sản phẩm xuất kho</h3>
                    <p class="mt-1 text-xs text-slate-500">Tìm sản phẩm, kiểm tra tồn kho và nhập số lượng xuất.</p>
                </div>

                <div class="relative mb-3">
                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text"
                        id="siModalPickerInput"
                        autocomplete="off"
                        placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                    <div id="siModalPickerPanel"
                        hidden
                        class="absolute left-0 right-0 top-[calc(100%+8px)] z-30 max-h-80 overflow-y-auto rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl"></div>
                </div>
                <div class="mb-3 hidden text-xs font-semibold text-red-600" data-si-error="items"></div>

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <div class="max-h-[390px] overflow-auto si-hidden-scrollbar">
                        <table class="min-w-[780px] table-fixed text-left">
                            <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                                <tr>
                                    <th class="w-[28%] px-4 py-3">Sản phẩm / SKU</th>
                                    <th class="w-[10%] px-3 py-3">Tồn kho</th>
                                    <th class="w-[12%] px-3 py-3 text-center">Số lượng</th>
                                    <th class="w-[16%] px-3 py-3 text-center">Đơn giá</th>
                                    <th class="w-[16%] px-3 py-3 text-right">Tổng</th>
                                    <th class="w-[10%] px-3 py-3 text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="siModalTableBody" class="divide-y divide-slate-100 text-sm"></tbody>
                        </table>

                        <div id="siModalTableEmpty" class="px-6 py-10 text-center">
                            <div class="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-box-open"></i>
                            </div>
                            <div class="text-sm font-semibold text-slate-600">Chưa có sản phẩm nào trong phiếu</div>
                            <div class="mt-1 text-xs text-slate-400">Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex justify-end rounded-2xl bg-emerald-50 px-4 py-3 ring-1 ring-emerald-100">
                    <div class="text-right text-sm font-bold uppercase tracking-[0.08em] text-slate-600">
                        Tổng cộng đơn xuất:
                        <span id="siModalTotalAmount" class="ml-2 text-2xl font-extrabold tracking-tight text-emerald-700 normal-case">0đ</span>
                    </div>
                </div>
            </section>
        </div>

        <div class="flex shrink-0 flex-wrap justify-end gap-3 border-t border-slate-200 bg-slate-50 px-5 py-3">
            <button type="button"
                id="cancelStockIssueModalBtn"
                class="inline-flex min-w-[112px] items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                Hủy bỏ
            </button>
            <button type="submit"
                data-si-modal-action="draft"
                class="inline-flex min-w-[112px] items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                <span>💾</span>
                <span>Lưu nháp</span>
            </button>
            <button type="submit"
                data-si-modal-action="issue"
                class="inline-flex min-w-[112px] items-center justify-center gap-2 rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-800 focus:ring-4 focus:ring-emerald-200">
                <span>✓</span>
                <span>Xuất kho ngay</span>
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const variants = @json($variants);
    const modal = document.getElementById('stockIssueModal');
    const openBtn = document.getElementById('openStockIssueModalBtn');
    const closeBtn = document.getElementById('closeStockIssueModalBtn');
    const cancelBtn = document.getElementById('cancelStockIssueModalBtn');
    const form = document.getElementById('stockIssueAjaxForm');
    const actionInput = document.getElementById('siModalActionInput');
    const pickerInput = document.getElementById('siModalPickerInput');
    const pickerPanel = document.getElementById('siModalPickerPanel');
    const tableBody = document.getElementById('siModalTableBody');
    const tableEmpty = document.getElementById('siModalTableEmpty');
    const totalEl = document.getElementById('siModalTotalAmount');
    const tableArea = document.querySelector('[data-admin-table-area]');
    let selected = {};

    if (!modal || !openBtn || !form) return;

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        setTimeout(() => pickerInput?.focus(), 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        clearErrors();
    }

    function esc(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function money(num) {
        return Math.round(num || 0).toLocaleString('vi-VN') + 'đ';
    }

    function resolveImageUrl(path) {
        if (!path) return 'https://placehold.co/96x96/f1f5f9/94a3b8?text=NOIR';
        if (path.startsWith('http://') || path.startsWith('https://')) return path;
        return '/' + path.replace(/^\/+/, '');
    }

    function clearErrors() {
        document.querySelectorAll('[data-si-error]').forEach(el => {
            el.textContent = '';
            el.classList.add('hidden');
        });
    }

    function showErrors(errors) {
        clearErrors();
        Object.entries(errors || {}).forEach(([key, messages]) => {
            const normalized = key.startsWith('items') ? 'items' : key;
            const target = document.querySelector(`[data-si-error="${normalized}"]`);
            if (!target) return;
            target.textContent = Array.isArray(messages) ? messages[0] : messages;
            target.classList.remove('hidden');
        });
    }

    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];
        return variants.filter(v => `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase().includes(query)).slice(0, 30);
    }

    function renderPicker(list) {
        if (list.length === 0) {
            pickerPanel.innerHTML = '<div class="px-5 py-5 text-center text-sm font-semibold text-slate-400">Không tìm thấy sản phẩm phù hợp hoặc đã hết hàng.</div>';
            pickerPanel.hidden = false;
            return;
        }

        pickerPanel.innerHTML = list.map(v => `
            <button type="button" class="flex w-full items-center gap-3 px-4 py-3 text-left transition hover:bg-slate-50" data-variant-id="${v.id}">
                <img class="h-11 w-11 flex-none rounded-xl object-cover ring-1 ring-slate-200" src="${resolveImageUrl(v.thumbnail)}" alt="">
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-bold text-slate-900">${esc(v.product_name)}</span>
                    <span class="mt-0.5 block truncate text-xs font-medium text-slate-500">${esc(v.color_name)} - ${esc(v.size_name)} | SKU: ${esc(v.sku)}</span>
                </span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500">Tồn: ${v.stock}</span>
            </button>
        `).join('');
        pickerPanel.hidden = false;
    }

    function addVariant(variantId) {
        const variant = variants.find(v => String(v.id) === String(variantId));
        if (!variant) return;
        if (selected[variantId]) return;
        selected[variantId] = { variant, quantity: 1, unit_price: variant.unit_price || 0 };
        renderTable();
    }

    function renderTable() {
        const ids = Object.keys(selected);
        tableEmpty.classList.toggle('hidden', ids.length > 0);
        tableBody.innerHTML = '';

        ids.forEach(variantId => {
            const item = selected[variantId];
            const { variant, quantity, unit_price } = item;
            const lineTotal = quantity * unit_price;
            const overStock = quantity > variant.stock;
            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.className = 'bg-white hover:bg-slate-50/80';
            tr.innerHTML = `
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img class="h-10 w-10 flex-none rounded-lg object-cover ring-1 ring-slate-200" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-slate-950">${esc(variant.product_name)}</div>
                            <div class="mt-0.5 truncate text-xs font-medium text-slate-500">${esc(variant.color_name)} - ${esc(variant.size_name)} | SKU: ${esc(variant.sku)}</div>
                        </div>
                    </div>
                    <input type="hidden" name="items[${variantId}][product_variant_id]" value="${variantId}">
                </td>
                <td class="px-3 py-3 text-sm font-semibold text-slate-500">${variant.stock} cái</td>
                <td class="px-3 py-3 text-center">
                    <input type="number" min="1" max="${variant.stock}" step="1"
                        class="mx-auto h-9 w-16 rounded-lg border ${overStock ? 'border-red-400' : 'border-slate-300'} bg-white text-center text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        data-field="quantity" name="items[${variantId}][quantity]" value="${quantity}">
                </td>
                <td class="px-3 py-3 text-center">
                    <input type="number" min="0" step="1000"
                        class="mx-auto h-9 w-24 rounded-lg border border-slate-300 bg-white text-center text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        data-field="unit_price" name="items[${variantId}][unit_price]" value="${unit_price}">
                </td>
                <td class="px-3 py-3 text-right text-sm font-bold text-slate-900" data-field="line-total">${money(lineTotal)}</td>
                <td class="px-3 py-3 text-center">
                    <button type="button" data-remove-variant="${variantId}" class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-red-50 hover:text-red-600">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        });
        updateTotal();
    }

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.unit_price, 0);
        totalEl.textContent = money(total);
    }

    async function refreshOutboundTable(url) {
        if (!tableArea) return;
        const res = await fetch(url || '{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        });
        if (!res.ok) return;
        const data = await res.json();
        if (data.html) tableArea.innerHTML = data.html;
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });

    pickerInput.addEventListener('input', function () { renderPicker(filterVariants(this.value)); });
    pickerPanel.addEventListener('click', function (e) {
        const item = e.target.closest('[data-variant-id]');
        if (!item) return;
        addVariant(item.dataset.variantId);
        pickerInput.value = '';
        pickerPanel.hidden = true;
    });

    tableBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr[data-variant-id]');
        if (!row) return;
        const variantId = row.dataset.variantId;
        const item = selected[variantId];
        if (!item) return;

        if (e.target.matches('[data-field="quantity"]')) {
            const qty = Math.max(1, parseInt(e.target.value, 10) || 1);
            item.quantity = qty;
            e.target.classList.toggle('border-red-400', qty > item.variant.stock);
        }

        if (e.target.matches('[data-field="unit_price"]')) {
            item.unit_price = Math.max(0, parseFloat(e.target.value) || 0);
        }

        row.querySelector('[data-field="line-total"]').textContent = money(item.quantity * item.unit_price);
        updateTotal();
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        delete selected[btn.dataset.removeVariant];
        renderTable();
    });

    document.querySelectorAll('[data-si-modal-action]').forEach(btn => {
        btn.addEventListener('click', function () { actionInput.value = this.dataset.siModalAction; });
    });

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        if (Object.keys(selected).length === 0) {
            showErrors({ items: ['Vui lòng thêm ít nhất một sản phẩm vào phiếu xuất kho.'] });
            return;
        }

        const submitter = e.submitter;
        submitter?.setAttribute('disabled', 'disabled');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                body: new FormData(form),
            });
            const data = await res.json().catch(() => ({}));

            if (!res.ok) {
                showErrors(data.errors || { items: [data.message || 'Không thể tạo phiếu xuất kho.'] });
                return;
            }

            selected = {};
            form.reset();
            actionInput.value = 'draft';
            renderTable();
            closeModal();
            await refreshOutboundTable(data.table_url);
            alert(data.message || 'Tạo phiếu xuất kho thành công.');
        } finally {
            submitter?.removeAttribute('disabled');
        }
    });
})();
</script>
