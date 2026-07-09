<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Http\Controllers\Admin\ColorController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ColorDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_blocks_color_used_by_variant(): void
    {
        $color = $this->createColorWithVariant();

        app(ColorController::class)->destroy((string) $color->id);

        $this->assertFalse($color->fresh()->trashed());
        $this->assertNotNull(session('error'));
    }

    public function test_force_delete_blocks_color_used_by_variant(): void
    {
        $color = $this->createColorWithVariant();
        $color->delete();

        app(ColorController::class)->forceDelete((string) $color->id);

        $this->assertNotNull(Color::onlyTrashed()->find($color->id));
        $this->assertNotNull(session('error'));
    }

    public function test_bulk_force_delete_checks_each_color(): void
    {
        $blockedColor = $this->createColorWithVariant('blocked-color');
        $deletableColor = Color::create([
            'name' => 'Deletable Color',
            'hex_code' => '#111111',
            'status' => true,
        ]);
        $blockedColor->delete();
        $deletableColor->delete();

        $request = Request::create('/admin/colors/trash/bulk-force-delete', 'POST', [
            'ids' => [$blockedColor->id, $deletableColor->id],
        ]);

        app(ColorController::class)->bulkForceDelete($request);

        $this->assertNotNull(Color::onlyTrashed()->find($blockedColor->id));
        $this->assertNotNull(Color::onlyTrashed()->find($deletableColor->id));
        $this->assertNotNull(session('error'));
    }

    public function test_store_rejects_invalid_hex_code(): void
    {
        $this->expectException(ValidationException::class);

        app(ColorController::class)->store(Request::create('/admin/colors', 'POST', [
            'name' => 'Invalid Hex',
            'hex_code' => '#zzzzzz',
        ]));
    }

    private function createColorWithVariant(string $slug = 'test-color'): Color
    {
        $category = Category::create([
            'name' => 'Test Category ' . $slug,
            'slug' => 'category-' . $slug,
            'status' => true,
        ]);
        $brand = Brand::create([
            'name' => 'Test Brand ' . $slug,
            'status' => true,
        ]);
        $color = Color::create([
            'name' => 'Test Color ' . $slug,
            'hex_code' => '#000000',
            'status' => true,
        ]);
        $size = Size::create([
            'name' => 'M ' . $slug,
            'status' => 1,
        ]);
        $product = Product::create([
            'name' => 'Test Product ' . $slug,
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

        return $color;
    }
}
