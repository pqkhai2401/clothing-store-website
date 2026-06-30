<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    // Trang danh sách sản phẩm yêu thích.
    public function index()
    {
        $userId = Auth::id();

        // Lấy tất cả wishlist items kèm thông tin sản phẩm
        $wishlistItems = Wishlist::where('user_id', $userId)
            ->with([
                'product.category',
                'product.productVariants.color',
                'product.productVariants.size',
            ])
            ->latest()
            ->get();

        // Lấy danh sách product_id trong wishlist để lọc gợi ý AI
        $wishlistProductIds = $wishlistItems->pluck('product.id')->filter();

        // Sản phẩm tương tự: lấy sản phẩm cùng danh mục với các sản phẩm đã yêu thích,
        $recommendedProducts = collect();

        if ($wishlistProductIds->isNotEmpty()) {
            $categoryIds = $wishlistItems
                ->pluck('product.category_id')
                ->filter()
                ->unique()
                ->values();

            $recommendedProducts = Product::whereIn('category_id', $categoryIds)
                ->whereNotIn('id', $wishlistProductIds)
                ->where('status', true)
                ->with('category')
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }

        return view('user.wishlist.index', compact('wishlistItems', 'recommendedProducts'));
    }

    /**
     * Toggle thêm/xóa sản phẩm khỏi wishlist (AJAX).
     * Trả về JSON: { added: bool, count: int }
     */
    public function toggle(int $productId): JsonResponse
    {
        $userId = Auth::id();

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            $added = false;
        } else {
            // Kiểm tra sản phẩm tồn tại
            $product = Product::where('id', $productId)->where('status', true)->first();
            if (!$product) {
                return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
            }

            Wishlist::create([
                'user_id'    => $userId,
                'product_id' => $productId,
            ]);
            $added = true;
        }

        $count = Wishlist::where('user_id', $userId)->count();

        return response()->json([
            'added' => $added,
            'count' => $count,
        ]);
    }

    
     // Xóa một sản phẩm cụ thể khỏi wishlist (AJAX).
    
    public function remove(int $productId): JsonResponse
    {
        $deleted = Wishlist::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->delete();

        $count = Wishlist::where('user_id', Auth::id())->count();

        return response()->json([
            'success' => (bool) $deleted,
            'count'   => $count,
        ]);
    }

    
    // Xóa toàn bộ wishlist của user (AJAX).
    
    public function clear(): JsonResponse
    {
        Wishlist::where('user_id', Auth::id())->delete();

        return response()->json(['success' => true, 'count' => 0]);
    }

    
    // Lấy số lượng sản phẩm trong wishlist (AJAX — dùng để refresh badge).
    
    public function count(): JsonResponse
    {
        $count = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->count()
            : 0;

        return response()->json(['count' => $count]);
    }
}
