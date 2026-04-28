<x-notification />

@php
    $activeSortBy = request('sort_by');
    $activeSortDir = request('sort_dir');
@endphp

<div class="js-ajax-table-container ajax-table-container">
    <div class="card shadow-sm border table-page-card">
        <div
            class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap border-bottom">
            <div class="d-flex align-items-center">
                <div class="bg-primary-subtle rounded-2 p-1 me-2 d-flex align-items-center justify-content-center">
                    <i class="fa-solid fa-image text-primary table-header-icon"></i>
                </div>
                <h6 class="fw-bold text-dark mb-0 fs-6 text-uppercase table-heading">
                    Danh sách hình ảnh
                    ({{ $data->total() }})
                </h6>
            </div>

            <div class="d-flex gap-2 align-items-center ms-auto flex-wrap">
                <div class="d-none align-items-center table-active-filter-badge animate__animated animate__fadeIn">
                </div>

                <form method="GET" action="{{ route('admin.images.list') }}"
                    class="d-flex align-items-center gap-2 m-0 js-ajax-search-form" role="search">
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    <input type="hidden" name="id" value="{{ request('id') }}">
                    <input type="hidden" name="patient_code" value="{{ request('patient_code') }}">
                    <input type="hidden" name="file_name" value="{{ request('file_name') }}">
                    <input type="hidden" name="created_at" value="{{ request('created_at') }}">
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                </form>

                <button class="btn  btn-outline-info shadow-sm fw-bold d-flex align-items-center" type="button"
                    data-bs-toggle="modal" data-bs-target="#filterModal">
                    <i class="fa-solid fa-filter me-1"></i> Bộ lọc
                </button>

                @include('images.filter')
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive js-ajax-table-wrapper">
                <table class="table table-hover align-middle mb-0 data-table js-ajax-table">
                    <thead class="table-light">
                        <tr class="text-secondary text-uppercase table-head-row">
                            <th class="text-center py-3 ps-4 fw-bold border-bottom-0 sortable-col" width="10%"
                                data-sort="id">
                                <span class="table-sort-content">
                                    <span>ID</span>
                                    <span class="table-sort-icons">
                                        <i
                                            class="fa-solid fa-caret-up {{ $activeSortBy === 'id' && $activeSortDir === 'asc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                        <i
                                            class="fa-solid fa-caret-down {{ $activeSortBy === 'id' && $activeSortDir === 'desc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                    </span>
                                </span>
                            </th>
                            <th class="py-3 fw-bold border-bottom-0 sortable-col" width="15%" data-sort="patient_code">
                                <span class="table-sort-content">
                                    <span>Mã bệnh nhân</span>
                                    <span class="table-sort-icons">
                                        <i
                                            class="fa-solid fa-caret-up {{ $activeSortBy === 'patient_code' && $activeSortDir === 'asc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                        <i
                                            class="fa-solid fa-caret-down {{ $activeSortBy === 'patient_code' && $activeSortDir === 'desc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                    </span>
                                </span>
                            </th>
                            <th class="py-3 fw-bold border-bottom-0 sortable-col" width="22%" data-sort="file_name">
                                <span class="table-sort-content">
                                    <span>File</span>
                                    <span class="table-sort-icons">
                                        <i
                                            class="fa-solid fa-caret-up {{ $activeSortBy === 'file_name' && $activeSortDir === 'asc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                        <i
                                            class="fa-solid fa-caret-down {{ $activeSortBy === 'file_name' && $activeSortDir === 'desc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                    </span>
                                </span>
                            </th>
                            <th class="py-3 fw-bold border-bottom-0 sortable-col" width="16%" data-sort="created_at">
                                <span class="table-sort-content">
                                    <span>Ngày upload</span>
                                    <span class="table-sort-icons">
                                        <i
                                            class="fa-solid fa-caret-up {{ $activeSortBy === 'created_at' && $activeSortDir === 'asc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                        <i
                                            class="fa-solid fa-caret-down {{ $activeSortBy === 'created_at' && $activeSortDir === 'desc' ? 'sort-state-active' : 'sort-state-inactive' }}"></i>
                                    </span>
                                </span>
                            </th>
                            <th class="py-3 fw-bold border-bottom-0" width="15%">
                                <span>Thêm bởi</span>
                            </th>
                            <th class="text-center py-3 fw-bold border-bottom-0" width="15%">
                                <span>Thao tác</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $image)
                            {{--
                            <pre>{{ json_encode($image, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                            --}}
                            <tr class="transition-base cursor-pointer">
                                <td class="py-3 text-muted fw-bold text-center">{{ $image->id }}</td>
                                <td class="py-3 text-muted fw-bold text-start">
                                    {{ $image->patient_code }}
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center table-cell-flex">
                                        <div class="p-1 me-2 d-flex align-items-center justify-content-center">
                                            <img src="{{ $image->image_url ?? '../../assets/img/avatar/default-avatar-2.png' }}"
                                                alt="{{ $image->file_name }}" class="table-avatar">
                                        </div>
                                        <span class="fw-bold text-dark table-cell-ellipsis"
                                            title="{{ $image->file_name }}">{{ $image->file_name }}</span>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $image->created_at ?? '' }}</td>
                                <td class="text-muted fw-bold">{{ $image->createdBy?->name ?? '' }}</td>
                                <!-- Thao tác -->
                                <td class="text-center">
                                    <div class="btn-group dropdown" data-bs-auto-close="outside">
                                        <button type="button"
                                            class="border-0 p-2 action-dropdown-toggle table-action-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-vertical text-secondary"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-3">
                                            <!-- view -->
                                            <li>
                                                <a class="dropdown-item text-warning py-2" href="#">
                                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                                        <span class="fw-medium">Xem chi tiết</span>
                                                        <i class="fas fa-edit text-center action-icon-fixed"></i>
                                                    </div>
                                                </a>
                                            </li>

                                            <!-- delete -->
                                            <li>
                                                <button type="button" class="dropdown-item text-danger py-2 m-0"
                                                    data-delete-url="{{ route('admin.images.destroy', $image->id) }}" data-delete-name="{{ $image->file_name }}"
                                                    data-delete-type="hình ảnh">
                                                    <div class="d-flex align-items-center justify-content-end gap-2">
                                                        <span class="fw-medium">Xoá</span>
                                                        <i class="fas fa-trash text-center action-icon-fixed"></i>
                                                    </div>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 border-bottom-0">
                                    <div
                                        class="d-flex flex-column align-items-center justify-content-center table-empty-state">
                                        <i class="fa-solid fa-inbox text-muted mb-3 table-empty-icon"></i>
                                        <h5 class="text-muted mb-2">Không tìm thấy kết quả phù hợp</h5>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @include('layouts.components.pagination', ['paginator' => $data])
            </div>
        </div>
    </div>
</div>

@push('scripts')
    @include('layouts.components.confirm.delete')
    @include('layouts.components.confirm.update')
    <script>
        (function () {
            if (window.__ajaxTableSearchBound) return;
            window.__ajaxTableSearchBound = true;

            let currentRequestController = null;
            let latestRequestId = 0;

            function getTableContainer(scopeEl) {
                if (scopeEl?.closest) {
                    const scopedContainer = scopeEl.closest('.js-ajax-table-container');
                    if (scopedContainer) return scopedContainer;
                }

                return document.querySelector('.js-ajax-table-container');
            }

            function replaceWithCleanUrl(sourceUrl) {
                const parsed = sourceUrl ? new URL(sourceUrl, window.location.origin) : new URL(window.location.href);
                window.history.replaceState({}, '', parsed.pathname + parsed.hash);
            }

            function buildSearchUrl(form, resetPage) {
                const formActionUrl = new URL(form.action, window.location.origin);
                const nextUrl = new URL(window.location.href);
                nextUrl.pathname = formActionUrl.pathname;
                nextUrl.search = '';

                const formData = new FormData(form);

                formData.forEach(function (rawValue, key) {
                    const value = typeof rawValue === 'string' ? rawValue.trim() : '';
                    if (value !== '') {
                        nextUrl.searchParams.set(key, value);
                    } else {
                        nextUrl.searchParams.delete(key);
                    }
                });

                if (resetPage) {
                    nextUrl.searchParams.set('page', '1');
                }

                return nextUrl;
            }

            async function loadAjaxList(url, container) {
                if (!container) return;

                if (currentRequestController) {
                    currentRequestController.abort();
                }

                currentRequestController = new AbortController();
                const requestId = ++latestRequestId;

                const tableWrapper = container.querySelector('.js-ajax-table-wrapper');
                if (tableWrapper) tableWrapper.style.opacity = '0.6';

                try {
                    const response = await fetch(url.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        signal: currentRequestController.signal,
                    });

                    if (!response.ok) {
                        throw new Error('Không thể tải dữ liệu.');
                    }

                    const html = await response.text();
                    if (requestId !== latestRequestId) return;

                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nextContainer = doc.querySelector('.js-ajax-table-container');
                    if (!nextContainer) return;

                    container.innerHTML = nextContainer.innerHTML;
                    replaceWithCleanUrl(url.toString());
                    document.dispatchEvent(new CustomEvent('ajaxTableLoaded'));
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        console.error(error);
                    }
                } finally {
                    const currentWrapper = container.querySelector('.js-ajax-table-wrapper');
                    if (currentWrapper) currentWrapper.style.opacity = '';
                }
            }

            document.addEventListener('submit', function (event) {
                const form = event.target.closest('.js-ajax-search-form');
                if (!form) return;

                event.preventDefault();
                const container = getTableContainer(form);
                const url = buildSearchUrl(form, true);
                loadAjaxList(url, container);
            });

            window.ajaxTableSearchNow = function (resetPage, sourceEl) {
                const container = getTableContainer(sourceEl);
                const form = container?.querySelector('.js-ajax-search-form');
                if (!form) return;

                const url = buildSearchUrl(form, resetPage !== false);
                loadAjaxList(url, container);
            };

            function handleStatusSubmenuToggle(event) {
                if (event.target.closest('.status-submenu')) {
                    return false;
                }

                const toggleStatusTarget = event.target.closest('.js-status-toggle-item, .js-toggle-status-menu');
                if (!toggleStatusTarget) return false;

                event.preventDefault();
                event.stopPropagation();

                const currentMenu = toggleStatusTarget.closest('.js-status-toggle-item');
                if (!currentMenu) return true;

                const submenu = currentMenu.querySelector('.status-submenu');
                if (!submenu) return true;

                document.querySelectorAll('.status-submenu').forEach(function (menu) {
                    if (menu !== submenu) {
                        menu.classList.add('d-none');
                    }
                });

                const isHidden = submenu.classList.contains('d-none');
                submenu.classList.toggle('d-none', !isHidden);

                const dropdownElement = currentMenu.closest('.dropdown');
                const dropdownToggle = dropdownElement?.querySelector('[data-bs-toggle="dropdown"]');
                if (dropdownToggle && window.bootstrap?.Dropdown) {
                    window.bootstrap.Dropdown.getOrCreateInstance(dropdownToggle).show();
                }

                return true;
            }

            function openStatusUpdateConfirm(triggerEl) {
                const form = triggerEl?.closest('form');
                if (!form) return;

                const itemName = triggerEl.getAttribute('data-update-name') || '';
                const itemType = triggerEl.getAttribute('data-update-type') || 'mục này';
                const statusLabel = triggerEl.getAttribute('data-update-status-label') || '';

                let message = 'Bạn có chắc chắn muốn cập nhật? Hành động này sẽ ghi đè dữ liệu hiện tại.';
                if (itemName && statusLabel) {
                    message = `Bạn có chắc chắn muốn cập nhật \"${itemName}\" sang trạng thái \"${statusLabel}\"?`;
                } else if (statusLabel) {
                    message = `Bạn có chắc chắn muốn cập nhật ${itemType} sang trạng thái \"${statusLabel}\"?`;
                } else if (itemName) {
                    message = `Bạn có chắc chắn muốn cập nhật \"${itemName}\"? Hành động này sẽ ghi đè dữ liệu hiện tại.`;
                } else {
                    message = `Bạn có chắc chắn muốn cập nhật ${itemType}? Hành động này sẽ ghi đè dữ liệu hiện tại.`;
                }

                if (typeof window.openUpdateConfirmModal === 'function') {
                    window.openUpdateConfirmModal({
                        title: `Cập nhật ${itemType}?`,
                        message: message,
                        onConfirm: function () {
                            form.submit();
                        }
                    });
                } else {
                    form.submit();
                }
            }

            document.addEventListener('click', function (event) {
                if (handleStatusSubmenuToggle(event)) return;

                const updateTrigger = event.target.closest('.js-update-status');
                if (updateTrigger) {
                    event.preventDefault();
                    event.stopPropagation();
                    openStatusUpdateConfirm(updateTrigger);
                    return;
                }

                const sortableTh = event.target.closest('.js-ajax-table thead th.sortable-col');
                if (sortableTh) {
                    event.preventDefault();

                    const container = getTableContainer(sortableTh);
                    const form = container?.querySelector('.js-ajax-search-form');
                    if (!form) return;

                    const sortColumn = sortableTh.getAttribute('data-sort');
                    const sortByInput = form.querySelector('input[name="sort_by"]');
                    const sortDirInput = form.querySelector('input[name="sort_dir"]');
                    if (!sortByInput || !sortDirInput || !sortColumn) return;

                    const currentBy = sortByInput.value;
                    const currentDir = sortDirInput.value;

                    if (currentBy !== sortColumn) {
                        sortByInput.value = sortColumn;
                        sortDirInput.value = 'asc';
                    } else if (currentDir === 'asc') {
                        sortByInput.value = sortColumn;
                        sortDirInput.value = 'desc';
                    } else if (currentDir === 'desc') {
                        sortByInput.value = '';
                        sortDirInput.value = '';
                    } else {
                        sortByInput.value = sortColumn;
                        sortDirInput.value = 'asc';
                    }

                    window.ajaxTableSearchNow(true, sortableTh);
                    return;
                }

                const link = event.target.closest('.js-ajax-table-container .pagination a');
                if (!link) return;

                event.preventDefault();
                const rawUrl = link.getAttribute('data-url') || link.getAttribute('href');
                if (!rawUrl || rawUrl === 'javascript:void(0);' || rawUrl === '#') return;

                const container = getTableContainer(link);
                const url = new URL(rawUrl, window.location.origin);
                loadAjaxList(url, container);
            });

            window.changeRowsPerPage = function (value) {
                if (!value) return;

                const container = getTableContainer();
                const form = container?.querySelector('.js-ajax-search-form');
                if (!form) return;

                let perPageInput = form.querySelector('input[name="per_page"]');
                if (!perPageInput) {
                    perPageInput = document.createElement('input');
                    perPageInput.type = 'hidden';
                    perPageInput.name = 'per_page';
                    form.appendChild(perPageInput);
                }
                perPageInput.value = value;

                const url = buildSearchUrl(form, true);
                loadAjaxList(url, container);
            };

            // Handle hover for status submenu
            document.addEventListener('mouseenter', function (event) {
                const toggleItem = event.target.closest('.js-status-toggle-item');
                if (!toggleItem) return;

                const submenu = toggleItem.querySelector('.status-submenu');
                if (!submenu) return;

                // Hide other submenus
                document.querySelectorAll('.status-submenu').forEach(function (menu) {
                    if (menu !== submenu) {
                        menu.classList.add('d-none');
                    }
                });

                submenu.classList.remove('d-none');
            }, true);

            // Handle mouseleave to hide submenu
            document.addEventListener('mouseleave', function (event) {
                const toggleItem = event.target.closest('.js-status-toggle-item');
                if (!toggleItem) return;

                const submenu = toggleItem.querySelector('.status-submenu');
                if (submenu) {
                    submenu.classList.add('d-none');
                }
            }, true);

            replaceWithCleanUrl();
        })();
    </script>
@endpush
