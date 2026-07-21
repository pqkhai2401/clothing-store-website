@extends('layouts.admin')

@section('title', 'Phiếu nhập kho ' . $goodsReceipt->code)

@push('styles')
    @include('admin.suppliers.styles')
    <style>
    .gr-badge { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
    .gr-badge--draft { background: #fef3c7; color: #92400e; }
    .gr-badge--completed { background: #dcfce7; color: #166534; }

    .gr-show-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .gr-show-table thead th {
        background: #f9fafb; text-align: left; padding: 10px 12px; font-weight: 700;
        color: #374151; border-bottom: 1.5px solid #e5e7eb; white-space: nowrap;
    }
    .gr-show-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
    .gr-show-product { display: flex; align-items: center; gap: 10px; }
    .gr-show-thumb { width: 42px; height: 42px; border-radius: 6px; object-fit: cover; flex-shrink: 0; background: #f3f4f6; }
    .gr-show-dot { width: 10px; height: 10px; border-radius: 50%; border: 1px solid rgba(0,0,0,.08); flex-shrink: 0; display: inline-block; }
    </style>
@endpush

@section('content')
<main class="app-main container-fluid py-4">
    <x-notification />

    <div class="d-flex align-items-center justify-content-between gap-3 mb-4 flex-wrap">
        <div>
            <h1 class="h4 fw-bold mb-1" style="color:#174761;">
                Phiếu nhập kho {{ $goodsReceipt->code }}
                @if($goodsReceipt->isCompleted())
                    <span class="gr-badge gr-badge--completed">Hoàn tất</span>
                @else
                    <span class="gr-badge gr-badge--draft">Nháp</span>
                @endif
            </h1>
            <p class="mb-0 text-muted" style="font-size:13px;">
                Tạo bởi {{ $goodsReceipt->creator->username ?? 'N/A' }} lúc {{ $goodsReceipt->created_at?->format('d/m/Y H:i') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            @if($goodsReceipt->isDraft())
                <form method="POST" action="{{ route('admin.goods-receipts.complete', $goodsReceipt->id) }}">
                    @csrf @method('PATCH')
                    <button type="button" class="btn btn-primary fw-bold"
                        onclick="window.showConfirm({title: 'Xác nhận', message: 'Hoàn tất phiếu nhập kho này sẽ cộng tồn kho ngay lập tức. Tiếp tục?', type: 'warning'}).then(ok => { if(ok) this.closest('form').submit(); })">
                        <i class="fa-solid fa-check me-1"></i> Hoàn tất nhập kho
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.goods-receipts.list') }}" class="btn btn-light border fw-bold">
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
                        <label>Nhà cung cấp</label>
                        <div class="fw-bold">{{ $goodsReceipt->supplier->name ?? '—' }}</div>
                        @if($goodsReceipt->supplier?->phone)
                            <div class="text-muted" style="font-size:13px;">{{ $goodsReceipt->supplier->phone }}</div>
                        @endif
                    </div>
                    <div class="edit-field">
                        <label>Ghi chú</label>
                        <div>{{ $goodsReceipt->note ?: '—' }}</div>
                    </div>
                    @if($goodsReceipt->isCompleted())
                        <div class="edit-field mb-0">
                            <label>Hoàn tất lúc</label>
                            <div>{{ $goodsReceipt->completed_at?->format('d/m/Y H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card edit-card shadow-sm mb-4">
                <div class="card-header"><span class="fw-bold" style="font-size:14px;">Sản phẩm nhập kho</span></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="gr-show-table mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá vốn</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($goodsReceipt->items as $item)
                                    @php
                                        $variant = $item->productVariant;
                                        $thumb = $variant?->product?->thumbnail;
                                        $thumbUrl = $thumb
                                            ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
                                            : 'https://placehold.co/80x80?text=No+Image';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="gr-show-product">
                                                <img class="gr-show-thumb" src="{{ $thumbUrl }}" alt="">
                                                <div>
                                                    <div class="fw-bold">{{ $variant?->product?->name ?? 'Sản phẩm đã xóa' }}</div>
                                                    <div class="text-muted" style="font-size:12px;">
                                                        <span class="gr-show-dot" style="background:{{ $variant?->color?->display_hex_code ?? '#ccc' }};"></span>
                                                        {{ $variant?->color?->name }} · {{ $variant?->size?->name }} · {{ $variant?->sku }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ number_format($item->quantity) }}</td>
                                        <td>{{ number_format($item->cost_price, 0, ',', '.') }}đ</td>
                                        <td class="fw-semibold">{{ number_format($item->quantity * $item->cost_price, 0, ',', '.') }}đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end fw-bold">Tổng giá trị phiếu nhập:</td>
                                    <td class="fw-bold" style="color:#174761;font-size:15px;">
                                        {{ number_format($goodsReceipt->total_amount, 0, ',', '.') }}đ
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
