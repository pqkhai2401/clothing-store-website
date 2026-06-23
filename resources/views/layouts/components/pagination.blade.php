@php
    $prefix = $prefix ?? '';
    $currentPerPage = (int) $paginator->perPage();
    $perPageOptions = [10, 25, 50, 100];
    $firstItem = $paginator->firstItem() ?? 0;
    $lastItem = $paginator->lastItem() ?? 0;
    $total = $paginator->total();
@endphp

<div class="table-pagination-footer">
    <div class="table-pagination-control">
        <span class="table-pagination-label">Rows per page:</span>

        <div class="dropdown">
            <button class="btn table-pagination-select" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span id="{{ $prefix }}perPageDropdownText">{{ $currentPerPage }}</span>
                <i class="fa-solid fa-caret-down ms-2"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end table-pagination-menu">
                @foreach ($perPageOptions as $option)
                    <li>
                        <a class="dropdown-item {{ $currentPerPage === $option ? 'active' : '' }}" href="#"
                            onclick="selectPerPageCustom(event, {{ $option }}, '{{ $prefix }}')">
                            {{ $option }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <select id="{{ $prefix }}hiddenPerPageSelect" class="d-none custom-select-pagination"
            onchange="changeRowsPerPage(this.value, '{{ $prefix }}')">
            @foreach ($perPageOptions as $option)
                <option value="{{ $option }}" {{ $currentPerPage === $option ? 'selected' : '' }}>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>

    <span class="table-pagination-range">
        {{ $firstItem }}-{{ $lastItem }} of {{ $total }}
    </span>

    <nav aria-label="Pagination">
        <ul class="pagination table-pagination-nav mb-0">
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link" aria-disabled="true">
                        <i class="fa-solid fa-chevron-left"></i>
                    </span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}">
                        <i class="fa-solid fa-chevron-left"></i>
                    </a>
                @endif
            </li>

            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}">
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                @else
                    <span class="page-link" aria-disabled="true">
                        <i class="fa-solid fa-chevron-right"></i>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
</div>

<style>
    .table-pagination-footer {
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 28px;
        color: #374151;
        font-size: 13px;
    }

    .table-pagination-control {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .table-pagination-label,
    .table-pagination-range {
        white-space: nowrap;
    }

    .table-pagination-select {
        min-width: 56px;
        height: 32px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #111827;
        background: transparent;
        border: 0;
        box-shadow: none !important;
        font-size: 13px;
    }

    .table-pagination-select:hover,
    .table-pagination-select:focus {
        background: #f3f4f6;
    }

    .table-pagination-menu {
        min-width: 72px;
        padding: 4px;
        border-radius: 4px;
    }

    .table-pagination-menu .dropdown-item {
        border-radius: 3px;
        font-size: 13px;
        text-align: center;
    }

    .table-pagination-menu .dropdown-item.active {
        color: #ffffff;
        background: #174761;
    }

    .table-pagination-nav {
        display: inline-flex;
        gap: 10px;
    }

    .table-pagination-nav .page-link {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #6b7280;
        background: transparent;
        border: 0;
        box-shadow: none;
    }

    .table-pagination-nav .page-link:hover {
        color: #111827;
        background: #f3f4f6;
    }

    .table-pagination-nav .page-item.disabled .page-link {
        color: #c7cdd6;
        background: transparent;
    }

    @media (max-width: 575.98px) {
        .table-pagination-footer {
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }
    }
</style>

<script>
    function selectPerPageCustom(event, value, prefix = '') {
        event.preventDefault();

        const dropdownText = document.getElementById(prefix + 'perPageDropdownText');
        const hiddenSelect = document.getElementById(prefix + 'hiddenPerPageSelect');

        if (dropdownText) {
            dropdownText.innerText = value;
        }

        if (hiddenSelect) {
            hiddenSelect.value = value;
        }

        changeRowsPerPage(value, prefix);
    }

    function changeRowsPerPage(value, prefix = '') {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.delete('perPage');
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }
</script>
