<?php

namespace App\Http\Controllers\User;

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

        // Lấy tất cả product_id trong wishlist trước (dùng cho AI gợi ý và chip tổng)
        $allWishlistRows = Wishlist::where('user_id', $userId)
            ->with(['product:id,category_id'])
            ->latest()
            ->get();

        $wishlistProductIds = $allWishlistRows->pluck('product.id')->filter();
        $totalCount         = $allWishlistRows->count();

        // Phân trang: tối đa 10 sản phẩm mỗi trang
        $wishlistItems = Wishlist::where('user_id', $userId)
            ->with([
                'product.category',
                'product.productVariants.color',
                'product.productVariants.size',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // AI gợi ý: sản phẩm cùng danh mục, loại trừ đã có trong wishlist
        $recommendedProducts = collect();

        if ($wishlistProductIds->isNotEmpty()) {
            $categoryIds = $allWishlistRows
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

        return view('user.wishlist.index', compact(
            'wishlistItems',
            'recommendedProducts',
            'totalCount'
        ));
    }

    /**
     * Thêm sản phẩm vào wishlist nếu chưa có (không xóa nếu đã tồn tại).
     * Trả về JSON: { added: bool, already: bool, count: int }
     */
    public function add(int $productId): JsonResponse
    {
        $userId = Auth::id();

        $exists = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            return response()->json([
                'added'   => false,
                'already' => true,
                'count'   => Wishlist::where('user_id', $userId)->count(),
            ]);
        }

        $product = Product::where('id', $productId)->where('status', true)->first();
        if (!$product) {
            return response()->json(['message' => 'Sản phẩm không tồn tại.'], 404);
        }

        Wishlist::create([
            'user_id'    => $userId,
            'product_id' => $productId,
        ]);

        return response()->json([
            'added'   => true,
            'already' => false,
            'count'   => Wishlist::where('user_id', $userId)->count(),
        ]);
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