{{-- Modal xem trước + in dùng chung cho hóa đơn (đơn hàng) và phiếu nhập/xuất (kho).
     Nạp trang in vào iframe để giữ CSS in độc lập; in bằng iframe.print() nên chỉ in đúng
     chứng từ. Trigger: bất kỳ phần tử nào có [data-print-url] (kèm [data-print-title] tùy chọn). --}}
@once
<div class="modal fade account-modal" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:920px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="printPreviewModalLabel">Xem trước &amp; in</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0" style="background:#eef2f7;">
                <div class="text-center py-5 text-muted" id="printPreviewLoading">
                    <span class="spinner-border spinner-border-sm me-2"></span> Đang tải...
                </div>
                <iframe id="printPreviewFrame" title="Xem trước bản in" style="width:100%;height:74vh;border:0;display:none;"></iframe>
            </div>
            <div class="modal-footer justify-content-between pb-4 px-4 gap-2 flex-wrap">
                <small class="text-muted" style="font-size:12px;max-width:340px;">
                    Bấm <b>Lưu PDF</b> rồi chọn <b>“Lưu dưới dạng PDF”</b> trong hộp thoại in — tên file = mã chứng từ.
                </small>
                <div class="d-flex gap-2">
                    <button type="button" class="btn account-action-btn btn-dark" id="printPreviewPdfBtn">
                        <i class="fa-solid fa-file-pdf me-1"></i> Lưu PDF
                    </button>
                    <button type="button" class="btn account-action-btn btn-light border" id="printPreviewPrintBtn">
                        <i class="fa-solid fa-print me-1"></i> In
                    </button>
                    <button type="button" class="btn account-action-btn btn-light" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modalEl = document.getElementById('printPreviewModal');
    if (!modalEl) return;
    const modal    = new bootstrap.Modal(modalEl);
    const frame    = document.getElementById('printPreviewFrame');
    const loading  = document.getElementById('printPreviewLoading');
    const titleEl  = document.getElementById('printPreviewModalLabel');
    const printBtn = document.getElementById('printPreviewPrintBtn');
    const pdfBtn   = document.getElementById('printPreviewPdfBtn');

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-print-url]');
        if (!trigger) return;
        e.preventDefault(); // JS lỗi thì href (nếu có) vẫn mở được tab bản in
        const url = trigger.getAttribute('data-print-url');
        if (!url) return;

        // Nếu trigger đang nằm trong 1 modal khác (popup chi tiết) thì đóng nó để không chồng modal.
        const parentModal = trigger.closest('.modal');
        if (parentModal && parentModal !== modalEl) {
            bootstrap.Modal.getInstance(parentModal)?.hide();
        }

        titleEl.textContent = trigger.getAttribute('data-print-title') || 'Xem trước & in';
        loading.style.display = '';
        frame.style.display = 'none';
        frame.src = url;
        modal.show();
    });

    frame.addEventListener('load', function () {
        if (!frame.src || frame.src === 'about:blank') return;
        loading.style.display = 'none';
        frame.style.display = 'block';
    });

    function printFrame() {
        if (!frame.contentWindow) return;
        // Tên file khi "Lưu dưới dạng PDF" = mã chứng từ. iframe đã đặt title = mã, nhưng một số
        // trình duyệt lấy title trang cha khi in iframe → tạm gán title cha rồi khôi phục.
        var code = '';
        try { code = frame.contentDocument ? frame.contentDocument.title : ''; } catch (err) {}
        var oldTitle = document.title;
        if (code) document.title = code;
        frame.contentWindow.focus();
        frame.contentWindow.print();
        setTimeout(function () { document.title = oldTitle; }, 1500);
    }

    printBtn.addEventListener('click', printFrame);
    pdfBtn.addEventListener('click', printFrame);

    modalEl.addEventListener('hidden.bs.modal', function () { frame.src = 'about:blank'; });
})();
</script>
@endpush
@endonce
