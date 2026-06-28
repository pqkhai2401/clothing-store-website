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
        if (categoryFilter) {
            categoryFilter.value = btn.dataset.value || '';
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
</script>
