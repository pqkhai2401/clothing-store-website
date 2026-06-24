@extends('layouts.app')

@section('title', 'Products | NOIR Premium Store')

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
        'items' => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Products', 'active' => true]
        ]
    ])

    <!-- Products Page Body -->
    <div class="py-5">
        <div class="container-fluid px-lg-5">
            <div class="row">
                
                <!-- 1. Sidebar Filters Column -->
                <div class="col-lg-3 sidebar-filters-col" id="sidebarFilters">
                    <!-- Close button on mobile -->
                    <div class="sidebar-close-btn d-none text-end mb-4">
                        <button class="btn btn-sm btn-outline-dark" id="closeFiltersBtn">CLOSE <i class="bi bi-x"></i></button>
                    </div>

                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseCategory">
                            Category <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseCategory">
                            <ul class="filter-list">
                                <li>
                                    <a href="#" class="filter-link active">All <span class="filter-count">(36)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Shirts <span class="filter-count">(10)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Outerwear <span class="filter-count">(8)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Denim <span class="filter-count">(6)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Blazers <span class="filter-count">(4)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Knitwear <span class="filter-count">(5)</span></a>
                                </li>
                                <li>
                                    <a href="#" class="filter-link">Dresses <span class="filter-count">(3)</span></a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Gender Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseGender">
                            Gender <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseGender">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="men" id="genderMen">
                                <label class="form-check-label" for="genderMen">Men</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="women" id="genderWomen">
                                <label class="form-check-label" for="genderWomen">Women</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="unisex" id="genderUnisex">
                                <label class="form-check-label" for="genderUnisex">Unisex</label>
                            </div>
                        </div>
                    </div>

                    <!-- Brand Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseBrand">
                            Brand <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseBrand">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="noir" id="brandNoir">
                                <label class="form-check-label" for="brandNoir">NOIR</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="cos" id="brandCos">
                                <label class="form-check-label" for="brandCos">COS</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="zara" id="brandZara">
                                <label class="form-check-label" for="brandZara">Zara</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="massimo" id="brandMassimo">
                                <label class="form-check-label" for="brandMassimo">Massimo Dutti</label>
                            </div>
                        </div>
                    </div>

                    <!-- Price Range Filter -->
                    <div class="filter-group">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapsePrice">
                            Price Range <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapsePrice">
                            <div class="price-inputs">
                                <input type="number" id="minPrice" placeholder="MIN ($)">
                                <input type="number" id="maxPrice" placeholder="MAX ($)">
                            </div>
                            <button class="btn btn-black w-100 mt-3 btn-sm">APPLY PRICE</button>
                        </div>
                    </div>

                    <!-- Tags Filter -->
                    <div class="filter-group border-0">
                        <h4 class="filter-title" data-bs-toggle="collapse" data-bs-target="#collapseTags">
                            Tags <i class="bi bi-chevron-down"></i>
                        </h4>
                        <div class="collapse show" id="collapseTags">
                            <div class="tag-cloud mt-2">
                                <span class="tag-badge active">All</span>
                                <span class="tag-badge">Cotton</span>
                                <span class="tag-badge">Wool</span>
                                <span class="tag-badge">Linen</span>
                                <span class="tag-badge">Sustainable</span>
                                <span class="tag-badge">Summer</span>
                                <span class="tag-badge">Winter</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Main Products Content Column -->
                <div class="col-lg-9">
                    
                    <!-- Products Page Header -->
                    <div class="products-header">
                        <h1 class="products-title">ALL PRODUCTS</h1>
                        <span class="products-count">Showing 1-9 of 36 products</span>
                    </div>

                    <!-- Toolbar Section -->
                    <div class="toolbar-section">
                        <!-- Mobile Filter Button Trigger -->
                        <button class="btn btn-outline-dark d-block d-lg-none" id="mobileFilterBtn">
                            <i class="bi bi-sliders me-1"></i> FILTERS
                        </button>

                        <!-- Search Bar -->
                        <div class="search-bar-inline d-none d-md-block">
                            <form action="#" method="GET" class="position-relative">
                                <input type="text" name="search" class="form-control" placeholder="Search products...">
                                <button type="submit" class="border-0 bg-transparent position-absolute end-0 top-0 mt-2 me-3 text-muted">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>
                        </div>

                        <!-- Sorting -->
                        <div class="sort-dropdown">
                            <select class="form-select">
                                <option value="popularity">Sort By: Popularity</option>
                                <option value="newest">Sort By: Newest</option>
                                <option value="price-low">Sort By: Price (Low to High)</option>
                                <option value="price-high">Sort By: Price (High to Low)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Inline Search Bar on Mobile -->
                    <div class="search-bar-inline d-block d-md-none mb-4">
                        <form action="#" method="GET" class="position-relative">
                            <input type="text" name="search" class="form-control" placeholder="Search products...">
                            <button type="submit" class="border-0 bg-transparent position-absolute end-0 top-0 mt-2 me-3 text-muted">
                                    <i class="bi bi-search"></i>
                            </button>
                        </form>
                    </div>

                    <!-- Product Cards Grid -->
                    @php
                        $catalogProducts = [
                            ['id' => 1, 'name' => 'Premium Oversized Trench', 'category' => 'Outerwear', 'price' => 2450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?q=80&w=600&auto=format&fit=crop', 'slug' => 'premium-oversized-trench', 'badge' => 'NEW'],
                            ['id' => 2, 'name' => 'Structured Cotton Shirt', 'category' => 'Shirts', 'price' => 890000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?q=80&w=600&auto=format&fit=crop', 'slug' => 'structured-cotton-shirt'],
                            ['id' => 3, 'name' => 'Classic Straight Jeans', 'category' => 'Denim', 'price' => 1200000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1541099649105-f69ad21f3246?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-straight-jeans'],
                            ['id' => 4, 'name' => 'Tailored Wool Blazer', 'category' => 'Blazers', 'price' => 1950000, 'discount' => 20, 'image' => 'https://images.unsplash.com/photo-1614975058789-41316d0e2e9c?q=80&w=600&auto=format&fit=crop', 'slug' => 'tailored-wool-blazer'],
                            ['id' => 5, 'name' => 'Organic Linen Dress', 'category' => 'Dresses', 'price' => 1650000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=600&auto=format&fit=crop', 'slug' => 'organic-linen-dress'],
                            ['id' => 6, 'name' => 'Relaxed Wool Cardigan', 'category' => 'Knitwear', 'price' => 1450000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1578587018452-892bacefd3f2?q=80&w=600&auto=format&fit=crop', 'slug' => 'relaxed-wool-cardigan'],
                            ['id' => 7, 'name' => 'Leather Crossbody Bag', 'category' => 'Bags', 'price' => 1800000, 'discount' => 15, 'image' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?q=80&w=600&auto=format&fit=crop', 'slug' => 'leather-crossbody-bag'],
                            ['id' => 8, 'name' => 'Minimalist Knit Sweater', 'category' => 'Knitwear', 'price' => 980000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1544441893-675973e31985?q=80&w=600&auto=format&fit=crop', 'slug' => 'minimalist-knit-sweater'],
                            ['id' => 9, 'name' => 'Classic Crewneck Tee', 'category' => 'Basics', 'price' => 350000, 'discount' => 0, 'image' => 'https://images.unsplash.com/photo-1618354691373-d851c5c3a990?q=80&w=600&auto=format&fit=crop', 'slug' => 'classic-crewneck-tee'],
                        ];
                    @endphp
                    @include('partials.product-grid', ['products' => $catalogProducts, 'cols' => 'col-6 col-md-4'])

                    <!-- Pagination -->
                    @include('partials.pagination')

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
