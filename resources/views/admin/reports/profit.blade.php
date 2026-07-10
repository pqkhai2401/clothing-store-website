@extends('layouts.admin')

@section('title', 'Báo cáo lãi gộp (FIFO)')

@push('styles')
<style>
    .rpt-stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; }
    .rpt-stat { background:#fff; border:1px solid #e5e7eb; border-radius:14px; padding:16px 18px; }
    .rpt-stat-label { font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.03em; }
    .rpt-stat-value { font-size:22px; font-weight:850; color:#0f172a; margin-top:6px; }
    .rpt-stat--profit .rpt-stat-value { color:#166534; }
    .rpt-stat--cogs .rpt-stat-value { color:#9a3412; }
    .rpt-table { width:100%; border-collapse:collapse; font-size:13px; }
    .rpt-table thead th { background:#f9fafb; text-align:left; padding:11px 14px; font-weight:800; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.03em; border-bottom:1.5px solid #e5e7eb; white-space:nowrap; }
    .rpt-table tbody td { padding:12px 14px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
    .rpt-table tbody tr:hover { background:#f8fafc; }
    .rpt-num { text-align:right; white-space:nowrap; font-variant-numeric:tabular-nums; }
    .rpt-profit-pos { color:#166534; font-weight:800; }
    .rpt-profit-neg { color:#b91c1c; font-weight:800; }
    .rpt-pill { font-size:11px; font-weight:800; padding:2px 8px; border-radius:99px; background:#eef2ff; color:#3730a3; }
    @media (prefers-color-scheme: dark) {
        .rpt-stat, .rpt-table thead th { background:#0b1220; border-color:#1e293b; }
        .rpt-stat-value { color:#e2e8f0; }
        .rpt-table tbody tr:hover { background:#0b1220; }
    }
</style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">Báo cáo lãi gộp (FIFO)</h1>
            <p class="mb-0 text-muted" style="font-size:13px;">
                Giá vốn tính theo lô đã trừ FIFO tại thời điểm bán (từ sổ cái kho).
            </p>
        </div>
        <a href="{{ route('admin.goods-receipts.list') }}" class="btn btn-light border fw-bold">
            <i class="fa-solid fa-arrow-left me-1"></i> Về quản lý kho
        </a>
    </div>

    {{-- Bộ lọc thời gian --}}
    <form method="GET" action="{{ route('admin.goods-receipts.reports.profit') }}"
          class="d-flex align-items-end gap-2 flex-wrap mb-4">
        <div>
            <label class="form-label mb-1" style="font-size:12px;font-weight:700;">Từ ngày</label>
            <input type="date" name="from" value="{{ $from }}" class="form-control form-control-sm">
        </div>
        <div>
            <label class="form-label mb-1" style="font-size:12px;font-weight:700;">Đến ngày</label>
            <input type="date" name="to" value="{{ $to }}" class="form-control form-control-sm">
        </div>
        <button type="submit" class="btn btn-primary btn-sm fw-bold">
            <i class="fa-solid fa-filter me-1"></i> Lọc
        </button>
    </form>

    {{-- Thẻ tổng hợp --}}
    <div class="rpt-stat-grid mb-4">
        <div class="rpt-stat">
            <div class="rpt-stat-label">Doanh thu</div>
            <div class="rpt-stat-value">{{ number_format($summary['revenue'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rpt-stat rpt-stat--cogs">
            <div class="rpt-stat-label">Giá vốn (FIFO)</div>
            <div class="rpt-stat-value">{{ number_format($summary['cogs'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rpt-stat rpt-stat--profit">
            <div class="rpt-stat-label">Lãi gộp</div>
            <div class="rpt-stat-value">{{ number_format($summary['profit'], 0, ',', '.') }}đ</div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-stat-label">Biên lãi gộp</div>
            <div class="rpt-stat-value">{{ number_format($summary['margin'], 1) }}%</div>
        </div>
        <div class="rpt-stat">
            <div class="rpt-stat-label">Số phiếu · SL bán</div>
            <div class="rpt-stat-value">{{ number_format($summary['orders']) }} · {{ number_format($summary['qty']) }}</div>
        </div>
    </div>

    {{-- Chi tiết theo phiếu xuất bán --}}
    <div class="card edit-card shadow-sm">
        <div class="card-header"><span class="fw-bold" style="font-size:14px;">Chi tiết theo phiếu xuất bán</span></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="rpt-table">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Ngày bán</th>
                            <th>Đơn hàng</th>
                            <th class="rpt-num">SL</th>
                            <th class="rpt-num">Doanh thu</th>
                            <th class="rpt-num">Giá vốn (FIFO)</th>
                            <th class="rpt-num">Lãi gộp</th>
                            <th class="rpt-num">Biên %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td class="fw-bold">{{ $row['issue']->code }}</td>
                                <td>{{ $row['issue']->issued_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if($row['issue']->order)
                                        <a href="{{ route('admin.orders.detail', $row['issue']->order->id) }}">
                                            {{ $row['issue']->order->order_code ?? ('#'.$row['issue']->order->id) }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="rpt-num">{{ number_format($row['qty']) }}</td>
                                <td class="rpt-num">{{ number_format($row['revenue'], 0, ',', '.') }}đ</td>
                                <td class="rpt-num">{{ number_format($row['cogs'], 0, ',', '.') }}đ</td>
                                <td class="rpt-num {{ $row['profit'] >= 0 ? 'rpt-profit-pos' : 'rpt-profit-neg' }}">
                                    {{ number_format($row['profit'], 0, ',', '.') }}đ
                                </td>
                                <td class="rpt-num">{{ number_format($row['margin'], 1) }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    Không có phiếu xuất bán nào trong khoảng thời gian này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($rows->isNotEmpty())
                        <tfoot>
                            <tr style="border-top:2px solid #e5e7eb;font-weight:800;">
                                <td colspan="3" class="text-end">Tổng cộng:</td>
                                <td class="rpt-num">{{ number_format($summary['qty']) }}</td>
                                <td class="rpt-num">{{ number_format($summary['revenue'], 0, ',', '.') }}đ</td>
                                <td class="rpt-num">{{ number_format($summary['cogs'], 0, ',', '.') }}đ</td>
                                <td class="rpt-num {{ $summary['profit'] >= 0 ? 'rpt-profit-pos' : 'rpt-profit-neg' }}">
                                    {{ number_format($summary['profit'], 0, ',', '.') }}đ
                                </td>
                                <td class="rpt-num">{{ number_format($summary['margin'], 1) }}%</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
