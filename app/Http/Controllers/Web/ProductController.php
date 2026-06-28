<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
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
