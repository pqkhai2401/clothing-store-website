<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\ProductVariant;
use App\Models\StockIssue;
use App\Models\StockIssueItem;
use App\Models\Stocktake;
use App\Models\StocktakeItem;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\DocumentSequenceService;
use App\Services\InventoryBatchService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * QUAN TRỌNG: mọi thay đổi tồn kho trong seeder này PHẢI đi qua
 * InventoryBatchService (receive/consumeFifo), giống hệt các Controller thật.
 * Không bao giờ được gán/tăng/giảm product_variants.stock trực tiếp — nếu không,
 * hệ thống sẽ phải tự sinh lô "AUTO-BACKFILL" để vá khi có người xuất/bán hàng
 * (vì tồn kho tồn tại mà không có Lô/Batch nào đứng sau nó).
 */
class InventorySeeder extends Seeder
{
    private InventoryBatchService $batchService;
    private DocumentSequenceService $sequence;

    public function __construct()
    {
        $this->batchService = app(InventoryBatchService::class);
        $this->sequence = app(DocumentSequenceService::class);
    }

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
            // Chỉ xảy ra khi UserSeeder chưa chạy — tạo tài khoản dự phòng với mật
            // khẩu ngẫu nhiên mạnh (không hardcode) và gán role admin đầy đủ,
            // is_protected để tránh bị xoá nhầm như tài khoản admin thật.
            $adminUser = User::create([
                'username' => 'Quản trị viên kho',
                'email' => 'admin.kho@gmail.com',
                'password' => bcrypt(Str::password(20)),
                'phone_number' => '0900000000',
                'is_active' => true,
                'is_protected' => true,
                'email_verified_at' => $now,
            ]);
            $adminUser->assignRole(UserRole::ADMIN->value);
        }

        $userId = $adminUser->id;
        $warehouseId = Warehouse::getDefault()?->id;

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
                [
                    'name' => 'Công Ty TNHH May Mặc Đà Nẵng Xanh',
                    'phone' => '0236384720',
                    'email' => 'danangxanh@garment.vn',
                    'address' => '120 Nguyễn Tất Thành, Đà Nẵng',
                    'note' => 'Nhà cung cấp đồ thể thao, áo khoác gió.',
                    'status' => true,
                ],
                [
                    'name' => 'Xưởng Da Giày & Phụ Kiện Bình Dương',
                    'phone' => '0274385291',
                    'email' => 'binhduongaccessories@gmail.com',
                    'address' => 'KCN Sóng Thần 2, Dĩ An, Bình Dương',
                    'note' => 'Từng có lô hàng giao trễ hẹn, cần theo dõi tiến độ.',
                    'status' => false,
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

        // Keep 4-5 variants for item associations (đơn nhập/xuất/kiểm kê demo)
        $variantList = $variants->take(5);

        // ─────────────────────────────────────────────────────────
        // 0. NHẬP TỒN ĐẦU KỲ THẬT cho MỌI biến thể chưa có tồn (stock=0)
        //    Thay thế hoàn toàn việc gán thẳng stock/cost_price ở ProductSeeder —
        //    mỗi đơn vị tồn kho ở đây đều có 1 Lô (ProductBatch) + StockMovement
        //    thật đứng sau, y hệt khi Admin tự tay tạo phiếu nhập trên giao diện.
        // ─────────────────────────────────────────────────────────
        $openingVariants = ProductVariant::where('stock', 0)->get();

        foreach ($openingVariants->chunk(60) as $chunkIndex => $chunk) {
            $receipt = GoodsReceipt::create([
                'code' => $this->sequence->generateGoodsReceiptCode(),
                'receipt_type' => GoodsReceipt::RECEIPT_TYPE_INITIAL,
                'source_type' => GoodsReceipt::SOURCE_TYPE_INTERNAL,
                'receipt_reason' => 'Nhập tồn kho đầu kỳ khi khởi tạo hệ thống.',
                'supplier_id' => null,
                'warehouse_id' => $warehouseId,
                'note' => 'Phiếu nhập tồn đầu kỳ tự động (đợt ' . ($chunkIndex + 1) . ').',
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => 0,
                'total_quantity' => 0,
                'received_at' => $now->copy()->subDays(30),
                'completed_at' => $now->copy()->subDays(30),
                'created_by' => $userId,
                'confirmed_by' => $userId,
                'confirmed_at' => $now->copy()->subDays(30),
            ]);

            $totalAmount = 0.0;
            $totalQuantity = 0;

            foreach ($chunk as $variant) {
                $qty = rand(5, 80);
                $cost = round((float) $variant->price * 0.6, 2);

                $item = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'cost_price' => $cost,
                ]);

                $this->batchService->receive(
                    $variant,
                    $qty,
                    $cost,
                    'goods_receipt',
                    $receipt->id,
                    $item->id,
                    $userId,
                    $receipt->received_at
                );

                $totalAmount += $qty * $cost;
                $totalQuantity += $qty;
            }

            $receipt->update([
                'total_amount' => $totalAmount,
                'total_quantity' => $totalQuantity,
            ]);
        }

        // Nạp lại để có stock/cost_price mới nhất cho các bước demo bên dưới.
        $variantList = ProductVariant::whereIn('id', $variantList->pluck('id'))->get();

        // ─────────────────────────────────────────────────────────
        // A. SEED GOODS RECEIPTS (Đơn nhập hàng demo - 4 dòng)
        // ─────────────────────────────────────────────────────────
        $receipts = [
            [
                'code' => $this->sequence->generateGoodsReceiptCode(),
                'supplier_id' => $supplierIds[0],
                'note' => 'Đợt nhập hàng áo thun basic chuẩn bị cho mùa hè.',
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => 0.00, // will calculate below
                'created_by' => $userId,
                'warehouse_id' => $warehouseId,
                'received_at' => $now->copy()->subDays(10),
                'completed_at' => $now->copy()->subDays(10),
            ],
            [
                'code' => $this->sequence->generateGoodsReceiptCode(),
                'supplier_id' => $supplierIds[1] ?? $supplierIds[0],
                'note' => 'Nhập kho bổ sung quần Jeans nam size M và L.',
                'status' => GoodsReceipt::STATUS_COMPLETED,
                'total_amount' => 0.00,
                'created_by' => $userId,
                'warehouse_id' => $warehouseId,
                'received_at' => $now->copy()->subDays(5),
                'completed_at' => $now->copy()->subDays(5),
            ],
            [
                'code' => $this->sequence->generateGoodsReceiptCode(),
                'supplier_id' => $supplierIds[2] ?? $supplierIds[0],
                'note' => 'Đơn nhập nháp thử nghiệm nguyên liệu phụ kiện.',
                'status' => GoodsReceipt::STATUS_DRAFT,
                'total_amount' => 0.00,
                'created_by' => $userId,
                'warehouse_id' => $warehouseId,
                'completed_at' => null,
            ],
        ];

        foreach ($receipts as $receiptData) {
            $receipt = GoodsReceipt::create($receiptData);

            // Create 2-3 items for each receipt
            $totalAmount = 0.00;
            $totalQuantity = 0;
            $itemsCount = rand(2, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variantList->random();
                $qty = rand(20, 50);
                $cost = rand(100, 250) * 1000; // e.g. 100k to 250k
                $totalAmount += $qty * $cost;
                $totalQuantity += $qty;

                $item = GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'cost_price' => $cost,
                ]);

                // Chỉ phiếu ĐÃ HOÀN TẤT mới sinh Lô + cộng tồn thật (giống nghiệp vụ thật).
                if ($receipt->status === GoodsReceipt::STATUS_COMPLETED) {
                    $this->batchService->receive(
                        $variant,
                        $qty,
                        $cost,
                        'goods_receipt',
                        $receipt->id,
                        $item->id,
                        $userId,
                        $receipt->received_at
                    );
                }
            }

            $receipt->update(['total_amount' => $totalAmount, 'total_quantity' => $totalQuantity]);
        }

        // ─────────────────────────────────────────────────────────
        // B. SEED STOCK ISSUES (Đơn xuất kho demo - 4 dòng)
        // ─────────────────────────────────────────────────────────
        $issues = [
            [
                'code' => $this->sequence->generateStockIssueCode(),
                'issue_type' => StockIssue::ISSUE_TYPE_DAMAGED,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất hủy sản phẩm bị ẩm mốc do ngập nước.',
                'note' => 'Đã có biên bản kiểm duyệt của thủ kho.',
                'status' => StockIssue::STATUS_COMPLETED,
                'created_by' => $userId,
                'issued_at' => $now->copy()->subDays(8),
            ],
            [
                'code' => $this->sequence->generateStockIssueCode(),
                'issue_type' => StockIssue::ISSUE_TYPE_RETURN_SUPPLIER,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất trả 10 áo thun bị lỗi may/rách chỉ cho nhà cung cấp.',
                'note' => 'Đã liên hệ đối tác để nhận hàng bù đợt sau.',
                'status' => StockIssue::STATUS_COMPLETED,
                'created_by' => $userId,
                'issued_at' => $now->copy()->subDays(4),
            ],
            [
                'code' => $this->sequence->generateStockIssueCode(),
                'issue_type' => StockIssue::ISSUE_TYPE_DAMAGED,
                'warehouse_id' => $warehouseId,
                'reason' => 'Xuất mẫu để làm chương trình chụp ảnh lookbook.',
                'note' => 'Đang chờ phê duyệt trả lại.',
                'status' => StockIssue::STATUS_DRAFT,
                'created_by' => $userId,
                'issued_at' => null,
            ],
        ];

        foreach ($issues as $issueData) {
            $issue = StockIssue::create($issueData);

            $totalQty = 0;
            $totalCost = 0.00;
            $totalSale = 0.00;
            $itemsCount = rand(2, 3);
            for ($i = 0; $i < $itemsCount; $i++) {
                $variant = $variantList->random()->fresh();
                $qty = min(rand(2, 8), (int) $variant->stock);

                if ($qty <= 0) {
                    continue; // Biến thể hiện không còn tồn — bỏ qua, không tạo xuất khống.
                }

                $costPrice = $variant->cost_price > 0 ? (float) $variant->cost_price : 120000.00;
                $salePrice = $variant->price > 0 ? (float) $variant->price : $costPrice;

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

                // Chỉ phiếu ĐÃ HOÀN TẤT mới trừ tồn thật — theo đúng FIFO qua các Lô.
                if ($issue->status === StockIssue::STATUS_COMPLETED) {
                    $this->batchService->consumeFifo($variant, $qty, 'stock_issue', $issue->id, $userId);
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
        // C. SEED STOCKTAKES (Phiếu kiểm kê demo - 4 dòng)
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
                $variant = $variantList->random()->fresh();
                $systemStock = (int) $variant->stock;
                // actual stock is slightly different (e.g. -2 or +1)
                $diff = rand(-3, 2);
                $actualStock = max(0, $systemStock + $diff);
                $cost = $variant->cost_price > 0 ? (float) $variant->cost_price : 115000.00;
                $realDiff = $actualStock - $systemStock;

                StocktakeItem::create([
                    'stocktake_id' => $stocktake->id,
                    'product_variant_id' => $variant->id,
                    'system_stock' => $systemStock,
                    'actual_stock' => $actualStock,
                    'unit_cost' => $cost,
                ]);

                // Chỉ phiếu ĐÃ DUYỆT mới điều chỉnh tồn thật, đúng như StocktakeController thật:
                // thiếu -> trừ FIFO qua các Lô; thừa -> sinh Lô điều chỉnh mới.
                if ($stocktake->status === Stocktake::STATUS_APPROVED && $realDiff !== 0) {
                    if ($realDiff < 0) {
                        $shortage = min(abs($realDiff), $systemStock);
                        if ($shortage > 0) {
                            $this->batchService->consumeFifo($variant, $shortage, 'stocktake', $stocktake->id, $userId);
                        }
                    } else {
                        $this->batchService->receive($variant, $realDiff, $cost, 'stocktake', $stocktake->id, null, $userId);
                    }
                }
            }
        }
    }
}
