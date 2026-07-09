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
</script>
