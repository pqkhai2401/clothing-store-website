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
</script>
<script src="{{ asset('assets/js/adminlte.js') }}"></script>
<script src="{{ asset('assets/js/Sortable.min.js') }}"></script>
<script src="{{ asset('assets/js/apexcharts.min.js') }}"></script>
<script src="{{ asset('assets/js/jsvectormap.min.js') }}"></script>
<script src="{{ asset('js/ajaxPagination.js') }}"></script>
<script src="{{ asset('js/apiService.js') }}"></script>
<script src="{{ asset('assets/js/jquery-validation.js') }}"></script>