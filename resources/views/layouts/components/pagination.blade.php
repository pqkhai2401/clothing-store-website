@php
    static $hkPgId = 0;
    $hkPgId++;
    $uid            = 'hkpg' . $hkPgId;
    $itemLabel      = $itemLabel ?? 'mục';
    $bulkDeleteUrl   = $bulkDeleteUrl ?? null;
    $bulkDeleteLabel = $bulkDeleteLabel ?? 'Xóa đã chọn';
    $bulkRestoreUrl  = $bulkRestoreUrl ?? null;
    $bulkStatusUrl   = $bulkStatusUrl ?? null;
    $bulkCustomActionsUrl   = $bulkCustomActionsUrl ?? null;
    $bulkCustomActionsField = $bulkCustomActionsField ?? 'value';
    $bulkCustomActions      = $bulkCustomActions ?? null;
    $currentPerPage = (int) $paginator->perPage();
    $perPageOptions = [10, 25, 50, 100];
    $total          = $paginator->total();
    $count          = $paginator->count();
    $currentPage    = $paginator->currentPage();
    $lastPage       = $paginator->lastPage();
    $isFirst        = $paginator->onFirstPage();
    $isLast         = ! $paginator->hasMorePages();
@endphp

<div class="hk-pagination" id="{{ $uid }}"
     data-uid="{{ $uid }}"
     data-label="{{ $itemLabel }}">

    {{-- ── LEFT: count + selection ────────────────────────── --}}
    <div class="hk-pg-left">
        <span class="hk-pg-info">
            Hiển thị <strong>{{ $count }}</strong> / <strong>{{ $total }}</strong> {{ $itemLabel }}.
        </span>

        <div class="hk-pg-selection" id="{{ $uid }}_sel" style="display:none;">
            <span class="hk-pg-sel-sep"> | </span>
            <span class="hk-pg-sel-count" id="{{ $uid }}_selCount">0 {{ $itemLabel }} được chọn</span>
            @if ($bulkRestoreUrl)
                <button type="button" class="hk-pg-sel-restore" id="{{ $uid }}_restoreBtn">
                    <i class="fa-solid fa-rotate-left"></i> Khôi phục đã chọn
                </button>
            @endif
            @if ($bulkDeleteUrl)
                <button type="button" class="hk-pg-sel-delete" id="{{ $uid }}_selBtn">
                    <i class="fa-regular fa-trash-can"></i> {{ $bulkDeleteLabel }}
                </button>
            @endif
            @if ($bulkStatusUrl)
                <span class="hk-pg-status-actions">
                    <button type="button" class="hk-pg-sel-status hk-pg-sel-status--active" data-status="1">
                        Kích hoạt đã chọn
                    </button>
                    <button type="button" class="hk-pg-sel-status hk-pg-sel-status--inactive" data-status="0">
                        Ngưng hoạt động đã chọn
                    </button>
                </span>
            @endif
            @if ($bulkCustomActions && $bulkCustomActionsUrl)
                <span class="hk-pg-status-actions" data-bulk-field="{{ $bulkCustomActionsField }}">
                    @foreach ($bulkCustomActions as $action)
                        <button type="button" class="hk-pg-sel-status {{ $action['class'] ?? '' }}" data-custom-value="{{ $action['value'] }}">
                            {{ $action['label'] }}
                        </button>
                    @endforeach
                </span>
            @endif
        </div>
    </div>

    {{-- ── RIGHT: per-page + page nav ────────────────────── --}}
    <div class="hk-pg-right">
        <span class="hk-pg-pp-label">Số hàng mỗi trang</span>
        <select class="hk-pg-pp-select" onchange="handlePerPageChange(this.value)">
            @foreach ($perPageOptions as $opt)
                <option value="{{ $opt }}" @selected($currentPerPage === $opt)>{{ $opt }}</option>
            @endforeach
        </select>

        <span class="hk-pg-pageinfo">Trang {{ $currentPage }} / {{ $lastPage }}</span>

        <div class="hk-pg-buttons">
            {{-- «  First --}}
            @if ($isFirst)
                <span class="hk-pg-btn hk-pg-btn--off" title="Trang đầu">
                    <i class="fa-solid fa-angles-left"></i>
                </span>
            @else
                <a class="hk-pg-btn" href="{{ $paginator->url(1) }}" title="Trang đầu">
                    <i class="fa-solid fa-angles-left"></i>
                </a>
            @endif

            {{-- ‹  Prev --}}
            @if ($isFirst)
                <span class="hk-pg-btn hk-pg-btn--off" title="Trang trước">
                    <i class="fa-solid fa-angle-left"></i>
                </span>
            @else
                <a class="hk-pg-btn" href="{{ $paginator->previousPageUrl() }}" title="Trang trước">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            @endif

            {{-- ›  Next --}}
            @if ($isLast)
                <span class="hk-pg-btn hk-pg-btn--off" title="Trang sau">
                    <i class="fa-solid fa-angle-right"></i>
                </span>
            @else
                <a class="hk-pg-btn" href="{{ $paginator->nextPageUrl() }}" title="Trang sau">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            @endif

            {{-- »  Last --}}
            @if ($isLast)
                <span class="hk-pg-btn hk-pg-btn--off" title="Trang cuối">
                    <i class="fa-solid fa-angles-right"></i>
                </span>
            @else
                <a class="hk-pg-btn" href="{{ $paginator->url($lastPage) }}" title="Trang cuối">
                    <i class="fa-solid fa-angles-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>

@if ($bulkRestoreUrl)
<form id="{{ $uid }}_brf" method="POST" action="{{ $bulkRestoreUrl }}" style="display:none;">
    @csrf
</form>
@endif

@if ($bulkDeleteUrl)
<form id="{{ $uid }}_bdf" method="POST" action="{{ $bulkDeleteUrl }}" style="display:none;">
    @csrf
</form>
@endif

@if ($bulkStatusUrl)
<form id="{{ $uid }}_bsf" method="POST" action="{{ $bulkStatusUrl }}" style="display:none;">
    @csrf
    @method('PATCH')
</form>
@endif

@if ($bulkCustomActions && $bulkCustomActionsUrl)
<form id="{{ $uid }}_bcf" method="POST" action="{{ $bulkCustomActionsUrl }}" style="display:none;">
    @csrf
</form>
@endif

{{-- ══════════════════════ CSS (inlined once, duplicates ignored by browser) ══════════════════════ --}}
<style>
/* ── Base layout ─────────────────────────────────── */
.hk-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    min-height: 52px;
    padding: 10px 4px;
    font-size: 13px;
    color: #374151;
    flex-wrap: wrap;
}

/* ── LEFT ────────────────────────────────────────── */
.hk-pg-left {
    display: flex;
    align-items: center;
    gap: 0;
    flex-wrap: wrap;
    flex: 1 1 auto;
    min-width: 0;
}
.hk-pg-info {
    white-space: nowrap;
    color: #111827;
    flex-shrink: 0;
}
.hk-pg-info strong { color: #111827; font-weight: 600; }

/* Selection strip */
.hk-pg-selection {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-left: 8px;
}
.hk-pg-sel-sep {
    color: #9ca3af;
    flex-shrink: 0;
    margin: 0 2px;
}
.hk-pg-sel-count {
    white-space: nowrap;
    color: #111827;
    font-weight: 600;
    font-size: 13px;
}
.hk-pg-sel-delete {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 12px;
    height: 28px;
    font-size: 12px;
    font-weight: 600;
    color: #dc2626;
    background: #fee2e2;
    border: 1px solid #fca5a5;
    border-radius: 999px;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    line-height: 1;
}
.hk-pg-sel-delete:hover { background: #fecaca; color: #b91c1c; }
.hk-pg-sel-restore {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 12px;
    height: 28px;
    font-size: 12px;
    font-weight: 600;
    color: #047857;
    background: #ecfdf5;
    border: 1px solid #86efac;
    border-radius: 999px;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.15s;
    line-height: 1;
}
.hk-pg-sel-restore:hover { background: #d1fae5; }
.hk-pg-status-actions {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.hk-pg-sel-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 28px;
    padding: 3px 12px;
    border-radius: 999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #111827;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
}
.hk-pg-sel-status--active {
    border-color: #86efac;
    background: #ecfdf5;
    color: #047857;
}
.hk-pg-sel-status--inactive {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #334155;
}
.hk-pg-sel-status:hover { filter: brightness(0.98); }

/* ── RIGHT ───────────────────────────────────────── */
.hk-pg-right {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.hk-pg-pp-label {
    color: #374151;
    white-space: nowrap;
    flex-shrink: 0;
}
.hk-pg-pp-select {
    height: 32px;
    padding: 0 8px;
    font-size: 13px;
    color: #111827;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    cursor: pointer;
    outline: none;
    min-width: 58px;
    transition: border-color 0.15s;
}
.hk-pg-pp-select:hover,
.hk-pg-pp-select:focus { border-color: #9ca3af; }

.hk-pg-divider {
    display: inline-block;
    width: 1px;
    height: 18px;
    background: #e5e7eb;
    flex-shrink: 0;
}
.hk-pg-pageinfo {
    white-space: nowrap;
    color: #374151;
    font-weight: 500;
}

/* ── Nav buttons ──────────────────────────────────── */
.hk-pg-buttons {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.hk-pg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: #fff;
    color: #374151;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.12s, border-color 0.12s, color 0.12s;
    user-select: none;
}
.hk-pg-btn:hover {
    background: #f3f4f6;
    border-color: #9ca3af;
    color: #111827;
}
.hk-pg-btn:active { background: #e5e7eb; }
/* Disabled / boundary state */
.hk-pg-btn--off {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}

/* ── Checkbox column ──────────────────────────────── */
.hk-cb-th {
    width: 42px !important;
    text-align: center;
    padding-left: 12px !important;
}
.hk-cb-td {
    text-align: center;
    padding-left: 12px !important;
}
.hk-cb-row,
.hk-cb-all {
    width: 15px;
    height: 15px;
    cursor: pointer;
    accent-color: #000;
}
/* Highlight selected row lightly */
tr.hk-row-selected { background-color: #f0f7ff !important; }

/* ── Dark mode ────────────────────────────────────── */
[data-theme="dark"] .hk-pagination         { color: #94a3b8; }
[data-theme="dark"] .hk-pg-info            { color: #2d1111; }
[data-theme="dark"] .hk-pg-info strong     { color: #e2e8f0; }
[data-theme="dark"] .hk-pg-sel-sep         { color: #334155; }
[data-theme="dark"] .hk-pg-sel-count       { color: #e2e8f0; }
[data-theme="dark"] .hk-pg-sel-delete       { color: #f87171; background: #1e1010; border-color: #7f1d1d; }
[data-theme="dark"] .hk-pg-sel-delete:hover { background: #2d1111; }
[data-theme="dark"] .hk-pg-sel-restore      { color: #4ade80; background: #052e16; border-color: #166534; }
[data-theme="dark"] .hk-pg-sel-restore:hover{ background: #064e3b; }
[data-theme="dark"] .hk-pg-pp-label        { color: #94a3b8; }
[data-theme="dark"] .hk-pg-pp-select       { color: #e2e8f0; border-color: #334155; background: #0f172a; }
[data-theme="dark"] .hk-pg-pp-select:hover { border-color: #475569; }
[data-theme="dark"] .hk-pg-pageinfo        { color: #94a3b8; }
[data-theme="dark"] .hk-pg-btn             { background: #0f172a; border-color: #334155; color: #94a3b8; }
[data-theme="dark"] .hk-pg-btn:hover       { background: #1e293b; border-color: #475569; color: #e2e8f0; }
[data-theme="dark"] tr.hk-row-selected     { background-color: #0f2033 !important; }

/* ── Responsive ───────────────────────────────────── */
@media (max-width: 640px) {
    .hk-pagination  { justify-content: center; gap: 10px; }
    .hk-pg-left     { justify-content: center; width: 100%; }
    .hk-pg-right    { justify-content: center; width: 100%; }
}
</style>

{{-- ══════════════════════ JS ══════════════════════ --}}
<script>
(function () {
    'use strict';

    /* Global per-page handler (same across all pagination instances) */
    window.handlePerPageChange = function (value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('perPage');
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    };

    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.hk-pagination').forEach(function (pgEl) {
            if (pgEl.closest('[data-admin-table-area]')) return;

            const uid      = pgEl.dataset.uid;
            const label    = pgEl.dataset.label || 'mục';
            const selEl    = document.getElementById(uid + '_sel');
            const cntEl    = document.getElementById(uid + '_selCount');
            const btnEl    = document.getElementById(uid + '_selBtn');
            const form     = document.getElementById(uid + '_bdf');
            const statusForm = document.getElementById(uid + '_bsf');
            const statusButtons = Array.from(document.querySelectorAll('#' + uid + '_sel .hk-pg-sel-status'));

            /* All row checkboxes and the select-all header checkbox.
               We search the entire document so it works with tables
               rendered outside the pagination div. */
            const allRows = Array.from(document.querySelectorAll('.hk-cb-row'));
            const cbAll   = document.querySelector('.hk-cb-all');

            if (!allRows.length) return;

            /* ─── updateSelectedState ──────────────────────── */
            function updateSelectedState() {
                const checkedBoxes = allRows.filter(function (cb) { return cb.checked; });
                const n = checkedBoxes.length;

                /* Show / hide the selection strip */
                if (selEl) selEl.style.display = n > 0 ? 'inline-flex' : 'none';

                /* Update count text */
                if (cntEl) cntEl.textContent = n + ' ' + label + ' được chọn';

                /* Highlight / unhighlight rows */
                allRows.forEach(function (cb) {
                    const row = cb.closest('tr');
                    if (row) row.classList.toggle('hk-row-selected', cb.checked);
                });
            }

            /* ─── handleSelectAll ──────────────────────────── */
            function handleSelectAll() {
                const checked = cbAll.checked;
                allRows.forEach(function (cb) { cb.checked = checked; });
                updateSelectedState();
            }

            /* ─── handleRowCheckboxChange ──────────────────── */
            function handleRowCheckboxChange() {
                const total   = allRows.length;
                const checked = allRows.filter(function (cb) { return cb.checked; }).length;

                if (cbAll) {
                    cbAll.checked       = (checked === total);
                    cbAll.indeterminate = (checked > 0 && checked < total);
                }
                updateSelectedState();
            }

            /* ─── handleBulkDelete ─────────────────────────── */
            function handleBulkDelete() {
                const ids = allRows
                    .filter(function (cb) { return cb.checked; })
                    .map(function (cb) { return cb.value; });

                if (!ids.length) return;

                var confirmed = window.confirm(
                    'Bạn có chắc chắn muốn xóa ' + ids.length + ' ' + label + ' đã chọn không?'
                );
                if (!confirmed) return;

                if (form) {
                    /* Remove previously appended inputs */
                    form.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });

                    ids.forEach(function (id) {
                        var input   = document.createElement('input');
                        input.type  = 'hidden';
                        input.name  = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });

                    form.submit();
                }
            }

            function selectedIds() {
                return allRows
                    .filter(function (cb) { return cb.checked; })
                    .map(function (cb) { return cb.value; });
            }

            function handleBulkStatus(status) {
                const ids = selectedIds();

                if (!ids.length || !statusForm) return;

                const labelText = status === '1' ? 'kích hoạt' : 'ngưng hoạt động';
                var confirmed = window.confirm(
                    'Bạn có chắc chắn muốn ' + labelText + ' ' + ids.length + ' ' + label + ' đã chọn không?'
                );
                if (!confirmed) return;

                statusForm.querySelectorAll('input[name="ids[]"], input[name="is_active"]').forEach(function (el) { el.remove(); });

                ids.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    statusForm.appendChild(input);
                });

                var statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'is_active';
                statusInput.value = status;
                statusForm.appendChild(statusInput);

                statusForm.submit();
            }

            /* ─── Wire up events ───────────────────────────── */
            allRows.forEach(function (cb) {
                cb.addEventListener('change', handleRowCheckboxChange);
            });

            const restoreBtn = document.getElementById(uid + '_restoreBtn');
            const restoreForm = document.getElementById(uid + '_brf');

            function handleBulkRestore() {
                const ids = allRows
                    .filter(function (cb) { return cb.checked; })
                    .map(function (cb) { return cb.value; });
                if (!ids.length || !restoreForm) return;
                if (!window.confirm('Bạn có chắc chắn muốn khôi phục ' + ids.length + ' ' + label + ' đã chọn không?')) return;
                restoreForm.querySelectorAll('input[name="ids[]"]').forEach(function (el) { el.remove(); });
                ids.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                    restoreForm.appendChild(input);
                });
                restoreForm.submit();
            }

            if (cbAll)  cbAll.addEventListener('change', handleSelectAll);
            if (btnEl)  btnEl.addEventListener('click',  handleBulkDelete);
            if (restoreBtn) restoreBtn.addEventListener('click', handleBulkRestore);
            statusButtons.forEach(function (button) {

                button.addEventListener('click', function () {
                    handleBulkStatus(this.dataset.status || '1');
                });
            });

            /* Initial sync (e.g. browser restores checked state on back-nav) */
            updateSelectedState();
        });

    });
}());
</script>
