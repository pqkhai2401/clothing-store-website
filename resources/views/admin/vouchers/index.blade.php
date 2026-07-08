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
                    <a href="{{ route('admin.vouchers.export') }}?{{ http_build_query(request()->except('page')) }}"
                       class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
                    </a>
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

                    <div class="voucher-date-range">
                        <input type="date" name="date_from" id="voucherDateFrom" data-admin-filter class="form-control voucher-date-input"
                            value="{{ $dateFrom ?? '' }}" title="Từ ngày">
                        <span class="voucher-date-sep">—</span>
                        <input type="date" name="date_to" id="voucherDateTo" data-admin-filter class="form-control voucher-date-input"
                            value="{{ $dateTo ?? '' }}" title="Đến ngày">
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

                codeInput?.addEventListener('input', function () {
                    const pos = this.selectionStart;
                    this.value = this.value.toUpperCase();
                    this.setSelectionRange(pos, pos);
                });

                function syncHint() {
                    if (!valueHint || !hidden) return;
                    valueHint.textContent = hidden.value === 'fixed'
                        ? 'Nhap so tien giam co dinh (VND).'
                        : 'Nhap phan tram giam (0-100).';
                }

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

                        if (!res.ok) {
                            if (res.status === 422) {
                                alert('Du lieu chua hop le. Vui long kiem tra lai form.');
                                return;
                            }
                            throw new Error('update failed');
                        }

                        closeModal();
                        window.location.reload();
                    } catch {
                        alert('Khong the cap nhat voucher. Vui long thu lai.');
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
        }());
    }());
    </script>
@endpush
