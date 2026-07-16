<?php

namespace App\Console\Commands;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Services\DocumentSequenceService;
use App\Services\InventoryBatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Chuẩn hoá dữ liệu kho hàng cũ/di trú thành "dữ liệu thật" — mọi đơn vị tồn kho
 * đều có Lô (ProductBatch) + Phiếu nhập kho (GoodsReceipt) thật đứng sau, thay vì
 * tồn kho "ma" (do seeder gán thẳng stock trước khi có hệ thống quản lý theo lô)
 * hoặc các lô AUTO-BACKFILL/OPEN- tự sinh mà chưa gắn với chứng từ nào.
 *
 * CHỈ THÊM DỮ LIỆU — không xoá, không đụng tới Order/Customer/StockMovement đã có.
 * An toàn chạy nhiều lần (idempotent): không còn lệch/mồ côi thì không làm gì cả.
 */
class BackfillOpeningInventoryBatches extends Command
{
    protected $signature = 'inventory:backfill-opening-batches';

    protected $description = 'Sinh Phiếu nhập tồn đầu kỳ thật cho tồn kho "ma" và gắn chứng từ cho các lô mồ côi (AUTO-BACKFILL/OPEN-).';

    public function handle(InventoryBatchService $batchService, DocumentSequenceService $sequence): int
    {
        $userId = optional(\App\Models\User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->first())->id
            ?? optional(\App\Models\User::first())->id;
        $warehouseId = \App\Models\Warehouse::getDefault()?->id;

        $createdReceipts = 0;
        $backfilledVariants = 0;
        $attachedOrphans = 0;

        DB::transaction(function () use ($batchService, $sequence, $userId, $warehouseId, &$createdReceipts, &$backfilledVariants, &$attachedOrphans) {
            // ── 1. Tồn kho "ma": cache stock lớn hơn tổng lô thật đang active ──
            $mismatches = [];
            ProductVariant::query()->chunkById(200, function ($variants) use (&$mismatches) {
                foreach ($variants as $variant) {
                    $realStock = (int) ProductBatch::where('product_variant_id', $variant->id)
                        ->where('status', ProductBatch::STATUS_ACTIVE)
                        ->sum('quantity_remaining');
                    $phantom = (int) $variant->stock - $realStock;
                    if ($phantom > 0) {
                        $mismatches[] = ['variant' => $variant, 'qty' => $phantom];
                    }
                }
            });

            foreach (array_chunk($mismatches, 60) as $chunk) {
                $receipt = GoodsReceipt::create([
                    'code' => $sequence->generateGoodsReceiptCode(),
                    'receipt_type' => GoodsReceipt::RECEIPT_TYPE_INITIAL,
                    'source_type' => GoodsReceipt::SOURCE_TYPE_INTERNAL,
                    'receipt_reason' => 'Chuẩn hoá tồn kho đầu kỳ cho dữ liệu di trú (tự động sinh).',
                    'supplier_id' => null,
                    'warehouse_id' => $warehouseId,
                    'note' => 'Sinh bởi lệnh inventory:backfill-opening-batches.',
                    'status' => GoodsReceipt::STATUS_COMPLETED,
                    'total_amount' => 0,
                    'total_quantity' => 0,
                    'received_at' => now(),
                    'completed_at' => now(),
                    'created_by' => $userId,
                    'confirmed_by' => $userId,
                    'confirmed_at' => now(),
                ]);
                $createdReceipts++;

                $totalAmount = 0.0;
                $totalQuantity = 0;

                foreach ($chunk as $row) {
                    $variant = $row['variant'];
                    $qty = $row['qty'];
                    $cost = (float) $variant->cost_price;

                    $item = GoodsReceiptItem::create([
                        'goods_receipt_id' => $receipt->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => $qty,
                        'cost_price' => $cost,
                    ]);

                    $batchService->receive($variant, $qty, $cost, 'goods_receipt', $receipt->id, $item->id, $userId, $receipt->received_at);

                    $totalAmount += $qty * $cost;
                    $totalQuantity += $qty;
                    $backfilledVariants++;
                }

                $receipt->update(['total_amount' => $totalAmount, 'total_quantity' => $totalQuantity]);
            }

            // ── 2. Lô mồ côi: đã tồn tại thật (AUTO-BACKFILL-*/OPEN-*) nhưng chưa gắn phiếu ──
            $orphanBatches = ProductBatch::whereNull('goods_receipt_item_id')
                ->where(function ($q) {
                    $q->where('batch_code', 'like', 'AUTO-BACKFILL-%')
                        ->orWhere('batch_code', 'like', 'OPEN-%');
                })
                ->get();

            foreach ($orphanBatches->chunk(60) as $chunk) {
                $receipt = GoodsReceipt::create([
                    'code' => $sequence->generateGoodsReceiptCode(),
                    'receipt_type' => GoodsReceipt::RECEIPT_TYPE_INITIAL,
                    'source_type' => GoodsReceipt::SOURCE_TYPE_INTERNAL,
                    'receipt_reason' => 'Hồi tố chứng từ cho lô hàng tự sinh trước đó (tự động sinh).',
                    'supplier_id' => null,
                    'warehouse_id' => $warehouseId,
                    'note' => 'Sinh bởi lệnh inventory:backfill-opening-batches.',
                    'status' => GoodsReceipt::STATUS_COMPLETED,
                    'total_amount' => 0,
                    'total_quantity' => 0,
                    'received_at' => now(),
                    'completed_at' => now(),
                    'created_by' => $userId,
                    'confirmed_by' => $userId,
                    'confirmed_at' => now(),
                ]);
                $createdReceipts++;

                $totalAmount = 0.0;
                $totalQuantity = 0;

                foreach ($chunk as $batch) {
                    $item = GoodsReceiptItem::create([
                        'goods_receipt_id' => $receipt->id,
                        'product_variant_id' => $batch->product_variant_id,
                        'quantity' => $batch->quantity_import,
                        'cost_price' => $batch->cost_price,
                    ]);

                    // Chỉ gắn chứng từ + đổi mã cho giống lô thật — KHÔNG đụng quantity_remaining
                    // (đã đúng rồi, vì StockMovement liên quan đã ghi nhận sẵn).
                    $batch->update([
                        'goods_receipt_item_id' => $item->id,
                        'batch_code' => $sequence->generateBatchCode(),
                    ]);

                    $totalAmount += (float) $batch->quantity_import * (float) $batch->cost_price;
                    $totalQuantity += $batch->quantity_import;
                    $attachedOrphans++;
                }

                $receipt->update(['total_amount' => $totalAmount, 'total_quantity' => $totalQuantity]);
            }
        });

        $this->info("Đã sinh {$createdReceipts} phiếu nhập tồn đầu kỳ.");
        $this->info("Đã bổ sung Lô thật cho {$backfilledVariants} biến thể có tồn kho \"ma\".");
        $this->info("Đã gắn chứng từ cho {$attachedOrphans} lô mồ côi (AUTO-BACKFILL/OPEN-).");

        if ($createdReceipts === 0) {
            $this->info('Không có gì cần chuẩn hoá — dữ liệu kho hàng đã sạch.');
        }

        return self::SUCCESS;
    }
}
