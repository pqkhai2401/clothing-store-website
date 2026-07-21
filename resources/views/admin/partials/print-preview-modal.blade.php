{{-- Mở trang in (hóa đơn / phiếu nhập / phiếu xuất) trong 1 popup riêng khi bấm phần tử có
     [data-print-url]. Trang in tự đặt <title> = mã chứng từ và tự bấm window.print() khi mở
     top-level (không qua iframe) nên tên file khi "Lưu dưới dạng PDF" luôn đúng = mã chứng từ. --}}
@once
@push('scripts')
<script>
(function () {
    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-print-url]');
        if (!trigger) return;
        e.preventDefault(); // JS lỗi thì href (nếu có) vẫn mở được tab bản in
        const url = trigger.getAttribute('data-print-url');
        if (!url) return;

        // Nếu trigger đang nằm trong 1 modal khác (vd popup "Xem chi tiết") thì đóng nó lại.
        const parentModal = trigger.closest('.modal');
        if (parentModal) {
            bootstrap.Modal.getInstance(parentModal)?.hide();
        }

        const w = Math.min(950, window.screen.availWidth - 80);
        const h = Math.min(1000, window.screen.availHeight - 80);
        window.open(url, '_blank', `popup=yes,width=${w},height=${h}`);
    });
})();
</script>
@endpush
@endonce
