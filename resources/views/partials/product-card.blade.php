@php
    $pId = is_object($product) ? $product->id : ($product['id'] ?? null);
    $pName = is_object($product) ? $product->name : ($product['name'] ?? 'Product');
    $pSlug = is_object($product) ? $product->slug : ($product['slug'] ?? '#');
    $pPrice = is_object($product) ? $product->price : ($product['price'] ?? 0);
    $pDiscount = is_object($product) ? ($product->discount ?? 0) : ($product['discount'] ?? 0);
    $pCategory = is_object($product) ? ($product->category->name ?? 'Collection') : ($product['category'] ?? 'Collection');
    $pImage = is_object($product) ? ($product->thumbnail ?? ($product->productImages->first()->image ?? 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop')) : ($product['image'] ?? 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop');
    $pBadge = is_object($product) ? ($product->badge ?? null) : ($product['badge'] ?? null);

    // Tính giá sau khi áp dụng discount (%)
    $pFinalPrice = $pDiscount > 0 ? $pPrice * (100 - $pDiscount) / 100 : null;

    // Resolve URL
    $pUrl = $pSlug !== '#' ? url('/san-pham/' . $pSlug) : '#';
@endphp

<div class="product-grid-card">
    <div class="product-img-wrapper">
        @if($pBadge)
            <span class="product-badge {{ strtolower($pBadge) === 'sale' ? 'badge-sale' : '' }}">{{ strtoupper($pBadge) }}</span>
        @elseif($pDiscount > 0)
            <span class="product-badge badge-sale">-{{ $pDiscount }}%</span>
        @endif
        
        @php $inWishlist = in_array($pId, $userWishlistIds ?? []); @endphp
        <button class="product-wishlist-btn wl-toggle-btn"
                title="{{ $inWishlist ? 'Xóa khỏi yêu thích' : 'Thêm vào yêu thích' }}"
                data-product-id="{{ $pId }}"
                data-in-wishlist="{{ $inWishlist ? 'true' : 'false' }}">
            <i class="bi {{ $inWishlist ? 'bi-heart-fill' : 'bi-heart' }}"
               style="{{ $inWishlist ? 'color:#d9534f;' : '' }}"></i>
        </button>
        
        <img src="{{ $pImage }}" alt="{{ $pName }}" class="product-img">

        <div class="product-actions">
            <button class="btn-product-action" title="Quick View" data-id="{{ $pId }}"><i class="bi bi-eye"></i></button>
            <button class="btn-product-action" title="Add to Cart" onclick="addToCart('{{ $pId }}')"><i class="bi bi-bag"></i></button>
        </div>

        <button class="product-add-cart-bar" type="button" data-add-to-cart data-product-id="{{ $pId }}">
            <span class="add-cart-label"><i class="bi bi-bag me-2"></i>Thêm vào giỏ</span>
            <span class="add-cart-spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </button>
    </div>
    <div class="product-info">
        <div class="product-category">{{ $pCategory }}</div>
        <h3 class="product-name"><a href="{{ $pUrl }}">{{ $pName }}</a></h3>
        <div class="product-price">
            @if($pFinalPrice)
                <span class="original-price">{{ number_format($pPrice, 0, ',', '.') }}đ</span>
                <span class="sale-price">{{ number_format($pFinalPrice, 0, ',', '.') }}đ</span>
            @else
                <span>{{ number_format($pPrice, 0, ',', '.') }}đ</span>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('click', async function (event) {
                const btn = event.target.closest('[data-add-to-cart]');
                if (!btn || btn.disabled) return;

                btn.disabled = true;
                btn.classList.add('is-loading');

                try {
                    const response = await fetch('{{ route('cart.add') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        },
                        body: JSON.stringify({ product_id: btn.dataset.productId }),
                    });

                    if (response.status === 401) {
                        window.location.href = '{{ route('auth.loginpage') }}';
                        return;
                    }

                    const data = await response.json();

                    if (!response.ok) {
                        alert(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                        return;
                    }

                    window.location.href = data.cart_url;
                } catch (error) {
                    alert('Đã có lỗi xảy ra. Vui lòng thử lại.');
                } finally {
                    btn.disabled = false;
                    btn.classList.remove('is-loading');
                }
            });
        </script>
    @endpush
@endonce
