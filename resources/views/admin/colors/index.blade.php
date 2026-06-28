@extends('layouts.admin')

@section('title', 'Quản lý màu sắc')

@push('styles')
    @include('admin.colors.styles')
@endpush

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý màu sắc</h1>
                <p class="product-header-desc mb-0">Danh sách màu sắc dùng cho biến thể sản phẩm.</p>
            </div>

            <form method="GET" action="{{ route('admin.colors.list') }}" id="colorSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="keyword" id="colorRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên màu sắc..." autocomplete="off">
                </div>
                <div class="product-tool-actions">
                    <a href="{{ route('admin.colors.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm màu sắc
                    </a>
                </div>
            </form>

            <div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="colorTable">
                        <thead>
                            <tr>
                                <th style="width:54px;">
                                    <input type="checkbox" class="form-check-input product-check" id="colorCheckAll">
                                </th>
                                <th style="width:76px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên màu sắc <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:200px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="variants_count" data-sort-type="number">
                                        Số biến thể SP <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:140px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:120px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($colors as $color)
                                <tr data-color-row="{{ $color->id }}"
                                    data-search-text="{{ Str::lower($color->name) }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check color-row-check" value="{{ $color->id }}">
                                    </td>
                                    <td data-sort-value="{{ $color->id }}" style="opacity:.55;">{{ $color->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $color->name }}">
                                        <div class="fw-bold text-dark">{{ $color->name }}</div>
                                    </td>
                                    <td data-cell="variants_count" data-sort-value="{{ $color->product_variants_count }}">
                                        <span class="fw-semibold">{{ number_format($color->product_variants_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $color->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $color->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td>
                                        <span class="status-badge status-badge--active">Hoạt động</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="dropdown">
                                            <button type="button" class="product-more-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-ellipsis"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end product-row-menu">
                                                <a href="{{ route('admin.colors.edit', $color->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.colors.destroy', $color->id) }}"
                                                    data-delete-name="{{ $color->name }}"
                                                    data-delete-type="màu sắc">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có màu sắc nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $colors,
                    'itemLabel'     => 'màu sắc',
                    'bulkDeleteUrl' => route('admin.colors.bulkDelete'),
                ])
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    <script>
    (function () {
        const table    = document.getElementById('colorTable');
        const searchEl = document.getElementById('colorRealtimeSearch');
        const checkAll = document.getElementById('colorCheckAll');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-color-row]'));
        let sortState = { key: 'id', dir: 'asc' };

        function normalize(v) { return String(v ?? '').toLowerCase().trim(); }

        function filterRows() {
            const kw = normalize(searchEl?.value);
            rows.forEach(r => { r.hidden = !!(kw && !normalize(r.dataset.searchText).includes(kw)); });
            if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
        }

        function cellValue(row, key, type) {
            if (key === 'id') return Number(row.children[1]?.dataset.sortValue || 0);
            const cell = row.querySelector(`[data-cell="${key}"]`);
            const val  = cell?.dataset.sortValue ?? cell?.innerText ?? '';
            return type === 'number' ? Number(val || 0) : normalize(val);
        }

        table.querySelectorAll('.product-sort-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const key  = this.dataset.sortKey;
                const type = this.dataset.sortType || 'text';
                const dir  = sortState.key === key && sortState.dir === 'asc' ? 'desc' : 'asc';
                sortState  = { key, dir };

                rows.sort((a, b) => {
                    const va = cellValue(a, key, type), vb = cellValue(b, key, type);
                    return va < vb ? (dir === 'asc' ? -1 : 1) : va > vb ? (dir === 'asc' ? 1 : -1) : 0;
                }).forEach(r => tbody.appendChild(r));

                table.querySelectorAll('.product-sort-btn').forEach(b => {
                    b.classList.remove('is-active');
                    const ic = b.querySelector('.product-sort-icon');
                    if (ic) ic.textContent = '↑↓';
                });
                this.classList.add('is-active');
                const ic = this.querySelector('.product-sort-icon');
                if (ic) ic.textContent = dir === 'asc' ? '↑' : '↓';
                filterRows();
            });
        });

        searchEl?.addEventListener('input', filterRows);
        searchEl?.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('colorSearchForm')?.submit(); }
        });

        checkAll?.addEventListener('change', function () {
            rows.filter(r => !r.hidden).forEach(r => {
                const cb = r.querySelector('.color-row-check');
                if (cb) cb.checked = this.checked;
            });
        });
    }());
    </script>
@endpush
