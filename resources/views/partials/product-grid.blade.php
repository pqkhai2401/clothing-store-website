@php
    $gridCols = $cols ?? 'col-6 col-md-4';
@endphp

<div class="row">
    @forelse($products as $product)
        <div class="{{ $gridCols }}">
            @include('partials.product-card', ['product' => $product])
        </div>
    @empty
        <div class="col-12">
            @include('partials.empty-state', [
                'icon' => 'bi-search',
                'title' => 'No products found',
                'message' => 'Try adjusting your search or filter values to find what you are looking for.',
                'button_text' => 'Clear Filters',
                'button_url' => url('/products')
            ])
        </div>
    @endforelse
</div>
