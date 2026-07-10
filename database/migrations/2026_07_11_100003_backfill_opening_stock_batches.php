<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Chuyển đổi dữ liệu: sinh 1 lô "tồn đầu kỳ" cho mỗi biến thể đang có tồn > 0,
 * lấy giá vốn hiện tại của biến thể làm giá vốn lô. Sau bước này, tổng
 * quantity_remaining của các lô == product_variants.stock (cache khớp nguồn thật).
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('product_variants')
            ->where('stock', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($variants) use ($now) {
                foreach ($variants as $variant) {
                    // Bỏ qua nếu biến thể đã có lô (chạy lại migration an toàn)
                    $already = DB::table('product_batches')->where('product_variant_id', $variant->id)->exists();
                    if ($already) {
                        continue;
                    }

                    $qty  = (int) $variant->stock;
                    $cost = (float) ($variant->cost_price ?? 0);

                    $batchId = DB::table('product_batches')->insertGetId([
                        'batch_code'            => 'OPEN-' . $variant->id . '-' . $now->format('Ymd'),
                        'product_variant_id'    => $variant->id,
                        'goods_receipt_item_id' => null,
                        'quantity_import'       => $qty,
                        'quantity_remaining'    => $qty,
                        'cost_price'            => $cost,
                        'received_at'           => $now,
                        'status'                => 'active',
                        'created_at'            => $now,
                        'updated_at'            => $now,
                    ]);

                    DB::table('stock_movements')->insert([
                        'product_variant_id' => $variant->id,
                        'product_batch_id'   => $batchId,
                        'reference_type'     => 'opening_balance',
                        'reference_id'       => $variant->id,
                        'movement_type'      => 'import',
                        'quantity'           => $qty,
                        'unit_cost'          => $cost,
                        'before_quantity'    => 0,
                        'after_quantity'     => $qty,
                        'created_by'         => null,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Xoá các lô + bút toán tồn đầu kỳ đã sinh ở up()
        $batchIds = DB::table('product_batches')->where('batch_code', 'like', 'OPEN-%')->pluck('id');
        if ($batchIds->isNotEmpty()) {
            DB::table('stock_movements')->whereIn('product_batch_id', $batchIds)->delete();
            DB::table('product_batches')->whereIn('id', $batchIds)->delete();
        }
    }
};
