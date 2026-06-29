<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search           = trim((string) $request->input('search', $request->input('keyword')));
        $keyword          = $search;
        $categoryId       = $request->input('category_id');
        $parentCategoryId = $request->input('parent_category_id');
        $brandId          = $request->input('brand_id');
        $sizeId           = $request->input('size_id');
        $colorId          = $request->input('color_id');
        $status           = $request->input('status');
        $sort       = $request->input('sort', 'id');
        $direction  = $request->input('direction', 'desc');
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Product::with(['category', 'brand', 'productVariants.size', 'productVariants.color'])
            ->withSum('productVariants', 'stock');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', "%{$search}%"));
            });
        }

        if ($parentCategoryId) {
            $childIds = Category::where('parent_id', $parentCategoryId)->pluck('id');
            $allIds = $childIds->push((int) $parentCategoryId)->unique()->values();
            $query->whereIn('category_id', $allIds);
        } elseif ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($brandId) {
            $query->where('brand_id', $brandId);
        }

        if ($sizeId) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('size_id', $sizeId));
        }

        if ($colorId) {
            $query->whereHas('productVariants', fn ($variantQuery) => $variantQuery->where('color_id', $colorId));
        }

        if (in_array($status, ['0', '1'], true)) {
            $query->where('status', (bool) (int) $status);
        }

        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
        match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'price' => $query->orderBy('price', $direction),
            'stock' => $query->orderBy('product_variants_sum_stock', $direction),
            'status' => $query->orderBy('status', $direction),
            default => $query->orderBy('id', $direction),
        };

        $products   = $query->paginate($perPage)->withQueryString();
        $categories = Category::whereNull('parent_id')->with('childrenCategories')->get();
        $sizes      = Size::orderBy('name')->get();
        $colors     = Color::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.table', compact('products'))->render(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories', 'sizes', 'colors', 'keyword', 'categoryId', 'parentCategoryId', 'brandId', 'sizeId', 'colorId', 'status', 'perPage'));
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

    public function toggleStatus(string $id)
    {
        $product = Product::findOrFail($id);
        $newStatus = !$product->status;
        $product->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Sản phẩm \"{$product->name}\" đã được hiển thị."
            : "Sản phẩm \"{$product->name}\" đã được ẩn khỏi website.";

        return back()->with('success', $msg);
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect()->route('admin.products.list')->with('success', 'Xóa sản phẩm thành công');
    }

    public function bulkRestore(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một sản phẩm.');
        }
        $restored = Product::onlyTrashed()->whereIn('id', $ids)->restore();
        return back()->with('success', "Đã khôi phục {$restored} sản phẩm thành công.");
    }

    public function bulkForceDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một sản phẩm.');
        }
        $count = Product::onlyTrashed()->whereIn('id', $ids)->count();
        Product::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return back()->with('success', "Đã xóa vĩnh viễn {$count} sản phẩm.");
    }

    public function bulkDelete(Request $request)
    {
        $ids = array_filter((array) $request->input('ids', []), 'is_numeric');

        if (empty($ids)) {
            return back()->with('error', 'Vui lòng chọn ít nhất một sản phẩm để xóa.');
        }

        $deleted = Product::whereIn('id', $ids)->delete();

        return back()->with('success', "Đã xóa {$deleted} sản phẩm thành công.");
    }

    public function trash(Request $request)
    {
        $keyword = trim((string) $request->input('search', $request->input('keyword')));
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page') : 10;

        $query = Product::onlyTrashed()->with(['category', 'brand'])->orderBy('deleted_at', 'desc');
        if ($keyword !== '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $products = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.trash-table', compact('products'))->render(),
            ]);
        }

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
