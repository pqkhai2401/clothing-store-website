<section class="py-5 my-5">
    <div class="container-fluid px-lg-5">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">You May Also Like</h2>
            <div class="section-subtitle">Complementary items from our collections</div>
        </div>
        
        @php
            // Mock related products for demonstration if none are passed
            $relatedProducts = $products ?? [
                ['id' => 10, 'name' => 'Structured Cotton Shirt', 'category' => 'Shirts', 'price' => 890000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop', 'slug' => 'structured-cotton-shirt'],
                ['id' => 11, 'name' => 'Classic Straight Jeans', 'category' => 'Denim', 'price' => 1200000, 'discount' => 20, 'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-straight-jeans'],
                ['id' => 12, 'name' => 'Relaxed Wool Cardigan', 'category' => 'Knitwear', 'price' => 450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?q=80&w=600&auto=format&fit=crop', 'slug' => 'relaxed-wool-cardigan'],
                ['id' => 13, 'name' => 'Tailored Wool Blazer', 'category' => 'Outerwear', 'price' => 2100000, 'discount' => 10, 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-wool-blazer'],
            ];
        @endphp
        
        @include('partials.product-grid', ['products' => $relatedProducts, 'cols' => 'col-6 col-md-3'])
    </div>
</section>
