<?php

namespace App\Http\Controllers\User;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\AiStylistService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        protected AiStylistService $stylist
    ) {
    }

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

        // 3. Lấy danh sách sản phẩm bán chạy nhất (Best Sellers) dựa trên số lượng đã bán thực tế
        $bestSellers = $this->buildBestSellers(4);

        // 4. Lấy danh sách sản phẩm đang thịnh hành (Trending) từ sản phẩm nổi bật (featured)
        $trendingNow = Product::where('status', true)
            ->where('is_featured', true)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        // 5. Gọi Cloud AI (Gemini) để gợi ý cá nhân hóa / Cold Start theo mùa.
        //    Guest -> tự bốc BST theo tháng hiện tại; user cũ -> đọc lịch sử hành vi.
        //    Nếu AI lỗi, service tự fallback về thuật toán SQL nên không sợ trắng trang.
        $recommendedProducts = $this->stylist->getHomepageRecommendations(Auth::user(), 8);

        return view('user.home.index', compact(
            'collections',
            'newArrivals',
            'bestSellers',
            'trendingNow',
            'recommendedProducts'
        ));
    }

    /**
     * Lấy $limit sản phẩm bán chạy nhất dựa trên tổng số lượng đã bán (order_items.quantity),
     * tính trên các đơn hàng chưa bị hủy. Nếu chưa đủ số lượng, lấp đầy bằng sản phẩm mới nhất.
     */
    private function buildBestSellers(int $limit)
    {
        $soldQtyByProductId = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('orders.status', '!=', OrderStatus::CANCELLED->value)
            ->where('products.status', true)
            ->selectRaw('products.id as product_id, SUM(order_items.quantity) as sold_qty')
            ->groupBy('products.id')
            ->orderByDesc('sold_qty')
            ->limit($limit)
            ->pluck('sold_qty', 'product_id');

        $bestSellers = Product::whereIn('id', $soldQtyByProductId->keys())
            ->with('category')
            ->get()
            ->sortByDesc(fn (Product $product) => $soldQtyByProductId[$product->id] ?? 0)
            ->values();

        if ($bestSellers->count() < $limit) {
            $fillCount = $limit - $bestSellers->count();

            $fillers = Product::where('status', true)
                ->whereNotIn('id', $bestSellers->pluck('id'))
                ->with('category')
                ->latest()
                ->limit($fillCount)
                ->get();

            $bestSellers = $bestSellers->concat($fillers)->values();
        }

        return $bestSellers;
    }
}
