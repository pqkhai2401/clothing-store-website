<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        $sizes      = Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get();
        $colors     = Color::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.table', compact('products'))->render(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories', 'sizes', 'colors', 'keyword', 'categoryId', 'parentCategoryId', 'brandId', 'sizeId', 'colorId', 'status', 'perPage'));
    }

    public function create()
    {
        $categories     = Category::with('childrenCategories')->orderBy('name')->get();
        $brands         = Brand::orderBy('name')->get();
        $genders        = Gender::labels();
        $colors         = Color::orderBy('name')->get();
        $sizes          = Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get();
        $existingVariants = [];

        return view('admin.products.create', compact(
            'categories', 'brands', 'genders', 'colors', 'sizes', 'existingVariants'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'    => ['required', 'integer', Rule::exists('brands', 'id')],
            'price'       => ['required', 'numeric', 'min:0'],
            'discount'    => ['required', 'integer', 'min:0', 'max:100'],
            'gender'      => ['required', Rule::in(Gender::values())],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_2'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_3'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_featured' => ['boolean'],
            'status'      => ['boolean'],
        ], [
            'name.required'        => 'Tên sản phẩm không được để trống.',
            'slug.unique'          => 'Slug này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'brand_id.required'    => 'Vui lòng chọn thương hiệu.',
            'price.required'       => 'Giá sản phẩm không được để trống.',
            'discount.min'         => 'Giảm giá không được âm.',
            'discount.max'         => 'Giảm giá không được vượt quá 100%.',
            'gender.required'      => 'Vui lòng chọn giới tính.',
            'description.required' => 'Mô tả sản phẩm không được để trống.',
            'thumbnail.image'      => 'File ảnh chính không hợp lệ.',
            'thumbnail.max'        => 'Ảnh chính không được vượt quá 2MB.',
            'image_2.image'        => 'Ảnh phụ 2 không hợp lệ.',
            'image_2.max'          => 'Ảnh phụ 2 không được vượt quá 2MB.',
            'image_3.image'        => 'Ảnh phụ 3 không hợp lệ.',
            'image_3.max'          => 'Ảnh phụ 3 không được vượt quá 2MB.',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name'));

        if (Product::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        // Upload tất cả ảnh trước khi mở transaction
        $uploadedPaths = [];
        $storeImage = function (string $field) use ($request, &$uploadedPaths): ?string {
            if (!$request->hasFile($field)) return null;
            $path = $request->file($field)->store('products', 'public');
            $uploadedPaths[] = $path;
            return 'storage/' . $path;
        };

        $thumbnailPath = $storeImage('thumbnail');
        $image2Path    = $storeImage('image_2');
        $image3Path    = $storeImage('image_3');

        try {
            $product = DB::transaction(function () use ($request, $slug, $thumbnailPath, $image2Path, $image3Path) {
                $product = Product::create([
                    'name'        => $request->input('name'),
                    'slug'        => $slug,
                    'category_id' => $request->input('category_id'),
                    'brand_id'    => $request->input('brand_id'),
                    'price'       => $request->input('price'),
                    'discount'    => $request->input('discount'),
                    'gender'      => $request->input('gender'),
                    'description' => $request->input('description'),
                    'thumbnail'   => $thumbnailPath,
                    'is_featured' => $request->boolean('is_featured'),
                    'status'      => $request->boolean('status'),
                ]);

                // Lưu ảnh phụ vào bảng product_images nếu có
                foreach (array_filter([$image2Path, $image3Path]) as $imgPath) {
                    $product->productImages()->create(['image' => $imgPath]);
                }

                // Lưu biến thể: variants[color_id][size_id] = stock
                foreach ($request->input('variants', []) as $colorId => $sizes) {
                    foreach ($sizes as $sizeId => $stock) {
                        $product->productVariants()->create([
                            'color_id' => $colorId,
                            'size_id'  => $sizeId,
                            'stock'    => max(0, (int) $stock),
                        ]);
                    }
                }

                return $product;
            });
        } catch (\Throwable $e) {
            // Xóa ảnh đã upload nếu DB thất bại
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return redirect()->route('admin.products.list')
            ->with('success', "Thêm sản phẩm \"{$product->name}\" thành công.");
    }

    public function edit(string $id)
    {
        $product    = Product::with(['productVariants', 'productImages'])->findOrFail($id);
        $categories = Category::with('childrenCategories')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $genders    = Gender::labels();
        $colors     = Color::orderBy('name')->get();
        $sizes      = Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get();

        $existingVariants = $product->productVariants
            ->groupBy('color_id')
            ->map(fn ($variants) => $variants->pluck('stock', 'size_id')->toArray())
            ->toArray();

        return view('admin.products.edit', compact(
            'product', 'categories', 'brands', 'genders',
            'colors', 'sizes', 'existingVariants'
        ));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::with('productImages')->findOrFail($id);

        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'    => ['required', 'integer', Rule::exists('brands', 'id')],
            'price'       => ['required', 'numeric', 'min:0'],
            'discount'    => ['required', 'integer', 'min:0', 'max:100'],
            'gender'      => ['required', Rule::in(Gender::values())],
            'description' => ['required', 'string'],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_2'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'image_3'     => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'is_featured' => ['boolean'],
            'status'      => ['boolean'],
        ], [
            'name.required'        => 'Tên sản phẩm không được để trống.',
            'slug.unique'          => 'Slug này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'brand_id.required'    => 'Vui lòng chọn thương hiệu.',
            'price.required'       => 'Giá sản phẩm không được để trống.',
            'discount.min'         => 'Giảm giá không được âm.',
            'discount.max'         => 'Giảm giá không được vượt quá 100%.',
            'gender.required'      => 'Vui lòng chọn giới tính.',
            'description.required' => 'Mô tả sản phẩm không được để trống.',
            'thumbnail.image'      => 'File ảnh chính không hợp lệ.',
            'thumbnail.max'        => 'Ảnh chính không được vượt quá 2MB.',
            'image_2.max'          => 'Ảnh phụ 2 không được vượt quá 2MB.',
            'image_3.max'          => 'Ảnh phụ 3 không được vượt quá 2MB.',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name'));

        if (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
            $slug = $slug . '-' . $id;
        }

        // Upload ảnh mới (nếu có) trước transaction
        $uploadedPaths = [];
        $storeImage = function (string $field) use ($request, &$uploadedPaths): ?string {
            if (!$request->hasFile($field)) return null;
            $path = $request->file($field)->store('products', 'public');
            $uploadedPaths[] = $path;
            return 'storage/' . $path;
        };

        $newThumbnail = $storeImage('thumbnail');
        $newImage2    = $storeImage('image_2');
        $newImage3    = $storeImage('image_3');

        try {
            DB::transaction(function () use ($request, $product, $id, $slug, $newThumbnail, $newImage2, $newImage3) {
                $product->update([
                    'name'        => $request->input('name'),
                    'slug'        => $slug,
                    'category_id' => $request->input('category_id'),
                    'brand_id'    => $request->input('brand_id'),
                    'price'       => $request->input('price'),
                    'discount'    => $request->input('discount'),
                    'gender'      => $request->input('gender'),
                    'description' => $request->input('description'),
                    'thumbnail'   => $newThumbnail ?? $product->thumbnail,
                    'is_featured' => $request->boolean('is_featured'),
                    'status'      => $request->boolean('status'),
                ]);

                // Cập nhật ảnh phụ: slot 1 → index 0, slot 2 → index 1
                $extraImages = $product->productImages->values();
                foreach ([0 => $newImage2, 1 => $newImage3] as $idx => $newPath) {
                    if ($newPath === null) continue;
                    $existing = $extraImages->get($idx);
                    if ($existing) {
                        $existing->update(['image' => $newPath]);
                    } else {
                        $product->productImages()->create(['image' => $newPath]);
                    }
                }

                // Sync biến thể
                $keep = [];
                foreach ($request->input('variants', []) as $colorId => $sizes) {
                    foreach ($sizes as $sizeId => $stock) {
                        $variant = ProductVariant::updateOrCreate(
                            ['product_id' => $product->id, 'color_id' => $colorId, 'size_id' => $sizeId],
                            ['stock' => max(0, (int) $stock)]
                        );
                        $keep[] = $variant->id;
                    }
                }
                $product->productVariants()->whereNotIn('id', $keep)->delete();
            });
        } catch (\Throwable $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

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
