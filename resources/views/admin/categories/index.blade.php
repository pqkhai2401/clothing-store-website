@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@push('styles')
    @include('admin.categories.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý danh mục</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả danh mục sản phẩm trong hệ thống.</p>
            </div>

            <form method="GET" action="{{ route('admin.categories.list') }}" id="catSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-filters-row">
                    <div class="product-toolbar-left">
                        <input type="search" name="search" data-admin-search id="catRealtimeSearch"
                            class="form-control product-search"
                            value="{{ $keyword }}"
                            placeholder="Tìm kiếm theo tên danh mục hoặc slug..." autocomplete="off">

                        @php
                            $statusVal = request('status', '');
                            $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Hoạt động', '0' => 'Ngưng hoạt động'];
                        @endphp
                        <input type="hidden" name="status" data-admin-filter id="catStatusFilter" value="{{ $statusVal }}">
                        <div class="hk-cat-filter" id="hkCatStatusFilter">
                            <button type="button" class="hk-cat-trigger" id="hkCatStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                                <span class="hk-cat-trigger-label" id="hkCatStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                                <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                            </button>
                            <div class="hk-cat-panel" id="hkCatStatusPanel" hidden>
                                <div class="hk-cat-list" id="hkCatStatusList" role="listbox">
                                    <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                    <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                    <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Ngưng hoạt động">Ngưng hoạt động</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="product-toolbar-right product-header-actions">
                        <a href="{{ route('admin.categories.trash') }}" class="btn btn-light border product-action-btn">
                            <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                        </a>
                        <button type="button" class="btn btn-dark product-action-btn"
                            data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <i class="fa-solid fa-plus me-1"></i> Thêm danh mục
                        </button>
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.categories.partials.table')
            </div>
        </section>

        {{-- Add Category Modal --}}
        <div class="modal fade hk-add-modal" id="addCategoryModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
                <div class="modal-content">
                    <form id="addCategoryForm" method="POST" autocomplete="off">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title">Thêm danh mục mới</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                        </div>
                        <div class="modal-body">
                            <div class="edit-field">
                                <label for="add_cat_name" class="form-label">
                                    Tên danh mục <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="add_cat_name" name="name" class="form-control"
                                    placeholder="Nhập tên danh mục...">
                                <div class="invalid-feedback d-block" data-error-for="name"></div>
                            </div>
                            <div class="edit-field">
                                <label for="add_cat_parent" class="form-label">Danh mục cha</label>
                                <select id="add_cat_parent" name="parent_id" class="form-select">
                                    <option value="">— Không có (Đây là danh mục cha) —</option>
                                    @foreach($parentCategories as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback d-block" data-error-for="parent_id"></div>
                                <div class="form-text">Bỏ trống nếu đây là danh mục cha. Chọn nếu đây là danh mục con.</div>
                            </div>
                            <div class="edit-field mb-0">
                                <label for="add_cat_status" class="form-label">
                                    Trạng thái <span class="text-danger">*</span>
                                </label>
                                <select id="add_cat_status" name="status" class="form-select">
                                    <option value="1">Hoạt động</option>
                                    <option value="0">Ẩn</option>
                                </select>
                                <div class="invalid-feedback d-block" data-error-for="status"></div>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-end">
                            <button type="button" class="btn btn-light border fw-semibold" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-dark fw-semibold" id="addCatSubmitBtn">
                                <i class="fa-solid fa-plus me-1"></i> Thêm danh mục
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        const modalEl   = document.getElementById('addCategoryModal');
        const form      = document.getElementById('addCategoryForm');
        const submitBtn = document.getElementById('addCatSubmitBtn');
        if (!modalEl || !form || !submitBtn) return;

        const csrfToken       = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const submitLabelHtml = submitBtn.innerHTML;

        const ICONS = {
            success: '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
            error:   '<path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" fill="none"/>',
        };

        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
            toast.style.pointerEvents = 'auto';
            toast.innerHTML = `
                <div class="toast-content">
                    <div class="toast-icon">
                        <svg style="flex-shrink:0;display:block;" viewBox="0 0 24 24" width="22" height="22">
                            ${ICONS[type] ?? ICONS.success}
                        </svg>
                    </div>
                    <div class="toast-message">${message}</div>
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

        function resetErrors() {
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            form.querySelectorAll('[data-error-for]').forEach(el => { el.textContent = ''; });
        }

        function showErrors(errors) {
            resetErrors();
            let first = null;
            Object.entries(errors || {}).forEach(([name, messages]) => {
                const field   = form.querySelector(`[name="${name}"]`);
                const errorEl = form.querySelector(`[data-error-for="${name}"]`);
                if (field) { field.classList.add('is-invalid'); first = first || field; }
                if (errorEl) errorEl.textContent = Array.isArray(messages) ? messages[0] : messages;
            });
            first?.focus();
        }

        modalEl.addEventListener('shown.bs.modal', () => form.elements['name']?.focus());
        modalEl.addEventListener('hidden.bs.modal', () => { form.reset(); resetErrors(); });

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            resetErrors();
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Đang thêm...';

            try {
                const res  = await fetch('{{ route("admin.categories.store") }}', {
                    method:  'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
                    body:    new FormData(form),
                });
                const data = await res.json();
                if (res.status === 422) { showErrors(data.errors || {}); return; }
                if (!res.ok) throw new Error(data.message || 'Có lỗi xảy ra.');
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                showToast(data.message || 'Thêm thành công');
                window.reloadAdminTable?.();
            } catch (err) {
                showToast(err.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitLabelHtml;
            }
        });
    })();
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function wireSimpleDropdown(config) {
                const root = document.getElementById(config.root);
                const trigger = document.getElementById(config.trigger);
                const panel = document.getElementById(config.panel);
                const label = document.getElementById(config.label);
                const list = document.getElementById(config.list);
                const hidden = document.getElementById(config.hidden);
                if (!root || !trigger || !panel || !list || !hidden) return;

                function open() {
                    panel.hidden = false;
                    trigger.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }

                function close() {
                    panel.hidden = true;
                    trigger.classList.remove('is-open');
                    trigger.setAttribute('aria-expanded', 'false');
                }

                trigger.addEventListener('click', function () {
                    panel.hidden ? open() : close();
                });

                list.addEventListener('click', function (event) {
                    const btn = event.target.closest('.hk-cat-item');
                    if (!btn) return;

                    list.querySelectorAll('.hk-cat-item').forEach(function (item) {
                        item.classList.remove('is-active');
                    });
                    btn.classList.add('is-active');
                    if (label) label.textContent = btn.dataset.label;
                    hidden.value = btn.dataset.value || '';
                    hidden.dispatchEvent(new Event('change', { bubbles: true }));
                    close();
                });

                document.addEventListener('click', function (event) {
                    if (!panel.hidden && !root.contains(event.target)) {
                        close();
                    }
                });
            }

            wireSimpleDropdown({
                root: 'hkCatStatusFilter',
                trigger: 'hkCatStatusTrigger',
                panel: 'hkCatStatusPanel',
                label: 'hkCatStatusLabel',
                list: 'hkCatStatusList',
                hidden: 'catStatusFilter',
            });

            const tableArea = document.querySelector('[data-admin-table-area]');
            if (!tableArea) return;

            let activeTooltip = null;
            let pinnedTooltip = null;

            function positionCategoryTooltip(container) {
                const tooltip = container.querySelector('.category-count-box');
                if (!tooltip) return;

                container.classList.add('is-open');

                const triggerRect = container.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                const viewportGap = 12;
                const spacing = 10;

                let left = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
                left = Math.max(viewportGap, Math.min(left, window.innerWidth - tooltipRect.width - viewportGap));

                let top = triggerRect.top - tooltipRect.height - spacing;
                if (top < viewportGap) {
                    top = triggerRect.bottom + spacing;
                }
                top = Math.max(viewportGap, Math.min(top, window.innerHeight - tooltipRect.height - viewportGap));

                tooltip.style.left = `${Math.round(left)}px`;
                tooltip.style.top = `${Math.round(top)}px`;
            }

            function closeCategoryTooltip() {
                if (!activeTooltip) return;

                const tooltip = activeTooltip.querySelector('.category-count-box');
                activeTooltip.classList.remove('is-open');
                if (tooltip) {
                    tooltip.style.left = '';
                    tooltip.style.top = '';
                }
                activeTooltip = null;
                pinnedTooltip = null;
            }

            function openCategoryTooltip(container, pinned = false) {
                if (activeTooltip && activeTooltip !== container) {
                    closeCategoryTooltip();
                }

                activeTooltip = container;
                if (pinned) pinnedTooltip = container;
                positionCategoryTooltip(container);
            }

            tableArea.addEventListener('pointerover', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || !tableArea.contains(container)) return;

                openCategoryTooltip(container);
            });

            tableArea.addEventListener('pointerout', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || container.contains(event.relatedTarget)) return;
                if (pinnedTooltip === container) return;

                closeCategoryTooltip();
            });

            tableArea.addEventListener('click', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || !tableArea.contains(container)) return;

                event.stopPropagation();

                if (event.target.closest('.category-count-box')) return;

                if (pinnedTooltip === container) {
                    closeCategoryTooltip();
                    return;
                }

                openCategoryTooltip(container, true);
            });

            tableArea.addEventListener('focusin', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (container) openCategoryTooltip(container);
            });

            tableArea.addEventListener('focusout', function () {
                if (!pinnedTooltip) closeCategoryTooltip();
            });

            document.addEventListener('click', function (event) {
                if (!activeTooltip || activeTooltip.contains(event.target)) return;
                closeCategoryTooltip();
            });

            window.addEventListener('scroll', function () {
                if (activeTooltip) positionCategoryTooltip(activeTooltip);
            }, true);

            window.addEventListener('resize', function () {
                if (activeTooltip) positionCategoryTooltip(activeTooltip);
            });
        });
    </script>
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        function closeAllPanels(except) {
            document.querySelectorAll('.category-status-panel').forEach(function (p) {
                if (p === except) return;
                p.hidden = true;
                p.closest('.category-status-dropdown')?.querySelector('.category-status-trigger')?.classList.remove('is-open');
            });
        }

        document.addEventListener('click', async function (e) {
            const trigger = e.target.closest('.category-status-trigger');
            if (trigger) {
                const dropdown = trigger.closest('.category-status-dropdown');
                const panel = dropdown.querySelector('.category-status-panel');
                const willOpen = panel.hidden;
                closeAllPanels();
                panel.hidden = !willOpen;
                trigger.classList.toggle('is-open', willOpen);
                return;
            }

            const item = e.target.closest('.category-status-panel .hk-cat-item');
            if (item) {
                const dropdown = item.closest('.category-status-dropdown');
                const btn = dropdown.querySelector('.category-status-trigger');
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

                    btn.className = 'status-badge category-status-trigger ' + newCss;
                    btn.dataset.value = newValue;
                    btn.querySelector('.category-status-trigger-label').textContent = item.textContent;
                    dropdown.querySelectorAll('.hk-cat-item').forEach(function (b) {
                        b.classList.toggle('is-active', b === item);
                    });
                } catch {
                    window.showAlert('Không thể cập nhật trạng thái. Vui lòng thử lại.', 'Lỗi', 'danger');
                    btn.className = 'status-badge category-status-trigger ' + (previousCss ?? '');
                    btn.dataset.value = previousValue;
                } finally {
                    btn.disabled = false;
                }
                return;
            }

            if (!e.target.closest('.category-status-dropdown')) {
                closeAllPanels();
            }
        });
    })();
    </script>
@endpush
