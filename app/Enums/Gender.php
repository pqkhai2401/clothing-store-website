<?php

declare(strict_types=1);

namespace App\Enums;

enum Gender: string
{
    case MEN = 'men';
    case WOMEN = 'women';
    case UNISEX = 'unisex';

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
            self::MEN->value    => 'Nam',
            self::WOMEN->value  => 'Nữ',
            self::UNISEX->value => 'Unisex',
        ];
    }
}
