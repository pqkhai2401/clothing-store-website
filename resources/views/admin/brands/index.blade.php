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
                <div class="product-tool-actions">
                    <a href="{{ route('admin.brands.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm thương hiệu
                    </a>
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
        });
    </script>
@endpush
