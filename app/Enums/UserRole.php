<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

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
            self::ADMIN->value => 'Quản trị viên',
            self::STAFF->value => 'Nhân viên',
            self::CUSTOMER->value => 'Khách hàng',
        ];
    }
}
