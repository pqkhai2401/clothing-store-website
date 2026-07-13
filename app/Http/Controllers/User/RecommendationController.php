<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\AiStylistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller ví dụ minh họa cách gọi AiStylistService (Hệ gợi ý lai + Cloud AI).
 *
 * Lưu ý: Service được TIÊM QUA CONSTRUCTOR (Dependency Injection) — Laravel tự
 * khởi tạo AiStylistService nhờ Service Container, nên không cần `new` thủ công.
 */
class RecommendationController extends Controller
{
    public function __construct(
        protected AiStylistService $stylist
    ) {
    }

    /* -------------------------------------------------------------------------
     |  KỊCH BẢN 4: Trang chủ — Cold Start (khách mới) / cá nhân hóa (khách cũ)
     * ------------------------------------------------------------------------- */

    /**
     * Trả dữ liệu gợi ý ra View Blade cho trang chủ.
     */
    public function home()
    {
        // Auth::user() = null nếu là khách vãng lai -> Service tự xử lý nhánh Cold Start theo mùa.
        $recommendedProducts = $this->stylist->getHomepageRecommendations(Auth::user(), 8);

        // $recommendedProducts là Collection<Product> thật, dùng ngay trong Blade
        // như mọi danh sách sản phẩm khác (name, thumbnail, final_price...).
        return view('user.home.index', compact('recommendedProducts'));
    }

    /* -------------------------------------------------------------------------
     |  KỊCH BẢN 1 + 2 + 3: Phối đồ (Mix & Match) theo một sản phẩm neo
     * ------------------------------------------------------------------------- */

    /**
     * Hiển thị khối "Phối cùng phong cách" trong trang chi tiết sản phẩm.
     */
    public function matchForProduct(Product $product)
    {
        $matchingProducts = $this->stylist->getMixAndMatch($product, 4);

        return view('user.products.partials.mix-and-match', [
            'anchor'   => $product,
            'products' => $matchingProducts,
        ]);
    }

    /**
     * Phiên bản AJAX: gọi khi khách vừa thêm sản phẩm vào giỏ, trả JSON gọn
     * để front-end render nhanh khối "Gợi ý phối cùng" mà không tải lại trang.
     */
    public function matchAjax(Request $request, Product $product): JsonResponse
    {
        $matchingProducts = $this->stylist->getMixAndMatch($product, 4);

        return response()->json([
            'anchor' => $product->only(['id', 'name']),
            'items'  => $matchingProducts->map(fn (Product $p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'price' => (float) $p->price,
                'thumbnail' => $p->thumbnail,
                'url'   => route('products.show', $p->slug ?? $p->id),
            ])->values(),
        ]);
    }
}
