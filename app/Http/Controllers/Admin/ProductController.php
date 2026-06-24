<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $keyword    = trim((string) $request->input('keyword'));
        $categoryId = $request->input('category_id');
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Product::with(['category'])
            ->orderBy('id', 'desc');

        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products   = $query->paginate($perPage)->appends($request->except('page'));
        $categories = Category::whereNull('parent_id')->with('childrenCategories')->get();

        return view('admin.products.list', compact('products', 'categories', 'keyword', 'categoryId', 'perPage'));
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.list')->with('success', 'Xóa sản phẩm thành công');
    }
}
