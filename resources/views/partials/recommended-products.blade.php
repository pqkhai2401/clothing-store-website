<section class="py-5 bg-light">
    <div class="container-fluid px-lg-5">
        <div class="section-header text-center mb-5">
            <h2 class="section-title">Recommended For You</h2>
            <div class="section-subtitle"><i class="bi bi-cpu me-1 text-dark"></i> Personalized by our AI Recommendation Engine</div>
        </div>
        
        <div class="row">
            @php
                // Mock recommended products for demonstration if none are passed
                $recommendedProducts = $products ?? [
                    ['id' => 1, 'name' => 'Organic Linen Dress', 'category' => 'Dresses', 'price' => 1650000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop', 'slug' => 'organic-linen-dress', 'badge' => 'AI Styled'],
                    ['id' => 2, 'name' => 'Relaxed Wool Cardigan', 'category' => 'Knitwear', 'price' => 1450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?q=80&w=600&auto=format&fit=crop', 'slug' => 'relaxed-wool-cardigan', 'badge' => 'AI Styled'],
                    ['id' => 3, 'name' => 'Leather Crossbody Bag', 'category' => 'Bags', 'price' => 1800000, 'discount' => 15, 'image' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=600&auto=format&fit=crop', 'slug' => 'leather-crossbody-bag', 'badge' => 'AI Styled'],
                    ['id' => 4, 'name' => 'Minimalist Knit Sweater', 'category' => 'Knitwear', 'price' => 980000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1544441893-675973e31985?q=80&w=600&auto=format&fit=crop', 'slug' => 'minimalist-knit-sweater', 'badge' => 'AI Styled'],
                    ['id' => 5, 'name' => 'Cropped Knit Vest', 'category' => 'Knitwear', 'price' => 750000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=600&auto=format&fit=crop', 'slug' => 'cropped-knit-vest', 'badge' => 'AI Styled'],
                    ['id' => 6, 'name' => 'Suede Chelsea Boots', 'category' => 'Shoes', 'price' => 2100000, 'discount' => 10, 'image' => 'https://images.unsplash.com/photo-1485968579580-b6d095142e6e?q=80&w=600&auto=format&fit=crop', 'slug' => 'suede-chelsea-boots', 'badge' => 'AI Styled'],
                    ['id' => 7, 'name' => 'Premium Silk Slip Skirt', 'category' => 'Skirts', 'price' => 1250000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1620799139834-6b8f844fbe61?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-silk-slip-skirt', 'badge' => 'AI Styled'],
                    ['id' => 8, 'name' => 'Minimalist Leather Belt', 'category' => 'Accessories', 'price' => 450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1509319117193-57bab727e09d?q=80&w=600&auto=format&fit=crop', 'slug' => 'minimalist-leather-belt', 'badge' => 'AI Styled'],
                ];
            @endphp
            
            @foreach(array_slice($recommendedProducts, 0, 8) as $product)
                <div class="col-6 col-md-3">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
