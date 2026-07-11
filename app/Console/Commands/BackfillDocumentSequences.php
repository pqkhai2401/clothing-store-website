<?php

namespace App\Console\Commands;

use App\Models\DocumentSequence;
use App\Services\DocumentSequenceService;
use Illuminate\Console\Command;

class BackfillDocumentSequences extends Command
{
    protected $signature = 'document-sequences:backfill';

    protected $description = 'Khởi tạo/đồng bộ bộ đếm document_sequences cho kỳ hôm nay theo số chứng từ đã tồn tại (idempotent).';

    public function handle(): int
    {
        DocumentSequenceService::backfillToday();

        $today = now()->format('Ymd');
        $rows = DocumentSequence::query()->where('period_key', $today)->orderBy('document_type')->get();

        $this->info("Đã backfill bộ đếm cho kỳ {$today}:");
        $this->table(
            ['document_type', 'prefix', 'current_number'],
            $rows->map(fn ($r) => [$r->document_type, $r->prefix, $r->current_number])->all(),
        );

        return self::SUCCESS;
    }
}
