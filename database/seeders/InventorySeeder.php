<?php

namespace Database\Seeders;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ProductVariant;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Get or create users
        $adminUser = User::whereHas('roles', function($q) {
            $q->where('name', 'admin');
        })->first() ?? User::first();

        if (!$adminUser) {
            $adminUser = User::create([
                'name' => 'Quản trị viên kho',
                'email' => 'admin.kho@gmail.com',
                'password' => bcrypt('password'),
            ]);
        }

        $userId = $adminUser->id;

        // 2. Get or create suppliers
        $suppliersCount = Supplier::count();
        if ($suppliersCount === 0) {
            $suppliers = [
                [
                    'name' => 'Tổng Kho May Mặc Hà Nội',
                    'phone' => '0912345678',
                    'email' => 'contact@hanoigarment.vn',
                    'address' => 'Số 15 Cầu Giấy, Hà Nội',
                    'note' => 'Nhà cung cấp áo thun, áo khoác chính.',
                    'status' => true,
                ],
                [
                    'name' => 'Xưởng May Sài Gòn Gold',
                    'phone' => '0987654321',
                    'email' => 'saigongold@gmail.com',
                    'address' => 'Đường Số 4, Tân Bình, TP. HCM',
                    'note' => 'Nhà cung cấp quần Jeans và phụ kiện.',
                    'status' => true,
                ],
                [
                    'name' => 'Công Ty Cổ Phần Dệt May Việt Tiến',
                    'phone' => '0283864009',
                    'email' => 'viettien@viettien.com.vn',
                    'address' => '07 Lê Minh Xuân, Tân Bình, TP. HCM',
                    'note' => 'Đối tác cung cấp áo sơ mi công sở cao cấp.',
                    'status' => true,
                ],
            ];
            foreach ($suppliers as $supplierData) {
                Supplier::create($supplierData);
            }
        }

        $supplierIds = Supplier::pluck('id')->toArray();

        // 3. Get product variants to seed inventory items
        $variants = ProductVariant::all();
        if ($variants->isEmpty()) {
            // If no variants exist, we cannot seed item records.
            return;
        }

        // Keep 4-5 variants for item associations
        $variantList = $variants->take(5);

        // ─────────────────────────────────────────────────────────
        // A. SEED GOODS RECEIPTS (Đơn nhập hàng - 4 dòng)
        // ─────────────────────────────────────────────────────────
        $receipts = [
            [
                'code' => 'NH26070101',
                'supplier_id' => $supplierIds[0],
                'note' => 'Đợt nhập hàng áo thun basic chuẩn bị cho mùa hè.',
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => 0.00, // will calculate below
                'created_by' => $userId,
                'completed_at' => $now->copy()->subDays(10),
            ],
            [
                'code' => 'NH26070502',
                'supplier_id' => $supplierIds[1] ?? $supplierIds[0],
                'note' => 'Nhập kho bổ sung quần Jeans nam size M và L.',
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => 0.00,
                'created_by' => $userId,
                'completed_at' => $now->copy()->subDays(5),
            ],
            [
                'code' => 'NH26070901',
                'supplier_id' => $supplierIds[2] ?? $supplierIds[0],
                'note' => 'Đơn nhập nháp thử nghiệm nguyên liệu phụ kiện.',
                'status' => GoodsReceipt::STATUS_DRAFT,
                'total_amount' => 0.00,
                'created_by' => $userId,
                'completed_at' => null,
            ],
            [
                'code' => 'NH26070902',
                'supplier_id' => $supplierIds[0],
                'note' => 'Đơn nhập hàng lỗi đã qua điều chỉnh.',
                'status' => GoodsReceipt::STATUS_ADJUSTED,
                'total_amount' => 0.00,
                'created_by' => $userId,
                'completed_at' => $now->copy()->subDays(2),
                'adjusted_by' => $userId,
                'adjusted_at' => $now->copy()->subDays(1),
                'adjustment_reason' => 'Điều chỉnh số lượng thực tế do nhà sản xuất giao thiếu 2 cái.',
            ]
        ];

        foreach ($receipts as $receiptData) {
            $receipt = GoodsReceipt::create($receiptData);

            // Create 2-3 items for each receipt
            $totalAmount = 0.00;
            $itemsCount = rand(2, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variantList->random();
                $qty = rand(20, 50);
                $cost = rand(100, 250) * 1000; // e.g. 100k to 250k
                $totalAmount += $qty * $cost;

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'cost_price' => $cost,
                ]);

                // Update variant inventory if completed
                if ($receipt->status === GoodsReceipt::STATUS_COMPLETED) {
                    $variant->increment('stock', $qty);
                }
            }

            $receipt->update(['total_amount' => $totalAmount]);
        }

        // ─────────────────────────────────────────────────────────
        // B. SEED STOCK ISSUES (Đơn xuất kho - 4 dòng)
        // ─────────────────────────────────────────────────────────
        $warehouseId = \App\Models\Warehouse::getDefault()?->id;

        $issues = [
            [
                'code' => 'XK26070201',
                'issue_type' => StockIssue::ISSUE_TYPE_DAMAGED,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất hủy sản phẩm bị ẩm mốc do ngập nước.',
                'note' => 'Đã có biên bản kiểm duyệt của thủ kho.',
                'status' => StockIssue::STATUS_COMPLETED,
                'created_by' => $userId,
                'issued_at' => $now->copy()->subDays(8),
            ],
            [
                'code' => 'XK26070601',
                'issue_type' => StockIssue::ISSUE_TYPE_RETURN_SUPPLIER,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất trả 10 áo thun bị lỗi may/rách chỉ cho nhà cung cấp.',
                'note' => 'Đã liên hệ đối tác để nhận hàng bù đợt sau.',
                'status' => StockIssue::STATUS_COMPLETED,
                'created_by' => $userId,
                'issued_at' => $now->copy()->subDays(4),
            ],
            [
                'code' => 'XK26070901',
                'issue_type' => StockIssue::ISSUE_TYPE_DAMAGED,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất mẫu để làm chương trình chụp ảnh lookbook.',
                'note' => 'Đang chờ phê duyệt trả lại.',
                'status' => StockIssue::STATUS_DRAFT,
                'created_by' => $userId,
                'issued_at' => null,
            ],
            [
                'code' => 'XK26070902',
                'issue_type' => StockIssue::ISSUE_TYPE_ADJUSTMENT,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất cân bằng hàng thừa thiếu sau kiểm kê.',
                'note' => 'Liên quan đến phiếu kiểm kê cuối tháng 6.',
                'status' => StockIssue::STATUS_COMPLETED,
                'created_by' => $userId,
                'issued_at' => $now->copy()->subDays(1),
            ]
        ];

        foreach ($issues as $issueData) {
            $issue = StockIssue::create($issueData);

            $totalQty = 0;
            $totalCost = 0.00;
            $totalSale = 0.00;
            $itemsCount = rand(2, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variantList->random();
                $qty = rand(2, 8);
                $costPrice = $variant->cost_price > 0 ? (float) $variant->cost_price : 120000.00;
                $salePrice = $variant->sale_price > 0 ? (float) $variant->sale_price : $costPrice;

                $totalQty  += $qty;
                $totalCost += $qty * $costPrice;
                $totalSale += $qty * $salePrice;

                StockIssueItem::create([
                    'stock_issue_id' => $issue->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'total_cost' => $qty * $costPrice,
                    'total_sale' => $qty * $salePrice,
                ]);

                // Update variant inventory if completed
                if ($issue->status === StockIssue::STATUS_COMPLETED) {
                    $variant->decrement('stock', min($variant->stock, $qty));
                }
            }

            $issue->update([
                'total_quantity' => $totalQty,
                'total_cost_amount' => $totalCost,
                'total_sale_amount' => $totalSale,
                'total_amount' => $totalSale,
            ]);
        }

        // ─────────────────────────────────────────────────────────
        // C. SEED STOCKTAKES (Phiếu kiểm kê - 4 dòng)
        // ─────────────────────────────────────────────────────────
        $stocktakes = [
            [
                'code' => 'KK26063001',
                'note' => 'Kiểm kê kho định kỳ cuối tháng 6/2026.',
                'status' => Stocktake::STATUS_APPROVED,
                'created_by' => $userId,
                'processed_by' => $userId,
                'processed_at' => $now->copy()->subDays(9),
            ],
            [
                'code' => 'KK26070701',
                'note' => 'Kiểm kê đột xuất nhóm hàng áo thun mùa hè.',
                'status' => Stocktake::STATUS_PENDING,
                'created_by' => $userId,
                'processed_by' => null,
                'processed_at' => null,
            ],
            [
                'code' => 'KK26070801',
                'note' => 'Kiểm kê sai mẫu, thủ kho đề xuất hủy phiếu lập lại.',
                'status' => Stocktake::STATUS_REJECTED,
                'created_by' => $userId,
                'processed_by' => $userId,
                'processed_at' => $now->copy()->subDays(1),
            ],
            [
                'code' => 'KK26070901',
                'note' => 'Kiểm kho nhanh trước ngày Sale giữa tháng.',
                'status' => Stocktake::STATUS_APPROVED,
                'created_by' => $userId,
                'processed_by' => $userId,
                'processed_at' => $now->copy()->subHours(5),
            ]
        ];

        foreach ($stocktakes as $stocktakeData) {
            $stocktake = Stocktake::create($stocktakeData);

            $itemsCount = rand(2, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variantList->random();
                $systemStock = $variant->stock > 0 ? $variant->stock : 30;
                // actual stock is slightly different (e.g. -2 or +1)
                $diff = rand(-3, 2);
                $actualStock = max(0, $systemStock + $diff);
                $cost = $variant->cost_price > 0 ? (float) $variant->cost_price : 115000.00;

                StocktakeItem::create([
                    'stocktake_id' => $stocktake->id,
                    'product_variant_id' => $variant->id,
                    'system_stock' => $systemStock,
                    'actual_stock' => $actualStock,
                    'unit_cost' => $cost,
                ]);

                // Update variant inventory to match actual stock if approved
                if ($stocktake->status === Stocktake::STATUS_APPROVED) {
                    $variant->update(['stock' => $actualStock]);
                }
            }
        }
    }
}
