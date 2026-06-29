<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Trang chi tiết sản phẩm: /san-pham/{slug}
     *
     * - Tìm sản phẩm theo slug, eager load biến thể kèm quan hệ color và size.
     * - Trích xuất danh sách Màu sắc duy nhất và Size duy nhất mà sản phẩm đang có.
     * - Lấy biến thể đầu tiên làm biến thể mặc định (hiển thị SKU, giá ban đầu).
     * - Query 4 sản phẩm liên quan cùng danh mục, loại trừ sản phẩm hiện tại.
     */
    public function show(string $slug)
    {
        // Tìm sản phẩm theo slug, kèm theo ảnh và biến thể (với color, size)
        $product = Product::where('slug', $slug)
            ->where('status', true)
            ->with(['category', 'brand', 'productImages', 'productVariants.color', 'productVariants.size'])
            ->firstOrFail();

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

        // Lấy 4 sản phẩm liên quan cùng danh mục, loại trừ sản phẩm hiện tại
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->where('status', true)
            ->with('category')
            ->latest()
            ->limit(4)
            ->get();

        return view('user.products.show', compact(
            'product',
            'colors',
            'sizes',
            'defaultVariant',
            'relatedProducts'
        ));
    }

    /**
     * API kiểm tra biến thể sản phẩm theo cặp Màu + Size.
     *
     * Nhận vào product_id, color_id, size_id.
     * Trả về JSON chứa: stock, sku, price, discount, final_price.
     */
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

    /**
     * Trang danh sách tất cả sản phẩm: /products
     */
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

    /**
     * Hiển thị danh sách sản phẩm theo danh mục (slug) và giới tính (gender).
     *
     * Logic xử lý:
     * - Nếu slug thuộc danh mục CHA (có con): lấy tất cả sản phẩm của các danh mục con.
     * - Nếu slug thuộc danh mục CON (không có con): lấy sản phẩm của chính danh mục đó.
     * - Lọc theo gender: nếu gender=men -> lấy sản phẩm 'men' + 'unisex'.
     *                     nếu gender=women -> lấy sản phẩm 'women' + 'unisex'.
     * - Chỉ lấy sản phẩm đang hoạt động (status = 1).
     * - Sắp xếp theo mới nhất, phân trang 12 sản phẩm/trang.
     */
    public function getProductsByCategory(Request $request, string $slug)
    {
        // Tìm danh mục theo slug, nếu không tồn tại -> 404
        $category = Category::where('slug', $slug)->firstOrFail();

        // Xác định danh sách category_id cần truy vấn
        $childrenIds = $category->childrenCategories()->pluck('id');

        if ($childrenIds->isNotEmpty()) {
            // Đây là danh mục CHA -> lấy sản phẩm của tất cả danh mục con
            $categoryIds = $childrenIds;
        } else {
            // Đây là danh mục CON -> chỉ lấy sản phẩm của chính nó
            $categoryIds = collect([$category->id]);
        }

        // Khởi tạo query: lọc theo danh mục + trạng thái active
        $query = Product::whereIn('category_id', $categoryIds)
            ->where('status', true);

        // Lọc theo giới tính (nếu có tham số gender trên URL)
        $gender = $request->query('gender');
        if ($gender === 'men') {
            $query->whereIn('gender', ['men', 'unisex']);
        } elseif ($gender === 'women') {
            $query->whereIn('gender', ['women', 'unisex']);
        }

        // Sắp xếp theo mới nhất + phân trang 12 sản phẩm/trang
        // appends() giữ lại các query string (gender) khi chuyển trang
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
