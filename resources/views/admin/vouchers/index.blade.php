@extends('layouts.admin')

@section('title', 'Quản lý voucher')

@push('styles')
    @include('admin.vouchers.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý voucher</h1>
                    <p class="product-header-desc mb-0">Danh sách tất cả mã giảm giá trong hệ thống.</p>
                </div>
                <div class="product-header-actions">
                    {{-- Nút Xuất Excel: ưu tiên checkbox đã chọn, nếu không thì theo bộ lọc --}}
                    <button type="button" id="voucherExportBtn"
                       class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
                    </button>
                    {{-- Form ẩn để POST danh sách IDs sang export --}}
                    <form id="voucherExportForm" method="GET" action="{{ route('admin.vouchers.export') }}" style="display:none;">
                        @csrf
                    </form>
                    <a href="{{ route('admin.vouchers.trash') }}" class="btn product-action-btn product-action-btn--trash">
                        <i class="fa-regular fa-trash-can me-1"></i> Th&#249;ng r&#225;c
                    </a>
                    <a href="{{ route('admin.vouchers.create') }}" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm voucher
                    </a>
                </div>
            </div>

            <div data-admin-stats-area>
                @include('admin.vouchers.partials.stats')
            </div>

            @php
                $statusVal = $statusFilter ?? '';
                $typeVal = $typeFilter ?? '';
                $typeLabelMap = ['' => 'Tất cả kiểu giảm', 'percentage' => 'Theo phần trăm %', 'fixed' => 'Theo số tiền cố định'];
                $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Hoạt động', '0' => 'Khóa'];
            @endphp

            <form method="GET" action="{{ route('admin.vouchers.list') }}" id="voucherFilterForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="status" data-admin-filter id="voucherStatusHidden" value="{{ $statusVal }}">
                <input type="hidden" name="type" data-admin-filter id="voucherTypeHidden" value="{{ $typeVal }}">

                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="voucherSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm theo mã voucher..." autocomplete="off">

                    {{-- Filter trạng thái --}}
                    <div class="hk-cat-filter" id="hkVoucherStatusDrop">
                        <button type="button" class="hk-cat-trigger" id="hkVoucherStatusTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkVoucherStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkVoucherStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkVoucherStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Khóa">Khóa</button>
                            </div>
                        </div>
                    </div>

                    {{-- Lọc theo khoảng ngày hiệu lực --}}
                    <div class="hk-cat-filter" id="hkVoucherTypeDrop">
                        <button type="button" class="hk-cat-trigger" id="hkVoucherTypeTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkVoucherTypeLabel">{{ $typeLabelMap[$typeVal] ?? 'Tất cả kiểu giảm' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkVoucherTypePanel" hidden>
                            <div class="hk-cat-list" id="hkVoucherTypeList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $typeVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả kiểu giảm">Tất cả kiểu giảm</button>
                                <button type="button" class="hk-cat-item {{ $typeVal === 'percentage' ? 'is-active' : '' }}" data-value="percentage" data-label="Theo phần trăm %">Theo phần trăm %</button>
                                <button type="button" class="hk-cat-item {{ $typeVal === 'fixed' ? 'is-active' : '' }}" data-value="fixed" data-label="Theo số tiền cố định">Theo số tiền cố định</button>
                            </div>
                        </div>
                    </div>

                    {{-- Bộ lọc kỳ: dropdown chọn nhanh Năm / Quý / Tháng --}}
                    @php
                        $dfVal = $dateFrom ?? '';
                        $dtVal = $dateTo   ?? '';
                        $periodLabel = 'Chọn kỳ';
                    @endphp

                    <div class="vk-period-wrap" id="vkPeriodWrap">
                        <button type="button" class="vk-period-trigger {{ ($dfVal || $dtVal) ? 'is-active' : '' }}" id="vkPeriodTrigger" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <span id="vkPeriodLabel">{{ $periodLabel }}</span>
                            <i class="fa-solid fa-chevron-down vk-period-caret"></i>
                        </button>

                        <div class="vk-period-panel" id="vkPeriodPanel" hidden>
                            {{-- Quick actions --}}
                            <div class="vk-quick-row">
                                <button type="button" class="vk-quick-btn" id="vkBtnThisYear">Năm nay</button>
                                <button type="button" class="vk-quick-btn" id="vkBtnLastYear">Năm ngoái</button>
                                <button type="button" class="vk-quick-clear" id="vkBtnClear">Xoá chọn</button>
                            </div>

                            {{-- Năm --}}
                            <div class="vk-section-row">
                                <span class="vk-section-label">Năm</span>
                                <select class="vk-year-select" id="vkYearSelect">
                                    @for($y = now()->year + 1; $y >= now()->year - 4; $y--)
                                        <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            {{-- Quý --}}
                            <div class="vk-section-label">QUÝ</div>
                            <div class="vk-chip-row">
                                <button type="button" class="vk-chip" data-quarter="1">Quý 1</button>
                                <button type="button" class="vk-chip" data-quarter="2">Quý 2</button>
                                <button type="button" class="vk-chip" data-quarter="3">Quý 3</button>
                                <button type="button" class="vk-chip" data-quarter="4">Quý 4</button>
                            </div>

                            {{-- Tháng --}}
                            <div class="vk-section-label">THÁNG</div>
                            <div class="vk-chip-row vk-chip-row--months">
                                @for($m = 1; $m <= 12; $m++)
                                    <button type="button" class="vk-chip" data-month="{{ $m }}">Th {{ $m }}</button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- 2 ô ngày đặt ra ngoài, cạnh nút Chọn kỳ --}}
                    <div class="vk-date-range">
                        <input type="date" name="date_from" id="voucherDateFrom" data-admin-filter class="form-control vk-date-range-input"
                            value="{{ $dfVal }}" title="Từ ngày">
                        <span class="vk-date-range-sep">—</span>
                        <input type="date" name="date_to" id="voucherDateTo" data-admin-filter class="form-control vk-date-range-input"
                            value="{{ $dtVal }}" title="Đến ngày">
                    </div>

                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.vouchers.partials.table')
            </div>

            <div class="hk-cat-panel voucher-status-shared-panel" id="voucherStatusSharedPanel" hidden>
                <div class="hk-cat-list" role="listbox">
                    <button type="button" class="hk-cat-item" data-value="1" data-css="status-badge--active">Hoáº¡t Ä‘á»™ng</button>
                    <button type="button" class="hk-cat-item" data-value="0" data-css="status-badge--inactive">KhÃ³a</button>
                </div>
            </div>
            <div class="voucher-edit-modal" id="voucherEditModal" hidden>
                <div class="voucher-edit-modal__overlay" data-voucher-edit-close></div>
                <div class="voucher-edit-modal__dialog" role="dialog" aria-modal="true">
                    <div class="voucher-edit-modal__header">
                        <div>
                            <h2>S&#7917;a voucher</h2>
                            <p>C&#7853;p nh&#7853;t th&#244;ng tin m&#227; gi&#7843;m gi&#225;.</p>
                        </div>
                        <button type="button" class="voucher-edit-modal__close" data-voucher-edit-close aria-label="Dong">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="voucher-edit-modal__body" id="voucherEditModalBody">
                        <div class="text-center text-muted py-5">Dang tai...</div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        /* ── Filter trạng thái ── */
        (function () {
            const trigger = document.getElementById('hkVoucherStatusTrigger');
            const panel   = document.getElementById('hkVoucherStatusPanel');
            const label   = document.getElementById('hkVoucherStatusLabel');
            const list    = document.getElementById('hkVoucherStatusList');
            const hidden  = document.getElementById('voucherStatusHidden');
            if (!trigger) return;

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

            trigger.addEventListener('click', () => panel.hidden ? open() : close());

            list.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                label.textContent = btn.dataset.label;
                hidden.value = btn.dataset.value;
                close();
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !document.getElementById('hkVoucherStatusDrop')?.contains(e.target)) close();
            });
        }());

        (function () {
            const trigger = document.getElementById('hkVoucherTypeTrigger');
            const panel   = document.getElementById('hkVoucherTypePanel');
            const label   = document.getElementById('hkVoucherTypeLabel');
            const list    = document.getElementById('hkVoucherTypeList');
            const hidden  = document.getElementById('voucherTypeHidden');
            if (!trigger) return;

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

            trigger.addEventListener('click', () => panel.hidden ? open() : close());

            list.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                label.textContent = btn.dataset.label;
                hidden.value = btn.dataset.value;
                close();
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !document.getElementById('hkVoucherTypeDrop')?.contains(e.target)) close();
            });
        }());

        /* ── Sổ xuống chọn nhanh trạng thái ngay trong bảng ── */
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            function closeAllPanels(except) {
                document.querySelectorAll('.voucher-status-panel').forEach(function (p) {
                    if (p === except) return;
                    p.hidden = true;
                    p.closest('.voucher-status-dropdown')?.querySelector('.voucher-status-trigger')?.classList.remove('is-open');
                });
            }

            document.addEventListener('click', async function (e) {
                const trigger = e.target.closest('.voucher-status-trigger');
                if (trigger) {
                    const dropdown = trigger.closest('.voucher-status-dropdown');
                    const panel = dropdown.querySelector('.voucher-status-panel');
                    const willOpen = panel.hidden;
                    closeAllPanels();
                    panel.hidden = !willOpen;
                    trigger.classList.toggle('is-open', willOpen);
                    return;
                }

                const item = e.target.closest('.voucher-status-panel .hk-cat-item');
                if (item) {
                    const dropdown = item.closest('.voucher-status-dropdown');
                    const btn = dropdown.querySelector('.voucher-status-trigger');
                    const newValue = item.dataset.value;
                    const newCss = item.dataset.css;
                    closeAllPanels();

                    if (newValue === btn.dataset.value) return;

                    const previousValue = btn.dataset.value;
                    const previousCss = Array.from(btn.classList).find(c => c.startsWith('status-badge--'));
                    btn.disabled = true;

                    try {
                        const res = await fetch(dropdown.dataset.toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Accept':           'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN':     csrf,
                            },
                            body: new URLSearchParams({ _method: 'PATCH' }),
                        });

                        if (!res.ok) throw new Error('toggle failed');

                        btn.className = 'status-badge voucher-status-trigger ' + newCss;
                        btn.dataset.value = newValue;
                        btn.querySelector('.voucher-status-trigger-label').textContent = item.textContent;
                        dropdown.querySelectorAll('.hk-cat-item').forEach(function (b) {
                            b.classList.toggle('is-active', b === item);
                        });
                    } catch {
                        alert('Không thể cập nhật trạng thái. Vui lòng thử lại.');
                        btn.className = 'status-badge voucher-status-trigger ' + (previousCss ?? '');
                        btn.dataset.value = previousValue;
                    } finally {
                        btn.disabled = false;
                    }
                    return;
                }

                if (!e.target.closest('.voucher-status-dropdown')) {
                    closeAllPanels();
                }
            });
        }());

        /* Panel tráº¡ng thÃ¡i ná»•i ngoÃ i báº£ng Ä‘á»ƒ khÃ´ng bá»‹ che bá»Ÿi table/pagination. */
        (function () {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const panel = document.getElementById('voucherStatusSharedPanel');
            let activeTrigger = null;

            if (!panel) return;

            function syncActive(value) {
                panel.querySelectorAll('.hk-cat-item').forEach(function (item) {
                    item.textContent = item.dataset.value === '1' ? 'Ho\u1ea1t \u0111\u1ed9ng' : 'Kh\u00f3a';
                    item.classList.toggle('is-active', item.dataset.value === value);
                });
            }

            function placePanel(trigger) {
                panel.hidden = false;
                const rect = trigger.getBoundingClientRect();
                const width = panel.offsetWidth || 180;
                const height = panel.offsetHeight || 96;
                const gap = 8;
                const safe = 12;
                const top = rect.bottom + gap + height <= window.innerHeight - safe
                    ? rect.bottom + gap
                    : Math.max(safe, rect.top - height - gap);
                const left = Math.min(Math.max(safe, rect.left), window.innerWidth - width - safe);

                panel.style.top = `${top}px`;
                panel.style.left = `${left}px`;
            }

            function closePanel() {
                panel.hidden = true;
                if (activeTrigger) {
                    activeTrigger.classList.remove('is-open');
                    activeTrigger.setAttribute('aria-expanded', 'false');
                }
                activeTrigger = null;
            }

            function openPanel(trigger) {
                if (activeTrigger && activeTrigger !== trigger) {
                    activeTrigger.classList.remove('is-open');
                    activeTrigger.setAttribute('aria-expanded', 'false');
                }

                activeTrigger = trigger;
                syncActive(trigger.dataset.value);
                placePanel(trigger);
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }

            document.addEventListener('click', async function (e) {
                const trigger = e.target.closest('.voucher-status-trigger');
                if (trigger) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    if (activeTrigger === trigger && !panel.hidden) {
                        closePanel();
                    } else {
                        openPanel(trigger);
                    }
                    return;
                }

                const item = e.target.closest('#voucherStatusSharedPanel .hk-cat-item');
                if (item) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const btn = activeTrigger;
                    if (!btn) return;

                    const newValue = item.dataset.value;
                    const newCss = item.dataset.css;
                    const newLabel = item.textContent.trim();
                    const previousValue = btn.dataset.value;
                    const previousCss = Array.from(btn.classList).find(c => c.startsWith('status-badge--'));
                    const previousLabel = btn.querySelector('.voucher-status-trigger-label')?.textContent ?? '';
                    const toggleUrl = btn.dataset.toggleUrl || btn.closest('.voucher-status-dropdown')?.dataset.toggleUrl;

                    closePanel();
                    if (newValue === previousValue) return;

                    btn.disabled = true;
                    try {
                        const res = await fetch(toggleUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: new URLSearchParams({ _method: 'PATCH' }),
                        });

                        if (!res.ok) throw new Error('toggle failed');

                        btn.className = 'status-badge voucher-status-trigger ' + newCss;
                        btn.dataset.value = newValue;
                        btn.querySelector('.voucher-status-trigger-label').textContent = newLabel;
                        btn.closest('td')?.setAttribute('data-sort-value', newValue);
                        const expiryBadge = btn.closest('tr')?.querySelector('[data-voucher-expiry-badge]');
                        if (expiryBadge) {
                            expiryBadge.classList.remove('voucher-expiry-badge--active', 'voucher-expiry-badge--soon', 'voucher-expiry-badge--expired', 'voucher-expiry-badge--paused');
                            if (newValue === '0') {
                                expiryBadge.classList.add('voucher-expiry-badge--paused');
                                expiryBadge.textContent = 'Tạm hoãn';
                            } else {
                                expiryBadge.classList.add(expiryBadge.dataset.activeCss || 'voucher-expiry-badge--active');
                                expiryBadge.textContent = expiryBadge.dataset.activeLabel || 'Còn hạn';
                            }
                        }
                    } catch {
                        alert('KhÃ´ng thá»ƒ cáº­p nháº­t tráº¡ng thÃ¡i. Vui lÃ²ng thá»­ láº¡i.');
                        btn.className = 'status-badge voucher-status-trigger ' + (previousCss ?? '');
                        btn.dataset.value = previousValue;
                        btn.querySelector('.voucher-status-trigger-label').textContent = previousLabel;
                    } finally {
                        btn.disabled = false;
                    }
                    return;
                }

                if (!e.target.closest('#voucherStatusSharedPanel')) closePanel();
            }, true);

            window.addEventListener('scroll', function () {
                if (activeTrigger && !panel.hidden) placePanel(activeTrigger);
            }, true);
            window.addEventListener('resize', closePanel);
        }());

        (function () {
            const topTrash = document.getElementById('voucherBulkTrashTop');
            if (!topTrash) return;

            topTrash.addEventListener('click', function () {
                const tableArea = document.querySelector('[data-admin-table-area]');
                const checked = Array.from(tableArea?.querySelectorAll('.hk-cb-row:checked') || []);
                if (!checked.length) {
                    alert('Vui long chon it nhat mot voucher de xoa.');
                    return;
                }

                const paginationDeleteBtn = tableArea?.querySelector('.hk-pg-sel-delete');
                if (paginationDeleteBtn) {
                    paginationDeleteBtn.click();
                }
            });
        }());

        (function () {
            const modal = document.getElementById('voucherEditModal');
            const body = document.getElementById('voucherEditModalBody');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            if (!modal || !body) return;

            function closeModal() {
                modal.hidden = true;
                body.innerHTML = '<div class="text-center text-muted py-5">Dang tai...</div>';
                document.body.style.overflow = '';
            }

            function initModalForm() {
                const form = body.querySelector('#voucherEditForm');
                const codeInput = body.querySelector('#code');
                const trigger = body.querySelector('#vcTypeTrigger');
                const panel = body.querySelector('#vcTypePanel');
                const label = body.querySelector('#vcTypeLabel');
                const list = body.querySelector('#vcTypeList');
                const hidden = body.querySelector('#vcTypeHidden');
                const valueHint = body.querySelector('#vcValueHint');

                const valueInput = form.querySelector('#value');
                const maxDiscountInput = form.querySelector('#max_discount_amount');

                codeInput?.addEventListener('input', function () {
                    const pos = this.selectionStart;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(pos, pos);
                });

                function syncHint() {
                    if (!valueHint || !hidden) return;
                    if (hidden.value === 'fixed') {
                        valueHint.textContent = 'Nhap so tien giam co dinh (VND).';
                        valueInput?.removeAttribute('max');
                        if (maxDiscountInput) {
                            maxDiscountInput.setAttribute('disabled', 'disabled');
                            maxDiscountInput.value = '';
                            maxDiscountInput.placeholder = 'Khong ap dung cho tien mat';
                        }
                    } else {
                        valueHint.textContent = 'Nhap phan tram giam (0-100).';
                        valueInput?.setAttribute('max', '100');
                        if (valueInput && parseFloat(valueInput.value) > 100) {
                            valueInput.value = 100;
                        }
                        if (maxDiscountInput) {
                            maxDiscountInput.removeAttribute('disabled');
                            maxDiscountInput.placeholder = 'Khong gioi han';
                        }
                    }
                }

                valueInput?.addEventListener('input', function () {
                    if (hidden.value === 'percentage') {
                        if (parseFloat(this.value) > 100) {
                            this.value = 100;
                        }
                    }
                });

                trigger?.addEventListener('click', function () {
                    panel.hidden = !panel.hidden;
                    trigger.classList.toggle('is-open', !panel.hidden);
                });

                list?.addEventListener('click', function (e) {
                    const btn = e.target.closest('.hk-cat-item');
                    if (!btn) return;
                    list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                    btn.classList.add('is-active');
                    label.textContent = btn.dataset.label;
                    hidden.value = btn.dataset.value;
                    panel.hidden = true;
                    syncHint();
                });

                form?.addEventListener('submit', async function (e) {
                    e.preventDefault();
                    const submitBtn = form.querySelector('button[type="submit"]');
                    submitBtn?.setAttribute('disabled', 'disabled');

                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrf,
                            },
                            body: new FormData(form),
                        });

                        if (res.status === 422) {
                            const data = await res.json();
                            const msgs = Object.values(data.errors || {}).flat();
                            showAdminToast(msgs.length ? msgs.join('<br>') : 'Dữ liệu chưa hợp lệ. Vui lòng kiểm tra lại.', 'error');
                            return;
                        }

                        if (!res.ok) throw new Error('update failed');

                        const data = await res.json();
                        closeModal();
                        showAdminToast(data.message || 'Cập nhật voucher thành công.', 'success');
                        if (data.warning) {
                            setTimeout(() => showAdminToast(data.warning, 'warning'), 400);
                        }
                        setTimeout(() => window.reloadAdminTable?.(), 600);
                    } catch {
                        showAdminToast('Không thể cập nhật voucher. Vui lòng thử lại.', 'error');
                    } finally {
                        submitBtn?.removeAttribute('disabled');
                    }
                });

                syncHint();
            }

            async function openEdit(url) {
                modal.hidden = false;
                document.body.style.overflow = 'hidden';
                body.innerHTML = '<div class="text-center text-muted py-5">Dang tai...</div>';

                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const form = doc.querySelector('#voucherEditForm');
                    if (!form) throw new Error('form not found');

                    body.innerHTML = '';
                    body.appendChild(form);
                    initModalForm();
                } catch {
                    body.innerHTML = '<div class="alert alert-danger mb-0">Khong the tai form chinh sua voucher.</div>';
                }
            }

            document.addEventListener('click', function (e) {
                const editLink = e.target.closest('[data-voucher-edit-url]');
                if (editLink) {
                    e.preventDefault();
                    openEdit(editLink.dataset.voucherEditUrl || editLink.href);
                    return;
                }

                if (e.target.closest('[data-voucher-edit-close]')) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hidden) closeModal();
            });

            // Auto open edit modal if ?edit=ID exists in URL parameters (redirected from direct link)
            (function autoOpenEditModal() {
                const params = new URLSearchParams(window.location.search);
                const editId = params.get('edit');
                if (editId) {
                    const cleanSearch = window.location.search.replace(/[?&]edit=[^&]+/, '').replace(/^&/, '?').replace(/\?$/, '');
                    const newUrl = window.location.pathname + cleanSearch;
                    window.history.replaceState({}, '', newUrl);

                    openEdit(`/admin/vouchers/${editId}/edit`);
                }
            }());
        }());

        /* ─────────────────────────────────────────────────────
         * PERIOD PICKER — Chọn kỳ (Năm / Quý / Tháng)
         * ───────────────────────────────────────────────────── */
        (function () {
            const trigger    = document.getElementById('vkPeriodTrigger');
            const panel      = document.getElementById('vkPeriodPanel');
            const label      = document.getElementById('vkPeriodLabel');
            const inputFrom  = document.getElementById('voucherDateFrom');
            const inputTo    = document.getElementById('voucherDateTo');
            const yearSelect = document.getElementById('vkYearSelect');

            if (!trigger) return;

            /* ── helpers ── */
            function pad2(n) { return String(n).padStart(2, '0'); }

            function fmtDisplay(from, to) {
                /* from/to: 'YYYY-MM-DD' */
                if (!from && !to) return 'Chọn kỳ';
                const f = from ? from.split('-').reverse().join('/') : '?';
                const t = to   ? to.split('-').reverse().join('/')   : '?';
                return f + ' – ' + t;
            }

            function applyDates(from, to, chipLabel) {
                inputFrom.value = from;
                inputTo.value   = to;

                label.textContent = chipLabel || fmtDisplay(from, to);
                trigger.classList.toggle('is-active', !!(from || to));

                /* sync active chip */
                panel.querySelectorAll('.vk-chip').forEach(function (c) {
                    c.classList.remove('is-active');
                });

                /* trigger realtime-table filter */
                inputFrom.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function getYear() { return parseInt(yearSelect.value, 10); }

            /* ── open / close ── */
            function openPanel() {
                panel.hidden = false;
                trigger.classList.add('is-open');
                trigger.setAttribute('aria-expanded', 'true');
            }
            function closePanel() {
                panel.hidden = true;
                trigger.classList.remove('is-open');
                trigger.setAttribute('aria-expanded', 'false');
            }

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                panel.hidden ? openPanel() : closePanel();
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !document.getElementById('vkPeriodWrap')?.contains(e.target)) {
                    closePanel();
                }
            });

            const nowYear = new Date().getFullYear();
            const yearsBack = 6;
            function iso(y, m, d) { return y + '-' + pad2(m) + '-' + pad2(d); }
            function lastDayOfMonth(y, m) { return new Date(y, m, 0).getDate(); }
            function yearRange(y) { return [iso(y, 1, 1), iso(y, 12, 31)]; }
            function quarterRange(y, q) {
                const startMonth = (q - 1) * 3 + 1;
                const endMonth   = startMonth + 2;
                return [iso(y, startMonth, 1), iso(y, endMonth, lastDayOfMonth(y, endMonth))];
            }
            function monthRange(y, m) {
                return [iso(y, m, 1), iso(y, m, lastDayOfMonth(y, m))];
            }

            function syncLabelFromInputs() {
                const fromVal = inputFrom.value;
                const toVal   = inputTo.value;
                if (!fromVal || !toVal) {
                    label.textContent = 'Chọn kỳ';
                    trigger.classList.remove('is-active');
                    panel.querySelectorAll('.vk-chip').forEach(c => c.classList.remove('is-active'));
                    return;
                }

                trigger.classList.add('is-active');

                for (let y = nowYear; y >= nowYear - yearsBack; y--) {
                    const yr = yearRange(y);
                    if (fromVal === yr[0] && toVal === yr[1]) {
                        label.textContent = y === nowYear ? 'Năm nay' : (y === nowYear - 1 ? 'Năm ngoái' : 'Năm ' + y);
                        yearSelect.value = String(y);
                        
                        panel.querySelectorAll('.vk-chip').forEach(c => c.classList.remove('is-active'));
                        return;
                    }
                    for (let q = 1; q <= 4; q++) {
                        const qr = quarterRange(y, q);
                        if (fromVal === qr[0] && toVal === qr[1]) {
                            label.textContent = 'Quý ' + q + ' năm ' + y;
                            yearSelect.value = String(y);
                            
                            panel.querySelectorAll('.vk-chip').forEach(c => {
                                c.classList.toggle('is-active', c.dataset.quarter === String(q));
                            });
                            return;
                        }
                    }
                    for (let m = 1; m <= 12; m++) {
                        const mr = monthRange(y, m);
                        if (fromVal === mr[0] && toVal === mr[1]) {
                            label.textContent = 'Tháng ' + m + '/' + y;
                            yearSelect.value = String(y);
                            
                            panel.querySelectorAll('.vk-chip').forEach(c => {
                                c.classList.toggle('is-active', c.dataset.month === String(m));
                            });
                            return;
                        }
                    }
                }

                label.textContent = fmtDisplay(fromVal, toVal);
                panel.querySelectorAll('.vk-chip').forEach(c => c.classList.remove('is-active'));
            }

            inputFrom.addEventListener('change', syncLabelFromInputs);
            inputTo.addEventListener('change', syncLabelFromInputs);

            /* ── Năm nay / Năm ngoái / Xoá chọn ── */
            document.getElementById('vkBtnThisYear')?.addEventListener('click', function () {
                const y = new Date().getFullYear();
                applyDates(y + '-01-01', y + '-12-31', 'Năm nay');
                if (yearSelect) yearSelect.value = y;
                closePanel();
            });

            document.getElementById('vkBtnLastYear')?.addEventListener('click', function () {
                const y = new Date().getFullYear() - 1;
                applyDates(y + '-01-01', y + '-12-31', 'Năm ngoái');
                if (yearSelect) yearSelect.value = y;
                closePanel();
            });

            document.getElementById('vkBtnClear')?.addEventListener('click', function () {
                applyDates('', '', 'Chọn kỳ');
                closePanel();
            });

            /* ── Quý ── */
            panel.querySelectorAll('.vk-chip[data-quarter]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const q = parseInt(this.dataset.quarter, 10);
                    const y = getYear();
                    const startMonth = (q - 1) * 3 + 1;
                    const endMonth   = q * 3;
                    const lastDay    = new Date(y, endMonth, 0).getDate();
                    const from = y + '-' + pad2(startMonth) + '-01';
                    const to   = y + '-' + pad2(endMonth)   + '-' + pad2(lastDay);
                    applyDates(from, to, 'Quý ' + q + ' năm ' + y);
                    this.classList.add('is-active');
                    closePanel();
                });
            });

            /* ── Tháng ── */
            panel.querySelectorAll('.vk-chip[data-month]').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const m = parseInt(this.dataset.month, 10);
                    const y = getYear();
                    const lastDay = new Date(y, m, 0).getDate();
                    const from = y + '-' + pad2(m) + '-01';
                    const to   = y + '-' + pad2(m) + '-' + pad2(lastDay);
                    applyDates(from, to, 'Tháng ' + m + '/' + y);
                    this.classList.add('is-active');
                    closePanel();
                });
            });

            /* ── Năm select (chỉ đổi năm, giữ tháng/quý đã chọn) ── */
            yearSelect?.addEventListener('change', function () {
                const activeChip = panel.querySelector('.vk-chip.is-active');
                if (!activeChip) return;
                /* re-trigger same chip at new year */
                activeChip.click();
            });

            /* Chạy đồng bộ nhãn lần đầu khi load trang */
            syncLabelFromInputs();
        }());


        /* ─────────────────────────────────────────────────────
         * EXPORT EXCEL — Ưu tiên checkbox, fallback bộ lọc
         * ───────────────────────────────────────────────────── */
        (function () {
            const exportBtn  = document.getElementById('voucherExportBtn');
            const exportForm = document.getElementById('voucherExportForm');
            if (!exportBtn || !exportForm) return;

            exportBtn.addEventListener('click', function () {
                /* Xoá inputs cũ trong form */
                exportForm.querySelectorAll('input:not([name="_token"])').forEach(i => i.remove());

                /* Thu thập checkbox đang được chọn */
                const tableArea = document.querySelector('[data-admin-table-area]');
                const checked   = Array.from(tableArea?.querySelectorAll('.hk-cb-row:checked') || []);

                if (checked.length > 0) {
                    /* Chế độ 1: xuất đúng danh sách ID checkbox */
                    checked.forEach(function (cb) {
                        const inp = document.createElement('input');
                        inp.type  = 'hidden';
                        inp.name  = 'ids[]';
                        inp.value = cb.value;
                        exportForm.appendChild(inp);
                    });
                } else {
                    /* Chế độ 2: xuất theo bộ lọc hiện tại */
                    const params = new URLSearchParams(window.location.search);
                    ['search','status','type','date_from','date_to'].forEach(function (key) {
                        if (params.get(key)) {
                            const inp = document.createElement('input');
                            inp.type  = 'hidden';
                            inp.name  = key;
                            inp.value = params.get(key);
                            exportForm.appendChild(inp);
                        }
                    });
                }

                exportForm.submit();
            });
        }());

    }());

    /* ── Toast helper — dùng chung toàn trang voucher ── */
    window.showAdminToast = function (message, type) {
        type = type || 'success';
        var container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }

        var iconPaths = {
            success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            warning: '<path d="M12 9v4M12 17h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            info:    '<path d="M12 16v-4M12 8h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        };

        var toast = document.createElement('div');
        toast.className = 'custom-toast server-toast toast-' + type;
        toast.innerHTML =
            '<div class="toast-content">' +
                '<div class="toast-icon">' +
                    '<svg viewBox="0 0 24 24" width="22" height="22" style="flex-shrink:0;display:block;">' +
                        (iconPaths[type] || iconPaths.success) +
                    '</svg>' +
                '</div>' +
                '<div class="toast-message">' + message + '</div>' +
            '</div>' +
            '<span class="toast-close" onclick="closeServerToast(this)">&times;</span>';

        container.appendChild(toast);

        setTimeout(function () {
            if (document.body.contains(toast)) {
                toast.classList.add('hiding');
                setTimeout(function () { toast.remove(); }, 300);
            }
        }, 5000);
    };
    </script>
@endpush
