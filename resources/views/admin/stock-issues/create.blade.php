@extends('layouts.admin')

@section('title', 'Tạo phiếu xuất kho mới')

@push('styles')
    @include('admin.suppliers.styles')
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .si-hidden-scrollbar {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .si-hidden-scrollbar::-webkit-scrollbar {
            width: 0;
            height: 0;
        }
    </style>
@endpush

@section('content')
<main class="app-main min-h-screen bg-slate-950/95">
    <div class="relative min-h-screen overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.18),_transparent_34%),linear-gradient(135deg,_#0f172a_0%,_#111827_45%,_#020617_100%)]"></div>

        <div class="absolute inset-0 blur-[2px] opacity-70">
            <div class="mx-auto max-w-7xl px-8 py-8">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <div class="h-4 w-36 rounded bg-slate-600/60"></div>
                        <div class="mt-3 h-8 w-72 rounded bg-slate-500/60"></div>
                    </div>
                    <div class="h-11 w-44 rounded-lg bg-emerald-500/80"></div>
                </div>

                <div class="grid grid-cols-4 gap-4">
                    <div class="h-28 rounded-2xl bg-white/10"></div>
                    <div class="h-28 rounded-2xl bg-white/10"></div>
                    <div class="h-28 rounded-2xl bg-white/10"></div>
                    <div class="h-28 rounded-2xl bg-white/10"></div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl bg-white/10 ring-1 ring-white/10">
                    <div class="grid grid-cols-6 gap-4 border-b border-white/10 px-6 py-4">
                        <div class="h-4 rounded bg-white/20"></div>
                        <div class="h-4 rounded bg-white/20"></div>
                        <div class="h-4 rounded bg-white/20"></div>
                        <div class="h-4 rounded bg-white/20"></div>
                        <div class="h-4 rounded bg-white/20"></div>
                        <div class="h-4 rounded bg-white/20"></div>
                    </div>
                    @for($i = 0; $i < 7; $i++)
                        <div class="grid grid-cols-6 gap-4 border-b border-white/5 px-6 py-5">
                            <div class="h-4 rounded bg-white/10"></div>
                            <div class="h-4 rounded bg-white/10"></div>
                            <div class="h-4 rounded bg-white/10"></div>
                            <div class="h-4 rounded bg-white/10"></div>
                            <div class="h-4 rounded bg-white/10"></div>
                            <div class="h-4 rounded bg-white/10"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>

        <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-10">
            <form method="POST"
                action="{{ route('admin.stock-issues.store') }}"
                id="stockIssueForm"
                class="w-full max-w-6xl overflow-hidden rounded-[22px] bg-white shadow-[0_28px_90px_rgba(15,23,42,0.34)] ring-1 ring-black/5">
                @csrf
                <input type="hidden" name="action" id="siActionInput" value="draft">

                <div class="flex items-start justify-between gap-6 border-b border-slate-200 px-8 py-6">
                    <div>
                        <h1 class="text-[22px] font-bold tracking-[-0.01em] text-slate-950">Tạo phiếu xuất kho mới</h1>
                        <p class="mt-1 text-xs font-medium text-slate-500">Chọn sản phẩm cần xuất và điền lý do tương ứng.</p>
                    </div>

                    <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-lg font-semibold text-slate-500 transition hover:bg-slate-200 hover:text-slate-900"
                        aria-label="Đóng">
                        ×
                    </a>
                </div>

                <div class="grid gap-7 px-8 py-7 lg:grid-cols-[0.9fr_1.8fr]">
                    <section class="rounded-2xl border border-slate-200 bg-slate-50/60 p-5">
                        <div class="mb-6">
                            <div class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-500">Trạng thái phiếu</div>
                            <div class="mt-2 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.08em] text-amber-700 ring-1 ring-amber-200">
                                Nháp
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div>
                                <label for="reason" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600">
                                    Lý do xuất kho <span class="text-red-500">*</span>
                                </label>
                                <input type="text"
                                    id="reason"
                                    name="reason"
                                    list="reasonSuggestions"
                                    value="{{ old('reason', 'Xuất hủy - Hàng lỗi rách, hỏng') }}"
                                    required
                                    class="h-11 w-full rounded-xl border border-sky-200 bg-sky-50 px-4 text-sm font-semibold text-slate-900 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-100">
                                <datalist id="reasonSuggestions">
                                    <option value="Xuất hủy - Hàng lỗi rách, hỏng">
                                    <option value="Xuất trả NCC - Hàng lỗi">
                                    <option value="Xuất mẫu trưng bày">
                                    <option value="Xuất bán buôn">
                                </datalist>
                                @error('reason') <div class="mt-2 text-xs font-semibold text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label for="siInternalNote" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.12em] text-slate-600">Thông tin ghi chú</label>
                                <textarea id="siInternalNote"
                                    rows="6"
                                    placeholder="Thêm ghi chú nội bộ cho phiếu xuất này..."
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-4 focus:ring-slate-100"></textarea>
                            </div>

                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-5 text-sm font-semibold text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700">
                                <span class="text-lg">📎</span>
                                <span>Đính kèm biên bản (nếu có)</span>
                                <input type="file" class="hidden">
                            </label>
                        </div>
                    </section>

                    <section>
                        <div class="relative mb-4">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </span>
                            <input type="text"
                                id="siPickerInput"
                                autocomplete="off"
                                placeholder="Tìm theo tên sản phẩm, SKU, màu hoặc size để thêm vào phiếu..."
                                class="h-12 w-full rounded-2xl border border-slate-200 bg-white pl-11 pr-4 text-sm font-medium text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100">
                            <div id="siPickerPanel"
                                hidden
                                class="absolute left-0 right-0 top-[calc(100%+8px)] z-30 max-h-80 overflow-y-auto rounded-2xl border border-slate-200 bg-white py-2 shadow-2xl"></div>
                        </div>
                        @error('items') <div class="mb-3 text-xs font-semibold text-red-600">{{ $message }}</div> @enderror

                        <div id="siTableWrap" class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                            <div class="max-h-[350px] overflow-y-auto si-hidden-scrollbar">
                                <table class="min-w-full table-fixed text-left">
                                    <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                                        <tr>
                                            <th class="w-[36%] px-4 py-3">Sản phẩm / SKU</th>
                                            <th class="w-[12%] px-3 py-3">Tồn kho</th>
                                            <th class="w-[14%] px-3 py-3 text-center">Số lượng</th>
                                            <th class="w-[18%] px-3 py-3 text-right">Tổng</th>
                                            <th class="w-[12%] px-3 py-3 text-center">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody id="siTableBody" class="divide-y divide-slate-100 text-sm"></tbody>
                                </table>

                                <div id="siTableEmpty" class="px-6 py-14 text-center">
                                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                        <i class="fa-solid fa-box-open"></i>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-600">Chưa có sản phẩm nào trong phiếu</div>
                                    <div class="mt-1 text-xs text-slate-400">Tìm và chọn sản phẩm ở ô phía trên để thêm vào phiếu.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <div class="text-right">
                                <div class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">Tổng cộng đơn xuất</div>
                                <div id="siTotalAmount" class="mt-1 text-3xl font-extrabold tracking-tight text-emerald-600">0đ</div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-200 bg-slate-100/80 px-8 py-5">
                    <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}"
                        class="inline-flex min-w-[120px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        Hủy bỏ
                    </a>
                    <button type="submit"
                        data-si-action="draft"
                        class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <span>💾</span>
                        <span>Lưu nháp</span>
                    </button>
                    <button type="submit"
                        data-si-action="issue"
                        class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-xl bg-emerald-700 px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-800 focus:ring-4 focus:ring-emerald-200">
                        <span>✓</span>
                        <span>Xuất kho ngay</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
(function () {
    const variants = @json($variants);

    const pickerInput = document.getElementById('siPickerInput');
    const pickerPanel = document.getElementById('siPickerPanel');
    const tableBody = document.getElementById('siTableBody');
    const tableEmpty = document.getElementById('siTableEmpty');
    const totalEl = document.getElementById('siTotalAmount');
    const form = document.getElementById('stockIssueForm');
    const actionInput = document.getElementById('siActionInput');

    const demoRows = [
        {
            product_name: 'Áo Thun Nam Basic Tee',
            sku: 'UNI001-WHT-S',
            color_name: 'White',
            size_name: 'S',
            stock: 43,
            quantity: 10,
            unit_price: 119400,
            thumbnail: 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=160&q=80',
            product_variant_id: null,
        },
        {
            product_name: 'Quần Jean Nam Ống Đứng',
            sku: 'QJ-022-L-32',
            color_name: 'Blue',
            size_name: '32',
            stock: 12,
            quantity: 2,
            unit_price: 280000,
            thumbnail: 'https://images.unsplash.com/photo-1542272604-787c3835535d?auto=format&fit=crop&w=160&q=80',
            product_variant_id: null,
        },
    ];

    let selected = {};

    seedInitialRows();

    function seedInitialRows() {
        const firstVariant = variants[0] || null;
        const secondVariant = variants.find(v => String(v.id) !== String(firstVariant?.id)) || null;
        const variantSeeds = [firstVariant, secondVariant].filter(Boolean);

        if (variantSeeds.length >= 2) {
            addVariant(variantSeeds[0].id, 10);
            addVariant(variantSeeds[1].id, 2);
            return;
        }

        demoRows.forEach((row, index) => {
            selected[`demo-${index}`] = {
                variant: row,
                quantity: row.quantity,
                unit_price: row.unit_price,
                isDemo: true,
            };
        });
        renderTable();
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

    function filterVariants(query) {
        query = query.trim().toLowerCase();
        if (!query) return [];

        return variants.filter(v => {
            const haystack = `${v.product_name} ${v.sku} ${v.color_name} ${v.size_name}`.toLowerCase();
            return haystack.includes(query);
        }).slice(0, 30);
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

    pickerInput.addEventListener('input', function () {
        renderPicker(filterVariants(this.value));
    });

    pickerInput.addEventListener('focus', function () {
        if (this.value.trim()) renderPicker(filterVariants(this.value));
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('#siPickerInput') && !e.target.closest('#siPickerPanel')) {
            pickerPanel.hidden = true;
        }
    });

    pickerPanel.addEventListener('click', function (e) {
        const item = e.target.closest('[data-variant-id]');
        if (!item) return;

        addVariant(item.dataset.variantId);
        pickerInput.value = '';
        pickerPanel.hidden = true;
        pickerInput.focus();
    });

    function addVariant(variantId, forcedQuantity = 1) {
        const variant = variants.find(v => String(v.id) === String(variantId));
        if (!variant) return;

        if (selected[variantId]) {
            const row = tableBody.querySelector(`tr[data-variant-id="${variantId}"]`);
            row?.querySelector('[data-field="quantity"]')?.focus();
            return;
        }

        selected[variantId] = {
            variant,
            quantity: Math.min(forcedQuantity, variant.stock),
            unit_price: variant.unit_price || 0,
            isDemo: false,
        };
        renderTable();
    }

    function removeVariant(variantId) {
        delete selected[variantId];
        renderTable();
    }

    function renderTable() {
        const ids = Object.keys(selected);
        tableEmpty.classList.toggle('hidden', ids.length > 0);
        tableBody.innerHTML = '';

        ids.forEach(function (variantId) {
            const item = selected[variantId];
            const { variant, quantity, unit_price, isDemo } = item;
            const lineTotal = quantity * unit_price;
            const overStock = quantity > variant.stock;

            const tr = document.createElement('tr');
            tr.dataset.variantId = variantId;
            tr.className = 'bg-white hover:bg-slate-50/80';
            tr.innerHTML = `
                <td class="px-4 py-4">
                    <div class="flex items-center gap-3">
                        <img class="h-12 w-12 flex-none rounded-xl object-cover ring-1 ring-slate-200" src="${resolveImageUrl(variant.thumbnail)}" alt="">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-bold text-slate-950">${esc(variant.product_name)}</div>
                            <div class="mt-0.5 truncate text-xs font-medium text-slate-500">${esc(variant.color_name)} - ${esc(variant.size_name)} | SKU: ${esc(variant.sku)}</div>
                        </div>
                    </div>
                    ${isDemo ? '' : `<input type="hidden" name="items[${variantId}][product_variant_id]" value="${variantId}">`}
                </td>
                <td class="px-3 py-4 text-sm font-semibold text-slate-500">${variant.stock} cái</td>
                <td class="px-3 py-4 text-center">
                    <input type="number" min="1" max="${variant.stock}" step="1"
                        class="mx-auto h-9 w-16 rounded-lg border ${overStock ? 'border-red-400' : 'border-slate-300'} bg-white text-center text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        data-field="quantity"
                        ${isDemo ? '' : `name="items[${variantId}][quantity]"`}
                        value="${quantity}">
                    ${isDemo ? '' : `<input type="hidden" data-field="unit-price-hidden" name="items[${variantId}][unit_price]" value="${unit_price}">`}
                </td>
                <td class="px-3 py-4 text-right text-sm font-bold text-slate-900" data-field="line-total">${money(lineTotal)}</td>
                <td class="px-3 py-4 text-center">
                    <button type="button"
                        data-remove-variant="${variantId}"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-500 transition hover:bg-red-50 hover:text-red-600"
                        title="Xóa">
                        <i class="fa-regular fa-trash-can"></i>
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
        if (!selected[variantId] || !e.target.matches('[data-field="quantity"]')) return;

        const stock = selected[variantId].variant.stock;
        const qty = Math.max(1, parseInt(e.target.value, 10) || 1);
        selected[variantId].quantity = qty;
        e.target.classList.toggle('border-red-400', qty > stock);

        const lineTotal = selected[variantId].quantity * selected[variantId].unit_price;
        row.querySelector('[data-field="line-total"]').textContent = money(lineTotal);
        updateTotal();
    });

    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-remove-variant]');
        if (!btn) return;
        removeVariant(btn.dataset.removeVariant);
    });

    function updateTotal() {
        const total = Object.values(selected).reduce((sum, item) => sum + item.quantity * item.unit_price, 0);
        totalEl.textContent = money(total);
    }

    document.querySelectorAll('[data-si-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            actionInput.value = this.dataset.siAction;
        });
    });

    form.addEventListener('submit', function (e) {
        const realItems = Object.values(selected).filter(item => !item.isDemo);
        if (realItems.length === 0) {
            e.preventDefault();
            alert('Vui lòng thêm ít nhất một sản phẩm thật từ danh sách tìm kiếm vào phiếu xuất kho.');
            return;
        }

        const overStockItem = realItems.find(item => item.quantity > item.variant.stock);
        if (overStockItem) {
            e.preventDefault();
            alert(`Số lượng xuất "${overStockItem.variant.product_name}" vượt quá tồn kho hiện có (${overStockItem.variant.stock}).`);
        }
    });
})();
</script>
@endpush
