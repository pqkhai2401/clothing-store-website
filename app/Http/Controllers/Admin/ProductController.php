<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Gender;
use App\Exports\ProductsExport;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public const LOW_STOCK_THRESHOLD = 10;

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
        $stockStatus      = $request->input('stock_status');
        $sort       = $request->input('sort', 'id');
        $direction  = $request->input('direction', 'desc');
        $perPage    = in_array((int) $request->input('per_page'), [10, 25, 50], true)
            ? (int) $request->input('per_page')
            : 10;

        $query = Product::with(['category', 'brand', 'productVariants.size', 'productVariants.color'])
            ->withSum('productVariants', 'stock')
            ->withMin('productVariants as min_price', 'price')
            ->withMax('productVariants as max_price', 'price')
            ->withMin('productVariants as min_cost_price', 'cost_price')
            ->withMax('productVariants as max_cost_price', 'cost_price');

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

        if ($stockStatus === 'low_stock') {
            // Không dùng having() trên cột alias của withSum() vì Eloquent::count()/paginate()
            // dựng lại câu COUNT(*) bỏ mất cột alias đó, khiến having() luôn lọc rỗng.
            $query->whereRaw(
                '(select coalesce(sum(product_variants.stock), 0) from product_variants where product_variants.product_id = products.id) < ?',
                [self::LOW_STOCK_THRESHOLD]
            );
        }

        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'desc';
        match ($sort) {
            'name' => $query->orderBy('name', $direction),
            'price' => $query->orderByRaw('(select coalesce(min(product_variants.price), 0) from product_variants where product_variants.product_id = products.id) '.$direction),
            'cost_price' => $query->orderByRaw('(select coalesce(min(product_variants.cost_price), 0) from product_variants where product_variants.product_id = products.id) '.$direction),
            'stock' => $query->orderBy('product_variants_sum_stock', $direction),
            'status' => $query->orderBy('status', $direction),
            'is_featured' => $query->orderBy('is_featured', $direction),
            default => $query->orderBy('id', $direction),
        };

        $products   = $query->paginate($perPage)->withQueryString();
        $categories = Category::whereNull('parent_id')->with('childrenCategories')->get();
        $sizes      = Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get();
        $colors     = Color::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.table', compact('products'))->render(),
            ]);
        }

        $totalProducts      = Product::count();
        $activeProducts     = Product::where('status', true)->count();
        $outOfStockProducts = Product::whereRaw(
            '(select coalesce(sum(product_variants.stock), 0) from product_variants where product_variants.product_id = products.id) <= 0'
        )->count();

        return view('admin.products.index', compact(
            'products', 'categories', 'sizes', 'colors', 'brands', 'keyword', 'categoryId', 'parentCategoryId',
            'brandId', 'sizeId', 'colorId', 'status', 'stockStatus', 'perPage',
            'totalProducts', 'activeProducts', 'outOfStockProducts'
        ));
    }

    public function export(Request $request)
    {
        return Excel::download(new ProductsExport($request), 'danh-sach-san-pham-' . now()->format('Ymd-His') . '.xlsx');
    }

    /**
     * Tạo nhanh Product + 1 ProductVariant từ màn hình lập phiếu nhập kho.
     * Sản phẩm tạo ra ở trạng thái ẩn (status=false) và thiếu ảnh/thương hiệu/mô tả,
     * chờ bộ phận quản lý catalog hoàn thiện và công bố (xem update()/toggleStatus()).
     */
    public function quickCreate(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'color_id'    => ['required', 'integer', Rule::exists('colors', 'id')],
            'size_id'     => ['required', 'integer', Rule::exists('sizes', 'id')],
            'cost_price'  => ['required', 'numeric', 'min:0'],
        ], [
            'name.required'        => 'Tên sản phẩm không được để trống.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'color_id.required'    => 'Vui lòng chọn màu sắc.',
            'size_id.required'     => 'Vui lòng chọn kích thước.',
            'cost_price.required'  => 'Vui lòng nhập giá vốn.',
            'cost_price.min'       => 'Giá vốn không được âm.',
        ]);

        $slug = Str::slug($request->input('name'));
        if (Product::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        $costPrice = max(0, (float) $request->input('cost_price'));

        $variant = DB::transaction(function () use ($request, $slug, $costPrice) {
            $product = Product::create([
                'name'        => $request->input('name'),
                'slug'        => $slug,
                'category_id' => $request->input('category_id'),
                'brand_id'    => null,
                'gender'      => Gender::UNISEX->value,
                'description' => null,
                'thumbnail'   => null,
                'is_featured' => false,
                'status'      => false,
            ]);

            return $product->productVariants()->create([
                'color_id'   => $request->input('color_id'),
                'size_id'    => $request->input('size_id'),
                'sku'        => Str::upper(Str::random(10)),
                'cost_price' => $costPrice,
                'price'      => $costPrice,
                'stock'      => 0,
            ])->load(['product:id,name,thumbnail', 'color:id,name,hex_code', 'size:id,name']);
        });

        return response()->json([
            'message' => "Đã tạo nhanh sản phẩm \"{$variant->product->name}\" (chưa công bố).",
            'variant' => [
                'id'           => $variant->id,
                'sku'          => $variant->sku,
                'product_name' => $variant->product?->name,
                'thumbnail'    => $variant->product?->thumbnail,
                'color_name'   => $variant->color?->name,
                'color_hex'    => $variant->color?->display_hex_code,
                'size_name'    => $variant->size?->name,
                'stock'        => $variant->stock,
                'cost_price'   => (float) $variant->cost_price,
            ],
        ], 201);
    }

    /**
     * Gợi ý sản phẩm có tên tương tự, dùng để cảnh báo trùng lặp trước khi Quick Create.
     */
    public function searchSimilar(Request $request)
    {
        $name = trim((string) $request->input('name'));

        if ($name === '') {
            return response()->json(['products' => []]);
        }

        $products = Product::where('name', 'like', "%{$name}%")
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'thumbnail']);

        return response()->json(['products' => $products]);
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
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')],
            'category_id'   => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'      => ['required', 'integer', Rule::exists('brands', 'id')],
            'discount_type'     => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_value'    => ['nullable', 'numeric', 'min:0'],
            'discount_start_at' => ['nullable', 'date'],
            'discount_end_at'   => ['nullable', 'date', 'after_or_equal:discount_start_at'],
            'gender'        => ['required', Rule::in(Gender::values())],
            'description'   => ['required', 'string'],
            'images'        => ['required', 'array', 'min:1'],
            'images.*'      => ['image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'primary_index' => ['nullable', 'integer', 'min:0'],
            'is_featured'   => ['boolean'],
            'status'        => ['boolean'],
        ], [
            'name.required'        => 'Tên sản phẩm không được để trống.',
            'slug.unique'          => 'Slug này đã tồn tại.',
            'category_id.required' => 'Vui lòng chọn danh mục.',
            'brand_id.required'    => 'Vui lòng chọn thương hiệu.',
            'discount_type.in'     => 'Loại giảm giá không hợp lệ.',
            'discount_value.min'   => 'Giá trị giảm không được âm.',
            'discount_end_at.after_or_equal' => 'Ngày kết thúc giảm giá phải sau ngày bắt đầu.',
            'gender.required'      => 'Vui lòng chọn giới tính.',
            'description.required' => 'Mô tả sản phẩm không được để trống.',
            'images.required'      => 'Vui lòng tải lên ít nhất một ảnh sản phẩm.',
            'images.min'           => 'Vui lòng tải lên ít nhất một ảnh sản phẩm.',
            'images.*.image'       => 'File ảnh không hợp lệ.',
            'images.*.max'         => 'Mỗi ảnh không được vượt quá 2MB.',
        ]);

        $slug = $request->filled('slug')
            ? Str::slug($request->input('slug'))
            : Str::slug($request->input('name'));

        if (Product::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . time();
        }

        // Upload tất cả ảnh trước khi mở transaction
        $uploadedPaths  = [];
        $imagePaths     = [];
        foreach ($request->file('images', []) as $file) {
            $path = $file->store('products', 'public');
            $uploadedPaths[] = $path;
            $imagePaths[]    = 'storage/' . $path;
        }

        $primaryIndex = (int) $request->input('primary_index', 0);
        if (!isset($imagePaths[$primaryIndex])) {
            $primaryIndex = 0;
        }

        try {
            $product = DB::transaction(function () use ($request, $slug, $imagePaths, $primaryIndex) {
                $thumbnailPath = $imagePaths[$primaryIndex] ?? null;

                $product = Product::create([
                    'name'        => $request->input('name'),
                    'slug'        => $slug,
                    'category_id' => $request->input('category_id'),
                    'brand_id'    => $request->input('brand_id'),
                    'discount_type'     => $request->filled('discount_value') && (float) $request->input('discount_value') > 0 ? $request->input('discount_type') : null,
                    'discount_value'    => max(0, (float) $request->input('discount_value', 0)),
                    'discount_start_at' => $request->input('discount_start_at'),
                    'discount_end_at'   => $request->input('discount_end_at'),
                    'gender'      => $request->input('gender'),
                    'description' => $request->input('description'),
                    'thumbnail'   => $thumbnailPath,
                    'is_featured' => $request->boolean('is_featured'),
                    'status'      => $request->boolean('status'),
                ]);

                // Lưu các ảnh còn lại (không phải ảnh chính) vào bảng product_images
                foreach ($imagePaths as $index => $imgPath) {
                    if ($index === $primaryIndex) continue;
                    $product->productImages()->create(['image' => $imgPath]);
                }

                // Lưu biến thể: variants[color_id][size_id] = {sku, cost_price, price, stock}
                foreach ($request->input('variants', []) as $colorId => $sizes) {
                    foreach ($sizes as $sizeId => $data) {
                        $sku = trim((string) ($data['sku'] ?? ''));
                        $product->productVariants()->create([
                            'color_id'   => $colorId,
                            'size_id'    => $sizeId,
                            'sku'        => $sku !== '' ? $sku : Str::upper(Str::random(10)),
                            // Tồn & giá vốn do KHO quản lý theo lô (product_batches) — khởi tạo 0,
                            // nhập hàng thực tế qua Phiếu nhập kho để sinh lô + giá vốn.
                            'cost_price' => 0,
                            'price'      => max(0, (float) ($data['price'] ?? $data['sale_price'] ?? 0)),
                            'stock'      => 0,
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

    public function edit(Request $request, string $id)
    {
        $data = $this->editFormData($id);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.products.partials.edit-content', $data)->render(),
            ]);
        }

        return view('admin.products.edit', $data);
    }

    private function editFormData(string $id): array
    {
        $product    = Product::with(['productVariants', 'productImages'])->findOrFail($id);
        $categories = Category::with('childrenCategories')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();
        $genders    = Gender::labels();
        $colors     = Color::orderBy('name')->get();
        $sizes      = Size::where('status', 1)->orderBy('sort_weight')->orderBy('name')->get();

        $existingVariants = $product->productVariants
            ->groupBy('color_id')
            ->map(fn ($variants) => $variants->keyBy('size_id')->map(fn ($v) => [
                'sku'        => $v->sku,
                'cost_price' => (float) $v->cost_price,
                'price'      => (float) $v->price,
                'stock'      => $v->stock,
            ])->toArray())
            ->toArray();

        return compact('product', 'categories', 'brands', 'genders', 'colors', 'sizes', 'existingVariants');
    }

    public function update(Request $request, string $id)
    {
        $product = Product::with('productImages')->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($id)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'brand_id'    => ['required', 'integer', Rule::exists('brands', 'id')],
            'discount_type'     => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_value'    => ['nullable', 'numeric', 'min:0'],
            'discount_start_at' => ['nullable', 'date'],
            'discount_end_at'   => ['nullable', 'date', 'after_or_equal:discount_start_at'],
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
            'discount_type.in'     => 'Loại giảm giá không hợp lệ.',
            'discount_value.min'   => 'Giá trị giảm không được âm.',
            'discount_end_at.after_or_equal' => 'Ngày kết thúc giảm giá phải sau ngày bắt đầu.',
            'gender.required'      => 'Vui lòng chọn giới tính.',
            'description.required' => 'Mô tả sản phẩm không được để trống.',
            'thumbnail.image'      => 'File ảnh chính không hợp lệ.',
            'thumbnail.max'        => 'Ảnh chính không được vượt quá 2MB.',
            'image_2.max'          => 'Ảnh phụ 2 không được vượt quá 2MB.',
            'image_3.max'          => 'Ảnh phụ 3 không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                $request->flash();
                $data = $this->editFormData($id);
                $errorBag = (new \Illuminate\Support\ViewErrorBag())->put('default', $validator->errors());
                $html = view('admin.products.partials.edit-content', $data)
                    ->with('errors', $errorBag)
                    ->render();

                return response()->json(['message' => 'Dữ liệu chưa hợp lệ.', 'html' => $html], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

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

        // Sản phẩm tạo nhanh (Quick Create) từ nhập kho có thể vẫn thiếu ảnh chính.
        // Chỉ Admin (quyền publish-products) được phép công bố khi thông tin còn thiếu.
        $willBeIncomplete = blank($newThumbnail ?? $product->thumbnail)
            || blank($request->input('brand_id'))
            || blank($request->input('description'));

        if ($request->boolean('status') && $willBeIncomplete && !$request->user()->can('publish-products')) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            $message = 'Sản phẩm chưa đủ thông tin (ảnh/thương hiệu/mô tả). Chỉ Admin mới có thể công bố sản phẩm chưa hoàn thiện.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->withErrors(['status' => $message])->withInput();
        }

        try {
            DB::transaction(function () use ($request, $product, $id, $slug, $newThumbnail, $newImage2, $newImage3) {
                $product->update([
                    'name'        => $request->input('name'),
                    'slug'        => $slug,
                    'category_id' => $request->input('category_id'),
                    'brand_id'    => $request->input('brand_id'),
                    'discount_type'     => $request->filled('discount_value') && (float) $request->input('discount_value') > 0 ? $request->input('discount_type') : null,
                    'discount_value'    => max(0, (float) $request->input('discount_value', 0)),
                    'discount_start_at' => $request->input('discount_start_at'),
                    'discount_end_at'   => $request->input('discount_end_at'),
                    'gender'      => $request->input('gender'),
                    'description' => $request->input('description'),
                    'thumbnail'   => $newThumbnail ?? $product->thumbnail,
                    'is_featured' => $request->boolean('is_featured'),
                    'status'      => $request->boolean('status'),
                ]);

                // Cập nhật ảnh phụ: slot 1 → index 0, slot 2 → index 1
                $extraImages = $product->productImages->values();
                
                // Xử lý xoá ảnh phụ nếu có tín hiệu xoá từ client
                foreach ([0 => 'remove_image_2', 1 => 'remove_image_3'] as $idx => $removeField) {
                    if ($request->input($removeField) == 1) {
                        $existing = $extraImages->get($idx);
                        if ($existing) {
                            $filePath = str_replace('storage/', '', $existing->image);
                            Storage::disk('public')->delete($filePath);
                            $existing->delete();
                        }
                    }
                }

                // Cập nhật hoặc thêm mới ảnh phụ
                foreach ([0 => $newImage2, 1 => $newImage3] as $idx => $newPath) {
                    if ($newPath === null) continue;
                    $existing = $extraImages->get($idx);
                    if ($existing) {
                        // Xoá file cũ nếu thay thế bằng file mới
                        $filePath = str_replace('storage/', '', $existing->image);
                        Storage::disk('public')->delete($filePath);
                        $existing->update(['image' => $newPath]);
                    } else {
                        $product->productImages()->create(['image' => $newPath]);
                    }
                }

                // Sync biến thể: variants[color_id][size_id] = {sku, cost_price, price, stock}
                $keep = [];
                foreach ($request->input('variants', []) as $colorId => $sizes) {
                    foreach ($sizes as $sizeId => $data) {
                        $sku = trim((string) ($data['sku'] ?? ''));
                        $variant = ProductVariant::firstOrNew(
                            ['product_id' => $product->id, 'color_id' => $colorId, 'size_id' => $sizeId]
                        );
                        $variant->sku    = $sku !== '' ? $sku : ($variant->sku ?: Str::upper(Str::random(10)));
                        $variant->price  = max(0, (float) ($data['price'] ?? $data['sale_price'] ?? 0));
                        $variant->status = 'Active'; // Kích hoạt lại hoạt động nếu trước đó bị ẩn

                        // Tồn & giá vốn do KHO quản lý theo lô (product_batches) — KHÔNG ghi đè từ form.
                        // Chỉ khởi tạo 0 cho biến thể mới; thêm hàng thực tế qua Phiếu nhập kho.
                        if (! $variant->exists) {
                            $variant->stock = 0;
                            $variant->cost_price = 0;
                        }
                        $variant->save();
                        $keep[] = $variant->id;
                    }
                }

                // Xử lý các biến thể bị bỏ chọn (không nằm trong danh sách keep)
                $variantsToRemove = $product->productVariants()->whereNotIn('id', $keep)->get();
                foreach ($variantsToRemove as $v) {
                    // Kiểm tra xem biến thể có nằm trong đơn hàng nào không
                    $hasOrders = DB::table('order_items')->where('product_variant_id', $v->id)->exists();
                    if ($hasOrders) {
                        // Nếu có đơn hàng, chuyển trạng thái thành Inactive để bảo toàn lịch sử hóa đơn tài chính
                        $v->update(['status' => 'Inactive']);
                    } else {
                        // Nếu chưa có đơn hàng nào, tiến hành xóa hoàn toàn khỏi DB
                        $v->delete();
                    }
                }
            });
        } catch (\Throwable $e) {
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        $message = "Cập nhật sản phẩm \"{$product->name}\" thành công.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('admin.products.list')->with('success', $message);
    }

    public function toggleStatus(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $newStatus = !$product->status;

        if ($newStatus && $product->isIncomplete() && !$request->user()->can('publish-products')) {
            $message = 'Sản phẩm chưa đủ thông tin (ảnh/thương hiệu/mô tả). Chỉ Admin mới có thể công bố sản phẩm chưa hoàn thiện.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return back()->with('error', $message);
        }

        $product->update(['status' => $newStatus]);

        $msg = $newStatus
            ? "Sản phẩm \"{$product->name}\" đã được hiển thị."
            : "Sản phẩm \"{$product->name}\" đã được ẩn khỏi website.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['status' => $newStatus, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function toggleFeatured(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $newFeatured = !$product->is_featured;
        $product->update(['is_featured' => $newFeatured]);

        $msg = $newFeatured
            ? "Đã đánh dấu \"{$product->name}\" là sản phẩm nổi bật."
            : "Đã bỏ đánh dấu nổi bật cho \"{$product->name}\".";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['is_featured' => $newFeatured, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    public function quickUpdate(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'discount_type'     => ['nullable', Rule::in(['percent', 'fixed'])],
            'discount_value'    => ['nullable', 'numeric', 'min:0'],
            'discount_start_at' => ['nullable', 'date'],
            'discount_end_at'   => ['nullable', 'date', 'after_or_equal:discount_start_at'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $data = $validator->validated();
        $discountValue = max(0, (float) ($data['discount_value'] ?? 0));

        $product->update([
            'discount_type' => $discountValue > 0 ? ($data['discount_type'] ?? 'percent') : null,
            'discount_value' => $discountValue,
            'discount_start_at' => $data['discount_start_at'] ?? null,
            'discount_end_at' => $data['discount_end_at'] ?? null,
        ]);

        return response()->json([
            'message' => "Cập nhật chương trình giảm giá sản phẩm \"{$product->name}\" thành công.",
            'discount_type' => $product->discount_type,
            'discount_value' => (float) $product->discount_value,
        ]);
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
        $products = Product::onlyTrashed()->whereIn('id', $ids)->get();
        $blockers = $products
            ->map(fn (Product $product) => $this->productForceDeleteBlocker($product))
            ->filter();

        if ($blockers->isNotEmpty()) {
            return back()->with('error', $blockers->first());
        }

        $count = 0;
        foreach ($products as $product) {
            $count += (int) $product->forceDelete();
        }
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

        $query = Product::onlyTrashed()->with(['category', 'brand', 'productVariants'])->orderBy('deleted_at', 'desc');
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
        $product = Product::onlyTrashed()->findOrFail($id);

        if ($message = $this->productForceDeleteBlocker($product)) {
            return redirect()->route('admin.products.trash')
                ->with('error', $message);
        }

        $product->forceDelete();

        return redirect()->route('admin.products.trash')->with('success', 'Xóa vĩnh viễn sản phẩm thành công');
    }

    private function productForceDeleteBlocker(Product $product): ?string
    {
        if ($product->productVariants()
            ->whereHas('orderItems')
            ->exists()) {
            return 'Không thể xóa vĩnh viễn sản phẩm này vì đã phát sinh đơn hàng. Hãy giữ sản phẩm trong thùng rác để bảo toàn lịch sử đơn hàng.';
        }

        return null;
    }
}

