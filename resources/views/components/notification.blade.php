<div id="toast-container"
    style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none;">

    {{-- Success Messages --}}
    @if (session('success'))
        <div class="custom-toast server-toast toast-success">
            <div class="toast-content">
                <div class="toast-icon">
                    <svg style="flex-shrink: 0; min-width: 22px; min-height: 22px; display: block;" viewBox="0 0 24 24"
                        width="22" height="22">
                        <path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
                <div class="toast-message">
                    {{ session('success') }}
                </div>
            </div>
            <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
        </div>
    @endif

    {{-- Error Messages --}}
    @if (session('error'))
        <div class="custom-toast server-toast toast-error">
            <div class="toast-content">
                <div class="toast-icon">
                    <svg style="flex-shrink: 0; min-width: 22px; min-height: 22px; display: block;" viewBox="0 0 24 24"
                        width="22" height="22">
                        <path d="M9 9l6 6M15 9l-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
                <div class="toast-message">
                    {{ session('error') }}
                </div>
            </div>
            <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="custom-toast server-toast toast-error">
            <div class="toast-content">
                <div class="toast-icon">
                    <svg style="flex-shrink: 0; min-width: 22px; min-height: 22px; display: block;" viewBox="0 0 24 24"
                        width="22" height="22">
                        <path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" fill="none" />
                    </svg>
                </div>
                <div class="toast-message">
                    <div style="font-weight: 600; margin-bottom: 4px;">Vui lòng kiểm tra lại:</div>
                    <ul style="margin: 0; padding-left: 18px; line-height: 1.5;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
        </div>
    @endif

</div>

<style>
    .custom-toast.server-toast::before,
    .custom-toast.server-toast::after {
        display: none !important;
        content: none !important;
    }

    #toast-container .custom-toast.server-toast {
        pointer-events: auto;
        padding: 12px 44px 12px 14px;
        border-radius: 6px;
        box-shadow: 0 6px 18px rgba(17, 24, 39, 0.08);
        min-width: 300px;
        max-width: 420px;
        word-break: break-word;
        position: relative;
        border: 1px solid rgba(15, 23, 42, 0.08) !important;
        animation: slideInRight 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .custom-toast.server-toast.hiding {
        animation: slideOutRight 0.25s ease-in forwards !important;
    }

    @keyframes slideOutRight {
        0% {
            transform: translateX(0);
            opacity: 1;
        }

        100% {
            transform: translateX(15px);
            opacity: 0;
        }
    }

    .toast-close {
        pointer-events: auto !important;
        user-select: none;
    }

    @keyframes slideInRight {
        0% {
            transform: translateX(20px);
            opacity: 0;
        }

        100% {
            transform: translateX(0);
            opacity: 1;
        }
    }

    #toast-container .custom-toast.server-toast.toast-success {
        background: #e8f7ed !important;
        color: #2b6b3f !important;
        border-color: rgba(34, 197, 94, 0.22) !important;
    }

    #toast-container .custom-toast.server-toast.toast-error {
        background: #fdebed !important;
        color: #a61b2b !important;
        border-color: rgba(239, 68, 68, 0.22) !important;
    }

    #toast-container .custom-toast.server-toast.toast-warning {
        background: #fff4dd !important;
        color: #7a5200 !important;
        border-color: rgba(245, 158, 11, 0.22) !important;
    }

    #toast-container .custom-toast.server-toast.toast-info {
        background: #e5f3fb !important;
        color: #0b5772 !important;
        border-color: rgba(59, 130, 246, 0.22) !important;
    }

    .toast-content {
        display: flex;
        align-items: center;
        gap: 10px;
        padding-right: 0;
    }

    #toast-container .toast-icon {
        width: 26px;
        height: 26px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
    }

    .toast-icon svg {
        display: block;
    }

    #toast-container .toast-success .toast-icon {
        background: rgba(34, 197, 94, 0.20) !important;
    }

    #toast-container .toast-error .toast-icon {
        background: rgba(239, 68, 68, 0.20) !important;
    }

    #toast-container .toast-warning .toast-icon {
        background: rgba(245, 158, 11, 0.20) !important;
    }

    #toast-container .toast-info .toast-icon {
        background: rgba(59, 130, 246, 0.20) !important;
    }

    .toast-message {
        flex: 1;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.5;
        font-weight: 500;
    }

    .toast-close {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 20px;
        line-height: 1;
        opacity: 0.55;
        color: rgba(15, 23, 42, 0.8);
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: opacity 0.2s;
    }

    .toast-close:hover {
        opacity: 0.8;
        background: rgba(15, 23, 42, 0.06);
    }
</style>

<script>
    window.closeServerToast = function (btn) {
        const toast = btn.closest('.server-toast');
        if (toast) {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        const serverToasts = document.querySelectorAll('.server-toast');

        serverToasts.forEach(function (toast) {
            setTimeout(function () {
                if (document.body.contains(toast)) {
                    toast.classList.add('hiding');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        });
    });
</script>
