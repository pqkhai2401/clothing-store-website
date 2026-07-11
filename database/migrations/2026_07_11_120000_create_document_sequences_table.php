<?php

use App\Services\DocumentSequenceService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30)->comment('ORDER | GOODS_RECEIPT | STOCK_ISSUE | RETURN | BATCH');
            $table->string('prefix', 10)->comment('Tiền tố mã: OD, PN, PX, LOT, ...');
            $table->unsignedBigInteger('current_number')->default(0)->comment('Số thứ tự đã cấp gần nhất trong kỳ');
            $table->string('reset_type', 10)->default('DAILY')->comment('NONE | DAILY | MONTHLY | YEARLY');
            $table->string('period_key', 12)->comment('Khoá kỳ đếm: 20260711 (DAILY), 202607 (MONTHLY), 2026 (YEARLY), ALL (NONE)');
            $table->timestamps();

            // Mỗi (loại chứng từ + kỳ) chỉ 1 dòng — cũng chính là dòng bị lockForUpdate khi cấp số.
            $table->unique(['document_type', 'period_key']);
        });

        // Khởi tạo bộ đếm cho kỳ hôm nay = số chứng từ đã tồn tại trong ngày (theo prefix cũ),
        // tránh trùng/nhảy số ngay sau khi chuyển sang DocumentSequenceService.
        DocumentSequenceService::backfillToday();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
