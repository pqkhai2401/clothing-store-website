@extends('layouts.admin')

@section('title', 'Nhật ký làm việc')

@push('styles')
    @include('admin.logs.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Nhật ký làm việc</h1>
                    <p class="product-header-desc mb-0">Lịch sử thao tác của nhân sự trên hệ thống quản trị.</p>
                </div>
                <div class="product-header-actions">
                    {{-- Xuất Excel: ưu tiên checkbox đã chọn, nếu không thì theo bộ lọc --}}
                    <button type="button" id="logExportBtn"
                        class="btn product-action-btn product-action-btn--neutral">
                        <i class="fa-solid fa-download me-1"></i> Xuất Excel
                    </button>
                    <form id="logExportForm" method="GET" action="{{ route('admin.logs.export') }}" style="display:none;">
                        @csrf
                    </form>

                    {{-- Dọn log cũ --}}
                    <div class="log-prune-wrap">
                        <button type="button" id="logPruneTrigger"
                            class="btn btn-light product-action-btn">
                            <i class="fa-solid fa-broom me-1"></i> Dọn log cũ
                        </button>
                        <div class="log-prune-panel" id="logPrunePanel" hidden>
                            <div class="log-prune-title">Xóa log cũ hơn</div>
                            <button type="button" class="log-prune-item" data-days="30">30 ngày</button>
                            <button type="button" class="log-prune-item" data-days="90">90 ngày (3 tháng)</button>
                            <button type="button" class="log-prune-item" data-days="180">180 ngày (6 tháng)</button>
                            <button type="button" class="log-prune-item" data-days="365">365 ngày (1 năm)</button>
                        </div>
                    </div>
                    <form id="logPruneForm" method="POST" action="{{ route('admin.logs.prune') }}" style="display:none;">
                        @csrf
                        <input type="hidden" name="older_than_days" id="logPruneDays" value="">
                    </form>
                </div>
            </div>

            <div data-admin-stats-area>
                @include('admin.logs.partials.stats', ['stats' => $stats])
            </div>

            <form method="GET" action="{{ route('admin.logs.list') }}" id="logFilterForm" class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="event" data-admin-filter id="logEventHidden" value="{{ $eventFilter }}">
                <input type="hidden" name="subject" data-admin-filter id="logSubjectHidden" value="{{ $subjectFilter }}">

                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="logSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm theo người thực hiện hoặc IP..." autocomplete="off">

                    {{-- Lọc hành động --}}
                    <div class="hk-cat-filter" id="hkLogEventDrop">
                        <button type="button" class="hk-cat-trigger" id="hkLogEventTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkLogEventLabel">{{ $eventOptions[$eventFilter] ?? 'Tất cả hành động' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkLogEventPanel" hidden>
                            <div class="hk-cat-list" id="hkLogEventList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $eventFilter === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả hành động">Tất cả hành động</button>
                                @foreach($eventOptions as $value => $label)
                                    <button type="button" class="hk-cat-item {{ $eventFilter === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Lọc đối tượng --}}
                    <div class="hk-cat-filter" id="hkLogSubjectDrop">
                        <button type="button" class="hk-cat-trigger" id="hkLogSubjectTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkLogSubjectLabel">{{ $subjectOptions[$subjectFilter] ?? 'Tất cả đối tượng' }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkLogSubjectPanel" hidden>
                            <div class="hk-cat-list" id="hkLogSubjectList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $subjectFilter === '' ? 'is-active' : '' }}" data-value="" data-label="Tất cả đối tượng">Tất cả đối tượng</button>
                                @foreach($subjectOptions as $value => $label)
                                    <button type="button" class="hk-cat-item {{ $subjectFilter === $value ? 'is-active' : '' }}" data-value="{{ $value }}" data-label="{{ $label }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Khoảng ngày --}}
                    <div class="vk-date-range">
                        <input type="date" name="date_from" id="logDateFrom" data-admin-filter class="form-control vk-date-range-input"
                            value="{{ $dateFrom }}" title="Từ ngày">
                        <span class="vk-date-range-sep">—</span>
                        <input type="date" name="date_to" id="logDateTo" data-admin-filter class="form-control vk-date-range-input"
                            value="{{ $dateTo }}" title="Đến ngày">
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.logs.partials.table')
            </div>
        </section>
    </div>

    {{-- Modal chi tiết log --}}
    <div class="hklog-modal" id="logDetailModal" hidden>
        <div class="hklog-modal__overlay" data-log-modal-close></div>
        <div class="hklog-modal__dialog" role="dialog" aria-modal="true">
            <div class="hklog-modal__header">
                <div>
                    <h2>Chi tiết nhật ký</h2>
                    <p>Thông tin thao tác và các thay đổi được ghi lại.</p>
                </div>
                <button type="button" class="hklog-modal__close" data-log-modal-close aria-label="Đóng">&times;</button>
            </div>
            <div class="hklog-modal__body" id="logDetailBody">
                <div class="hklog-empty">Đang tải...</div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('admin.partials.realtime-table')
    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        /* ── Dropdown lọc (hành động / đối tượng) ── */
        function initHkDropdown(dropId, triggerId, panelId, labelId, listId, hiddenId) {
            const trigger = document.getElementById(triggerId);
            const panel   = document.getElementById(panelId);
            const label   = document.getElementById(labelId);
            const list    = document.getElementById(listId);
            const hidden  = document.getElementById(hiddenId);
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
                if (!panel.hidden && !document.getElementById(dropId)?.contains(e.target)) close();
            });
        }

        initHkDropdown('hkLogEventDrop', 'hkLogEventTrigger', 'hkLogEventPanel', 'hkLogEventLabel', 'hkLogEventList', 'logEventHidden');
        initHkDropdown('hkLogSubjectDrop', 'hkLogSubjectTrigger', 'hkLogSubjectPanel', 'hkLogSubjectLabel', 'hkLogSubjectList', 'logSubjectHidden');

        /* ── Dọn log cũ ── */
        (function () {
            const trigger = document.getElementById('logPruneTrigger');
            const panel   = document.getElementById('logPrunePanel');
            const form    = document.getElementById('logPruneForm');
            const daysInp = document.getElementById('logPruneDays');
            if (!trigger) return;

            trigger.addEventListener('click', function (e) {
                e.stopPropagation();
                panel.hidden = !panel.hidden;
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !e.target.closest('.log-prune-wrap')) panel.hidden = true;
            });

            panel.querySelectorAll('.log-prune-item').forEach(function (item) {
                item.addEventListener('click', function () {
                    const days = this.dataset.days;
                    panel.hidden = true;
                    if (window.confirm('Bạn có chắc chắn muốn xóa toàn bộ nhật ký cũ hơn ' + this.textContent.trim() + ' không? Hành động này không thể hoàn tác.')) {
                        daysInp.value = days;
                        form.submit();
                    }
                });
            });
        }());

        /* ── Xuất Excel ── */
        (function () {
            const exportBtn  = document.getElementById('logExportBtn');
            const exportForm = document.getElementById('logExportForm');
            if (!exportBtn || !exportForm) return;

            exportBtn.addEventListener('click', function () {
                exportForm.querySelectorAll('input:not([name="_token"])').forEach(i => i.remove());

                const tableArea = document.querySelector('[data-admin-table-area]');
                const checked   = Array.from(tableArea?.querySelectorAll('.hk-cb-row:checked') || []);

                if (checked.length > 0) {
                    checked.forEach(function (cb) {
                        const inp = document.createElement('input');
                        inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = cb.value;
                        exportForm.appendChild(inp);
                    });
                } else {
                    const params = new URLSearchParams(window.location.search);
                    ['search', 'event', 'subject', 'date_from', 'date_to'].forEach(function (key) {
                        if (params.get(key)) {
                            const inp = document.createElement('input');
                            inp.type = 'hidden'; inp.name = key; inp.value = params.get(key);
                            exportForm.appendChild(inp);
                        }
                    });
                }

                exportForm.submit();
            });
        }());

        /* ── Modal chi tiết ── */
        (function () {
            const modal = document.getElementById('logDetailModal');
            const body  = document.getElementById('logDetailBody');
            if (!modal || !body) return;

            function openModal() { modal.hidden = false; document.body.style.overflow = 'hidden'; }
            function closeModal() {
                modal.hidden = true;
                document.body.style.overflow = '';
                body.innerHTML = '<div class="hklog-empty">Đang tải...</div>';
            }

            document.addEventListener('click', async function (e) {
                const btn = e.target.closest('[data-log-show-url]');
                if (btn) {
                    e.preventDefault();
                    openModal();
                    try {
                        const res = await fetch(btn.dataset.logShowUrl, {
                            headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        if (!res.ok) throw new Error('load failed');
                        body.innerHTML = await res.text();
                    } catch {
                        body.innerHTML = '<div class="hklog-empty">Không thể tải chi tiết nhật ký.</div>';
                    }
                    return;
                }

                if (e.target.closest('[data-log-modal-close]')) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && !modal.hidden) closeModal();
            });
        }());
    }());
    </script>
@endpush
