<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderSource: string
{
    case ONLINE = 'online';
    case ADMIN = 'admin';

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
            self::ONLINE->value => 'Khách đặt trên website',
            self::ADMIN->value => 'Admin tạo thủ công',
        ];
    }
}
