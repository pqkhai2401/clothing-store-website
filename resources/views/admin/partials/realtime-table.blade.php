<style>
    [data-admin-table-area] {
        position: relative;
        transition: opacity .15s ease;
    }

    [data-admin-table-area].is-loading {
        opacity: .55;
        pointer-events: none;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tableArea = document.querySelector('[data-admin-table-area]');
    if (!tableArea) return;

    const searchInput = document.querySelector('[data-admin-search]');
    const filterInputs = () => Array.from(document.querySelectorAll('[data-admin-filter]'));
    let searchTimer = null;
    let currentController = null;

    function buildUrl(baseUrl = window.location.href, resetPage = false) {
        const url = new URL(baseUrl, window.location.origin);
        const search = searchInput ? searchInput.value.trim() : '';

        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        url.searchParams.delete('keyword');

        filterInputs().forEach(function (input) {
            const key = input.name || input.dataset.name;
            const value = input.value;
            if (!key) return;

            if (value !== null && String(value).trim() !== '') {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });

        if (resetPage) {
            url.searchParams.set('page', '1');
        }

        return url;
    }

    function fetchTable(url) {
        if (currentController) {
            currentController.abort();
        }

        currentController = new AbortController();
        tableArea.classList.add('is-loading');

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            signal: currentController.signal,
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Không thể tải dữ liệu.');
                return response.json();
            })
            .then(function (data) {
                tableArea.innerHTML = data.html || '';
                window.history.pushState({}, '', url.toString());
                wireBulkActions();

                if (typeof data.stats === 'string') {
                    const statsArea = document.querySelector('[data-admin-stats-area]');
                    if (statsArea) statsArea.innerHTML = data.stats;
                }
            })
            .catch(function (error) {
                if (error.name !== 'AbortError') {
                    console.error(error);
                    window.showAlert('Có lỗi xảy ra khi tải dữ liệu.', 'Lỗi', 'danger');
                }
            })
            .finally(function () {
                tableArea.classList.remove('is-loading');
            });
    }

    function requestTable(resetPage = true, baseUrl = window.location.href) {
        fetchTable(buildUrl(baseUrl, resetPage));
    }

    function selectedRows() {
        return Array.from(tableArea.querySelectorAll('.hk-cb-row')).filter(function (cb) {
            return cb.checked;
        });
    }

    function updateSelectedState() {
        const rows = Array.from(tableArea.querySelectorAll('.hk-cb-row'));
        const checked = selectedRows();
        const cbAll = tableArea.querySelector('.hk-cb-all');
        const selection = tableArea.querySelector('.hk-pg-selection');
        const count = tableArea.querySelector('.hk-pg-sel-count');
        const label = tableArea.querySelector('.hk-pagination')?.dataset.label || 'mục';

        if (selection) selection.style.display = checked.length > 0 ? 'inline-flex' : 'none';
        if (count) count.textContent = checked.length + ' ' + label + ' được chọn';

        rows.forEach(function (cb) {
            cb.closest('tr')?.classList.toggle('hk-row-selected', cb.checked);
        });

        if (cbAll) {
            cbAll.checked = rows.length > 0 && checked.length === rows.length;
            cbAll.indeterminate = checked.length > 0 && checked.length < rows.length;
        }
    }

    function submitBulk(form, extraFields) {
        const ids = selectedRows().map(function (cb) { return cb.value; });
        if (!ids.length || !form) return;

        form.querySelectorAll('input[name="ids[]"], input[name="is_active"]').forEach(function (el) {
            el.remove();
        });

        ids.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        Object.entries(extraFields || {}).forEach(function ([name, value]) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });

        form.submit();
    }

    function wireBulkActions() {
        updateSelectedState();
        updateSortState();
    }

    function updateSortState() {
        const url       = new URL(window.location.href);
        const activeKey = url.searchParams.get('sort') || url.searchParams.get('sort_by') || '';
        const activeDir = url.searchParams.get('direction') || url.searchParams.get('sort_dir') || 'asc';

        document.querySelectorAll(
            '.product-sort-btn[data-sort-key], .account-sort-btn[data-sort-key], [data-admin-sort][data-sort-key]'
        ).forEach(function (btn) {
            const key      = btn.dataset.sortKey;
            const icon     = btn.querySelector('.product-sort-icon, .account-sort-icon');
            const isActive = Boolean(key && activeKey && key === activeKey);

            btn.classList.toggle('is-active', isActive);

            if (icon) {
                icon.textContent = isActive ? (activeDir === 'desc' ? '↓' : '↑') : '↑↓';
            }
        });
    }

    searchInput?.addEventListener('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function () {
            requestTable(true);
        }, 500);
    });

    searchInput?.closest('form')?.addEventListener('submit', function (event) {
        event.preventDefault();
        requestTable(true);
    });

    filterInputs().forEach(function (input) {
        input.addEventListener('change', function () {
            requestTable(true);
        });
    });

    document.addEventListener('click', function (event) {
        const paginationLink = event.target.closest('[data-admin-table-area] .hk-pagination a');
        if (paginationLink) {
            event.preventDefault();
            requestTable(false, paginationLink.href);
            return;
        }

        const sortButton = event.target.closest('[data-admin-table-area] .product-sort-btn, [data-admin-table-area] .account-sort-btn, [data-admin-sort]');
        if (sortButton) {
            event.preventDefault();
            const currentUrl = new URL(window.location.href);
            const key = sortButton.dataset.sort || sortButton.dataset.sortKey;
            if (!key) return;

            const currentSort = currentUrl.searchParams.get('sort') || currentUrl.searchParams.get('sort_by') || 'id';
            const currentDirection = currentUrl.searchParams.get('direction') || currentUrl.searchParams.get('sort_dir') || 'asc';
            const nextDirection = currentSort === key && currentDirection === 'asc' ? 'desc' : 'asc';

            currentUrl.searchParams.set('sort', key);
            currentUrl.searchParams.set('direction', nextDirection);
            currentUrl.searchParams.delete('sort_by');
            currentUrl.searchParams.delete('sort_dir');
            currentUrl.searchParams.set('page', '1');
            fetchTable(buildUrl(currentUrl.toString(), true));
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.matches('[data-admin-table-area] .hk-cb-all')) {
            tableArea.querySelectorAll('.hk-cb-row').forEach(function (cb) {
                cb.checked = event.target.checked;
            });
            updateSelectedState();
            return;
        }

        if (event.target.matches('[data-admin-table-area] .hk-cb-row')) {
            updateSelectedState();
            return;
        }

        if (event.target.matches('[data-admin-table-area] .hk-pg-pp-select')) {
            const url = buildUrl(window.location.href, true);
            url.searchParams.set('per_page', event.target.value);
            fetchTable(url);
        }
    });

    document.addEventListener('click', function (event) {
        const restoreButton = event.target.closest('[data-admin-table-area] .hk-pg-sel-restore');
        if (restoreButton) {
            const form  = tableArea.querySelector('form[id$="_brf"]');
            const label = tableArea.querySelector('.hk-pagination')?.dataset.label || 'mục';
            const count = selectedRows().length;
            if (count) {
                window.showConfirm({
                    title: 'Khôi phục ' + count + ' ' + label,
                    message: 'Bạn có chắc chắn muốn khôi phục ' + count + ' ' + label + ' đã chọn không?',
                    type: 'info',
                    confirmText: 'Khôi phục'
                }).then(function(ok) { if (ok) submitBulk(form); });
            }
            return;
        }

        const deleteButton = event.target.closest('[data-admin-table-area] .hk-pg-sel-delete');
        if (deleteButton) {
            const form = tableArea.querySelector('form[id$="_bdf"]');
            const label = tableArea.querySelector('.hk-pagination')?.dataset.label || 'mục';
            const count = selectedRows().length;
            if (count) {
                window.showConfirm({
                    title: 'Xóa ' + count + ' ' + label,
                    message: 'Bạn có chắc chắn muốn xóa ' + count + ' ' + label + ' đã chọn không?',
                    type: 'danger',
                    confirmText: 'Xóa'
                }).then(function(ok) { if (ok) submitBulk(form); });
            }
            return;
        }

        const customActionButton = event.target.closest('[data-admin-table-area] [data-custom-value]');
        if (customActionButton) {
            const form = tableArea.querySelector('form[id$="_bcf"]');
            const field = customActionButton.closest('.hk-pg-status-actions')?.dataset.bulkField || 'value';
            const value = customActionButton.dataset.customValue;
            const label = tableArea.querySelector('.hk-pagination')?.dataset.label || 'mục';
            const actionLabel = customActionButton.textContent.trim();
            const count = selectedRows().length;
            if (count) {
                window.showConfirm({
                    title: actionLabel + ' ' + count + ' ' + label,
                    message: 'Bạn có chắc chắn muốn "' + actionLabel + '" cho ' + count + ' ' + label + ' đã chọn không?',
                    type: 'info',
                    confirmText: 'Đồng ý'
                }).then(function(ok) { if (ok) submitBulk(form, { [field]: value }); });
            }
            return;
        }

        const statusButton = event.target.closest('[data-admin-table-area] .hk-pg-sel-status');
        if (statusButton) {
            const form = tableArea.querySelector('form[id$="_bsf"]');
            const status = statusButton.dataset.status || '1';
            const label = tableArea.querySelector('.hk-pagination')?.dataset.label || 'mục';
            const count = selectedRows().length;
            const action = status === '1' ? 'kích hoạt' : 'ngưng hoạt động';
            if (count) {
                window.showConfirm({
                    title: (status === '1' ? 'Kích hoạt ' : 'Ngưng hoạt động ') + count + ' ' + label,
                    message: 'Bạn có chắc chắn muốn ' + action + ' ' + count + ' ' + label + ' đã chọn không?',
                    type: status === '1' ? 'info' : 'warning',
                    confirmText: 'Đồng ý'
                }).then(function(ok) { if (ok) submitBulk(form, { is_active: status }); });
            }
        }
    });

    window.handlePerPageChange = function (value) {
        const url = buildUrl(window.location.href, true);
        url.searchParams.set('per_page', value);
        fetchTable(url);
    };

    window.addEventListener('popstate', function () {
        if (searchInput) {
            searchInput.value = new URL(window.location.href).searchParams.get('search') || '';
        }
        fetchTable(new URL(window.location.href));
    });

    wireBulkActions();
    updateSortState();

    window.reloadAdminTable = function () { requestTable(false); };
});
</script>
