<?php

use App\Enums\OrderSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Phân biệt đơn khách tự đặt qua website (mặc định) với đơn admin tạo thủ công
            // (vd. đặt hộ khách qua điện thoại/tại quầy).
            $table->string('source')->default(OrderSource::ONLINE->value)->after('voucher_id');
            // Chỉ có giá trị khi source = admin — ghi lại nhân sự đã tạo đơn để tra soát khi cần.
            $table->foreignId('created_by')->nullable()->after('source')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('source');
        });
    }
};
