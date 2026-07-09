<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('goods_receipts', 'receipt_type')) {
                $table->string('receipt_type', 30)->default('purchase')->after('code');
            }
            if (! Schema::hasColumn('goods_receipts', 'source_type')) {
                $table->string('source_type', 30)->default('supplier')->after('receipt_type');
            }
            if (! Schema::hasColumn('goods_receipts', 'confirmed_by')) {
                $table->foreignId('confirmed_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('goods_receipts', 'confirmed_at')) {
                $table->timestamp('confirmed_at')->nullable()->after('confirmed_by');
            }
            if (! Schema::hasColumn('goods_receipts', 'cancelled_by')) {
                $table->foreignId('cancelled_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('goods_receipts', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            }
        });

        if (Schema::hasColumn('goods_receipts', 'supplier_id')) {
            Schema::table('goods_receipts', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->change();
            });
        }

        if (! Schema::hasTable('stock_movements')) {
            Schema::create('stock_movements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
                $table->string('reference_type', 50);
                $table->unsignedBigInteger('reference_id');
                $table->string('movement_type', 30);
                $table->integer('quantity');
                $table->integer('before_quantity');
                $table->integer('after_quantity');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['reference_type', 'reference_id']);
            });
        }

        if (! Schema::hasTable('goods_receipt_logs')) {
            Schema::create('goods_receipt_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('goods_receipt_id')->constrained('goods_receipts')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 30);
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_logs');
        Schema::dropIfExists('stock_movements');

        Schema::table('goods_receipts', function (Blueprint $table) {
            foreach (['confirmed_by', 'cancelled_by'] as $column) {
                if (Schema::hasColumn('goods_receipts', $column)) {
                    $table->dropConstrainedForeignId($column);
                }
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('goods_receipts', 'receipt_type') ? 'receipt_type' : null,
                Schema::hasColumn('goods_receipts', 'source_type') ? 'source_type' : null,
                Schema::hasColumn('goods_receipts', 'confirmed_at') ? 'confirmed_at' : null,
                Schema::hasColumn('goods_receipts', 'cancelled_at') ? 'cancelled_at' : null,
            ]));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
