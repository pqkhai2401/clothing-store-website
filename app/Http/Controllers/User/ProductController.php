<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductView;
use App\Models\Review;
use App\Models\Tag;
use App\Models\Wishlist;
use App\Models\Collection as ProductCollection;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    
     //Trang chi tiết sản phẩm
    public function show(string $slug)
    {
        // Tìm sản phẩm theo slug, kèm theo ảnh và biến thể (với color, size)
        $product = Product::where('slug', $slug)
            ->where('status', true)
            ->with([
                'category',
                'brand',
                'productImages',
                'productVariants' => fn ($q) => $q->where('status', 'Active'),
                'productVariants.color',
                'productVariants.size',
            ])
            ->firstOrFail();

        // Tăng lượt xem cho mọi lượt truy cập
        $product->increment('views_count');

        // Chỉ ghi nhận hành vi xem sản phẩm vào bảng product_views khi user đã đăng nhập.
        if (Auth::check()) {
            $userId = Auth::id();

            // Khoảng thời gian tối thiểu giữa 2 lần ghi nhận (đơn vị: phút)
            $antiSpamMinutes = 10;

            // Tìm bản ghi xem gần nhất của user này cho sản phẩm này
            $lastView = ProductView::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->orderBy('viewed_at', 'desc')
                ->first();

            // Kiểm tra điều kiện ghi nhận:
            // - $lastView === null: User chưa từng xem sản phẩm này -> ghi nhận lần đầu.
            // - Carbon::parse($lastView->viewed_at)->diffInMinutes(now()) >= 10:
            //   Lần xem gần nhất đã cách đây >= 10 phút -> đây là lượt xem hợp lệ, ghi nhận.
            $shouldRecord = !$lastView
                || Carbon::parse($lastView->viewed_at)->diffInMinutes(now()) >= $antiSpamMinutes;

            if ($shouldRecord) {
                // Tạo bản ghi mới trong bảng product_views
                // với viewed_at = thời điểm hiện tại, phục vụ cho việc
                // phân tích xu hướng xem sản phẩm theo thời gian của hệ thống AI.
                ProductView::create([
                    'user_id'    => $userId,
                    'product_id' => $product->id,
                    'viewed_at'  => now(),
                ]);
            }
        }

        // Lấy danh sách Màu sắc duy nhất từ các biến thể thực tế của sản phẩm
        $colors = $product->productVariants
            ->pluck('color')
            ->unique('id')
            ->values();

        // Lấy danh sách Size duy nhất từ các biến thể thực tế của sản phẩm
        $sizes = $product->productVariants
            ->pluck('size')
            ->unique('id')
            ->values();

        // Biến thể mặc định (đầu tiên) để hiển thị SKU và giá ban đầu
        $defaultVariant = $product->productVariants->first();

        // Lấy 4 sản phẩm TƯƠNG TỰ (cùng danh mục) bằng thuật toán Content-based filtering.
        $relatedProducts = \App\Services\RecommendationService::getSimilarProducts($product, 4);

        // Lấy 4 sản phẩm PHỐI CÙNG (Mix & Match) do Cloud AI đóng vai Stylist gợi ý:
        // khác công năng, không xung đột phong cách, ưu tiên cùng Bộ sưu tập/mùa.
        // AI lỗi -> service tự fallback về sản phẩm tương tự.
        $mixAndMatchProducts = app(\App\Services\AiStylistService::class)->getMixAndMatch($product, 4);

        // Kiểm tra sản phẩm có trong wishlist của user không (dùng cho nút ❤️ real-time)
        $isInWishlist = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists()
            : false;

        // ============================================================
        //  DỮ LIỆU CHO KHU VỰC ĐÁNH GIÁ SẢN PHẨM
        // ============================================================

        // Danh sách đánh giá đã được AI DUYỆT (approved) -> hiển thị công khai.
        $reviews = Review::approved()
            ->where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->get();

        // Mặc định: khách vãng lai / chưa đăng nhập thì không được đánh giá.
        $canReview     = false;   // Đủ điều kiện viết đánh giá hay không.
        $hasReviewed   = false;   // User đã từng đánh giá sản phẩm này chưa.
        $averageRating = round((float) $reviews->avg('rating'), 1); // Điểm trung bình.

        if (Auth::check()) {
            $userId = Auth::id();

            // Điều kiện đánh giá: có đơn hàng ở trạng thái "completed" chứa sản phẩm này.
            // (order_items -> product_variant -> product_id).
            $hasCompletedPurchase = Order::where('user_id', $userId)
                ->where('status', OrderStatus::COMPLETED->value)
                ->whereHas('orderItems.productVariant', function ($query) use ($product) {
                    $query->where('product_id', $product->id);
                })
                ->exists();

            // Kiểm tra user đã đánh giá sản phẩm này chưa (chống hiển thị form trùng).
            $hasReviewed = Review::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->exists();

            // Chỉ cho phép đánh giá khi: đã mua & hoàn thành, và CHƯA từng đánh giá.
            $canReview = $hasCompletedPurchase && !$hasReviewed;
        }

        return view('user.products.show', compact(
            'product',
            'colors',
            'sizes',
            'defaultVariant',
            'relatedProducts',
            'mixAndMatchProducts',
            'isInWishlist',
            'reviews',
            'canReview',
            'hasReviewed',
            'averageRating'
        ));
    }

   //kiểm tra biến thể sản phẩm theo cặp Màu + Size
    public function checkVariant(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'color_id'   => 'required|integer|exists:colors,id',
            'size_id'    => 'required|integer|exists:sizes,id',
        ]);

        // Tìm biến thể khớp chính xác với product_id + color_id + size_id
        $variant = ProductVariant::where('product_id', $request->product_id)
            ->where('color_id', $request->color_id)
            ->where('size_id', $request->size_id)
            ->where('status', 'Active')
            ->first();

        if (!$variant) {
            return response()->json([
                'found' => false,
                'message' => 'Biến thể không tồn tại.',
            ], 404);
        }

        // Lấy thông tin giá từ bảng products (giá chung cho sản phẩm)
        $product = Product::findOrFail($request->product_id);

        $price = (float) $variant->price;
        $finalPrice = $product->discountedPrice($price);

        return response()->json([
            'found'       => true,
            'stock'       => $variant->stock,
            'sku'         => $variant->sku,
            'price'       => $price,
            'discount_type' => $product->discount_type,
            'discount_value' => (float) $product->discount_value,
            'has_discount' => $product->hasActiveDiscount(),
            'final_price' => $finalPrice,
            'image'       => $variant->image,
        ]);
    }

    //Trang danh sách sp
    public function index(Request $request)
    {
        $query = Product::where('status', true);

        // Xác định tiêu đề và thứ tự sắp xếp mặc định theo route
        $routeName = $request->route()?->getName();
        $sort      = $request->query('sort', '');

        if ($routeName === 'new-arrivals') {
            $pageTitle = 'Hàng mới về';
            if ($sort === '') $sort = 'newest';
        } elseif ($routeName === 'collections') {
            $query->where('is_featured', true);
            $pageTitle = 'Bộ sưu tập';
            if ($sort === '') $sort = 'newest';
        } else {
            $pageTitle = 'Tất cả sản phẩm';
            if ($sort === '') $sort = 'popularity';
        }

        $this->applyFilters($query, $request);
        $this->applySort($query, $sort);

        $products = $query->with(['category', 'productVariants'])
            ->paginate(12)
            ->appends($request->query());

        return view('user.products.index', array_merge(
            compact('products', 'pageTitle'),
            $this->filterViewData($request)
        ));
    }

    // Tìm kiếm sản phẩm theo từ khóa
    public function search(Request $request)
    {
        $q = trim($request->query('q', ''));

        $query = Product::where('status', true);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhereHas('category', fn ($c) => $c->where('name', 'LIKE', "%{$q}%"));
            });

            // Ghi nhận lịch sử tìm kiếm cho người dùng đã đăng nhập phục vụ AI gợi ý
            if (Auth::check()) {
                \App\Models\SearchHistory::create([
                    'user_id' => Auth::id(),
                    'keyword' => $q,
                ]);
            }
        }

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->query('sort', 'popularity'));

        $products = $query->with(['category', 'productVariants'])
            ->paginate(12)
            ->appends($request->query());

        $pageTitle = $q !== ''
            ? "Kết quả cho: \"{$q}\""
            : 'Tìm kiếm sản phẩm';

        return view('user.products.index', array_merge(
            compact('products', 'pageTitle', 'q'),
            $this->filterViewData($request)
        ));
    }

    /**
     * Áp dụng các bộ lọc sidebar (category, gender, brand, tags, khoảng giá)
     * dùng chung cho index(), search() và getProductsByCategory().
     */
    private function applyFilters($query, Request $request): void
    {
        // Category (multi-select, theo slug)
        $categorySlugs = array_filter((array) $request->query('category', []));
        if (!empty($categorySlugs)) {
            $categoryIds = Category::whereIn('slug', $categorySlugs)->pluck('id');
            $query->whereIn('category_id', $categoryIds);
        }

        // Gender (multi-select; vẫn tương thích với link cũ ?gender=men dạng chuỗi đơn)
        $genders = array_filter((array) $request->query('gender', []));
        if (!empty($genders)) {
            $query->where(function ($sub) use ($genders) {
                foreach ($genders as $g) {
                    if ($g === 'men') {
                        $sub->orWhereIn('gender', ['men', 'unisex']);
                    } elseif ($g === 'women') {
                        $sub->orWhereIn('gender', ['women', 'unisex']);
                    } elseif ($g === 'unisex') {
                        $sub->orWhere('gender', 'unisex');
                    }
                }
            });
        }

        // Brand (multi-select, theo id)
        $brandIds = array_filter((array) $request->query('brand', []));
        if (!empty($brandIds)) {
            $query->whereIn('brand_id', $brandIds);
        }

        // Tags (multi-select, theo id)
        $tagIds = array_filter((array) $request->query('tags', []));
        if (!empty($tagIds)) {
            $query->whereHas('tags', fn ($t) => $t->whereIn('tags.id', $tagIds));
        }

        // Khoảng giá (VNĐ)
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        if (is_numeric($minPrice)) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('price', '>=', (float) $minPrice));
        }
        if (is_numeric($maxPrice)) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('price', '<=', (float) $maxPrice));
        }
    }

    // Áp dụng sắp xếp dùng chung
    private function applySort($query, string $sort): void
    {
        switch ($sort) {
            case 'newest':
                $query->latest();
                break;
            case 'price-low':
                $query->orderByRaw('(select coalesce(min(product_variants.price), 0) from product_variants where product_variants.product_id = products.id) asc');
                break;
            case 'price-high':
                $query->orderByRaw('(select coalesce(max(product_variants.price), 0) from product_variants where product_variants.product_id = products.id) desc');
                break;
            case 'best-selling':
            case 'popularity':
            default:
                $query->orderBy('views_count', 'desc');
                break;
        }
    }

    // Dữ liệu cho sidebar filter (danh mục, thương hiệu, tag + giá trị đang chọn)
    private function filterViewData(Request $request): array
    {
        $categories = Category::whereNotNull('parent_id')
            ->where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)])
            ->orderBy('name')
            ->get();

        $brands = Brand::where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)])
            ->orderBy('name')
            ->get();

        $tags = Tag::orderBy('name')->get();

        return [
            'filterCategories'   => $categories,
            'filterBrands'       => $brands,
            'filterTags'         => $tags,
            'selectedCategories' => array_filter((array) $request->query('category', [])),
            'selectedGenders'    => array_filter((array) $request->query('gender', [])),
            'selectedBrands'     => array_filter((array) $request->query('brand', [])),
            'selectedTags'       => array_filter((array) $request->query('tags', [])),
            'minPrice'           => $request->query('min_price'),
            'maxPrice'           => $request->query('max_price'),
            'currentSort'        => $request->query('sort', 'popularity'),
            'currentSearch'      => $request->query('q', ''),
        ];
    }

    // Gợi ý tìm kiếm nhanh (AJAX autocomplete)
    public function suggestions(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $items = Product::where('status', true)
            ->where('name', 'LIKE', "%{$q}%")
            ->with(['category', 'productVariants'])
            ->latest()
            ->limit(6)
            ->get(['id', 'name', 'slug', 'discount_type', 'discount_value', 'discount_start_at', 'discount_end_at', 'thumbnail', 'category_id']);

        return response()->json($items->map(fn ($p) => [
            'name'     => $p->name,
            'url'      => url('/san-pham/' . $p->slug),
            'price'    => $p->min_variant_price !== null ? 'Từ ' . number_format($p->min_variant_price, 0, ',', '.') . 'đ' : 'Liên hệ',
            'final'    => $p->hasActiveDiscount() && $p->min_final_price !== null
                            ? 'Từ ' . number_format($p->min_final_price, 0, ',', '.') . 'đ'
                            : null,
            'category' => $p->category->name ?? '',
            'image'    => $p->thumbnail,
            'discount' => $p->hasActiveDiscount() ? (float) $p->discount_value : 0,
        ]));
    }

    // Hiển thị danh sách sản phẩm theo danh mục (slug) và giới tính (gender)
    public function getProductsByCategory(Request $request, string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $childrenIds = $category->childrenCategories()->pluck('id');

        if ($childrenIds->isNotEmpty()) {
            // Đây là danh mục CHA -> lấy sản phẩm của tất cả danh mục con
            $categoryIds = $childrenIds;
        } else {
            // Đây là danh mục CON -> chỉ lấy sản phẩm của chính nó
            $categoryIds = collect([$category->id]);
        }

        // lọc theo danh mục + trạng thái active
        $query = Product::whereIn('category_id', $categoryIds)
            ->where('status', true);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->query('sort', 'popularity'));

        // Phân trang 12 sản phẩm/trang
        $products = $query->with(['category', 'productVariants'])
            ->paginate(12)
            ->appends($request->query());

        // Xác định tiêu đề trang hiển thị
        $gender    = $request->query('gender');
        $pageTitle = $category->name;
        if ($gender === 'men') {
            $pageTitle .= ' Nam';
        } elseif ($gender === 'women') {
            $pageTitle .= ' Nữ';
        }

        return view('user.products.index', array_merge($this->filterViewData($request), [
            'products'    => $products,
            'pageTitle'   => $pageTitle,
            'gender'      => $gender,
        ]));
    }

    /**
     * Hiển thị trang chi tiết một bộ sưu tập theo mùa (slug) + lọc theo giới tính.
     */
    public function getProductsByCollection(Request $request, string $slug)
    {
        $collection = ProductCollection::where('slug', $slug)
            ->where('status', true)
            ->firstOrFail();

        // Lấy sản phẩm thuộc bộ sưu tập (bảng pivot collection_product), chỉ hàng đang active
        $query = $collection->products()
            ->where('products.status', true);

        $this->applyFilters($query, $request);
        $this->applySort($query, $request->query('sort', 'popularity'));

        $products = $query->with('category')
            ->paginate(12)
            ->appends($request->query());

        $gender = $request->query('gender');

        return view('user.collections.show', compact('collection', 'products', 'gender'));
    }
}