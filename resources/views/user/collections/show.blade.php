@extends('layouts.app')

@section('title', $collection->name . ' | HK Store')

@section('css')
<style>
    .collection-banner-section {
        height: 40vh;
        background-size: cover;
        background-position: center;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        text-align: center;
        position: relative;
        margin-bottom: 50px;
    }
    .collection-banner-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
    }
    .collection-banner-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        padding: 0 20px;
    }
    .collection-title {
        font-family: var(--font-serif);
        font-size: 3.5rem;
        font-weight: 400;
        text-transform: uppercase;
        letter-spacing: 3px;
        margin-bottom: 15px;
        color: #ffffff;
    }
    .collection-desc {
        font-size: 16px;
        line-height: 1.6;
        opacity: 0.9;
        font-weight: 300;
        color: #ffffff;
    }
    @media (max-width: 768px) {
        .collection-banner-section {
            height: 30vh;
        }
        .collection-title {
            font-size: 2.2rem;
        }
        .collection-desc {
            font-size: 13px;
        }
    }
    /* Simple Gender Filters */
    .collection-nav-filters {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 40px;
    }
    .collection-nav-btn {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 8px 24px;
        border: 1px solid var(--border-color);
        color: var(--secondary-color);
        text-decoration: none;
        transition: all 0.3s;
    }
    .collection-nav-btn:hover, .collection-nav-btn.active {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #ffffff;
    }
</style>
@endsection

@section('content')
    <!-- Banner -->
    <div class="collection-banner-section" style="background-image: url('{{ $collection->banner ? asset($collection->banner) : 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1200&auto=format&fit=crop' }}');">
        <div class="collection-banner-content">
            <h1 class="collection-title">{{ $collection->name }}</h1>
            <p class="collection-desc">{{ $collection->description }}</p>
        </div>
    </div>

    <div class="container py-4">
        <!-- Gender Filter Nav -->
        <div class="collection-nav-filters">
            <a href="{{ route('collections.show', $collection->slug) }}" class="collection-nav-btn {{ !$gender ? 'active' : '' }}">Tất cả</a>
            <a href="{{ route('collections.show', $collection->slug) }}?gender=men" class="collection-nav-btn {{ $gender === 'men' ? 'active' : '' }}">Nam</a>
            <a href="{{ route('collections.show', $collection->slug) }}?gender=women" class="collection-nav-btn {{ $gender === 'women' ? 'active' : '' }}">Nữ</a>
        </div>

        <div class="row">
            <div class="col-12">
                <!-- Products Grid -->
                @if($products->total() === 0)
                    <div class="text-center py-5">
                        <i class="bi bi-bag-x" style="font-size:48px; color:#ccc;"></i>
                        <p class="mt-3 mb-1" style="font-size:18px; font-weight:500;">Bộ sưu tập hiện tại chưa có sản phẩm nào phù hợp</p>
                        <p class="text-muted" style="font-size:14px;"><a href="{{ url('/san-pham') }}">Khám phá sản phẩm khác</a></p>
                    </div>
                @else
                    @include('partials.product-grid', ['products' => $products, 'cols' => 'col-6 col-md-3'])
                @endif

                <!-- Pagination -->
                @if($products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                    @include('partials.pagination', ['paginator' => $products])
                @else
                    @include('partials.pagination')
                @endif
            </div>
        </div>
    </div>
@endsection
