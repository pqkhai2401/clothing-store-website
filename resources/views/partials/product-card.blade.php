@php
    $pId = is_object($product) ? $product->id : ($product['id'] ?? null);
    $pName = is_object($product) ? $product->name : ($product['name'] ?? 'Product');
    $pSlug = is_object($product) ? $product->slug : ($product['slug'] ?? '#');
    $pPrice = is_object($product) ? $product->price : ($product['price'] ?? 0);
    $pSalePrice = is_object($product) ? $product->sale_price : ($product['sale_price'] ?? null);
    $pCategory = is_object($product) ? ($product->category->name ?? 'Collection') : ($product['category'] ?? 'Collection');
    $pImage = is_object($product) ? ($product->images->first()->image_path ?? 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop') : ($product['image'] ?? 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop');
    $pBadge = is_object($product) ? ($product->badge ?? null) : ($product['badge'] ?? null);
    
    // Resolve URL
    $pUrl = $pSlug !== '#' ? url('/products/' . $pSlug) : '#';
@endphp

<div class="product-grid-card">
    <div class="product-img-wrapper">
        @if($pBadge)
            <span class="product-badge {{ strtolower($pBadge) === 'sale' ? 'badge-sale' : '' }}">{{ strtoupper($pBadge) }}</span>
        @elseif($pSalePrice)
            <span class="product-badge badge-sale">SALE</span>
        @endif
        
        <button class="product-wishlist-btn" title="Add to Wishlist" data-id="{{ $pId }}">
            <i class="bi bi-heart"></i>
        </button>
        
        <img src="{{ $pImage }}" alt="{{ $pName }}" class="product-img">
        
        <div class="product-actions">
            <button class="btn-product-action" title="Quick View" data-id="{{ $pId }}"><i class="bi bi-eye"></i></button>
            <button class="btn-product-action" title="Add to Cart" onclick="addToCart('{{ $pId }}')"><i class="bi bi-bag"></i></button>
        </div>
    </div>
    <div class="product-info">
        <div class="product-category">{{ $pCategory }}</div>
        <h3 class="product-name"><a href="{{ $pUrl }}">{{ $pName }}</a></h3>
        <div class="product-price">
            @if($pSalePrice)
                <span class="original-price">${{ number_format($pPrice, 2) }}</span>
                <span class="sale-price">${{ number_format($pSalePrice, 2) }}</span>
            @else
                <span>${{ number_format($pPrice, 2) }}</span>
            @endif
        </div>
    </div>
</div>
