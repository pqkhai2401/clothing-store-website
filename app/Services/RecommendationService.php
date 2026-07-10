<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\ProductView;
use App\Models\SearchHistory;
use App\Models\Wishlist;
use App\Models\Cart;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Thuật toán Content-Based Filtering: Gợi ý các sản phẩm tương tự một sản phẩm cho trước.
     *
     * @param Product $product Sản phẩm gốc
     * @param int $limit Số lượng gợi ý tối đa
     * @return Collection
     */
    public static function getSimilarProducts(Product $product, int $limit = 4): Collection
    {
        $targetProductId = $product->id;
        $targetCategoryId = $product->category_id;
        $targetParentId = $product->category->parent_id ?? null;
        $targetBrandId = $product->brand_id;
        $targetGender = $product->gender;
        $product->loadMissing('productVariants');
        $targetPrice = (float) ($product->min_variant_price ?? 0);
        $targetTagIds = $product->tags->pluck('id')->toArray();

        // Lấy tất cả các sản phẩm đang bán (trừ sản phẩm hiện tại)
        $candidates = Product::where('status', true)
            ->where('id', '!=', $targetProductId)
            ->with(['category', 'brand', 'tags', 'productVariants'])
            ->get();

        return $candidates->map(function ($candidate) use (
            $targetCategoryId,
            $targetParentId,
            $targetBrandId,
            $targetGender,
            $targetPrice,
            $targetTagIds
        ) {
            $score = 0.0;

            // 1. Trọng số cùng Danh mục (Category)
            if ($candidate->category_id == $targetCategoryId) {
                $score += 5.0;
            } elseif ($targetParentId && $candidate->category->parent_id == $targetParentId) {
                // Cùng danh mục cha
                $score += 2.5;
            }

            // 2. Trọng số cùng Giới tính (Gender)
            if ($candidate->gender == $targetGender) {
                $score += 3.0;
            } elseif ($candidate->gender == 'unisex' || $targetGender == 'unisex') {
                $score += 1.5;
            }

            // 3. Trọng số cùng Thương hiệu (Brand)
            if ($candidate->brand_id == $targetBrandId) {
                $score += 2.0;
            }

            // // 4. Trọng số giao thoa Thẻ từ khóa (Tags Similarity)
            // if (!empty($targetTagIds)) {
            //     $candidateTagIds = $candidate->tags->pluck('id')->toArray();
            //     $sharedTagsCount = count(array_intersect($targetTagIds, $candidateTagIds));
            //     $score += $sharedTagsCount * 1.5;
            // }

            // 5. Trọng số khoảng cách Giá (Price Proximity)
            $candidatePrice = (float) ($candidate->min_variant_price ?? 0);
            $priceDiff = abs($targetPrice - $candidatePrice);
            $priceSum = $targetPrice + $candidatePrice + 1; // +1 để tránh chia cho 0
            $priceProximity = 1.0 - ($priceDiff / $priceSum);
            $score += $priceProximity * 2.0;

            $candidate->similarity_score = $score;
            return $candidate;
        })
        ->sortByDesc('similarity_score')
        ->take($limit)
        ->values();
    }

    /**
     * Thuật toán Rule-Based & Behavior-Based Recommendation: Gợi ý cá nhân hóa dựa trên hành vi.
     *
     * @param User|null $user Người dùng hiện tại
     * @param int $limit Số lượng gợi ý tối đa
     * @return Collection
     */
    public static function getPersonalizedRecommendations(?User $user, int $limit = 8): Collection
    {
        // 1. Cơ chế Fallback cho Khách vãng lai (Guest) hoặc người dùng mới chưa đăng nhập
        if (!$user) {
            return self::getPopularProducts($limit);
        }

        $userId = $user->id;

        // Tập hợp các ID sản phẩm cần LOẠI TRỪ (Đã có trong Cart, Wishlist hoặc đã mua)
        $excludeProductIds = collect();

        // A. Thu thập Lịch sử mua hàng (Purchases) - Trọng số cao nhất
        $purchasedCategoryIds = collect();
        $purchasedBrandIds = collect();
        $purchasedGenders = collect();

        $orders = Order::where('user_id', $userId)
            ->where('status', 'Completed') // chỉ tính đơn hoàn thành
            ->with('orderItems.productVariant.product')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderItems as $item) {
                $product = $item->productVariant->product ?? null;
                if ($product) {
                    $excludeProductIds->push($product->id);
                    $purchasedCategoryIds->push($product->category_id);
                    $purchasedBrandIds->push($product->brand_id);
                    $purchasedGenders->push($product->gender);
                }
            }
        }

        // B. Thu thập Giỏ hàng hiện tại (Cart)
        $cart = Cart::where('user_id', $userId)->first();
        $cartCategoryIds = collect();
        $cartBrandIds = collect();

        if ($cart) {
            $cartItems = $cart->cartItems()->with('productVariant.product')->get();
            foreach ($cartItems as $item) {
                $product = $item->productVariant->product ?? null;
                if ($product) {
                    $excludeProductIds->push($product->id);
                    $cartCategoryIds->push($product->category_id);
                    $cartBrandIds->push($product->brand_id);
                }
            }
        }

        // C. Thu thập Danh sách yêu thích (Wishlist)
        $wishlist = Wishlist::where('user_id', $userId)->with('product')->get();
        $wishlistCategoryIds = collect();
        $wishlistBrandIds = collect();

        foreach ($wishlist as $item) {
            if ($item->product) {
                $excludeProductIds->push($item->product_id);
                $wishlistCategoryIds->push($item->product->category_id);
                $wishlistBrandIds->push($item->product->brand_id);
            }
        }

        // D. Thu thập Lịch sử xem sản phẩm (Views) trong 30 ngày qua
        $recentViews = ProductView::where('user_id', $userId)
            ->where('viewed_at', '>=', Carbon::now()->subDays(30))
            ->with('product')
            ->get();

        $viewedCategoryIds = collect();
        $viewedBrandIds = collect();
        $viewedGenders = collect();

        foreach ($recentViews as $view) {
            if ($view->product) {
                $viewedCategoryIds->push($view->product->category_id);
                $viewedBrandIds->push($view->product->brand_id);
                $viewedGenders->push($view->product->gender);
            }
        }

        // E. Thu thập Lịch sử tìm kiếm (Search History) - 5 từ khóa gần nhất
        $searchKeywords = SearchHistory::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->pluck('keyword')
            ->toArray();

        // 2. Xây dựng Hồ sơ Sở thích (User Profile Interest Score)
        $categoryScores = [];
        $brandScores = [];

        // Cộng điểm Danh mục
        self::addScores($categoryScores, $purchasedCategoryIds, 5.0);
        self::addScores($categoryScores, $cartCategoryIds, 4.0);
        self::addScores($categoryScores, $wishlistCategoryIds, 3.0);
        self::addScores($categoryScores, $viewedCategoryIds, 2.0);

        // Cộng điểm Thương hiệu
        self::addScores($brandScores, $purchasedBrandIds, 3.0);
        self::addScores($brandScores, $cartBrandIds, 2.0);
        self::addScores($brandScores, $wishlistBrandIds, 1.5);
        self::addScores($brandScores, $viewedBrandIds, 1.0);

        // Xác định giới tính ưa thích (Gender preference)
        $allViewedGenders = $purchasedGenders->merge($viewedGenders)->filter();
        $genderCounts = $allViewedGenders->countBy();
        $preferredGender = null;
        if ($genderCounts->isNotEmpty()) {
            $preferredGender = $genderCounts->sortDesc()->keys()->first();
        }

        // Nếu người dùng hoàn toàn chưa có tương tác (Cold Start) -> Chuyển sang popular
        if (empty($categoryScores) && empty($brandScores) && !$preferredGender && empty($searchKeywords)) {
            return self::getPopularProducts($limit);
        }

        // 3. Truy vấn các ứng viên sản phẩm khả dụng (Không nằm trong exclude list)
        $excludeProductIds = $excludeProductIds->unique()->toArray();
        
        $candidates = Product::where('status', true)
            ->whereNotIn('id', $excludeProductIds)
            ->with(['category', 'brand'])
            ->get();

        // 4. Chấm điểm sản phẩm ứng viên
        $recommended = $candidates->map(function ($product) use (
            $categoryScores,
            $brandScores,
            $preferredGender,
            $searchKeywords
        ) {
            $score = 0.0;

            // Điểm danh mục
            if (isset($categoryScores[$product->category_id])) {
                $score += $categoryScores[$product->category_id];
            }

            // Điểm thương hiệu
            if (isset($brandScores[$product->brand_id])) {
                $score += $brandScores[$product->brand_id];
            }

            // Điểm giới tính
            if ($preferredGender && $product->gender == $preferredGender) {
                $score += 3.0;
            } elseif ($product->gender == 'unisex' || $preferredGender == 'unisex') {
                $score += 1.5;
            }

            // Điểm trùng khớp Từ khóa tìm kiếm gần đây
            if (!empty($searchKeywords)) {
                $productName = mb_strtolower($product->name);
                $categoryName = mb_strtolower($product->category->name ?? '');

                foreach ($searchKeywords as $keyword) {
                    $kw = mb_strtolower($keyword);
                    if (str_contains($productName, $kw) || str_contains($categoryName, $kw)) {
                        $score += 4.0;
                    }
                }
            }

            // Một chút điểm cộng dựa trên views_count để ưu tiên sản phẩm hot khi điểm bằng nhau
            $score += $product->views_count * 0.001;

            $product->recommendation_score = $score;
            return $product;
        })
        ->filter(fn ($p) => $p->recommendation_score > 0) // Chỉ gợi ý sản phẩm có điểm tương tác > 0
        ->sortByDesc('recommendation_score')
        ->take($limit)
        ->values();

        // 5. Backfill: Nếu thiếu sản phẩm gợi ý cá nhân hóa thì bù đắp bằng sản phẩm phổ biến
        if ($recommended->count() < $limit) {
            $alreadyRecommendedIds = $recommended->pluck('id')->toArray();
            $needed = $limit - $recommended->count();

            $backfills = Product::where('status', true)
                ->whereNotIn('id', array_merge($excludeProductIds, $alreadyRecommendedIds))
                ->orderBy('views_count', 'desc')
                ->limit($needed)
                ->get();

            $recommended = $recommended->concat($backfills);
        }

        return $recommended;
    }

    /**
     * Helper: Tính điểm lũy tiến vào mảng điểm số.
     */
    private static function addScores(array &$scoreArray, Collection $ids, float $weight): void
    {
        $counts = $ids->countBy();
        foreach ($counts as $id => $count) {
            if (!isset($scoreArray[$id])) {
                $scoreArray[$id] = 0.0;
            }
            // Điểm cộng dồn = trọng số hành vi * log(số lần tương tác + 1) để tránh đột biến quá lớn
            $scoreArray[$id] += $weight * log($count + 1);
        }
    }

    /**
     * Fallback: Lấy các sản phẩm phổ biến nhất (nhiều lượt xem và đánh giá).
     */
    public static function getPopularProducts(int $limit = 8): Collection
    {
        return Product::where('status', true)
            ->with(['category', 'brand'])
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
