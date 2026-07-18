@extends('layouts.admin')

@section('title', 'Quản lý đánh giá')

@push('styles')
    @include('admin.reviews.styles')
@endpush

@section('content')
    <div class="product-admin-page container-fluid py-4">
        <x-notification />

        <section class="px-3 px-md-4">
            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
                <div>
                    <h1 class="product-header-title mb-2">Quản lý đánh giá</h1>
                    <p class="product-header-desc mb-0">Danh sách tất cả đánh giá của khách hàng về sản phẩm.</p>
                </div>
                <div class="product-header-actions">
                    <a href="{{ route('admin.reviews.trash') }}" class="btn btn-light border product-action-btn">
                        <i class="fa-regular fa-trash-can me-1"></i> Thùng rác
                    </a>
                </div>
            </div>

            @php
                $ratingVal = $ratingFilter ? (string) $ratingFilter : '';
                $statusVal = $statusFilter ?? '';
                $statusLabels = [
                    'pending'  => 'Chờ duyệt',
                    'approved' => 'Đã duyệt',
                    'flagged'  => 'Chờ Admin (gắn cờ)',
                    'rejected' => 'Bị từ chối',
                ];
                $selectedRatingLabel = $ratingVal ? ($ratingVal . ' sao') : 'Tất cả sao';
                $selectedStatusLabel = $statusVal ? ($statusLabels[$statusVal] ?? 'Tất cả trạng thái') : 'Tất cả trạng thái';
            @endphp

            <form method="GET" action="{{ route('admin.reviews.list') }}" id="reviewFilterForm" class="product-toolbar">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input type="hidden" name="rating" data-admin-filter id="reviewRatingHidden" value="{{ $ratingVal }}">
                <input type="hidden" name="status" data-admin-filter id="reviewStatusHidden" value="{{ $statusVal }}">

                <div class="product-toolbar-left">
                    <input type="search" name="search" data-admin-search id="reviewSearch"
                        class="form-control product-search"
                        value="{{ $keyword }}"
                        placeholder="Tên SP, khách hàng, nội dung..." autocomplete="off">

                    {{-- Filter số sao --}}
                    <div class="hk-cat-filter" id="hkReviewRatingDrop">
                        <button type="button" class="hk-cat-trigger" id="hkReviewRatingTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkReviewRatingLabel">{{ $selectedRatingLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkReviewRatingPanel" hidden>
                            <div class="hk-cat-list" id="hkReviewRatingList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $ratingVal === '' ? 'is-active' : '' }}"
                                    data-value="" data-label="Tất cả sao">Tất cả sao</button>
                                @for($i = 5; $i >= 1; $i--)
                                    <button type="button" class="hk-cat-item {{ $ratingVal === (string) $i ? 'is-active' : '' }}"
                                        data-value="{{ $i }}" data-label="{{ $i }} sao">{{ $i }} sao</button>
                                @endfor
                            </div>
                        </div>
                    </div>

                    {{-- Filter trạng thái kiểm duyệt --}}
                    <div class="hk-cat-filter" id="hkReviewStatusDrop">
                        <button type="button" class="hk-cat-trigger" id="hkReviewStatusTrigger"
                            aria-haspopup="listbox" aria-expanded="false">
                            <span class="hk-cat-trigger-label" id="hkReviewStatusLabel">{{ $selectedStatusLabel }}</span>
                            <i class="fa-solid fa-chevron-down hk-cat-arrow"></i>
                        </button>
                        <div class="hk-cat-panel" id="hkReviewStatusPanel" hidden>
                            <div class="hk-cat-list" id="hkReviewStatusList" role="listbox">
                                <button type="button" class="hk-cat-item {{ $statusVal === '' ? 'is-active' : '' }}"
                                    data-value="" data-label="Tất cả trạng thái">Tất cả trạng thái</button>
                                @foreach($statusLabels as $val => $label)
                                    <button type="button" class="hk-cat-item {{ $statusVal === $val ? 'is-active' : '' }}"
                                        data-value="{{ $val }}" data-label="{{ $label }}">{{ $label }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div data-admin-table-area>
                @include('admin.reviews.partials.table')
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    @include('layouts.components.confirm.delete')

    <script>
    (function () {
        /* ── Filter đơn giản dạng "trigger + panel + hidden input" (số sao / trạng thái) ── */
        function initSimpleFilterDropdown(dropId, triggerId, panelId, labelId, listId, hiddenId) {
            const drop    = document.getElementById(dropId);
            const trigger = document.getElementById(triggerId);
            const panel   = document.getElementById(panelId);
            const label   = document.getElementById(labelId);
            const list    = document.getElementById(listId);
            const hidden  = document.getElementById(hiddenId);
            if (!trigger) return;

            function open()  { panel.hidden = false; trigger.classList.add('is-open'); trigger.setAttribute('aria-expanded', 'true'); }
            function close() { panel.hidden = true;  trigger.classList.remove('is-open'); trigger.setAttribute('aria-expanded', 'false'); }

            trigger.addEventListener('click', () => panel.hidden ? open() : close());

            list.addEventListener('click', function (e) {
                const btn = e.target.closest('.hk-cat-item');
                if (!btn) return;
                list.querySelectorAll('.hk-cat-item').forEach(b => b.classList.remove('is-active'));
                btn.classList.add('is-active');
                label.textContent = btn.dataset.label;
                hidden.value = btn.dataset.value;
                close();
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
            });

            document.addEventListener('click', function (e) {
                if (!panel.hidden && !drop?.contains(e.target)) close();
            });
        }

        initSimpleFilterDropdown('hkReviewRatingDrop', 'hkReviewRatingTrigger', 'hkReviewRatingPanel', 'hkReviewRatingLabel', 'hkReviewRatingList', 'reviewRatingHidden');
        initSimpleFilterDropdown('hkReviewStatusDrop', 'hkReviewStatusTrigger', 'hkReviewStatusPanel', 'hkReviewStatusLabel', 'hkReviewStatusList', 'reviewStatusHidden');
    }());
    </script>

    @include('admin.partials.realtime-table')
@endpush
