<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPING = 'shipping';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

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
            self::PENDING->value => 'Pending',
            self::PROCESSING->value => 'Processing',
            self::SHIPPING->value => 'Shipping',
            self::COMPLETED->value => 'Completed',
            self::CANCELLED->value => 'Cancelled',
        ];
    }
}
