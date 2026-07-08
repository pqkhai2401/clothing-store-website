@php
    $currentSort = $sort ?? 'created_at';
    $currentDir  = $direction ?? 'desc';

    $sortIcon = function (string $key) use ($currentSort, $currentDir): string {
        if ($currentSort !== $key) return '↑↓';
        return $currentDir === 'asc' ? '↑' : '↓';
    };

    $isActive = fn (string $key) => $currentSort === $key ? 'is-active' : '';

    $typeBadgeCss = [
        'percentage' => 'voucher-type-badge--percentage',
        'fixed'      => 'voucher-type-badge--fixed',
    ];

    $now = now();
@endphp

<div class="product-table-wrap voucher-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover product-table align-middle" id="voucherTable">
            <thead>
                <tr>
                    <th style="width: 44px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th style="width:150px;">Mã Voucher</th>
                    <th style="width:110px;">Kiểu giảm</th>
                    <th style="width:130px;">
                        <button type="button" class="product-sort-btn {{ $isActive('value') }}" data-sort-key="value" data-sort-type="number">
                            Giá trị giảm <span class="product-sort-icon">{{ $sortIcon('value') }}</span>
                        </button>
                    </th>
                    <th style="width:130px;">Đơn tối thiểu</th>
                    <th style="width:140px;">
                        <button type="button" class="product-sort-btn {{ $isActive('used_count') }}" data-sort-key="used_count" data-sort-type="number">
                            Lượt dùng <span class="product-sort-icon">{{ $sortIcon('used_count') }}</span>
                        </button>
                    </th>
                    <th style="width:220px;">
                        <button type="button" class="product-sort-btn {{ $isActive('end_date') }}" data-sort-key="end_date">
                            Hạn dùng <span class="product-sort-icon">{{ $sortIcon('end_date') }}</span>
                        </button>
                    </th>
                    <th style="width:150px;">Trạng thái</th>
                    <th class="text-center" style="width:90px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                    @php
                        $isExpired = $voucher->end_date < $now;
                        $isDepleted = $voucher->used_count >= $voucher->quantity;
                        if (! $voucher->status) {
                            $expiryCss = 'voucher-expiry-badge--paused';
                            $expiryLabel = 'Tạm hoãn';
                        } elseif ($isExpired) {
                            $expiryCss = 'voucher-expiry-badge--expired';
                            $expiryLabel = 'Đã hết hạn';
                        } elseif ($isDepleted) {
                            $expiryCss = 'voucher-expiry-badge--expired';
                            $expiryLabel = 'Hết lượt dùng';
                        } elseif ($voucher->end_date <= $now->copy()->addDays(3)) {
                            $expiryCss = 'voucher-expiry-badge--soon';
                            $expiryLabel = 'Sắp hết hạn';
                        } else {
                            $expiryCss = 'voucher-expiry-badge--active';
                            $expiryLabel = 'Còn hạn';
                        }
                    @endphp
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $voucher->id }}">
                        </td>
                        <td data-sort-value="{{ $voucher->code }}">
                            <span class="voucher-code">{{ $voucher->code }}</span>
                        </td>
                        <td>
                            <span class="voucher-type-badge {{ $typeBadgeCss[$voucher->type] ?? '' }}">
                                {{ $typeLabels[$voucher->type] ?? $voucher->type }}
                            </span>
                        </td>
                        <td data-sort-value="{{ $voucher->value }}">
                            <span class="fw-bold" style="color:#0F172A;">
                                @if($voucher->type === 'percentage')
                                    {{ rtrim(rtrim(number_format($voucher->value, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    {{ number_format($voucher->value, 0, ',', '.') }}₫
                                @endif
                            </span>
                            @if($voucher->type === 'percentage' && $voucher->max_discount_amount !== null)
                                <div class="text-muted" style="font-size:11px;">Tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}₫</div>
                            @endif
                        </td>
                        <td style="color:#64748B;font-size:13px;">
                            @if($voucher->min_order_amount > 0)
                                {{ number_format($voucher->min_order_amount, 0, ',', '.') }}₫
                            @else
                                <span class="text-muted">Không yêu cầu</span>
                            @endif
                        </td>
                        <td data-sort-value="{{ $voucher->used_count }}">
                            <span class="fw-bold" style="color:#0F172A;">{{ $voucher->used_count }}</span>
                            <span class="text-muted">/ {{ $voucher->quantity }}</span>
                        </td>
                        <td data-sort-value="{{ $voucher->end_date?->timestamp }}">
                            <div style="color:#64748B;font-size:12px;">
                                {{ $voucher->start_date?->format('d/m/Y') ?? '—' }} - {{ $voucher->end_date?->format('d/m/Y') ?? '—' }}
                            </div>
                            <span class="voucher-expiry-badge {{ $expiryCss }}"
                                data-voucher-expiry-badge
                                data-active-css="{{ $voucher->end_date < $now || $voucher->used_count >= $voucher->quantity ? 'voucher-expiry-badge--expired' : ($voucher->end_date <= $now->copy()->addDays(3) ? 'voucher-expiry-badge--soon' : 'voucher-expiry-badge--active') }}"
                                data-active-label="{{ $voucher->end_date < $now ? 'Đã hết hạn' : ($voucher->used_count >= $voucher->quantity ? 'Hết lượt dùng' : ($voucher->end_date <= $now->copy()->addDays(3) ? 'Sắp hết hạn' : 'Còn hạn')) }}">{{ $expiryLabel }}</span>
                        </td>
                        <td data-sort-value="{{ $voucher->status ? 1 : 0 }}" data-voucher-status-cell="{{ $voucher->id }}">
                            <div class="hk-cat-filter voucher-status-dropdown" data-voucher-id="{{ $voucher->id }}"
                                data-toggle-url="{{ route('admin.vouchers.toggleStatus', $voucher->id) }}">
                                <button type="button" class="status-badge voucher-status-trigger {{ $voucher->status ? 'status-badge--active' : 'status-badge--inactive' }}"
                                    data-value="{{ $voucher->status ? 1 : 0 }}"
                                    data-toggle-url="{{ route('admin.vouchers.toggleStatus', $voucher->id) }}"
                                    aria-haspopup="listbox" aria-expanded="false">
                                    <span class="voucher-status-trigger-label">{{ $voucher->status ? 'Hoạt động' : 'Khóa' }}</span>
                                    <i class="fa-solid fa-chevron-down voucher-status-caret"></i>
                                </button>
                                <div class="hk-cat-panel voucher-status-panel" hidden>
                                    <div class="hk-cat-list" role="listbox">
                                        <button type="button" class="hk-cat-item {{ $voucher->status ? 'is-active' : '' }}" data-value="1" data-css="status-badge--active">Hoạt động</button>
                                        <button type="button" class="hk-cat-item {{ !$voucher->status ? 'is-active' : '' }}" data-value="0" data-css="status-badge--inactive">Khóa</button>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('admin.vouchers.edit', $voucher->id) }}" class="voucher-row-action-btn" data-voucher-edit-url="{{ route('admin.vouchers.edit', $voucher->id) }}" title="Sửa">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <button type="button" class="voucher-row-action-btn"
                                    data-delete-url="{{ route('admin.vouchers.destroy', $voucher->id) }}"
                                    data-delete-name="{{ $voucher->code }}"
                                    data-delete-type="voucher"
                                    title="Xóa">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr data-empty-row>
                        <td colspan="9" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size:42px;display:block;"></i>
                            <div class="fw-semibold text-muted">Chưa có voucher nào</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white border border-top-0 rounded-bottom px-3 py-2 voucher-pagination-bar">
    @include('layouts.components.pagination', [
        'paginator'     => $vouchers,
        'itemLabel'     => 'voucher',
        'bulkDeleteUrl' => route('admin.vouchers.bulkDelete'),
    ])
</div>
