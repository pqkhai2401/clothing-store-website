<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\SizeController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReferenceDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_force_delete_is_blocked_when_products_exist(): void
    {
        [$category] = $this->createProductGraph('category-force');
        $category->delete();

        app(CategoryController::class)->forceDelete((string) $category->id);

        $this->assertNotNull(Category::onlyTrashed()->find($category->id));
        $this->assertNotNull(session('error'));
    }

    public function test_brand_bulk_force_delete_checks_each_brand(): void
    {
        [, $blockedBrand] = $this->createProductGraph('brand-bulk-force');
        $freeBrand = Brand::create(['name' => 'Free Brand', 'status' => true]);
        $blockedBrand->delete();
        $freeBrand->delete();

        app(BrandController::class)->bulkForceDelete(Request::create('/admin/brands/trash/bulk-force-delete', 'POST', [
            'ids' => [$blockedBrand->id, $freeBrand->id],
        ]));

        $this->assertNotNull(Brand::onlyTrashed()->find($blockedBrand->id));
        $this->assertNotNull(Brand::onlyTrashed()->find($freeBrand->id));
        $this->assertNotNull(session('error'));
    }

    public function test_color_destroy_and_force_delete_are_blocked_when_variants_exist(): void
    {
        [, , $color] = $this->createProductGraph('color-delete');

        app(ColorController::class)->destroy((string) $color->id);
        $this->assertFalse($color->fresh()->trashed());

        $color->delete();
        app(ColorController::class)->forceDelete((string) $color->id);

        $this->assertNotNull(Color::onlyTrashed()->find($color->id));
        $this->assertNotNull(session('error'));
    }

    public function test_size_bulk_force_delete_checks_each_size(): void
    {
        [, , , $blockedSize] = $this->createProductGraph('size-bulk-force');
        $freeSize = Size::create(['name' => 'Free Size', 'status' => 1]);
        $blockedSize->delete();
        $freeSize->delete();

        app(SizeController::class)->bulkForceDelete(Request::create('/admin/sizes/trash/bulk-force-delete', 'POST', [
            'ids' => [$blockedSize->id, $freeSize->id],
        ]));

        $this->assertNotNull(Size::onlyTrashed()->find($blockedSize->id));
        $this->assertNotNull(Size::onlyTrashed()->find($freeSize->id));
        $this->assertNotNull(session('error'));
    }

    private function createProductGraph(string $slug): array
    {
        $category = Category::create([
            'name' => 'Category ' . $slug,
            'slug' => 'category-' . $slug,
            'status' => true,
        ]);
        $brand = Brand::create([
            'name' => 'Brand ' . $slug,
            'status' => true,
        ]);
        $color = Color::create([
            'name' => 'Color ' . $slug,
            'hex_code' => '#000000',
            'status' => true,
        ]);
        $size = Size::create([
            'name' => 'Size ' . $slug,
            'status' => 1,
        ]);
        $product = Product::create([
            'name' => 'Product ' . $slug,
            'slug' => 'product-' . $slug,
            'description' => 'Test description',
            'price' => 100000,
            'cost_price' => 50000,
            'discount' => 0,
            'thumbnail' => 'test.jpg',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'gender' => Gender::UNISEX->value,
            'views_count' => 0,
            'is_featured' => false,
            'status' => true,
        ]);
        ProductVariant::create([
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_id' => $size->id,
            'sku' => 'SKU-' . $slug,
            'cost_price' => 50000,
            'sale_price' => 100000,
            'stock' => 10,
            'status' => 'Active',
        ]);

        return [$category, $brand, $color, $size, $product];
    }
}
