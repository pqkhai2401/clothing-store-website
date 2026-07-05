@extends('layouts.admin')

@section('title', 'Phiếu xuất kho ' . $stockIssue->code)

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }
    .gr-btn-emerald { background: #0f9d58; border-color: #0f9d58; color: #fff; }
    .gr-btn-emerald:hover { background: #0c7a45; border-color: #0c7a45; color: #fff; }

    .si-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .si-show-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .si-show-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .si-show-product { display: flex; align-items: center; gap: 10px; }
    .si-show-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .si-show-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; display: inline-block; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">
                Phiếu xuất kho {{ $stockIssue->code }}
                @if($stockIssue->isIssued())
                    <span class="gr-badge gr-badge--completed">Đã xuất kho</span>
                @else
                    <span class="gr-badge gr-badge--draft">Nháp</span>
                @endif
            </h1>
            <p class="mb-0 text-muted" style="font-size:13px;">
                Tạo bởi {{ $stockIssue->creator->name ?? 'N/A' }} lúc {{ $stockIssue->created_at?->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($stockIssue->isDraft())
                <form method="POST" action="{{ route('admin.stock-issues.issue', $stockIssue->id) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn gr-btn-emerald fw-bold"
                        onclick="return confirm('Xuất kho phiếu này sẽ trừ tồn kho ngay lập tức. Tiếp tục?')">
                        <i class="fa-solid fa-check me-1"></i> Xuất kho ngay
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.goods-receipts.list', ['tab' => 'outbound']) }}" class="btn btn-light border fw-bold">
                <i class="fa-solid fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card edit-card shadow-sm mb-4">
                <div class="card-header"><span class="fw-bold" style="font-size:14px;">Thông tin phiếu</span></div>
                <div class="card-body p-4">
                    <div class="edit-field">
                        <label>Lý do xuất kho</label>
                        <div class="fw-bold">{{ $stockIssue->reason }}</div>
                    </div>
                    @if($stockIssue->note)
                        <div class="edit-field">
                            <label>Ghi chú</label>
                            <div>{{ $stockIssue->note }}</div>
                        </div>
                    @endif
                    @if($stockIssue->isIssued())
                        <div class="edit-field mb-0">
                            <label>Xuất kho lúc</label>
                            <div>{{ $stockIssue->issued_at?->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card edit-card shadow-sm mb-4">
                <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm xuất kho</span></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="si-show-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stockIssue->items as $item)
                                    @php
                                        $variant = $item->productVariant;
                                        $thumb = $variant?->product?->thumbnail;
                                        $thumbUrl = $thumb
                                            ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                                            : 'https://placehold.co/80x80?text=No+Image';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="si-show-product">
                                                <img class="si-show-thumb" src="{{ $thumbUrl }}" alt="">
                                                <div>
                                                    <div class="fw-bold">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                                    <div class="text-muted" style="font-size:12px;">
                                                        <span class="si-show-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#ccc' }};"></span>
                                                        {{ $variant?->color?->name }} · {{ $variant?->size?->name }} · {{ $variant?->sku }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($item->quantity) }}</td>
                                        <td>{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                                        <td class="fw-semibold">{{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng giá trị xuất kho:</td>
                                    <td class="fw-bold" style="color:#0f9d58;font-size:15px;">
                                        {{ number_format($stockIssue->total_amount, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
