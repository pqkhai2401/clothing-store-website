<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case VIEWER = 'viewer';
    case CONTRIBUTOR = 'contributor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
