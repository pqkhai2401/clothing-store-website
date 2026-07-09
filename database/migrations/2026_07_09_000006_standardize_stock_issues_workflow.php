<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Chuẩn hóa bảng stock_issues
        Schema::table('stock_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_issues', 'issue_type')) {
                $table->string('issue_type', 30)->default('sale')->after('code');
            }
            if (!Schema::hasColumn('stock_issues', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('issue_type')->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_issues', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('warehouse_id')->constrained('orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_issues', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_issues', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            }
            if (!Schema::hasColumn('stock_issues', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('stock_issues', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
            if (!Schema::hasColumn('stock_issues', 'total_quantity')) {
                $table->integer('total_quantity')->default(0)->after('status');
            }
            if (!Schema::hasColumn('stock_issues', 'total_cost_amount')) {
                $table->decimal('total_cost_amount', 15, 2)->default(0)->after('total_quantity');
            }
            if (!Schema::hasColumn('stock_issues', 'total_sale_amount')) {
                $table->decimal('total_sale_amount', 15, 2)->default(0)->after('total_cost_amount');
            }
        });

        // 2. Chuyển đổi dữ liệu cũ từ reason_type -> issue_type nếu có
        if (Schema::hasColumn('stock_issues', 'reason_type')) {
            // Cập nhật các trường dữ liệu cũ
            DB::table('stock_issues')->where('reason_type', 'XUAT_HUY')->update(['issue_type' => 'damaged']);
            DB::table('stock_issues')->where('reason_type', 'XUAT_KIEM_KHO')->update(['issue_type' => 'adjustment']);
            DB::table('stock_issues')->where('reason_type', 'XUAT_TRA_NCC')->update(['issue_type' => 'return_supplier']);
            DB::table('stock_issues')->where('reason_type', 'DIEU_CHINH')->update(['issue_type' => 'adjustment']);
            
            // Xóa cột reason_type cũ
            Schema::table('stock_issues', function (Blueprint $table) {
                $table->dropColumn('reason_type');
            });
        }

        // Điền kho mặc định cho các phiếu cũ
        $defaultWhId = DB::table('warehouses')->where('is_default', true)->value('id') 
            ?? DB::table('warehouses')->value('id');
        if ($defaultWhId) {
            DB::table('stock_issues')->whereNull('warehouse_id')->update(['warehouse_id' => $defaultWhId]);
        }

        // Cập nhật lại trạng thái cũ từ issued -> completed
        DB::table('stock_issues')->where('status', 'issued')->update(['status' => 'completed']);

        // 3. Chuẩn hóa bảng stock_issue_items
        Schema::table('stock_issue_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_issue_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('stock_issue_id')->constrained('products')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('stock_issue_items', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('quantity');
            }
            if (!Schema::hasColumn('stock_issue_items', 'sale_price')) {
                $table->decimal('sale_price', 15, 2)->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('stock_issue_items', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0)->after('sale_price');
            }
            if (!Schema::hasColumn('stock_issue_items', 'total_sale')) {
                $table->decimal('total_sale', 15, 2)->default(0)->after('total_cost');
            }
        });

        // Di chuyển dữ liệu đơn giá cũ và điền cột mới
        $items = DB::table('stock_issue_items')->get();
        foreach ($items as $item) {
            // Lấy product_id từ product_variants
            $variant = DB::table('product_variants')->where('id', $item->product_variant_id)->first();
            $productId = $variant ? $variant->product_id : null;
            
            // Lấy đơn giá vốn tại thời điểm hiện tại làm giá vốn cũ nếu cần
            $costPrice = $variant ? $variant->cost_price : 0;
            $salePrice = Schema::hasColumn('stock_issue_items', 'unit_price') ? $item->unit_price : ($variant ? $variant->sale_price : 0);

            DB::table('stock_issue_items')->where('id', $item->id)->update([
                'product_id' => $productId,
                'cost_price' => $costPrice,
                'sale_price' => $salePrice,
                'total_cost' => $costPrice * $item->quantity,
                'total_sale' => $salePrice * $item->quantity,
            ]);
        }

        // Xóa cột unit_price cũ
        if (Schema::hasColumn('stock_issue_items', 'unit_price')) {
            Schema::table('stock_issue_items', function (Blueprint $table) {
                $table->dropColumn('unit_price');
            });
        }

        // Cập nhật lại tổng số lượng và tổng số tiền của stock_issues
        $issues = DB::table('stock_issues')->get();
        foreach ($issues as $issue) {
            $sumQty = DB::table('stock_issue_items')->where('stock_issue_id', $issue->id)->sum('quantity') ?: 0;
            $sumCost = DB::table('stock_issue_items')->where('stock_issue_id', $issue->id)->sum('total_cost') ?: 0;
            $sumSale = DB::table('stock_issue_items')->where('stock_issue_id', $issue->id)->sum('total_sale') ?: 0;

            DB::table('stock_issues')->where('id', $issue->id)->update([
                'total_quantity' => $sumQty,
                'total_cost_amount' => $sumCost,
                'total_sale_amount' => $sumSale,
            ]);
        }

        // 4. Tạo bảng mới stock_issue_logs
        if (!Schema::hasTable('stock_issue_logs')) {
            Schema::create('stock_issue_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_issue_id')->constrained('stock_issues')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 30);
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_issue_logs');

        Schema::table('stock_issue_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_issue_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0)->after('quantity');
            }
        });

        // Khôi phục cột unit_price từ sale_price
        $items = DB::table('stock_issue_items')->get();
        foreach ($items as $item) {
            DB::table('stock_issue_items')->where('id', $item->id)->update([
                'unit_price' => $item->sale_price,
            ]);
        }

        Schema::table('stock_issue_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn(['cost_price', 'sale_price', 'total_cost', 'total_sale']);
        });

        Schema::table('stock_issues', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_issues', 'reason_type')) {
                $table->string('reason_type', 30)->nullable()->after('reason');
            }
        });

        // Điền lại reason_type
        DB::table('stock_issues')->where('issue_type', 'damaged')->update(['reason_type' => 'XUAT_HUY']);
        DB::table('stock_issues')->where('issue_type', 'adjustment')->update(['reason_type' => 'XUAT_KIEM_KHO']);
        DB::table('stock_issues')->where('issue_type', 'return_supplier')->update(['reason_type' => 'XUAT_TRA_NCC']);

        DB::table('stock_issues')->where('status', 'completed')->update(['status' => 'issued']);

        Schema::table('stock_issues', function (Blueprint $table) {
            $table->dropConstrainedForeignId('warehouse_id');
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'issue_type',
                'confirmed_at',
                'cancelled_at',
                'total_quantity',
                'total_cost_amount',
                'total_sale_amount'
            ]);
        });
    }
};
