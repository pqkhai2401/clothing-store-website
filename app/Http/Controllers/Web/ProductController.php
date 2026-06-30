<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductView;
use App\Models\Wishlist;
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
            ->with(['category', 'brand', 'productImages', 'productVariants.color', 'productVariants.size'])
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

        // Lấy 4 sản phẩm liên quan cùng danh mục
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', true)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        // Kiểm tra sản phẩm có trong wishlist của user không (dùng cho nút ❤️ real-time)
        $isInWishlist = Auth::check()
            ? Wishlist::where('user_id', Auth::id())->where('product_id', $product->id)->exists()
            : false;

        return view('user.products.show', compact(
            'product',
            'colors',
            'sizes',
            'defaultVariant',
            'relatedProducts',
            'isInWishlist'
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
            ->first();

        if (!$variant) {
            return response()->json([
                'found' => false,
                'message' => 'Biến thể không tồn tại.',
            ], 404);
        }

        // Lấy thông tin giá từ bảng products (giá chung cho sản phẩm)
        $product = Product::findOrFail($request->product_id);

        return response()->json([
            'found'       => true,
            'stock'       => $variant->stock,
            'sku'         => $variant->sku,
            'price'       => $product->price,
            'discount'    => $product->discount,
            'final_price' => $product->final_price,
            'image'       => $variant->image,
        ]);
    }

    //Trang danh sách sp
    public function index(Request $request)
    {
        $query = Product::where('status', true);

        // Lọc theo gender nếu có
        $gender = $request->query('gender');
        if ($gender === 'men') {
            $query->whereIn('gender', ['men', 'unisex']);
        } elseif ($gender === 'women') {
            $query->whereIn('gender', ['women', 'unisex']);
        }

        $products = $query->with('category')
            ->latest()
            ->paginate(12)
            ->appends($request->query());

        $pageTitle = 'Tất cả sản phẩm';

        return view('user.products.index', compact('products', 'pageTitle'));
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

        // Lọc theo giới tính 
        $gender = $request->query('gender');
        if ($gender === 'men') {
            $query->whereIn('gender', ['men', 'unisex']);
        } elseif ($gender === 'women') {
            $query->whereIn('gender', ['women', 'unisex']);
        }

        // Sắp xếp theo mới nhất + phân trang 12 sản phẩm/trang
        $products = $query->with('category')
            ->latest()
            ->paginate(12)
            ->appends($request->query());

        // Xác định tiêu đề trang hiển thị
        $pageTitle = $category->name;
        if ($gender === 'men') {
            $pageTitle .= ' Nam';
        } elseif ($gender === 'women') {
            $pageTitle .= ' Nữ';
        }

        return view('user.products.index', [
            'products'    => $products,
            'category'    => $category,
            'pageTitle'   => $pageTitle,
            'currentSlug' => $slug,
            'gender'      => $gender,
        ]);
    }
}
