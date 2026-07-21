{{-- Tra cứu "hoàn tiền cho ai, vào đâu" cho đơn đã thu tiền nhưng bị hủy.
     Đặt ở TRANG CHA (không nằm trong detail-content) vì khối chi tiết đơn được nạp qua AJAX
     bằng innerHTML — script bên trong sẽ không tự chạy. Dùng event delegation trên document
     nên vẫn bắt được nút dù khối chi tiết mở trong popup hay ở trang chi tiết đầy đủ. --}}
@once
@push('scripts')
<script>
(function () {
    const money = (n) => Number(n || 0).toLocaleString('vi-VN') + '₫';
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));

    document.addEventListener('click', async function (e) {
        const trigger = e.target.closest('[data-refund-lookup]');
        if (!trigger) return;
        e.preventDefault();

        const card = trigger.closest('.ord-refund-card');
        const box  = card?.querySelector('[data-refund-result]');
        if (!box) return;

        const oldHtml = trigger.innerHTML;
        trigger.disabled = true;
        trigger.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang tra cứu...';
        box.style.display = '';
        box.innerHTML = '<div class="text-muted" style="font-size:13px;">Đang hỏi cổng thanh toán...</div>';

        try {
            const res = await fetch(trigger.getAttribute('data-refund-lookup'), {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            if (!res.ok) throw new Error('lookup failed');
            const d = await res.json();

            let html = '';

            if (Array.isArray(d.payers) && d.payers.length) {
                html += '<div class="ord-refund-payer">'
                     +  '<div class="fw-bold mb-2" style="font-size:13px;">Người đã chuyển tiền — chuyển khoản lại theo thông tin này:</div>';
                d.payers.forEach(p => {
                    html += '<div class="ord-refund-payer-row">'
                         +  '<div><span class="text-muted">Chủ tài khoản:</span> <b>' + esc(p.name || '—') + '</b></div>'
                         +  '<div><span class="text-muted">Số tài khoản:</span> <b style="font-family:monospace;">' + esc(p.account || '—') + '</b></div>'
                         +  '<div><span class="text-muted">Ngân hàng:</span> <b>' + esc(p.bank || '—') + '</b></div>'
                         +  '<div><span class="text-muted">Số tiền đã nhận:</span> <b>' + money(p.amount) + '</b></div>'
                         + (p.paid_at ? '<div><span class="text-muted">Thời điểm:</span> ' + esc(p.paid_at) + '</div>' : '')
                         +  '</div>';
                });
                html += '</div>';
            }

            if (d.message) {
                html += '<div class="ord-refund-note mt-2">' + esc(d.message) + '</div>';
            }

            box.innerHTML = html || '<div class="text-muted" style="font-size:13px;">Không có thông tin bổ sung.</div>';
        } catch {
            box.innerHTML = '<div class="text-danger" style="font-size:13px;">Không tra cứu được. Vui lòng thử lại hoặc tra thủ công trong dashboard cổng thanh toán.</div>';
        } finally {
            trigger.disabled = false;
            trigger.innerHTML = oldHtml;
        }
    });

    /* Hộp xác nhận dùng chung của admin (layouts.components.confirm.update) thay cho confirm()
       mặc định của trình duyệt. Nếu vì lý do nào đó chưa nạp được thì lùi về confirm() để
       thao tác vẫn dùng được, không bị chặn cứng. */
    function askConfirm(title, message, onYes) {
        window.showConfirm({ title, message, type: 'warning' }).then(function(ok) { if (ok) onYes(); });
    }

    function notify(message, type = 'success') {
        if (typeof window.showAdminToast === 'function') {
            window.showAdminToast(message, type);
        } else {
            window.showAlert(message, 'Thông báo', type === 'error' ? 'danger' : type);
        }
    }

    /* Duyệt / Từ chối YÊU CẦU HỦY ĐƠN do khách gửi. */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-cancelreq-action]');
        if (!btn) return;
        e.preventDefault();

        const action = btn.getAttribute('data-cancelreq-action');
        const isApprove = action === 'approve';

        askConfirm(
            isApprove ? 'Duyệt yêu cầu hủy đơn' : 'Từ chối yêu cầu hủy',
            isApprove
                ? 'Đơn sẽ bị HỦY và kho được hoàn về đúng lô. Nếu đơn đã thanh toán, hệ thống sẽ báo cần hoàn tiền.'
                : 'Bạn chắc chắn từ chối yêu cầu hủy đơn này?',
            async function () {
                const card = btn.closest('.ord-cancelreq-card');
                const note = card?.querySelector('[data-cancelreq-note]')?.value ?? '';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                const oldHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang xử lý...';

                try {
                    const res = await fetch(btn.getAttribute('data-cancelreq-url'), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ action, admin_note: note }),
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        notify(data.message || 'Không xử lý được yêu cầu.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = oldHtml;
                        return;
                    }

                    window.location.reload();
                } catch {
                    notify('Không kết nối được. Vui lòng thử lại.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = oldHtml;
                }
            }
        );
    });

    /* Đánh dấu ĐÃ HOÀN TIỀN — đổi payment_status sang 'refunded' để cảnh báo tự tắt. */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-refund-done]');
        if (!btn) return;
        e.preventDefault();

        askConfirm(
            'Xác nhận đã hoàn tiền',
            'Bạn đã chuyển tiền lại cho khách? Cảnh báo "Cần hoàn tiền" sẽ tắt và đơn không còn được tính doanh thu.',
            async function () {
                const card = btn.closest('.ord-refund-card');
                const note = card?.querySelector('[data-refund-note]')?.value ?? '';
                const refundAmount = card?.querySelector('[data-refund-amount]')?.value ?? '';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                const oldHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Đang lưu...';

                try {
                    const res = await fetch(btn.getAttribute('data-refund-done'), {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ note, refund_amount: refundAmount || null }),
                    });
                    const data = await res.json().catch(() => ({}));

                    if (!res.ok) {
                        notify(data.message || 'Không ghi nhận được. Vui lòng thử lại.', 'error');
                        btn.disabled = false;
                        btn.innerHTML = oldHtml;
                        return;
                    }

                    window.location.reload();
                } catch {
                    notify('Không kết nối được. Vui lòng thử lại.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = oldHtml;
                }
            }
        );
    });
})();
</script>
@endpush
@endonce
