@extends('layouts.admin')

@section('title', 'Quản lý danh mục')

@section('css')
    @include('admin.products.styles')
    <style>
        .parent-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #DBEAFE;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }
        .child-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            background: #F0FDF4;
            color: #166534;
            border: 1px solid #86EFAC;
        }
        .slug-code {
            font-size: 12px;
            color: #6b7280;
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        [data-theme="dark"] .parent-tag {
            background: rgba(59,130,246,0.15) !important;
            color: #93C5FD !important;
            border-color: rgba(59,130,246,0.3) !important;
        }
        [data-theme="dark"] .child-tag {
            background: rgba(34,197,94,0.12) !important;
            color: #86EFAC !important;
            border-color: rgba(34,197,94,0.3) !important;
        }
        [data-theme="dark"] .slug-code {
            color: #94A3B8 !important;
            background: #162843 !important;
        }
    </style>
@endsection

@section('content')
    <main class="app-main product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div>
                <h1 class="product-header-title mb-2">Quản lý danh mục</h1>
                <p class="product-header-desc mb-0">Danh sách tất cả danh mục sản phẩm trong hệ thống.</p>
            </div>

            <form method="GET" action="{{ route('admin.categories.list') }}" id="catSearchForm"
                  class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="product-toolbar-left">
                    <input type="search" name="keyword" id="catRealtimeSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tìm kiếm theo tên danh mục hoặc slug..." autocomplete="off">
                </div>
                <div class="product-tool-actions">
                    <a href="{{ route('admin.categories.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                    <a href="#" class="btn btn-dark product-action-btn">
                        <i class="fa-solid fa-plus me-1"></i> Thêm danh mục
                    </a>
                </div>
            </form>

            <div class="product-table-wrap">
                <div class="table-responsive">
                    <table class="table table-hover product-table align-middle" id="catTable">
                        <thead>
                            <tr>
                                <th style="width:54px;">
                                    <input type="checkbox" class="form-check-input product-check" id="catCheckAll">
                                </th>
                                <th style="width:76px;">
                                    <button type="button" class="product-sort-btn is-active" data-sort-key="id" data-sort-type="number">
                                        ID <span class="product-sort-icon">↑</span>
                                    </button>
                                </th>
                                <th>
                                    <button type="button" class="product-sort-btn" data-sort-key="name">
                                        Tên danh mục <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:190px;">Danh mục cha</th>
                                <th style="width:200px;">Slug</th>
                                <th style="width:130px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="products_count" data-sort-type="number">
                                        Số sản phẩm <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:130px;">
                                    <button type="button" class="product-sort-btn" data-sort-key="created_at">
                                        Ngày tạo <span class="product-sort-icon">↑↓</span>
                                    </button>
                                </th>
                                <th style="width:120px;">Trạng thái</th>
                                <th class="text-end pe-4" style="width:90px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr data-cat-row="{{ $category->id }}"
                                    data-search-text="{{ Str::lower($category->name . ' ' . $category->slug . ' ' . ($category->parentCategory?->name ?? '')) }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input product-check cat-row-check" value="{{ $category->id }}">
                                    </td>
                                    <td data-sort-value="{{ $category->id }}" style="opacity:.55;">{{ $category->id }}</td>
                                    <td data-cell="name" data-sort-value="{{ $category->name }}">
                                        <div class="fw-bold text-dark">{{ $category->name }}</div>
                                        @if(is_null($category->parent_id))
                                            <span class="parent-tag mt-1">Cha</span>
                                        @else
                                            <span class="child-tag mt-1">Con</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold">{{ $category->parentCategory?->name ?? 'Danh mục gốc' }}</span>
                                    </td>
                                    <td>
                                        <code class="slug-code">{{ $category->slug }}</code>
                                    </td>
                                    <td data-cell="products_count" data-sort-value="{{ $category->products_count }}">
                                        <span class="fw-semibold">{{ number_format($category->products_count) }}</span>
                                    </td>
                                    <td data-cell="created_at" data-sort-value="{{ $category->created_at?->format('Ymd') ?? '0' }}">
                                        {{ $category->created_at?->format('d/m/Y') ?? '—' }}
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
                                                <a href="{{ route('admin.categories.edit', $category->id) }}" class="dropdown-item">
                                                    <i class="fa-regular fa-pen-to-square"></i> Sửa
                                                </a>
                                                <button type="button" class="dropdown-item text-danger"
                                                    data-delete-url="{{ route('admin.categories.destroy', $category->id) }}"
                                                    data-delete-name="{{ $category->name }}"
                                                    data-delete-type="danh mục">
                                                    <i class="fa-regular fa-trash-can"></i> Xóa
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                                        <div class="fw-semibold text-muted">Chưa có danh mục nào</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white border border-top-0 rounded-bottom px-3 py-2">
                @include('layouts.components.pagination', [
                    'paginator'     => $categories,
                    'itemLabel'     => 'danh mục',
                    'bulkDeleteUrl' => route('admin.categories.bulkDelete'),
                ])
            </div>
        </section>
    </main>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')
    <script>
    (function () {
        const table    = document.getElementById('catTable');
        const searchEl = document.getElementById('catRealtimeSearch');
        const checkAll = document.getElementById('catCheckAll');
        if (!table) return;

        const tbody = table.querySelector('tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-cat-row]'));
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
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('catSearchForm')?.submit(); }
        });

        checkAll?.addEventListener('change', function () {
            rows.filter(r => !r.hidden).forEach(r => {
                const cb = r.querySelector('.cat-row-check');
                if (cb) cb.checked = this.checked;
            });
        });
    }());
    </script>
@endpush
