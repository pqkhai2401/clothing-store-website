<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Admin\ProductController;
use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_force_delete_blocks_product_used_in_order(): void
    {
        $product = $this->createOrderedProduct();
        $product->delete();

        app(ProductController::class)->forceDelete((string) $product->id);

        $this->assertNotNull(Product::onlyTrashed()->find($product->id));
        $this->assertNotNull(session('error'));
    }

    public function test_bulk_force_delete_checks_each_product(): void
    {
        $blockedProduct = $this->createOrderedProduct('blocked-product');
        $deletableProduct = $this->createProduct('deletable-product');
        $blockedProduct->delete();
        $deletableProduct->delete();

        $request = Request::create('/admin/products/trash/bulk-force-delete', 'POST', [
            'ids' => [$blockedProduct->id, $deletableProduct->id],
        ]);

        app(ProductController::class)->bulkForceDelete($request);

        $this->assertNotNull(Product::onlyTrashed()->find($blockedProduct->id));
        $this->assertNotNull(Product::onlyTrashed()->find($deletableProduct->id));
        $this->assertNotNull(session('error'));
    }

    public function test_toggle_status_only_changes_status(): void
    {
        $product = $this->createProduct('toggle-product');

        app(ProductController::class)->toggleStatus(Request::create('/admin/products/toggle', 'POST'), (string) $product->id);

        $product->refresh();
        $this->assertFalse($product->status);
        $this->assertNull($product->deleted_at);
    }

    private function createOrderedProduct(string $slug = 'ordered-product'): Product
    {
        $product = $this->createProduct($slug);
        $variant = $product->productVariants()->firstOrFail();
        $user = User::create([
            'username' => 'customer-' . $slug,
            'email' => $slug . '@example.test',
            'password' => 'password',
        ]);
        $address = Address::create([
            'user_id' => $user->id,
            'city' => 'Ho Chi Minh',
            'district' => 'Quan 1',
            'ward' => 'Ben Nghe',
            'apartment_number' => '1',
            'is_default' => true,
        ]);
        $paymentMethod = PaymentMethod::create([
            'name' => 'COD',
            'status' => true,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'address_id' => $address->id,
            'payment_method_id' => $paymentMethod->id,
            'order_code' => 'ORD-' . $slug,
            'phone' => '0900000000',
            'total_money' => 100000,
            'shipping_fee' => 0,
            'status' => OrderStatus::COMPLETED->value,
            'payment_status' => PaymentStatus::PAID->value,
        ]);
        $order->orderItems()->create([
            'product_variant_id' => $variant->id,
            'unit_price' => 100000,
            'quantity' => 1,
        ]);

        return $product;
    }

    private function createProduct(string $slug): Product
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
            'name' => 'Black ' . $slug,
            'hex_code' => '#000000',
            'status' => true,
        ]);
        $size = Size::create([
            'name' => 'M ' . $slug,
            'status' => 1,
        ]);
        $product = Product::create([
            'name' => 'Test Product ' . $slug,
            'slug' => $slug,
            'description' => 'Test description',            'thumbnail' => 'test.jpg',
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
            'price' => 100000,
            'stock' => 10,
            'status' => 'Active',
        ]);

        return $product;
    }
}

