@extends('layouts.admin')

@section('title', 'Quản lý thương hiệu')

@push('styles')
    @include('admin.brands.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý thương hiệu</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả thương hiệu sản phẩm trong hệ thống.</p>
                <div class="product-header-actions">
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm thương hiệu
                    </a>
                    <a href="{{ route('admin.brands.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.brands.list') }}" id="brandSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="brandRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên thương hiệu..." autocomplete="off">

                    @php
                        $statusVal = request('status', '');
                        $statusLabelMap = ['' => 'Tất cả trạng thái', '1' => 'Hoạt động', '0' => 'Ngưng hoạt động'];
                    @endphp
                    <input type="hidden" name="status" data-admin-filter id="brandStatusFilter" value="{{ $statusVal }}">
                    <div class="hk-cat-filter" id="hkBrandStatusFilter">
                        <button type="button" class="hk-cat-trigger" id="hkBrandStatusTrigger" aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkBrandStatusLabel">{{ $statusLabelMap[$statusVal] ?? 'Tất cả trạng thái' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkBrandStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkBrandStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '1' ? 'is-active' : '' }}" data-value="1" data-label="Hoạt động">Hoạt động</button>
                                <button type="button" class="hk-cat-item {{ $statusVal === '0' ? 'is-active' : '' }}" data-value="0" data-label="Ngưng hoạt động">Ngưng hoạt động</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.brands.partials.table')
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('admin.partials.realtime-table')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const root = document.getElementById('hkBrandStatusFilter');
            const trigger = document.getElementById('hkBrandStatusTrigger');
            const panel = document.getElementById('hkBrandStatusPanel');
            const label = document.getElementById('hkBrandStatusLabel');
            const list = document.getElementById('hkBrandStatusList');
            const hidden = document.getElementById('brandStatusFilter');
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

            const tableArea = document.querySelector('[data-admin-table-area]');
            if (!tableArea) return;

            let activeTooltip = null;
            let pinnedTooltip = null;

            function positionCountTooltip(container) {
                const tooltip = container.querySelector('.category-count-box');
                if (!tooltip) return;
                container.classList.add('is-open');
                const triggerRect = container.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                const gap = 12, spacing = 10;
                let left = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
                left = Math.max(gap, Math.min(left, window.innerWidth - tooltipRect.width - gap));
                let top = triggerRect.top - tooltipRect.height - spacing;
                if (top < gap) top = triggerRect.bottom + spacing;
                top = Math.max(gap, Math.min(top, window.innerHeight - tooltipRect.height - gap));
                tooltip.style.left = `${Math.round(left)}px`;
                tooltip.style.top = `${Math.round(top)}px`;
            }

            function closeCountTooltip() {
                if (!activeTooltip) return;
                const tooltip = activeTooltip.querySelector('.category-count-box');
                activeTooltip.classList.remove('is-open');
                if (tooltip) { tooltip.style.left = ''; tooltip.style.top = ''; }
                activeTooltip = null;
                pinnedTooltip = null;
            }

            function openCountTooltip(container, pinned = false) {
                if (activeTooltip && activeTooltip !== container) closeCountTooltip();
                activeTooltip = container;
                if (pinned) pinnedTooltip = container;
                positionCountTooltip(container);
            }

            tableArea.addEventListener('pointerover', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || !tableArea.contains(container)) return;
                openCountTooltip(container);
            });

            tableArea.addEventListener('pointerout', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || container.contains(event.relatedTarget)) return;
                if (pinnedTooltip === container) return;
                closeCountTooltip();
            });

            tableArea.addEventListener('click', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (!container || !tableArea.contains(container)) return;
                event.stopPropagation();
                if (event.target.closest('.category-count-box')) return;
                if (pinnedTooltip === container) { closeCountTooltip(); return; }
                openCountTooltip(container, true);
            });

            tableArea.addEventListener('focusin', function (event) {
                const container = event.target.closest('.category-count-tooltip');
                if (container) openCountTooltip(container);
            });

            tableArea.addEventListener('focusout', function () {
                if (!pinnedTooltip) closeCountTooltip();
            });

            document.addEventListener('click', function (event) {
                if (!activeTooltip || activeTooltip.contains(event.target)) return;
                closeCountTooltip();
            });

            window.addEventListener('scroll', function () {
                if (activeTooltip) positionCountTooltip(activeTooltip);
            }, true);

            window.addEventListener('resize', function () {
                if (activeTooltip) positionCountTooltip(activeTooltip);
            });
        });
    </script>
@endpush
