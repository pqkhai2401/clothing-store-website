@extends('layouts.app')

@section('title', ($pageTitle ?? 'Products') . ' | HK Store')

@section('css')
<style>
    /* Listing Header & Breadcrumb overrides */
    .products-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 25px;
        margin-bottom: 40px;
    }

    .products-title {
        font-size: 36px;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin-bottom: 5px;
    }

    .products-count {
        font-size: 13px;
        color: var(--muted-text);
    }

    /* Filters Sidebar */
    .filter-group {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 25px;
        margin-bottom: 25px;
    }

    .filter-title {
        font-family: var(--font-sans);
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        color: var(--primary-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
    }

    .filter-list {
        list-style: none;
        padding-left: 0;
        margin-bottom: 0;
    }

    .filter-list li {
        margin-bottom: 10px;
    }

    .filter-link {
        font-size: 13px;
        color: var(--muted-text);
        transition: color 0.3s;
        display: flex;
        justify-content: space-between;
    }

    .filter-link:hover, .filter-link.active {
        color: var(--primary-color);
        font-weight: 500;
    }

    .filter-count {
        font-size: 11px;
        color: #b2b2b2;
    }

    /* Checkbox list styles */
    .form-check {
        margin-bottom: 10px;
    }

    .form-check-input {
        border-radius: 0;
        border-color: #cccccc;
    }

    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .form-check-label {
        font-size: 13px;
        color: var(--secondary-color);
        cursor: pointer;
    }

    /* Price Range Inputs */
    .price-inputs {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .price-inputs input {
        border-radius: 0;
        border: 1px solid var(--border-color);
        padding: 8px;
        font-size: 12px;
        width: 100%;
        text-align: center;
    }

    .price-inputs input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: none;
    }

    /* Tag Cloud */
    .tag-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag-badge {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 6px 12px;
        background-color: var(--hover-bg);
        color: var(--secondary-color);
        transition: all 0.3s;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .tag-badge:hover, .tag-badge.active {
        background-color: var(--primary-color);
        color: #ffffff;
    }

    /* Toolbar Section */
    .toolbar-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .sort-dropdown .form-select {
        border-radius: 0;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid var(--border-color);
        padding: 10px 30px 10px 15px;
        min-width: 180px;
        cursor: pointer;
    }

    .sort-dropdown .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    /* Search Bar Inline */
    .search-bar-inline .form-control {
        border-radius: 0;
        border: 1px solid var(--border-color);
        padding: 10px 15px;
        font-size: 13px;
        min-width: 250px;
    }

    .search-bar-inline .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: none;
    }

    /* Mobile Filters Trigger Styles */
    .mobile-filter-trigger {
        display: none;
    }

    @media (max-width: 991px) {
        .sidebar-filters-col {
            display: none;
        }
        .sidebar-filters-col.show-mobile {
            display: block;
            position: fixed;
            top: 0;
            left: 0;
            width: 80%;
            height: 100%;
            background-color: #ffffff;
            z-index: 2040;
            padding: 30px;
            overflow-y: auto;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        .sidebar-close-btn {
            display: block !important;
        }
        .filter-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 2030;
        }
    }
</style>
@endsection

@section('content')

    <!-- Breadcrumbs -->
    @include('partials.breadcrumb', [
        'items' => isset($q)
            ? [
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => 'Tìm kiếm', 'active' => true],
              ]
            : [
                ['name' => 'Trang chủ', 'url' => url('/')],
                ['name' => $pageTitle ?? 'Sản phẩm', 'active' => true],
              ]
    ])

    <!-- Products Page Body -->
    <div class="py-5">
        <div class="container-fluid px-lg-5">
            <div class="row">
                
                @php
                    // Query hiện tại, dùng để giữ lại các filter khác khi xóa 1 nhóm filter
                    $currentQuery = request()->query();
                    $withoutQuery = fn (array $except) => http_build_query(collect($currentQuery)->except($except)->all());

                    $selectedCategories = $selectedCategories ?? [];
                    $selectedGenders    = $selectedGenders ?? [];
                    $selectedBrands     = $selectedBrands ?? [];
                    $selectedTags       = $selectedTags ?? [];
                @endphp

                <!-- 1. Sidebar Filters Column -->
                <div class="col-lg-3 sidebar-filters-col" id="sidebarFilters">
                    <!-- Close button on mobile -->
                    <div class="sidebar-close-btn d-none text-end mb-4">
                        <button class="btn btn-sm btn-outline-dark" id="closeFiltersBtn">ĐÓNG <i class="bi bi-x"></i></button>
                    </div>

                    <form method="GET" action="{{ url()->current() }}" id="productFilterForm">

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseCategory">
                            Danh mục <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseCategory">
                            <ul class="filter-list">
                                <li>
                                    <a href="{{ url()->current() }}?{{ $withoutQuery(['category', 'page']) }}"
                                       class="filter-link {{ empty($selectedCategories) ? 'active' : '' }}">
                                        Tất cả <span class="filter-count">({{ $filterCategories->sum('products_count') }})</span>
                                    </a>
                                </li>
                                @foreach($filterCategories as $cat)
                                    <li>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="category[]"
                                                   value="{{ $cat->slug }}" id="cat-{{ $cat->id }}"
                                                   {{ in_array($cat->slug, $selectedCategories) ? 'checked' : '' }}
                                                   onchange="document.getElementById('productFilterForm').submit()">
                                            <label class="form-check-label d-flex justify-content-between" for="cat-{{ $cat->id }}">
                                                <span>{{ $cat->name }}</span>
                                                <span class="filter-count">({{ $cat->products_count }})</span>
                                            </label>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Gender Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseGender">
                            Giới tính <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseGender">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gender[]" value="men" id="genderMen"
                                       {{ in_array('men', $selectedGenders) ? 'checked' : '' }}
                                       onchange="document.getElementById('productFilterForm').submit()">
                                <label class="form-check-label" for="genderMen">Nam</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gender[]" value="women" id="genderWomen"
                                       {{ in_array('women', $selectedGenders) ? 'checked' : '' }}
                                       onchange="document.getElementById('productFilterForm').submit()">
                                <label class="form-check-label" for="genderWomen">Nữ</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="gender[]" value="unisex" id="genderUnisex"
                                       {{ in_array('unisex', $selectedGenders) ? 'checked' : '' }}
                                       onchange="document.getElementById('productFilterForm').submit()">
                                <label class="form-check-label" for="genderUnisex">Unisex</label>
                            </div>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseBrand">
                            Thương hiệu <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseBrand">
                            @foreach($filterBrands as $brand)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="brand[]"
                                           value="{{ $brand->id }}" id="brand-{{ $brand->id }}"
                                           {{ in_array((string) $brand->id, $selectedBrands) ? 'checked' : '' }}
                                           onchange="document.getElementById('productFilterForm').submit()">
                                    <label class="form-check-label d-flex justify-content-between" for="brand-{{ $brand->id }}">
                                        <span>{{ $brand->name }}</span>
                                        <span class="filter-count">({{ $brand->products_count }})</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapsePrice">
                            Khoảng giá <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapsePrice">
                            <div class="price-inputs">
                                <input type="number" name="min_price" id="minPrice" placeholder="TỪ (đ)" value="{{ $minPrice }}" min="0">
                                <input type="number" name="max_price" id="maxPrice" placeholder="ĐẾN (đ)" value="{{ $maxPrice }}" min="0">
                            </div>
                            <button type="submit" class="btn btn-black w-100 mt-3 btn-sm">ÁP DỤNG GIÁ</button>
                        </div>
                    </div>

                    <!-- Tags Filter -->
                    <div class="filter-group border-0">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseTags">
                            Nhãn <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseTags">
                            <div class="tag-cloud mt-2">
                                <a href="{{ url()->current() }}?{{ $withoutQuery(['tags', 'page']) }}"
                                   class="tag-badge {{ empty($selectedTags) ? 'active' : '' }}">Tất cả</a>
                                @foreach($filterTags as $tag)
                                    <label class="tag-badge {{ in_array((string) $tag->id, $selectedTags) ? 'active' : '' }}">
                                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" class="d-none"
                                               {{ in_array((string) $tag->id, $selectedTags) ? 'checked' : '' }}
                                               onchange="document.getElementById('productFilterForm').submit()">
                                        {{ $tag->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="q" value="{{ $currentSearch ?? '' }}">
                    </form>

                    @if(!empty($selectedCategories) || !empty($selectedGenders) || !empty($selectedBrands) || !empty($selectedTags) || $minPrice || $maxPrice)
                        <a href="{{ url()->current() }}" class="filter-link d-inline-block mt-2" style="font-size:12px;">
                            <i class="bi bi-x-circle me-1"></i> Xóa tất cả bộ lọc
                        </a>
                    @endif
                </div>

                <!-- 2. Main Products Content Column -->
                <div class="col-lg-9">
                    
                    <!-- Products Page Header -->
                    <div class="products-header">
                        <h1 class="products-title">{{ $pageTitle ?? 'TẤT CẢ SẢN PHẨM' }}</h1>
                        @if($products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                            <span class="products-count">
                                Hiển thị {{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}
                                trên tổng {{ $products->total() }} sản phẩm
                            </span>
                        @endif
                    </div>

                    <!-- Toolbar Section -->
                    <div class="toolbar-section">
                        <!-- Mobile Filter Button Trigger -->
                        <button class="btn btn-outline-dark d-block d-lg-none" id="mobileFilterBtn">
                            <i class="bi bi-sliders me-1"></i> BỘ LỌC
                        </button>

                        <!-- Sorting -->
                        <div class="sort-dropdown">
                            <select class="form-select" name="sort" form="productFilterForm"
                                    onchange="document.getElementById('productFilterForm').submit()">
                                <option value="popularity" {{ ($currentSort ?? 'popularity') === 'popularity' ? 'selected' : '' }}>Sắp xếp: Phổ biến</option>
                                <option value="newest" {{ ($currentSort ?? '') === 'newest' ? 'selected' : '' }}>Sắp xếp: Mới nhất</option>
                                <option value="price-low" {{ ($currentSort ?? '') === 'price-low' ? 'selected' : '' }}>Sắp xếp: Giá tăng dần</option>
                                <option value="price-high" {{ ($currentSort ?? '') === 'price-high' ? 'selected' : '' }}>Sắp xếp: Giá giảm dần</option>
                            </select>
                        </div>
                    </div>

                    <!-- Product Cards Grid -->
                    @if($products->total() === 0)
                        <div class="text-center py-5">
                            <i class="bi bi-search" style="font-size:48px; color:#ccc;"></i>
                            <p class="mt-3 mb-1" style="font-size:18px; font-weight:500;">Không tìm thấy sản phẩm nào</p>
                            <p class="text-muted" style="font-size:14px;">
                                @if(isset($q))
                                    Thử tìm với từ khóa khác hoặc
                                @else
                                    Thử điều chỉnh lại bộ lọc hoặc
                                @endif
                                <a href="{{ url('/san-pham') }}">xem tất cả sản phẩm</a>
                            </p>
                        </div>
                    @else
                        @include('partials.product-grid', ['products' => $products, 'cols' => 'col-6 col-md-4'])
                    @endif

                    <!-- Phân trang -->
                    @if($products instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                        @include('partials.pagination', ['paginator' => $products])
                    @else
                        @include('partials.pagination')
                    @endif

                </div>

            </div>
        </div>
    </div>

    <!-- Mobile filters backdrop -->
    <div class="filter-backdrop d-none" id="filterBackdrop"></div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileFilterBtn = document.getElementById('mobileFilterBtn');
        const sidebarFilters = document.getElementById('sidebarFilters');
        const closeFiltersBtn = document.getElementById('closeFiltersBtn');
        const filterBackdrop = document.getElementById('filterBackdrop');
        
        if (mobileFilterBtn && sidebarFilters && closeFiltersBtn && filterBackdrop) {
            mobileFilterBtn.addEventListener('click', function() {
                sidebarFilters.classList.add('show-mobile');
                filterBackdrop.classList.remove('d-none');
            });
            
            closeFiltersBtn.addEventListener('click', function() {
                sidebarFilters.classList.remove('show-mobile');
                filterBackdrop.classList.add('d-none');
            });

            filterBackdrop.addEventListener('click', function() {
                sidebarFilters.classList.remove('show-mobile');
                filterBackdrop.classList.add('d-none');
            });
        }
    });
</script>
@endpush
