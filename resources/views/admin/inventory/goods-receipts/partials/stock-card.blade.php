@php
    $thumb = $variant->image ?: $variant->product?->thumbnail;
    $thumbUrl = $thumb
        ? (\Illuminate\Support\Str::startsWith($thumb, 'http') ? $thumb : asset($thumb))
        : 'https://placehold.co/96x96?text=No+Image';

    $variantName = trim(($variant->product?->name ?? 'Sản phẩm đã xóa')
        . ' - ' . ($variant->color?->name ?? 'Không màu')
        . ' - Size ' . ($variant->size?->name ?? 'N/A'));

    $badgeClasses = [
        'nhap_kho' => 'skc-badge skc-badge--in',
        'xuat_kho' => 'skc-badge skc-badge--out',
        'huy_don' => 'skc-badge skc-badge--cancel',
        'dieu_chinh_kho' => 'skc-badge skc-badge--adjust',
    ];
@endphp

<div class="modal-header skc-header border-bottom">
    <div class="skc-product">
        <img class="skc-thumb" src="{{ $thumbUrl }}" alt="">
        <div>
            <h1 class="skc-title">{{ $variantName }}</h1>
            <div class="skc-meta">
                <span>SKU: <strong>{{ $variant->sku }}</strong></span>
                <span class="skc-dot"></span>
                <span>Current stock: <strong>{{ number_format($variant->stock) }}</strong></span>
            </div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
</div>

<div class="modal-body skc-body">
    @once
        <style>
            .skc-section-title { font-size: 13px; font-weight: 800; color: #0f172a; margin: 2px 0 8px; }
            .skc-section-title + .skc-table-wrap { margin-bottom: 18px; }
            .skc-batch--muted td { opacity: .5; }
            .skc-bstatus { font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: .03em; }
            .skc-bstatus--active { background:#dcfce7; color:#166534; }
            .skc-bstatus--depleted { background:#f1f5f9; color:#64748b; }
            .skc-bstatus--locked { background:#fee2e2; color:#991b1b; }
            .skc-bstatus--expired { background:#ffedd5; color:#9a3412; }
        </style>
    @endonce

    {{-- TỒN THEO LÔ (Batch) — nguồn dữ liệu thật của tồn kho; lô còn hàng (FIFO) lên trước --}}
    <div class="skc-section-title">Tồn theo lô (Batch)</div>
    <div class="skc-table-wrap">
        <table class="skc-table">
            <thead>
                <tr>
                    <th>Mã lô</th>
                    <th>Ngày nhập</th>
                    <th>SL nhập</th>
                    <th>Còn lại</th>
                    <th>Giá vốn</th>
                    <th>Trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    @php
                        $isActive = $batch->status === \App\Models\ProductBatch::STATUS_ACTIVE && $batch->quantity_remaining > 0;
                        $bStatus = [
                            'active'   => ['Còn hàng', 'skc-bstatus--active'],
                            'depleted' => ['Đã hết',  'skc-bstatus--depleted'],
                            'locked'   => ['Khóa lô', 'skc-bstatus--locked'],
                            'expired'  => ['Hết hạn', 'skc-bstatus--expired'],
                        ][$batch->status] ?? ['Đã hết', 'skc-bstatus--depleted'];
                    @endphp
                    <tr class="{{ $isActive ? '' : 'skc-batch--muted' }}">
                        <td class="fw-bold">{{ $batch->batch_code }}</td>
                        <td class="skc-date">{{ $batch->received_at?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ number_format($batch->quantity_import) }}</td>
                        <td><strong>{{ number_format($batch->quantity_remaining) }}</strong></td>
                        <td>{{ number_format($batch->cost_price, 0, ',', '.') }}đ</td>
                        <td><span class="skc-bstatus {{ $bStatus[1] }}">{{ $bStatus[0] }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="skc-empty">Biến thể này chưa có lô hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="skc-section-title">Lịch sử giao dịch kho</div>
    <div class="skc-table-wrap">
        <table class="skc-table">
            <thead>
                <tr>
                    <th>Ngày &amp; Giờ</th>
                    <th>Loại Giao Dịch</th>
                    <th>Mã phiếu</th>
                    <th>Thay Đổi Số Lượng</th>
                    <th>Số Lượng Cuối Cùng</th>
                    <th>Người Phụ Trách</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    @php
                        $qty = (int) $transaction['quantity_change'];
                    @endphp
                    <tr>
                        <td class="skc-date">{{ $transaction['at']?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="{{ $badgeClasses[$transaction['type']] ?? 'skc-badge' }}">
                                {{ $transaction['type_label'] }}
                            </span>
                        </td>
                        <td>
                            @if($transaction['document_url'])
                                <a class="skc-doc-link" href="{{ $transaction['document_url'] }}"
                                    @if(in_array($transaction['type'], ['nhap_kho', 'xuat_kho', 'dieu_chinh_kho'], true))
                                        data-stock-card-document-trigger
                                        data-document-url="{{ $transaction['document_url'] }}"
                                        data-document-code="{{ $transaction['document_code'] }}"
                                        onclick="event.preventDefault();"
                                    @else
                                        target="_blank"
                                    @endif>
                                    {{ $transaction['document_code'] }}
                                </a>
                            @else
                                <span class="skc-doc-link skc-doc-link--static">{{ $transaction['document_code'] }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="skc-qty {{ $qty >= 0 ? 'skc-qty--pos' : 'skc-qty--neg' }}">
                                {{ $qty > 0 ? '+' : '' }}{{ number_format($qty) }}
                            </span>
                        </td>
                        <td class="skc-ending">{{ number_format($transaction['ending_stock']) }}</td>
                        <td class="skc-user">{{ $transaction['user'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="skc-empty">Chưa có giao dịch kho nào cho biến thể này.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
