@php
    $prefix = $prefix ?? '';
    $perPageKey = $prefix.'perPage';
    $currentPerPage = $paginator->perPage();
@endphp

@if ($paginator->total() > 0 && ($paginator->hasPages() || $currentPerPage > 10))
    <div class="d-flex align-items-center justify-content-end mt-2 pt-3">
        <div class="d-flex align-items-center border-bottom pb-2 ps-3">
            <div class="d-flex align-items-center me-4 text-muted custom-dropdown-wrapper">
                <label class="text-muted fw-medium me-2 mb-0" style="font-size: 0.85rem;">Hiển thị:</label>

                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center justify-content-between custom-btn-select"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span id="{{ $prefix }}perPageDropdownText" class="fw-normal text-dark">{{ $currentPerPage }}</span>
                        <i class="fas fa-chevron-down ms-2 text-muted" style="font-size: 0.7rem; margin-top: 2px;"></i>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu shadow-sm">
                        <li><a class="dropdown-item {{ $currentPerPage == 10 ? 'active' : '' }}" href="#"
                                onclick="selectPerPageCustom(event, 10, '{{ $prefix }}')">10</a></li>
                        <li><a class="dropdown-item {{ $currentPerPage == 20 ? 'active' : '' }}" href="#"
                                onclick="selectPerPageCustom(event, 20, '{{ $prefix }}')">20</a></li>
                        <li><a class="dropdown-item {{ $currentPerPage == 50 ? 'active' : '' }}" href="#"
                                onclick="selectPerPageCustom(event, 50, '{{ $prefix }}')">50</a></li>
                        <li><a class="dropdown-item {{ $currentPerPage == 100 ? 'active' : '' }}" href="#"
                                onclick="selectPerPageCustom(event, 100, '{{ $prefix }}')">100</a></li>
                    </ul>
                </div>

                <select id="{{ $prefix }}hiddenPerPageSelect" class="d-none custom-select-pagination" @if(! isset($isAjax) || ! $isAjax) onchange="changeRowsPerPage(this.value, '{{ $prefix }}')" @endif>
                    <option value="10" {{ $currentPerPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ $currentPerPage == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>

            <div class="d-flex align-items-center">
                <span class="text-muted me-3 fw-medium" style="font-size: 0.85rem;">
                    {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
                </span>

                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0 custom-pagination">
                        @if ($paginator->onFirstPage())
                            <li class="page-item disabled">
                                <span class="page-link" style="border-color: #e2e8f0;">&lt;</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" style="border-color: #e2e8f0; color:black " href="{{ request()->url() }}"
                                    data-url="{{ $paginator->previousPageUrl() }}">&lt;</a>
                            </li>
                        @endif

                        @if ($paginator->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" style="border-color: #e2e8f0; color:black " href="{{ request()->url() }}"
                                    data-url="{{ $paginator->nextPageUrl() }}">&gt;</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <span class="page-link" style="border-color: #e2e8f0;">&gt;</span>
                            </li>
                        @endif
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endif

<style>
    .ajax-spinner-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.6);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .ajax-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid transparent;
        border-top: 4px solid #0d6efd;
        border-right: 4px solid #0d6efd;
        border-bottom: 4px solid #0d6efd;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .custom-btn-select {
        border: 1px solid #e2e8f0;
        background-color: #ffffff;
        border-radius: 6px;
        padding: 4px 10px;
        min-width: 65px;
        height: 32px;
        box-shadow: none !important;
        transition: all 0.2s ease;
    }

    .custom-btn-select:hover,
    .custom-btn-select:focus {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }

    .custom-dropdown-menu {
        border: 1px solid #f1f5f9;
        border-radius: 8px;
        padding: 6px;
        min-width: 70px;
        margin-top: 4px !important;
    }

    .custom-dropdown-menu .dropdown-item {
        border-radius: 6px;
        text-align: center;
        padding: 6px 0;
        margin-bottom: 2px;
        color: #475569;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.15s ease;
    }

    .custom-dropdown-menu .dropdown-item:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }

    .custom-dropdown-menu .dropdown-item.active {
        background-color: #eff6ff !important;
        color: #111010 !important;
        font-weight: 600 !important;
    }

    .custom-pagination .page-item {
        margin: 0 3px;
    }

    .custom-pagination .page-link {
        border-radius: 8px !important;
        border: 1px solid transparent;
        color: #6c757d;
        font-weight: 500;
        min-width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 10px;
        transition: all 0.25s ease-in-out;
    }

    .custom-pagination .page-link:hover {
        background-color: #dddfe2;
        color: #0d6efd;
        border-color: #e9ecef;
        transform: translateY(-2px);
    }

    .custom-pagination .page-item.active .page-link {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.25);
        transform: translateY(-1px);
    }

    .custom-pagination .page-item.disabled .page-link {
        background-color: transparent;
        color: #dee2e6;
        cursor: not-allowed;
    }
</style>

<script>
    function selectPerPageCustom(event, value, prefix = '') {
        event.preventDefault();

        document.getElementById(prefix + 'perPageDropdownText').innerText = value;

        let items = event.target.closest('.custom-dropdown-menu').querySelectorAll('.dropdown-item');
        items.forEach(item => item.classList.remove('active'));
        event.target.classList.add('active');

        let hiddenSelect = document.getElementById(prefix + 'hiddenPerPageSelect');
        hiddenSelect.value = value;
        hiddenSelect.dispatchEvent(new Event('change', { bubbles: true }));
    }
</script>