{{-- Tái dùng hệ thống style chung của admin (header, toolbar, hk-cat-filter) + bảng kiểu account --}}
@include('admin.products.styles')
@include('admin.users.styles')

<style>
    /* ── Badge hành động (tone theo loại sự kiện) ───────────────────────── */
    .log-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        padding: 5px 12px;
        min-height: 28px;
        line-height: 1;
        white-space: nowrap;
    }
    .log-badge i { font-size: 11px; }

    .log-badge--success { background: #ECFDF5; border: 1.5px solid #86EFAC; color: #16A34A; }
    .log-badge--info    { background: #EFF6FF; border: 1.5px solid #BFDBFE; color: #1D4ED8; }
    .log-badge--danger  { background: #FEF2F2; border: 1.5px solid #FECACA; color: #DC2626; }
    .log-badge--warning { background: #FFFBEB; border: 1.5px solid #FDE68A; color: #92400E; }
    .log-badge--neutral { background: #EEF2FF; border: 1.5px solid #C7D2FE; color: #4338CA; }
    .log-badge--muted   { background: #F1F5F9; border: 1.5px solid #CBD5E1; color: #475569; }

    [data-theme="dark"] .log-badge--success { background: rgba(34,197,94,0.12) !important;  border-color: rgba(34,197,94,0.3) !important;  color: #4ADE80 !important; }
    [data-theme="dark"] .log-badge--info    { background: rgba(59,130,246,0.12) !important; border-color: rgba(59,130,246,0.3) !important; color: #60A5FA !important; }
    [data-theme="dark"] .log-badge--danger  { background: rgba(248,113,113,0.12) !important;border-color: rgba(248,113,113,0.3) !important;color: #F87171 !important; }
    [data-theme="dark"] .log-badge--warning { background: rgba(251,191,36,0.12) !important; border-color: rgba(251,191,36,0.3) !important; color: #FCD34D !important; }
    [data-theme="dark"] .log-badge--neutral { background: rgba(99,102,241,0.14) !important; border-color: rgba(99,102,241,0.35) !important;color: #A5B4FC !important; }
    [data-theme="dark"] .log-badge--muted   { background: rgba(148,163,184,0.14) !important;border-color: rgba(148,163,184,0.3) !important; color: #CBD5E1 !important; }

    /* ── Ô đối tượng / người thực hiện ──────────────────────────────────── */
    .log-subject-label { font-weight: 700; color: #0F172A; }
    .log-subject-desc  { color: #64748B; font-size: 12.5px; }
    .log-ip { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px; color: #475569; }
    .log-time-main { font-weight: 600; color: #0F172A; }
    .log-time-sub  { color: #94A3B8; font-size: 12px; }

    [data-theme="dark"] .log-subject-label { color: #F8FAFC; }
    [data-theme="dark"] .log-subject-desc  { color: #94A3B8; }
    [data-theme="dark"] .log-ip            { color: #94A3B8; }
    [data-theme="dark"] .log-time-main     { color: #F8FAFC; }

    /* ── Nút "Chi tiết" trong bảng ──────────────────────────────────────── */
    .log-detail-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        height: 30px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1.5px solid #D8E0EA;
        background: #fff;
        color: #334155;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s;
    }
    .log-detail-btn:hover { background: #F8FAFC; border-color: #CBD5E1; color: #0F172A; }
    [data-theme="dark"] .log-detail-btn { background: #0F1B31; border-color: #22324D; color: #CBD5E1; }
    [data-theme="dark"] .log-detail-btn:hover { background: #16233D; border-color: #33507D; color: #F8FAFC; }

    /* ── Modal chi tiết ─────────────────────────────────────────────────── */
    .hklog-modal { position: fixed; inset: 0; z-index: 1080; display: flex; align-items: center; justify-content: center; }
    .hklog-modal[hidden] { display: none; }
    .hklog-modal__overlay { position: absolute; inset: 0; background: rgba(15,23,42,.55); }
    .hklog-modal__dialog {
        position: relative;
        width: min(680px, calc(100vw - 32px));
        max-height: calc(100vh - 64px);
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(15,23,42,.35);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .hklog-modal__header {
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
        padding: 20px 22px 14px; border-bottom: 1px solid #E2E8F0;
    }
    .hklog-modal__header h2 { font-size: 18px; font-weight: 800; color: #0F172A; margin: 0; }
    .hklog-modal__header p  { margin: 4px 0 0; font-size: 13px; color: #64748B; }
    .hklog-modal__close {
        border: none; background: transparent; font-size: 20px; line-height: 1; color: #94A3B8; cursor: pointer;
        width: 34px; height: 34px; border-radius: 8px;
    }
    .hklog-modal__close:hover { background: #F1F5F9; color: #0F172A; }
    .hklog-modal__body { padding: 18px 22px 22px; overflow-y: auto; }

    .hklog-meta { display: grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 10px 20px; margin-bottom: 18px; }
    .hklog-meta__item { display: flex; flex-direction: column; gap: 2px; }
    .hklog-meta__label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94A3B8; }
    .hklog-meta__value { font-size: 13.5px; color: #0F172A; font-weight: 600; word-break: break-word; }

    .hklog-diff { width: 100%; border-collapse: collapse; font-size: 13px; }
    .hklog-diff th, .hklog-diff td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #EEF2F7; vertical-align: top; }
    .hklog-diff thead th { font-size: 11px; text-transform: uppercase; letter-spacing: .03em; color: #94A3B8; }
    .hklog-diff .k { font-weight: 700; color: #334155; white-space: nowrap; }
    .hklog-diff .old { color: #B91C1C; }
    .hklog-diff .new { color: #15803D; }
    .hklog-diff .arrow { color: #94A3B8; }
    .hklog-empty { color: #94A3B8; font-size: 13px; text-align: center; padding: 24px 0; }

    [data-theme="dark"] .hklog-modal__dialog { background: #0F1B31; box-shadow: 0 24px 60px rgba(0,0,0,.55); }
    [data-theme="dark"] .hklog-modal__header { border-color: #22324D; }
    [data-theme="dark"] .hklog-modal__header h2 { color: #F8FAFC; }
    [data-theme="dark"] .hklog-modal__header p  { color: #94A3B8; }
    [data-theme="dark"] .hklog-modal__close:hover { background: #16233D; color: #F8FAFC; }
    [data-theme="dark"] .hklog-meta__value { color: #F8FAFC; }
    [data-theme="dark"] .hklog-diff th, [data-theme="dark"] .hklog-diff td { border-color: #22324D; }
    [data-theme="dark"] .hklog-diff .k { color: #CBD5E1; }
    [data-theme="dark"] .hklog-diff .old { color: #F87171; }
    [data-theme="dark"] .hklog-diff .new { color: #4ADE80; }

    /* ── Dropdown "Dọn log cũ" ──────────────────────────────────────────── */
    .log-prune-wrap { position: relative; }
    .log-prune-panel {
        position: absolute; right: 0; top: calc(100% + 6px); z-index: 30;
        background: #fff; border: 1px solid #E2E8F0; border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15,23,42,.16); padding: 8px; min-width: 220px;
    }
    .log-prune-panel[hidden] { display: none; }
    .log-prune-panel .log-prune-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #94A3B8; padding: 6px 10px; }
    .log-prune-item {
        display: block; width: 100%; text-align: left; border: none; background: transparent;
        padding: 9px 10px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;
    }
    .log-prune-item:hover { background: #FEF2F2; color: #B91C1C; }
    [data-theme="dark"] .log-prune-panel { background: #0F1B31; border-color: #22324D; box-shadow: 0 16px 40px rgba(0,0,0,.45); }
    [data-theme="dark"] .log-prune-item { color: #CBD5E1; }
    [data-theme="dark"] .log-prune-item:hover { background: rgba(248,113,113,0.12); color: #F87171; }

    /* ── Ô lọc theo khoảng ngày ─────────────────────────────────────────── */
    .vk-date-range { display: inline-flex; align-items: center; gap: 8px; }
    .vk-date-range-input {
        height: 38px; width: 150px; border-radius: 10px; border: 1.5px solid #D8E0EA;
        background: #fff; color: #0F172A; font-size: 13px; padding: 6px 10px;
    }
    .vk-date-range-input:focus { border-color: #16A34A; box-shadow: none; outline: none; }
    .vk-date-range-sep { color: #94A3B8; }
    [data-theme="dark"] .vk-date-range-input { background: #0F1B31; border-color: #22324D; color: #F8FAFC; }
    [data-theme="dark"] .vk-date-range-input:focus { border-color: #3B82F6; }
    [data-theme="dark"] .vk-date-range-sep { color: #64748B; }

    @media (max-width: 640px) {
        .hklog-meta { grid-template-columns: 1fr; }
        .vk-date-range-input { width: 100%; }
    }
</style>
