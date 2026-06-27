<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table          = document.getElementById('productTable');
        const searchInput    = document.getElementById('productRealtimeSearch');
        const categoryFilter = document.getElementById('productCategoryFilter');
        const checkAll       = document.getElementById('productCheckAll');

        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-product-row]'));
        let sortState = { key: 'id', dir: 'asc' };

        function normalize(value) {
            return String(value || '').toLowerCase().trim();
        }

        function filterRows() {
            const keyword    = normalize(searchInput?.value);
            const categoryId = String(categoryFilter?.value || '');

            rows.forEach(row => {
                const matchesKeyword  = !keyword || normalize(row.dataset.searchText).includes(keyword);
                const matchesCategory = !categoryId || String(row.dataset.categoryId || '') === categoryId;
                row.hidden = !(matchesKeyword && matchesCategory);
            });

            if (checkAll) {
                checkAll.checked       = false;
                checkAll.indeterminate = false;
            }
        }

        function cellValue(row, key, type) {
            if (key === 'id') {
                return Number(row.children[1]?.dataset.sortValue || 0);
            }
            const cell  = row.querySelector(`[data-cell="${key}"]`);
            const value = cell?.dataset.sortValue ?? cell?.innerText ?? '';
            return type === 'number' ? Number(value || 0) : normalize(value);
        }

        function setSortIcon(button, dir) {
            table.querySelectorAll('.product-sort-btn').forEach(btn => {
                const icon = btn.querySelector('.product-sort-icon');
                btn.classList.remove('is-active');
                if (icon) icon.textContent = '↑↓';
            });
            const icon = button.querySelector('.product-sort-icon');
            button.classList.add('is-active');
            if (icon) icon.textContent = dir === 'asc' ? '↑' : '↓';
        }

        table.querySelectorAll('.product-sort-btn').forEach(button => {
            button.addEventListener('click', function () {
                const key  = this.dataset.sortKey;
                const type = this.dataset.sortType || 'text';
                const dir  = sortState.key === key && sortState.dir === 'asc' ? 'desc' : 'asc';
                sortState  = { key, dir };

                rows.sort((a, b) => {
                    const valueA = cellValue(a, key, type);
                    const valueB = cellValue(b, key, type);
                    if (valueA < valueB) return dir === 'asc' ? -1 : 1;
                    if (valueA > valueB) return dir === 'asc' ? 1 : -1;
                    return 0;
                }).forEach(row => tbody.appendChild(row));

                setSortIcon(this, dir);
                filterRows();
            });
        });

        searchInput?.addEventListener('input', filterRows);
        categoryFilter?.addEventListener('change', filterRows);

        /* ── Custom category dropdown ── */
        const catTrigger = document.getElementById('hkCatTrigger');
        const catPanel   = document.getElementById('hkCatPanel');
        const catSearch  = document.getElementById('hkCatSearch');
        const catLabel   = document.getElementById('hkCatLabel');
        const catList    = document.getElementById('hkCatList');

        function openCatPanel() {
            catPanel.hidden = false;
            catTrigger.classList.add('is-open');
            catTrigger.setAttribute('aria-expanded', 'true');
            catSearch.value = '';
            showAllCatItems();
            catSearch.focus();
        }

        function closeCatPanel() {
            catPanel.hidden = true;
            catTrigger.classList.remove('is-open');
            catTrigger.setAttribute('aria-expanded', 'false');
        }

        function showAllCatItems() {
            catList.querySelectorAll('.hk-cat-item').forEach(btn => btn.hidden = false);
            catList.querySelector('.hk-cat-empty')?.remove();
        }

        catTrigger?.addEventListener('click', () => catPanel.hidden ? openCatPanel() : closeCatPanel());

        catSearch?.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;
            catList.querySelectorAll('.hk-cat-item').forEach(btn => {
                const match = !q || btn.textContent.toLowerCase().includes(q);
                btn.hidden = !match;
                if (match) visible++;
            });
            const existing = catList.querySelector('.hk-cat-empty');
            if (visible === 0 && !existing) {
                const msg = document.createElement('div');
                msg.className   = 'hk-cat-empty';
                msg.textContent = 'Không tìm thấy danh mục';
                catList.appendChild(msg);
            } else if (visible > 0 && existing) {
                existing.remove();
            }
        });

        catList?.addEventListener('click', function (e) {
            const btn = e.target.closest('.hk-cat-item');
            if (!btn) return;
            catList.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            catLabel.textContent = btn.dataset.label;
            if (categoryFilter) {
                categoryFilter.value = btn.dataset.value;
                categoryFilter.dispatchEvent(new Event('change'));
            }
            closeCatPanel();
        });

        document.addEventListener('click', function (e) {
            if (!catPanel?.hidden && !document.getElementById('hkCatFilter')?.contains(e.target)) {
                closeCatPanel();
            }
        });

        /* ── Select-all checkbox ── */
        checkAll?.addEventListener('change', function () {
            rows.filter(row => !row.hidden).forEach(row => {
                const cb = row.querySelector('.product-row-check');
                if (cb) cb.checked = this.checked;
            });
        });
    });
</script>
