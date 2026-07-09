<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ với dữ liệu động và gợi ý AI.
     */
    public function index(Request $request)
    {
        // 1. Lấy 4 bộ sưu tập theo mùa
        $collections = Collection::where('status', true)->limit(4)->get();

        // 2. Lấy danh sách sản phẩm mới về (New Arrivals)
        $newArrivals = Product::where('status', true)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        // 3. Lấy danh sách sản phẩm bán chạy nhất (Best Sellers) dựa trên lượt xem cao
        $bestSellers = Product::where('status', true)
            ->with('category')
            ->orderBy('views_count', 'desc')
            ->limit(4)
            ->get();

        // 4. Lấy danh sách sản phẩm đang thịnh hành (Trending) từ sản phẩm nổi bật (featured)
        $trendingNow = Product::where('status', true)
            ->where('is_featured', true)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        // 5. Gọi AI Engine để lấy danh sách gợi ý cá nhân hóa cho người dùng hiện tại
        $recommendedProducts = RecommendationService::getPersonalizedRecommendations(Auth::user(), 8);

        return view('user.home.index', compact(
            'collections',
            'newArrivals',
            'bestSellers',
            'trendingNow',
            'recommendedProducts'
        ));
    }
}
