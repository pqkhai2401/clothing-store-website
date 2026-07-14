<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Http\Controllers\Admin\BrandController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class BrandDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_destroy_blocks_brand_with_product(): void
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'status' => true,
        ]);
        $this->createProduct($brand, true);

        app(BrandController::class)->destroy((string) $brand->id);

        $this->assertFalse($brand->fresh()->trashed());
        $this->assertNotNull(session('error'));
    }

    public function test_force_delete_blocks_brand_with_soft_deleted_product(): void
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'status' => true,
        ]);
        $product = $this->createProduct($brand, false);
        $product->delete();
        $brand->delete();

        app(BrandController::class)->forceDelete((string) $brand->id);

        $this->assertNotNull(Brand::onlyTrashed()->find($brand->id));
        $this->assertNotNull(session('error'));
    }

    public function test_bulk_force_delete_checks_each_brand(): void
    {
        $blockedBrand = Brand::create([
            'name' => 'Blocked Brand',
            'status' => true,
        ]);
        $deletableBrand = Brand::create([
            'name' => 'Deletable Brand',
            'status' => true,
        ]);
        $this->createProduct($blockedBrand, false)->delete();
        $blockedBrand->delete();
        $deletableBrand->delete();

        $request = Request::create('/admin/brands/trash/bulk-force-delete', 'POST', [
            'ids' => [$blockedBrand->id, $deletableBrand->id],
        ]);

        app(BrandController::class)->bulkForceDelete($request);

        $this->assertNotNull(Brand::onlyTrashed()->find($blockedBrand->id));
        $this->assertNotNull(Brand::onlyTrashed()->find($deletableBrand->id));
        $this->assertNotNull(session('error'));
    }

    private function createProduct(Brand $brand, bool $status): Product
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . $brand->id,
            'status' => true,
        ]);

        return Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-brand-' . $brand->id,
            'description' => 'Test description',            'thumbnail' => 'test.jpg',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'gender' => Gender::UNISEX->value,
            'views_count' => 0,
            'is_featured' => false,
            'status' => $status,
        ]);
    }
}

