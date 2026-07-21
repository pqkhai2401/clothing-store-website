<?php

use App\Services\DocumentSequenceService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_sequences')) {
            return;
        }

        Schema::create('document_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30);
            $table->string('prefix', 10);
            $table->unsignedBigInteger('current_number')->default(0);
            $table->string('reset_type', 10)->default('DAILY');
            $table->string('period_key', 12);
            $table->timestamps();

            $table->unique(['document_type', 'period_key']);
        });

        DocumentSequenceService::backfillToday();
    }

    public function down(): void
    {
        Schema::dropIfExists('document_sequences');
    }
};
