<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Website chỉ bán QUẦN ÁO -> gỡ bỏ hoàn toàn nhánh "Phụ kiện" (mũ/nón, túi xách,
 * kính râm):
 *   1. Xóa sản phẩm thuộc danh mục phụ kiện cùng dữ liệu catalog đi kèm
 *      (biến thể, ảnh, tag, bộ sưu tập, lượt xem, wishlist, giỏ hàng).
 *   2. Xóa các danh mục outfit_type = 'accessory' và danh mục cha "Phụ kiện".
 *   3. Thu hẹp enum outfit_type (bỏ 'accessory') và style_group (bỏ 'neutral' —
 *      giá trị này chỉ sinh ra để phục vụ phụ kiện) để không thể tạo lại nhánh này.
 *
 * AN TOÀN: migration DỪNG (throw) nếu phát hiện sản phẩm phụ kiện đã dính dữ liệu
 * giao dịch/kho (đơn hàng, phiếu nhập, kiểm kê, xuất kho, lô hàng). Xóa cứng khi đó
 * sẽ làm hỏng lịch sử nghiệp vụ -> phải xử lý thủ công trước khi chạy lại.
 */
return new class extends Migration
{
    public function up(): void
    {
        $accessoryCategoryIds = DB::table('categories')
            ->where('outfit_type', 'accessory')
            ->pluck('id')
            ->all();

        if (!empty($accessoryCategoryIds)) {
            $productIds = DB::table('products')
                ->whereIn('category_id', $accessoryCategoryIds)
                ->pluck('id')
                ->all();

            $variantIds = empty($productIds) ? [] : DB::table('product_variants')
                ->whereIn('product_id', $productIds)
                ->pluck('id')
                ->all();

            $this->guardAgainstTransactionalData($productIds, $variantIds);

            DB::transaction(function () use ($accessoryCategoryIds, $productIds, $variantIds) {
                if (!empty($variantIds)) {
                    DB::table('cart_items')->whereIn('product_variant_id', $variantIds)->delete();
                    DB::table('product_variants')->whereIn('id', $variantIds)->delete();
                }

                if (!empty($productIds)) {
                    foreach (['collection_product', 'product_images', 'product_tags', 'product_views', 'wishlists', 'reviews'] as $table) {
                        DB::table($table)->whereIn('product_id', $productIds)->delete();
                    }
                    DB::table('products')->whereIn('id', $productIds)->delete();
                }

                $parentIds = DB::table('categories')
                    ->whereIn('id', $accessoryCategoryIds)
                    ->whereNotNull('parent_id')
                    ->pluck('parent_id')
                    ->unique()
                    ->all();

                DB::table('categories')->whereIn('id', $accessoryCategoryIds)->delete();

                // Danh mục cha "Phụ kiện" chỉ bị xóa khi đã RỖNG HOÀN TOÀN (hết danh mục
                // con lẫn sản phẩm) — chặn trường hợp đụng nhầm nhánh Nam/Nữ nếu dữ liệu
                // thực tế có gắn phụ kiện làm con của chúng.
                foreach ($parentIds as $parentId) {
                    $stillUsed = DB::table('categories')->where('parent_id', $parentId)->exists()
                        || DB::table('products')->where('category_id', $parentId)->exists();

                    if (!$stillUsed) {
                        DB::table('categories')->where('id', $parentId)->delete();
                    }
                }
            });
        }

        // Enum chỉ còn các nhóm trang phục thực sự bán -> DB tự chặn việc tạo lại
        // danh mục phụ kiện, không phụ thuộc vào tầng ứng dụng.
        DB::statement("ALTER TABLE `categories` MODIFY COLUMN `outfit_type` ENUM('top','bottom','skirt','dress','outerwear') NULL");
        DB::statement("ALTER TABLE `categories` MODIFY COLUMN `style_group` ENUM('formal','smart_casual','casual','sporty') NULL");
    }

    /**
     * Rollback chỉ khôi phục ĐỊNH NGHĨA enum. Dữ liệu phụ kiện đã xóa không thể dựng
     * lại từ migration — chạy lại CategorySeeder/ProductSeeder của phiên bản cũ nếu cần.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `categories` MODIFY COLUMN `outfit_type` ENUM('top','bottom','skirt','dress','outerwear','accessory') NULL");
        DB::statement("ALTER TABLE `categories` MODIFY COLUMN `style_group` ENUM('formal','smart_casual','casual','sporty','neutral') NULL");
    }

    /**
     * Chặn xóa cứng khi sản phẩm phụ kiện đã phát sinh giao dịch/nghiệp vụ kho.
     *
     * @param  int[]  $productIds
     * @param  int[]  $variantIds
     */
    protected function guardAgainstTransactionalData(array $productIds, array $variantIds): void
    {
        $blockers = [];

        $byVariant = [
            'order_items'        => 'đơn hàng',
            'goods_receipt_items'=> 'phiếu nhập kho',
            'stocktake_items'    => 'phiếu kiểm kê',
            'product_batches'    => 'lô hàng',
            'stock_movements'    => 'biến động kho',
        ];

        foreach ($byVariant as $table => $label) {
            if (!empty($variantIds) && Schema::hasTable($table)
                && DB::table($table)->whereIn('product_variant_id', $variantIds)->exists()) {
                $blockers[] = $label;
            }
        }

        if (!empty($productIds) && Schema::hasTable('stock_issue_items')
            && DB::table('stock_issue_items')->whereIn('product_id', $productIds)->exists()) {
            $blockers[] = 'phiếu xuất kho';
        }

        if (!empty($blockers)) {
            throw new \RuntimeException(
                'Không thể xóa sản phẩm phụ kiện vì đã phát sinh: ' . implode(', ', $blockers)
                . '. Hãy xử lý/ẩn thủ công các sản phẩm này trước khi chạy migration.'
            );
        }
    }
};
