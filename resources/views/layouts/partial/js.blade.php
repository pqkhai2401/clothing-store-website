<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/tom-select.complete.min.js') }}"></script>
<script src="{{ asset('assets/js/crm-tom-select.js') }}"></script>

<script src="{{ asset('assets/js/overlayscrollbars.browser.es6.min.js') }}"></script>

<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
<script>
    /* Cho phép dropdown "Thao tác" trong các bảng admin (.product-more-btn) tràn ra
       ngoài vùng bảng thay vì bị .product-table-wrap/.table-responsive cắt mất.
       Áp dụng position: fixed cho popper để nó thoát khỏi mọi overflow của bảng cha. */
    document.addEventListener('show.bs.dropdown', function (event) {
        var toggle = event.target;
        if (!toggle.classList.contains('product-more-btn')) return;

        var instance = bootstrap.Dropdown.getOrCreateInstance(toggle);
        if (instance && instance._config) {
            instance._config.popperConfig = function (defaultConfig) {
                return Object.assign({}, defaultConfig, { strategy: 'fixed' });
            };
        }
    });

    /* Bootstrap không tự xếp lớp (z-index) khi mở modal xác nhận (updateConfirmModal/
       globalConfirmModal/deleteConfirmModal...) đè lên một modal chi tiết đang mở sẵn
       (ví dụ: modal xác nhận huỷ phiếu kiểm kê mở trên modal chi tiết phiếu kiểm kê).
       Kết quả: 2 lớp backdrop chồng nhau (nền tối gấp đôi) và modal thứ 2 có thể bị che
       khuất/không bấm được vì cùng z-index với modal thứ nhất. Tự nâng z-index của mỗi
       modal + backdrop mới mở theo số modal đang mở để chúng luôn xếp đúng thứ tự trên cùng. */
    document.addEventListener('show.bs.modal', function (event) {
        var openCount = document.querySelectorAll('.modal.show').length;
        event.target.style.zIndex = 1055 + openCount * 20;

        setTimeout(function () {
            var backdrops = document.querySelectorAll('.modal-backdrop:not([data-zfixed])');
            var lastBackdrop = backdrops[backdrops.length - 1];
            if (lastBackdrop) {
                lastBackdrop.style.zIndex = 1055 + openCount * 20 - 1;
                lastBackdrop.setAttribute('data-zfixed', '1');
            }
        }, 0);
    });

    /* Khi đóng 1 modal con trong khi vẫn còn modal cha đang mở, Bootstrap có thể gỡ nhầm
       class "modal-open" khỏi <body> (tưởng không còn modal nào mở) khiến trang bị khoá
       scroll/tương tác dù modal cha vẫn hiển thị. Trả lại class này nếu vẫn còn modal mở. */
    document.addEventListener('hidden.bs.modal', function () {
        if (document.querySelectorAll('.modal.show').length) {
            document.body.classList.add('modal-open');
        }
    });
</script>
<script src="{{ asset('assets/js/adminlte.js') }}"></script>
<script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('js/ajaxPagination.js') }}"></script>
<script src="{{ asset('js/apiService.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validation.js') }}"></script>