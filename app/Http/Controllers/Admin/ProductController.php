<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

    public function edit(string $id)
    {
        $product    = Product::findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $genders    = Gender::labels();

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'genders'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'    => ['required', 'integer', Rule::exists('brands', 'id')],
            'price'       => ['required', 'numeric', 'min:0'],
            'discount'    => ['required', 'integer', 'min:0', 'max:100'],
            'gender'      => ['required', Rule::in(Gender::values())],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'is_featured' => ['boolean'],
            'status'      => ['boolean'],
        ], [
            'name.required'        => 'Tên sản phẩm không được để trống.',
            'slug.unique'          => 'Slug này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'category_id.exists'   => 'Danh mục không hợp lệ.',
            'brand_id.required'    => 'Vui lòng chọn thương hiệu.',
            'brand_id.exists'      => 'Thương hiệu không hợp lệ.',
            'price.required'       => 'Giá sản phẩm không được để trống.',
            'price.min'            => 'Giá sản phẩm không được âm.',
            'discount.min'         => 'Giảm giá không được âm.',
            'discount.max'         => 'Giảm giá không được vượt quá 100%.',
            'gender.required'      => 'Vui lòng chọn giới tính.',
            'gender.in'            => 'Giới tính không hợp lệ.',
            'description.required' => 'Mô tả sản phẩm không được để trống.',
            'thumbnail.image'      => 'File tải lên phải là hình ảnh.',
            'thumbnail.max'        => 'Ảnh không được vượt quá 3MB.',
        ]);

        // Tạo slug: dùng slug nhập tay (nếu có) hoặc sinh từ tên
        $slug = $validated['slug']
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        if (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . $id;
        }

        // Xử lý ảnh thumbnail: chỉ cập nhật nếu admin upload ảnh mới
        $thumbnailPath = $product->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('products', 'public');
            $thumbnailPath = 'storage/' . $thumbnailPath;
        }

        $product->update([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'category_id' => $validated['category_id'],
            'brand_id'    => $validated['brand_id'],
            'price'       => $validated['price'],
            'discount'    => $validated['discount'],
            'gender'      => $validated['gender'],
            'description' => $validated['description'],
            'thumbnail'   => $thumbnailPath,
            'is_featured' => $request->boolean('is_featured'),
            'status'      => $request->boolean('status'),
        ]);

        return redirect()->route('admin.products.list')
            ->with('success', "Cập nhật sản phẩm \"{$product->name}\" thành công.");
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.list')->with('success', 'Xóa sản phẩm thành công');
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('keyword'));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Product::onlyTrashed()->with('category')->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate($perPage)->appends($request->except('page'));

        return view('admin.products.trash', compact('products', 'keyword', 'perPage'));
    }

    public function restore(string $id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.products.trash')->with('success', 'Khôi phục sản phẩm thành công');
    }

    public function forceDelete(string $id)
    {
        Product::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.products.trash')->with('success', 'Xóa vĩnh viễn sản phẩm thành công');
    }
}
