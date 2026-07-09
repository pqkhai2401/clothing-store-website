<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Http\Controllers\Admin\CategoryController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CategoryDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_delete_blocks_category_with_active_product(): void
    {
        $category = Category::create([
            'name' => 'Ao thun',
            'slug' => 'ao-thun',
            'status' => true,
        ]);
        $this->createProduct($category, true);

        $request = Request::create('/admin/categories/bulk-delete', 'POST', [
            'ids' => [$category->id],
        ]);

        app(CategoryController::class)->bulkDelete($request);

        $this->assertFalse($category->fresh()->trashed());
        $this->assertNotNull(session('error'));
    }

    public function test_force_delete_blocks_category_with_soft_deleted_product(): void
    {
        $category = Category::create([
            'name' => 'Quan jeans',
            'slug' => 'quan-jeans',
            'status' => true,
        ]);
        $product = $this->createProduct($category, false);
        $product->delete();
        $category->delete();

        app(CategoryController::class)->forceDelete((string) $category->id);

        $this->assertNotNull(Category::onlyTrashed()->find($category->id));
        $this->assertNotNull(session('error'));
    }

    public function test_store_generates_unique_slug_against_trashed_categories(): void
    {
        $category = Category::create([
            'name' => 'Ao thun',
            'slug' => 'ao-thun',
            'status' => true,
        ]);
        $category->delete();

        $request = Request::create('/admin/categories', 'POST', [
            'name' => 'Ao thun',
            'status' => 1,
        ]);

        app(CategoryController::class)->store($request);

        $this->assertDatabaseHas('categories', [
            'name' => 'Ao thun',
            'slug' => 'ao-thun-1',
            'deleted_at' => null,
        ]);
    }

    private function createProduct(Category $category, bool $status): Product
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'status' => true,
        ]);

        return Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . $category->id,
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
            'status' => $status,
        ]);
    }
}
