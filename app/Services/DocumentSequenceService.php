<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nguồn sinh mã chứng từ TẬP TRUNG cho toàn hệ thống (Order, phiếu nhập, phiếu
 * xuất, lô hàng, ...).
 *
 * Cơ chế chống trùng: mỗi (document_type + period_key) là 1 dòng trong bảng
 * document_sequences. Mỗi lần cấp số, ta mở 1 DB::transaction, lockForUpdate
 * ĐÚNG 1 dòng đó rồi tăng current_number. Nhờ vậy 2 worker/2 server cùng trỏ
 * vào 1 MySQL sẽ được xếp hàng tuần tự -> không thể tính ra cùng 1 số.
 *
 * KHÔNG dùng MAX(id)/COUNT(*)/ORDER BY code DESC như logic cũ (vốn bị race).
 * KHÔNG phụ thuộc Queue/Redis/Snowflake.
 *
 * Định dạng mã: {PREFIX}{YYYYMMDD}{SEQ>=4 số}. Ví dụ: OD202607110001,
 * PN202607110001, PX202607110001, LOT202607110001. Sequence tối thiểu 4 chữ số,
 * tự nới rộng khi vượt 9999/ngày.
 */
class DocumentSequenceService
{
    public const TYPE_ORDER         = 'ORDER';
    public const TYPE_GOODS_RECEIPT = 'GOODS_RECEIPT';
    public const TYPE_STOCK_ISSUE   = 'STOCK_ISSUE';
    public const TYPE_RETURN        = 'RETURN';
    public const TYPE_BATCH         = 'BATCH';
    public const TYPE_STOCKTAKE     = 'STOCKTAKE';

    public const RESET_NONE    = 'NONE';
    public const RESET_DAILY   = 'DAILY';
    public const RESET_MONTHLY = 'MONTHLY';
    public const RESET_YEARLY  = 'YEARLY';

    /** Prefix cố định theo từng loại chứng từ. */
    public const PREFIXES = [
        self::TYPE_ORDER         => 'OD',
        self::TYPE_GOODS_RECEIPT => 'PN',
        self::TYPE_STOCK_ISSUE   => 'PX',
        self::TYPE_RETURN        => 'PTH',
        self::TYPE_BATCH         => 'LOT',
        self::TYPE_STOCKTAKE     => 'PKK',
    ];

    /** Hiện tại mọi loại đều reset theo NGÀY (đồng nhất). */
    public const RESET_TYPES = [
        self::TYPE_ORDER         => self::RESET_DAILY,
        self::TYPE_GOODS_RECEIPT => self::RESET_DAILY,
        self::TYPE_STOCK_ISSUE   => self::RESET_DAILY,
        self::TYPE_RETURN        => self::RESET_DAILY,
        self::TYPE_BATCH         => self::RESET_DAILY,
        self::TYPE_STOCKTAKE     => self::RESET_DAILY,
    ];

    public function generateOrderCode(): string
    {
        return $this->build(self::TYPE_ORDER);
    }

    public function generateGoodsReceiptCode(): string
    {
        return $this->build(self::TYPE_GOODS_RECEIPT);
    }

    public function generateStockIssueCode(): string
    {
        return $this->build(self::TYPE_STOCK_ISSUE);
    }

    public function generateReturnCode(): string
    {
        return $this->build(self::TYPE_RETURN);
    }

    public function generateBatchCode(): string
    {
        return $this->build(self::TYPE_BATCH);
    }

    public function generateStocktakeCode(): string
    {
        return $this->build(self::TYPE_STOCKTAKE);
    }

    /**
     * Xem trước mã kế tiếp SẼ được cấp cho 1 loại chứng từ, KHÔNG tăng số đếm
     * (chỉ đọc, dùng để hiển thị gợi ý "Mã phiếu sẽ là: ..." trên UI trước khi
     * submit). Không cần khoá/atomic vì chỉ là preview — mã thật vẫn được cấp
     * qua build() lúc submit thật, đảm bảo không trùng dù preview có bị stale
     * do 2 người cùng mở form cùng lúc.
     */
    public function peekNextCode(string $documentType): string
    {
        $resetType = self::RESET_TYPES[$documentType];
        $periodKey = self::periodKey($resetType);

        $current = DocumentSequence::query()
            ->where('document_type', $documentType)
            ->where('period_key', $periodKey)
            ->value('current_number') ?? 0;

        return self::PREFIXES[$documentType]
            . $periodKey
            . str_pad((string) ($current + 1), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Ghép mã hiển thị: {PREFIX}{period_key}{SEQ}. Với reset DAILY thì period_key
     * chính là YYYYMMDD nên mã đúng chuẩn {PREFIX}{YYYYMMDD}{SEQ}.
     */
    private function build(string $documentType): string
    {
        $resetType = self::RESET_TYPES[$documentType];
        [$number, $periodKey] = $this->nextNumber($documentType, $resetType);

        return self::PREFIXES[$documentType]
            . $periodKey
            . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Cấp số kế tiếp cho 1 loại chứng từ trong kỳ hiện tại.
     *
     * @return array{0:int,1:string} [số vừa cấp, period_key]
     */
    private function nextNumber(string $documentType, string $resetType): array
    {
        $periodKey = self::periodKey($resetType);

        // DB::transaction có retry 3 lần để chịu được deadlock hiếm gặp.
        $number = DB::transaction(function () use ($documentType, $resetType, $periodKey) {
            // Đảm bảo dòng của kỳ hiện tại tồn tại trước khi lock.
            // Nếu 2 worker cùng tạo -> unique(document_type, period_key) làm INSERT
            // thứ 2 văng QueryException; ta nuốt lỗi vì dòng đã tồn tại sau đó.
            try {
                DocumentSequence::firstOrCreate(
                    ['document_type' => $documentType, 'period_key' => $periodKey],
                    [
                        'prefix'         => self::PREFIXES[$documentType],
                        'current_number' => 0,
                        'reset_type'     => $resetType,
                    ],
                );
            } catch (QueryException $e) {
                // Dòng vừa bị worker khác chèn — bỏ qua, phần dưới sẽ lock lại.
            }

            $sequence = DocumentSequence::query()
                ->where('document_type', $documentType)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            $sequence->current_number++;
            $sequence->save();

            return $sequence->current_number;
        }, 3);

        return [$number, $periodKey];
    }

    /** Khoá kỳ đếm theo reset_type (dùng để phân biệt kỳ, không so qua updated_at). */
    public static function periodKey(string $resetType, ?Carbon $at = null): string
    {
        $at ??= Carbon::now();

        return match ($resetType) {
            self::RESET_DAILY   => $at->format('Ymd'),
            self::RESET_MONTHLY => $at->format('Ym'),
            self::RESET_YEARLY  => $at->format('Y'),
            default             => 'ALL', // RESET_NONE
        };
    }

    /**
     * Khởi tạo current_number cho kỳ HÔM NAY dựa trên số chứng từ đã tồn tại
     * (theo logic prefix cũ), để sau khi chuyển đổi không nhảy/không trùng số.
     *
     * Idempotent: chạy lại nhiều lần không hạ thấp current_number đang có
     * (luôn lấy GREATEST với giá trị hiện tại).
     */
    public static function backfillToday(): void
    {
        $today = Carbon::now()->format('Ymd');

        // [document_type, prefix mới, số chứng từ đã tồn tại trong ngày theo logic cũ]
        $seeds = [
            self::TYPE_ORDER => (int) DB::table('orders')
                ->where('order_code', 'like', "ORD-{$today}-%")->count(),
            self::TYPE_GOODS_RECEIPT => (int) DB::table('goods_receipts')
                ->where('code', 'like', "PN{$today}%")->count(),
            self::TYPE_STOCK_ISSUE => (int) DB::table('stock_issues')
                ->where('code', 'like', "PXK{$today}%")->count(),
            self::TYPE_BATCH => (int) DB::table('product_batches')
                ->where('batch_code', 'like', "LO{$today}-%")->count(),
        ];

        foreach ($seeds as $documentType => $existingCount) {
            $existingRow = DocumentSequence::query()
                ->where('document_type', $documentType)
                ->where('period_key', $today)
                ->first();

            $seed = max($existingCount, (int) ($existingRow->current_number ?? 0));

            DocumentSequence::updateOrCreate(
                ['document_type' => $documentType, 'period_key' => $today],
                [
                    'prefix'         => self::PREFIXES[$documentType],
                    'reset_type'     => self::RESET_TYPES[$documentType],
                    'current_number' => $seed,
                ],
            );
        }
    }
}
