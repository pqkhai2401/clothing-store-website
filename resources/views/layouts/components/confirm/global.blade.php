{{--
  Global Confirm & Alert Modal
  ============================
  Thay thế hoàn toàn window.confirm() và window.alert() bằng hộp thoại pop-up đẹp hơn.

  API:
  ────
  1. window.showConfirm(config) → Promise<boolean>
     config = {
       title   : string  (mặc định: 'Xác nhận')
       message : string  (mặc định: 'Bạn có chắc chắn muốn thực hiện thao tác này không?')
       icon    : string  (FA class, mặc định: 'fa-solid fa-circle-question')
       iconBg  : string  (CSS color, mặc định: amber gradient)
       iconColor: string (CSS color, mặc định: #f59e0b)
       confirmText: string (mặc định: 'Xác nhận')
       cancelText : string (mặc định: 'Hủy bỏ')
       confirmClass: string (thêm class vào nút xác nhận)
       type    : 'warning'|'danger'|'info'|'success' (preset)
     }
     Trả về Promise<true> nếu nhấn Xác nhận, Promise<false> nếu nhấn Hủy / đóng.

  2. window.showAlert(message, title?, icon?) → Promise<void>
     Hiển thị hộp thoại thông báo chỉ có nút "Đóng".
--}}

{{-- ─── Modal HTML ─────────────────────────────────────────────────────── --}}
<div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 shadow-lg" id="globalConfirmContent"
             style="border-radius: 20px; overflow: hidden;">
            <div class="modal-body text-center p-5">

                {{-- Icon --}}
                <div class="mb-4">
                    <div id="gcmIconWrapper"
                         class="gcm-icon-wrapper mx-auto d-flex align-items-center justify-content-center"
                         style="width: 84px; height: 84px; border-radius: 50%;">
                        <i id="gcmIcon" style="font-size: 34px;"></i>
                    </div>
                </div>

                {{-- Title --}}
                <h5 id="gcmTitle" class="fw-bold mb-3" style="color: #0f172a; font-size: 1.4rem; line-height: 1.3;"></h5>

                {{-- Message --}}
                <p id="gcmMessage" class="mb-4" style="font-size: 0.93rem; line-height: 1.65; color: #64748b;"></p>

                {{-- Buttons --}}
                <div class="d-flex gap-3 justify-content-center flex-wrap" id="gcmBtnGroup">
                    <button type="button" id="gcmCancelBtn"
                            style="background:#f1f5f9; color:#475569; border:none; border-radius:12px;
                                   font-weight:500; min-width:120px; padding:10px 24px;
                                   transition:all .25s ease; font-size:.93rem;">
                        Hủy bỏ
                    </button>
                    <button type="button" id="gcmConfirmBtn"
                            style="border:none; border-radius:12px; font-weight:600;
                                   min-width:120px; padding:10px 24px; color:#fff;
                                   transition:all .25s ease; font-size:.93rem;">
                        Xác nhận
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
/* ─── Base ─────────────────────────────────────────────────── */
#gcmCancelBtn:hover  { background:#e2e8f0 !important; transform:translateY(-1px); }
#gcmCancelBtn:active { transform:translateY(0); }
#gcmConfirmBtn:hover { filter:brightness(1.1); transform:translateY(-1px); }
#gcmConfirmBtn:active{ transform:translateY(0); }

.gcm-icon-wrapper { animation: gcm-pulse 2.2s ease-in-out infinite; }
@keyframes gcm-pulse {
    0%,100% { transform: scale(1); }
    50%      { transform: scale(1.06); }
}

/* ─── Type presets ─────────────────────────────────────────── */
.gcm--warning .gcm-icon-wrapper {
    background: linear-gradient(135deg,#fef3c7 0%,#fff7ed 100%);
    box-shadow: 0 4px 16px rgba(245,158,11,.2);
}
.gcm--warning #gcmIcon   { color:#f59e0b; }
.gcm--warning #gcmConfirmBtn { background:#f59e0b; box-shadow:0 4px 12px rgba(245,158,11,.38); }
.gcm--warning #gcmConfirmBtn:hover { background:#d97706 !important; }

.gcm--danger .gcm-icon-wrapper {
    background: linear-gradient(135deg,#ffe5e5 0%,#fff0f0 100%);
    box-shadow: 0 4px 16px rgba(220,53,69,.18);
}
.gcm--danger #gcmIcon    { color:#dc3545; }
.gcm--danger #gcmConfirmBtn { background:#dc3545; box-shadow:0 4px 12px rgba(220,53,69,.34); }
.gcm--danger #gcmConfirmBtn:hover { background:#b91c2c !important; }

.gcm--info .gcm-icon-wrapper {
    background: linear-gradient(135deg,#dbeafe 0%,#eff6ff 100%);
    box-shadow: 0 4px 16px rgba(59,130,246,.18);
}
.gcm--info #gcmIcon      { color:#3b82f6; }
.gcm--info #gcmConfirmBtn { background:#3b82f6; box-shadow:0 4px 12px rgba(59,130,246,.34); }
.gcm--info #gcmConfirmBtn:hover { background:#2563eb !important; }

.gcm--success .gcm-icon-wrapper {
    background: linear-gradient(135deg,#dcfce7 0%,#f0fdf4 100%);
    box-shadow: 0 4px 16px rgba(34,197,94,.18);
}
.gcm--success #gcmIcon   { color:#16a34a; }
.gcm--success #gcmConfirmBtn { background:#16a34a; box-shadow:0 4px 12px rgba(22,163,74,.3); }
.gcm--success #gcmConfirmBtn:hover { background:#15803d !important; }

/* Alert mode (no cancel btn) */
#gcmCancelBtn.d-none + #gcmConfirmBtn { min-width:150px; }
</style>

<script>
(function () {
    /* ── Internal helpers ──────────────────────────────────────── */
    const TYPE_DEFAULTS = {
        warning : { icon:'fa-solid fa-circle-exclamation', iconBg:'linear-gradient(135deg,#fef3c7,#fff7ed)', iconColor:'#f59e0b', confirmBg:'#f59e0b', confirmShadow:'rgba(245,158,11,.38)' },
        danger  : { icon:'fa-solid fa-circle-xmark',       iconBg:'linear-gradient(135deg,#ffe5e5,#fff0f0)', iconColor:'#dc3545', confirmBg:'#dc3545', confirmShadow:'rgba(220,53,69,.34)' },
        info    : { icon:'fa-solid fa-circle-info',        iconBg:'linear-gradient(135deg,#dbeafe,#eff6ff)', iconColor:'#3b82f6', confirmBg:'#3b82f6', confirmShadow:'rgba(59,130,246,.34)' },
        success : { icon:'fa-solid fa-circle-check',       iconBg:'linear-gradient(135deg,#dcfce7,#f0fdf4)', iconColor:'#16a34a', confirmBg:'#16a34a', confirmShadow:'rgba(22,163,74,.30)' },
    };

    function getEl(id) { return document.getElementById(id); }

    let _resolve = null;

    function _openModal(cfg, alertMode) {
        const type = cfg.type || 'warning';
        const preset = TYPE_DEFAULTS[type] || TYPE_DEFAULTS.warning;

        /* Apply type class */
        const content = getEl('globalConfirmContent');
        content.className = content.className.replace(/\bgcm--\S+/g, '');
        content.classList.add('gcm--' + type);

        /* Icon */
        const wrapper = getEl('gcmIconWrapper');
        wrapper.style.background  = cfg.iconBg    || preset.iconBg;
        wrapper.style.boxShadow   = '0 4px 16px ' + (cfg.iconColor || preset.iconColor).replace('#','') + '33';
        const iconEl = getEl('gcmIcon');
        iconEl.className = cfg.icon || preset.icon;
        iconEl.style.color = cfg.iconColor || preset.iconColor;

        /* Text */
        getEl('gcmTitle').textContent   = cfg.title   || 'Xác nhận';
        getEl('gcmMessage').textContent = cfg.message || 'Bạn có chắc chắn muốn thực hiện thao tác này không?';

        /* Buttons */
        const cancelBtn  = getEl('gcmCancelBtn');
        const confirmBtn = getEl('gcmConfirmBtn');

        cancelBtn.textContent  = cfg.cancelText  || 'Hủy bỏ';
        confirmBtn.textContent = cfg.confirmText || 'Xác nhận';
        confirmBtn.style.background  = cfg.confirmBg || preset.confirmBg;
        confirmBtn.style.boxShadow   = '0 4px 12px ' + (cfg.confirmShadow || preset.confirmShadow);

        if (alertMode) {
            cancelBtn.classList.add('d-none');
            confirmBtn.textContent = cfg.confirmText || 'Đóng';
        } else {
            cancelBtn.classList.remove('d-none');
        }

        /* Show */
        const modal = bootstrap.Modal.getOrCreateInstance(getEl('globalConfirmModal'));
        modal.show();

        /* Return promise */
        return new Promise(function (resolve) {
            _resolve = resolve;
        });
    }

    /* ── Button listeners ──────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function () {
        getEl('gcmConfirmBtn').addEventListener('click', function () {
            bootstrap.Modal.getInstance(getEl('globalConfirmModal'))?.hide();
            if (_resolve) { _resolve(true); _resolve = null; }
        });
        getEl('gcmCancelBtn').addEventListener('click', function () {
            bootstrap.Modal.getInstance(getEl('globalConfirmModal'))?.hide();
            if (_resolve) { _resolve(false); _resolve = null; }
        });
        getEl('globalConfirmModal').addEventListener('hide.bs.modal', function () {
            /* If hidden by backdrop-click or ESC (though disabled), resolve false */
            setTimeout(function () {
                if (_resolve) { _resolve(false); _resolve = null; }
            }, 0);
        });
    });

    /* ══════════════════════════════════════════════════════════════
       PUBLIC API
       ══════════════════════════════════════════════════════════════ */

    /**
     * window.showConfirm(config) → Promise<boolean>
     * Thay thế confirm() của trình duyệt.
     *
     * Ví dụ:
     *   const ok = await window.showConfirm({ title:'Xóa?', type:'danger' });
     *   if (!ok) return;
     */
    window.showConfirm = function (config) {
        if (typeof config === 'string') config = { message: config };
        return _openModal(config || {}, false);
    };

    /**
     * window.showAlert(message, title?, type?) → Promise<void>
     * Thay thế alert() của trình duyệt.
     *
     * Ví dụ:
     *   await window.showAlert('Vui lòng chọn sản phẩm.', 'Thiếu thông tin', 'info');
     */
    window.showAlert = function (message, title, type) {
        return _openModal({ message: message, title: title || 'Thông báo', type: type || 'info', icon: 'fa-solid fa-circle-info' }, true);
    };

    /**
     * Backward-compat: openConfirmModal({ title, message, type, onConfirm, onCancel })
     * Dùng được cho các code cũ không dùng async/await.
     */
    window.openConfirmModal = function (cfg) {
        window.showConfirm(cfg).then(function (ok) {
            if (ok && typeof cfg.onConfirm === 'function') cfg.onConfirm();
            if (!ok && typeof cfg.onCancel  === 'function') cfg.onCancel();
        });
    };

})();
</script>
