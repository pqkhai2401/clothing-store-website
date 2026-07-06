<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->text('note')->nullable();
            $table->string('status', 20)->default('draft'); // draft | completed
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_receipts');
    }
};
