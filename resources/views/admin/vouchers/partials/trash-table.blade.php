@php
    $typeLabels = [
        'percentage' => 'Theo %',
        'fixed' => 'Tiền mặt',
    ];

    $typeBadgeCss = [
        'percentage' => 'voucher-type-badge--percentage',
        'fixed' => 'voucher-type-badge--fixed',
    ];
@endphp

<div class="voucher-trash-table-wrap">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 voucher-trash-table">
            <thead>
                <tr>
                    <th style="width: 54px;">
                        <input type="checkbox" class="form-check-input product-check hk-cb-all">
                    </th>
                    <th class="ps-4" style="width: 86px;">ID</th>
                    <th style="width: 180px;">Mã voucher</th>
                    <th style="width: 150px;">Kiểu giảm</th>
                    <th style="width: 180px;">Giá trị giảm</th>
                    <th>Thời gian hiệu lực</th>
                    <th style="width: 210px;">Ngày xóa</th>
                    <th class="text-end pe-4" style="width: 260px;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vouchers as $voucher)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input product-check hk-cb-row" value="{{ $voucher->id }}">
                        </td>
                        <td class="ps-4 voucher-trash-id">{{ $voucher->id }}</td>
                        <td>
                            <span class="voucher-code">{{ $voucher->code }}</span>
                        </td>
                        <td>
                            <span class="voucher-type-badge {{ $typeBadgeCss[$voucher->type] ?? '' }}">
                                {{ $typeLabels[$voucher->type] ?? $voucher->type }}
                            </span>
                        </td>
                        <td>
                            <div class="voucher-trash-value">
                                @if($voucher->type === 'percentage')
                                    {{ rtrim(rtrim(number_format($voucher->value, 2, ',', '.'), '0'), ',') }}%
                                @else
                                    {{ number_format($voucher->value, 0, ',', '.') }}đ
                                @endif
                            </div>
                            <div class="voucher-trash-sub">
                                Đã dùng {{ number_format($voucher->used_count) }} / {{ number_format($voucher->quantity) }}
                            </div>
                        </td>
                        <td class="voucher-trash-date-range">
                            {{ $voucher->start_date?->format('d/m/Y') ?? '—' }} - {{ $voucher->end_date?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="voucher-trash-date">
                            {{ $voucher->deleted_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-inline-flex gap-2">
                                <form method="POST" action="{{ route('admin.vouchers.restore', $voucher->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Khôi phục
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger fw-semibold"
                                    data-delete-url="{{ route('admin.vouchers.forceDelete', $voucher->id) }}"
                                    data-delete-name="{{ $voucher->code }}"
                                    data-delete-type="voucher (vĩnh viễn)">
                                    <i class="fa-solid fa-trash me-1"></i> Xóa vĩnh viễn
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fa-solid fa-inbox text-muted mb-3" style="font-size: 42px; display: block;"></i>
                            <div class="fw-semibold text-muted">Thùng rác trống</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="border-top px-4 py-2 voucher-trash-pagination">
        @include('layouts.components.pagination', [
            'paginator'        => $vouchers,
            'itemLabel'        => 'voucher',
            'bulkRestoreUrl'   => route('admin.vouchers.bulkRestore'),
            'bulkDeleteUrl'    => route('admin.vouchers.bulkForceDelete'),
            'bulkDeleteLabel'  => 'Xóa vĩnh viễn đã chọn',
        ])
    </div>
</div>
