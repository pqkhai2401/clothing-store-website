<script>
document.addEventListener('DOMContentLoaded', function () {
    const categoryFilter = document.getElementById('productCategoryFilter');
    const catTrigger = document.getElementById('hkCatTrigger');
    const catPanel = document.getElementById('hkCatPanel');
    const catSearch = document.getElementById('hkCatSearch');
    const catLabel = document.getElementById('hkCatLabel');
    const catList = document.getElementById('hkCatList');

    if (!catTrigger || !catPanel || !catList) return;

    function showAllCatItems() {
        catList.querySelectorAll('.hk-cat-item').forEach(function (btn) {
            btn.hidden = false;
        });
        catList.querySelector('.hk-cat-empty')?.remove();
    }

    function openCatPanel() {
        catPanel.hidden = false;
        catTrigger.classList.add('is-open');
        catTrigger.setAttribute('aria-expanded', 'true');
        if (catSearch) {
            catSearch.value = '';
            showAllCatItems();
            catSearch.focus();
        }
    }

    function closeCatPanel() {
        catPanel.hidden = true;
        catTrigger.classList.remove('is-open');
        catTrigger.setAttribute('aria-expanded', 'false');
    }

    catTrigger.addEventListener('click', function () {
        catPanel.hidden ? openCatPanel() : closeCatPanel();
    });

    catSearch?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        let visible = 0;

        catList.querySelectorAll('.hk-cat-item').forEach(function (btn) {
            const match = !q || btn.textContent.toLowerCase().includes(q);
            btn.hidden = !match;
            if (match) visible++;
        });

        const existing = catList.querySelector('.hk-cat-empty');
        if (visible === 0 && !existing) {
            const msg = document.createElement('div');
            msg.className = 'hk-cat-empty';
            msg.textContent = 'Không tìm thấy danh mục';
            catList.appendChild(msg);
        } else if (visible > 0 && existing) {
            existing.remove();
        }
    });

    catList.addEventListener('click', function (event) {
        const btn = event.target.closest('.hk-cat-item');
        if (!btn) return;

        catList.querySelectorAll('.hk-cat-item').forEach(function (item) {
            item.classList.remove('is-active');
        });
        btn.classList.add('is-active');
        if (catLabel) catLabel.textContent = btn.dataset.label;

        const parentCategoryFilter = document.getElementById('productParentCategoryFilter');
        const isParent = btn.dataset.type === 'parent';

        if (categoryFilter) categoryFilter.value = isParent ? '' : (btn.dataset.value || '');
        if (parentCategoryFilter) parentCategoryFilter.value = isParent ? (btn.dataset.value || '') : '';

        if (categoryFilter) {
            categoryFilter.dispatchEvent(new Event('change', { bubbles: true }));
        }
        closeCatPanel();
    });

    document.addEventListener('click', function (event) {
        if (!catPanel.hidden && !document.getElementById('hkCatFilter')?.contains(event.target)) {
            closeCatPanel();
        }
    });
});

document.addEventListener('DOMContentLoaded', function () {
    function wireSimpleDropdown(config) {
        const root = document.getElementById(config.root);
        const trigger = document.getElementById(config.trigger);
        const panel = document.getElementById(config.panel);
        const label = document.getElementById(config.label);
        const list = document.getElementById(config.list);
        const hidden = document.getElementById(config.hidden);
        if (!root || !trigger || !panel || !list || !hidden) return;

        function open() {
            panel.hidden = false;
            trigger.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        }

        function close() {
            panel.hidden = true;
            trigger.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
        }

        trigger.addEventListener('click', function () {
            panel.hidden ? open() : close();
        });

        list.addEventListener('click', function (event) {
            const btn = event.target.closest('.hk-cat-item');
            if (!btn) return;

            list.querySelectorAll('.hk-cat-item').forEach(function (item) {
                item.classList.remove('is-active');
            });
            btn.classList.add('is-active');
            if (label) label.textContent = btn.dataset.label;
            hidden.value = btn.dataset.value || '';
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close();
        });

        document.addEventListener('click', function (event) {
            if (!panel.hidden && !root.contains(event.target)) {
                close();
            }
        });
    }

    wireSimpleDropdown({
        root: 'hkProductStatusFilter',
        trigger: 'hkProductStatusTrigger',
        panel: 'hkProductStatusPanel',
        label: 'hkProductStatusLabel',
        list: 'hkProductStatusList',
        hidden: 'productStatusFilter',
    });

    wireSimpleDropdown({
        root: 'hkProductSizeFilter',
        trigger: 'hkProductSizeTrigger',
        panel: 'hkProductSizePanel',
        label: 'hkProductSizeLabel',
        list: 'hkProductSizeList',
        hidden: 'productSizeFilter',
    });

    wireSimpleDropdown({
        root: 'hkProductColorFilter',
        trigger: 'hkProductColorTrigger',
        panel: 'hkProductColorPanel',
        label: 'hkProductColorLabel',
        list: 'hkProductColorList',
        hidden: 'productColorFilter',
    });

    wireSimpleDropdown({
        root: 'hkProductBrandFilter',
        trigger: 'hkProductBrandTrigger',
        panel: 'hkProductBrandPanel',
        label: 'hkProductBrandLabel',
        list: 'hkProductBrandList',
        hidden: 'productBrandFilter',
    });

    /* ── Chip lọc nhanh "Sắp hết hàng" (tổng tồn kho < 10) ── */
    const lowStockChip = document.getElementById('productLowStockChip');
    const stockStatusFilter = document.getElementById('productStockStatusFilter');
    if (lowStockChip && stockStatusFilter) {
        lowStockChip.addEventListener('click', function () {
            const next = stockStatusFilter.value === 'low_stock' ? '' : 'low_stock';
            stockStatusFilter.value = next;
            lowStockChip.classList.toggle('is-active', next === 'low_stock');
            stockStatusFilter.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const tableArea = document.querySelector('[data-admin-table-area]');
    if (!tableArea) return;

    let activeTooltip = null;
    let pinnedTooltip = null;

    function positionStockTooltip(container) {
        const tooltip = container.querySelector('.tooltip-box');
        if (!tooltip) return;

        container.classList.add('is-open');

        const triggerRect = container.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();
        const viewportGap = 12;
        const spacing = 10;

        let left = triggerRect.left + (triggerRect.width / 2) - (tooltipRect.width / 2);
        left = Math.max(viewportGap, Math.min(left, window.innerWidth - tooltipRect.width - viewportGap));

        let top = triggerRect.top - tooltipRect.height - spacing;
        if (top < viewportGap) {
            top = triggerRect.bottom + spacing;
        }
        top = Math.max(viewportGap, Math.min(top, window.innerHeight - tooltipRect.height - viewportGap));

        tooltip.style.left = `${Math.round(left)}px`;
        tooltip.style.top = `${Math.round(top)}px`;
    }

    function closeStockTooltip() {
        if (!activeTooltip) return;

        const tooltip = activeTooltip.querySelector('.tooltip-box');
        activeTooltip.classList.remove('is-open');
        if (tooltip) {
            tooltip.style.left = '';
            tooltip.style.top = '';
        }
        activeTooltip = null;
        pinnedTooltip = null;
    }

    function openStockTooltip(container, pinned = false) {
        if (activeTooltip && activeTooltip !== container) {
            closeStockTooltip();
        }

        activeTooltip = container;
        if (pinned) pinnedTooltip = container;
        positionStockTooltip(container);
    }

    tableArea.addEventListener('pointerover', function (event) {
        const container = event.target.closest('.tooltip-container');
        if (!container || !tableArea.contains(container)) return;

        openStockTooltip(container);
    });

    tableArea.addEventListener('pointerout', function (event) {
        const container = event.target.closest('.tooltip-container');
        if (!container || container.contains(event.relatedTarget)) return;
        if (pinnedTooltip === container) return;

        closeStockTooltip();
    });

    tableArea.addEventListener('focusin', function (event) {
        const container = event.target.closest('.tooltip-container');
        if (!container) return;

        openStockTooltip(container);
    });

    tableArea.addEventListener('focusout', function () {
        if (!pinnedTooltip) closeStockTooltip();
    });

    tableArea.addEventListener('click', function (event) {
        const container = event.target.closest('.tooltip-container');
        if (!container || !tableArea.contains(container)) return;

        event.stopPropagation();

        if (event.target.closest('.tooltip-box')) {
            return;
        }

        if (pinnedTooltip === container) {
            closeStockTooltip();
            return;
        }

        openStockTooltip(container, true);
    });

    document.addEventListener('click', function (event) {
        if (!activeTooltip || activeTooltip.contains(event.target)) return;

        closeStockTooltip();
    });

    window.addEventListener('scroll', function () {
        if (activeTooltip) positionStockTooltip(activeTooltip);
    }, true);

    window.addEventListener('resize', function () {
        if (activeTooltip) positionStockTooltip(activeTooltip);
    });
});

/* ── Xuất Excel: ưu tiên các dòng đã chọn (checkbox), nếu không có dòng nào được chọn thì xuất theo bộ lọc hiện tại ── */
function exportProducts() {
    const checked = Array.from(document.querySelectorAll('.product-row-check:checked'));
    const params = new URLSearchParams();

    if (checked.length > 0) {
        params.set('ids', checked.map(cb => cb.value).join(','));
    } else {
        const fields = {
            category_id: 'productCategoryFilter',
            parent_category_id: 'productParentCategoryFilter',
            brand_id: 'productBrandFilter',
            size_id: 'productSizeFilter',
            color_id: 'productColorFilter',
            status: 'productStatusFilter',
            stock_status: 'productStockStatusFilter',
            search: 'productRealtimeSearch',
        };

        Object.entries(fields).forEach(([param, elementId]) => {
            const value = document.getElementById(elementId)?.value;
            if (value !== undefined && value !== null && value !== '') {
                params.set(param, value);
            }
        });
    }

    window.location.href = '{{ route('admin.products.export') }}?' + params.toString();
}

/* ── Chạy lại các thẻ <script> bên trong một khối HTML vừa được nạp qua innerHTML ──
   (trình duyệt không tự thực thi <script> được gán qua innerHTML, phải dựng lại thủ công) ── */
window.runInjectedScripts = function (container) {
    Array.from(container.querySelectorAll('script')).forEach(function (oldScript) {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.replaceWith(newScript);
    });
};

/* ── Panel "Sửa sản phẩm" trượt từ phải: nạp form qua AJAX ──
   Dùng event delegation vì bảng được nạp lại qua AJAX. */
document.addEventListener('click', function (e) {
    const trigger = e.target.closest('[data-product-edit-trigger]');
    if (!trigger) return;
    e.preventDefault();

    const url = trigger.dataset.editUrl;
    const offcanvasEl = document.getElementById('productEditOffcanvas');
    const bodyEl = offcanvasEl?.querySelector('[data-product-edit-body]');
    if (!url || !offcanvasEl || !bodyEl) return;

    bodyEl.innerHTML = '<div class="offcanvas-body text-center py-5"><div class="spinner-border text-secondary" role="status"></div></div>';
    bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
        .then(res => { if (!res.ok) throw new Error('request-failed'); return res.json(); })
        .then(data => {
            bodyEl.innerHTML = data.html;
            window.runInjectedScripts(bodyEl);
        })
        .catch(() => {
            bodyEl.innerHTML = '<div class="offcanvas-body text-danger p-4">Không thể tải form sửa sản phẩm. Vui lòng thử lại.</div>';
        });
});

/* ── Toast thông báo (đồng bộ với component thông báo chung của trang) ── */
function showProductToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) { alert(message); return; }
    const toast = document.createElement('div');
    toast.className = `custom-toast server-toast ${type === 'error' ? 'toast-error' : 'toast-success'}`;
    toast.style.pointerEvents = 'auto';
    toast.innerHTML = `
        <div class="toast-content">
            <div class="toast-message">${message}</div>
        </div>
        <span class="toast-close" onclick="closeServerToast(this)">&times;</span>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        if (document.body.contains(toast)) {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

/* ── Chuyển trạng thái Đang bán / Ẩn ngay trên bảng bằng nút gạt (không cần vào trang Sửa) ──
   Dùng event delegation vì bảng được nạp lại qua AJAX. */
document.addEventListener('change', function (e) {
    const toggle = e.target.closest('.product-status-switch');
    if (!toggle) return;

    const url = toggle.dataset.toggleStatusUrl;
    const label = toggle.closest('.product-status-switch-wrap')?.querySelector('.product-status-switch-label');
    const previousChecked = !toggle.checked;
    toggle.disabled = true;

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({}),
    })
        .then(res => { if (!res.ok) throw new Error('request-failed'); return res.json(); })
        .then(data => {
            if (label) label.textContent = data.status ? 'Đang bán' : 'Ẩn';
            showProductToast(data.message);
        })
        .catch(() => {
            toggle.checked = previousChecked;
            showProductToast('Không thể cập nhật trạng thái sản phẩm. Vui lòng thử lại.', 'error');
        })
        .finally(() => { toggle.disabled = false; });
});

/* ── Đánh dấu / bỏ đánh dấu "Sản phẩm nổi bật" bằng icon ngôi sao ──
   Dùng event delegation vì bảng được nạp lại qua AJAX. */
document.addEventListener('click', function (e) {
    const star = e.target.closest('.product-featured-star');
    if (!star) return;

    const url = star.dataset.toggleFeaturedUrl;
    const wasActive = star.classList.contains('is-active');
    star.disabled = true;
    star.classList.toggle('is-active');

    fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
        body: JSON.stringify({}),
    })
        .then(res => { if (!res.ok) throw new Error('request-failed'); return res.json(); })
        .then(data => {
            star.classList.toggle('is-active', data.is_featured);
            star.title = data.is_featured ? 'Bỏ đánh dấu nổi bật' : 'Đánh dấu sản phẩm nổi bật';
            showProductToast(data.message);
        })
        .catch(() => {
            star.classList.toggle('is-active', wasActive);
            showProductToast('Không thể cập nhật trạng thái nổi bật. Vui lòng thử lại.', 'error');
        })
        .finally(() => { star.disabled = false; });
});

/* ── Sửa nhanh Giá / Giảm giá bằng double-click ngay trên dòng bảng ──
   Dùng event delegation vì bảng được nạp lại qua AJAX. */
(function () {
    function formatMoney(num) {
        return Math.round(num || 0).toLocaleString('vi-VN') + '₫';
    }

    function closeCell(cell) {
        cell.querySelector('.product-quickedit-view').classList.remove('d-none');
        cell.querySelector('.product-quickedit-form').classList.add('d-none');
    }

    function closeAllCells() {
        document.querySelectorAll('.product-quickedit-cell').forEach(closeCell);
    }

    document.addEventListener('dblclick', function (e) {
        const cell = e.target.closest('.product-quickedit-cell');
        if (!cell) return;
        closeAllCells();
        cell.querySelector('.product-quickedit-view').classList.add('d-none');
        cell.querySelector('.product-quickedit-form').classList.remove('d-none');
        cell.querySelector('[data-field="price"]')?.focus();
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.product-quickedit-cell')) closeAllCells();
    });

    document.addEventListener('click', function (e) {
        const cancelBtn = e.target.closest('.product-quickedit-cancel');
        if (!cancelBtn) return;
        const cell = cancelBtn.closest('.product-quickedit-cell');
        const priceInput = cell.querySelector('[data-field="price"]');
        const discountInput = cell.querySelector('[data-field="discount"]');
        priceInput.value = priceInput.defaultValue;
        discountInput.value = discountInput.defaultValue;
        closeCell(cell);
    });

    function saveCell(cell) {
        const url = cell.dataset.quickeditUrl;
        const priceInput = cell.querySelector('[data-field="price"]');
        const discountInput = cell.querySelector('[data-field="discount"]');
        const saveBtn = cell.querySelector('.product-quickedit-save');
        const viewEl = cell.querySelector('.product-quickedit-view');

        saveBtn.disabled = true;

        fetch(url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ price: priceInput.value, discount: discountInput.value }),
        })
            .then(res => res.json().then(data => ({ ok: res.ok, data })))
            .then(({ ok, data }) => {
                if (!ok) {
                    showProductToast(data.message || 'Không thể cập nhật giá.', 'error');
                    return;
                }

                priceInput.value = data.price;
                discountInput.value = data.discount;
                priceInput.defaultValue = data.price;
                discountInput.defaultValue = data.discount;

                if (data.discount > 0) {
                    viewEl.innerHTML = `
                        <div class="price-display">
                            <span class="price-sale">${formatMoney(data.price * (100 - data.discount) / 100)}</span>
                            <span class="price-original">${formatMoney(data.price)}</span>
                        </div>
                    `;
                } else {
                    viewEl.innerHTML = `<span class="price-normal">${formatMoney(data.price)}</span>`;
                }

                closeCell(cell);
                showProductToast(data.message);
            })
            .catch(() => {
                showProductToast('Không thể kết nối tới máy chủ. Vui lòng thử lại.', 'error');
            })
            .finally(() => { saveBtn.disabled = false; });
    }

    document.addEventListener('click', function (e) {
        const saveBtn = e.target.closest('.product-quickedit-save');
        if (!saveBtn) return;
        e.stopPropagation();
        saveCell(saveBtn.closest('.product-quickedit-cell'));
    });

    document.addEventListener('click', function (e) {
        if (e.target.closest('.product-quickedit-form')) e.stopPropagation();
    });

    document.addEventListener('keydown', function (e) {
        const cell = e.target.closest('.product-quickedit-cell');
        if (!cell) return;
        if (e.key === 'Enter') {
            e.preventDefault();
            saveCell(cell);
        } else if (e.key === 'Escape') {
            cell.querySelector('.product-quickedit-cancel')?.click();
        }
    });
})();
</script>
