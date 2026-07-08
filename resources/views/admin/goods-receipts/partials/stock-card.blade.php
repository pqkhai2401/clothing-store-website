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
