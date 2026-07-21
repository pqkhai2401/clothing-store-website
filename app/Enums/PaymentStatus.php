<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
    /** Đơn đã thu tiền nhưng bị hủy và admin ĐÃ hoàn tiền lại cho khách. */
    case REFUNDED = 'refunded';

    /**
     * Get all raw values.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get human-readable labels.
     *
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::UNPAID->value => 'Unpaid',
            self::PAID->value => 'Paid',
            self::REFUNDED->value => 'Refunded',
        ];
    }
}
