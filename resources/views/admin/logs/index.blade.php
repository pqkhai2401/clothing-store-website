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

                    {{-- Bộ chọn kỳ: Hôm nay / Hôm qua / Năm / Quý / Tháng --}}
                    <div class="vk-period-wrap" id="logPeriodWrap">
                        <button type="button" class="vk-period-trigger {{ ($dateFrom || $dateTo) ? 'is-active' : '' }}" id="logPeriodTrigger" aria-haspopup="true" aria-expanded="false">
                            <i class="fa-regular fa-calendar me-1"></i>
                            <span id="logPeriodLabel">Chọn kỳ</span>
                            <i class="fa-solid fa-chevron-down vk-period-caret"></i>
                        </button>

                        <div class="vk-period-panel" id="logPeriodPanel" hidden>
                            {{-- Quick actions --}}
                            <div class="vk-quick-row">
                                <button type="button" class="vk-quick-btn" id="logBtnToday">Hôm nay</button>
                                <button type="button" class="vk-quick-btn" id="logBtnYesterday">Hôm qua</button>
                                <button type="button" class="vk-quick-btn" id="logBtnThisYear">Năm nay</button>
                                <button type="button" class="vk-quick-btn" id="logBtnLastYear">Năm ngoái</button>
                                <button type="button" class="vk-quick-clear" id="logBtnClear">Xoá chọn</button>
                            </div>

                            {{-- Năm --}}
                            <div class="vk-section-row">
                                <span class="vk-section-label">Năm</span>
                                <select class="vk-year-select" id="logYearSelect">
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

                    {{-- 2 ô ngày đặt cạnh nút Chọn kỳ --}}
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
                    window.showConfirm({
                        title: 'Xác nhận xóa',
                        message: 'Bạn có chắc chắn muốn xóa toàn bộ nhật ký cũ hơn ' + this.textContent.trim() + ' không? Hành động này không thể hoàn tác.',
                        type: 'danger'
                    }).then(function(ok) {
                        if (ok) {
                            daysInp.value = days;
                            form.submit();
                        }
                    });
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

        /* ── Bộ chọn kỳ (Hôm nay / Hôm qua / Năm / Quý / Tháng) ── */
        (function () {
            const trigger    = document.getElementById('logPeriodTrigger');
            const panel      = document.getElementById('logPeriodPanel');
            const label      = document.getElementById('logPeriodLabel');
            const inputFrom  = document.getElementById('logDateFrom');
            const inputTo    = document.getElementById('logDateTo');
            const yearSelect = document.getElementById('logYearSelect');
            const wrap       = document.getElementById('logPeriodWrap');
            if (!trigger) return;

            const pad2    = n => String(n).padStart(2, '0');
            const iso     = (y, m, d) => y + '-' + pad2(m) + '-' + pad2(d);
            const lastDay = (y, m) => new Date(y, m, 0).getDate();
            const isoToday = () => { const d = new Date(); return iso(d.getFullYear(), d.getMonth() + 1, d.getDate()); };
            function fmt(from, to) {
                if (!from && !to) return 'Chọn kỳ';
                const f = from ? from.split('-').reverse().join('/') : '?';
                const t = to   ? to.split('-').reverse().join('/')   : '?';
                return f === t ? f : (f + ' – ' + t);
            }

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }
            trigger.addEventListener('click', e => { e.stopPropagation(); panel.hidden ? open() : close(); });
            document.addEventListener('click', e => { if (!panel.hidden && !wrap.contains(e.target)) close(); });

            function applyDates(from, to, chipLabel) {
                inputFrom.value = from;
                inputTo.value   = to;
                label.textContent = chipLabel || fmt(from, to);
                trigger.classList.toggle('is-active', !!(from || to));
                panel.querySelectorAll('.vk-chip').forEach(c => c.classList.remove('is-active'));
                // Kích hoạt realtime-table (lắng nghe change trên [data-admin-filter])
                inputFrom.dispatchEvent(new Event('change', { bubbles: true }));
            }
            const getYear = () => parseInt(yearSelect.value, 10);

            document.getElementById('logBtnToday').addEventListener('click', () => {
                const s = isoToday(); applyDates(s, s, 'Hôm nay'); close();
            });
            document.getElementById('logBtnYesterday').addEventListener('click', () => {
                const d = new Date(); d.setDate(d.getDate() - 1);
                const s = iso(d.getFullYear(), d.getMonth() + 1, d.getDate());
                applyDates(s, s, 'Hôm qua'); close();
            });
            document.getElementById('logBtnThisYear').addEventListener('click', () => {
                const y = new Date().getFullYear(); applyDates(y + '-01-01', y + '-12-31', 'Năm nay'); yearSelect.value = y; close();
            });
            document.getElementById('logBtnLastYear').addEventListener('click', () => {
                const y = new Date().getFullYear() - 1; applyDates(y + '-01-01', y + '-12-31', 'Năm ngoái'); yearSelect.value = y; close();
            });
            document.getElementById('logBtnClear').addEventListener('click', () => {
                applyDates('', '', 'Chọn kỳ'); close();
            });

            panel.querySelectorAll('.vk-chip[data-quarter]').forEach(btn => btn.addEventListener('click', function () {
                const q = parseInt(this.dataset.quarter, 10), y = getYear();
                const sm = (q - 1) * 3 + 1, em = q * 3;
                applyDates(iso(y, sm, 1), iso(y, em, lastDay(y, em)), 'Quý ' + q + ' năm ' + y);
                this.classList.add('is-active'); close();
            }));
            panel.querySelectorAll('.vk-chip[data-month]').forEach(btn => btn.addEventListener('click', function () {
                const m = parseInt(this.dataset.month, 10), y = getYear();
                applyDates(iso(y, m, 1), iso(y, m, lastDay(y, m)), 'Tháng ' + m + '/' + y);
                this.classList.add('is-active'); close();
            }));

            // Đồng bộ nhãn khi user tự chỉnh 2 ô ngày
            function syncManual() {
                label.textContent = fmt(inputFrom.value, inputTo.value);
                trigger.classList.toggle('is-active', !!(inputFrom.value || inputTo.value));
            }
            inputFrom.addEventListener('change', () => { if (document.activeElement === inputFrom) syncManual(); });
            inputTo.addEventListener('change', () => { if (document.activeElement === inputTo) syncManual(); });

            // Nhãn ban đầu theo giá trị có sẵn
            if (inputFrom.value || inputTo.value) { label.textContent = fmt(inputFrom.value, inputTo.value); }
        }());
    }());
    </script>
@endpush
