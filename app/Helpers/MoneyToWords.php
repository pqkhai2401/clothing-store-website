<?php

namespace App\Helpers;

use Illuminate\Support\Str;

/**
 * Đổi số tiền (VND) sang chữ tiếng Việt để in trên hóa đơn.
 * Ví dụ: 1234000 -> "Một triệu hai trăm ba mươi bốn nghìn đồng".
 */
class MoneyToWords
{
    private const DIGITS = ['không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];

    /** Đơn vị theo từng nhóm 3 chữ số (từ hàng đơn vị trở lên). */
    private const SCALES = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ', 'tỷ tỷ'];

    public static function vnd(int|float $amount): string
    {
        $amount = (int) round($amount);

        if ($amount === 0) {
            return 'Không đồng';
        }

        $prefix = $amount < 0 ? 'Âm ' : '';
        $words  = self::readNumber(abs($amount));

        return $prefix . Str::ucfirst($words) . ' đồng';
    }

    private static function readNumber(int $number): string
    {
        // Tách thành các nhóm 3 chữ số: groups[0] = hàng đơn vị, [1] = nghìn, [2] = triệu...
        $groups = [];
        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = intdiv($number, 1000);
        }

        $lastIndex = count($groups) - 1;
        $parts = [];

        for ($i = $lastIndex; $i >= 0; $i--) {
            if ($groups[$i] === 0) {
                continue;
            }

            // Nhóm không phải nhóm cao nhất thì đọc đủ 3 chữ số ("không trăm...", "lẻ...").
            $block = self::readThreeDigits($groups[$i], $i !== $lastIndex);
            $parts[] = trim($block . ' ' . self::SCALES[$i]);
        }

        return implode(' ', $parts);
    }

    private static function readThreeDigits(int $number, bool $full): string
    {
        $hundred = intdiv($number, 100);
        $ten     = intdiv($number % 100, 10);
        $unit    = $number % 10;

        $s = '';

        if ($hundred > 0) {
            $s .= self::DIGITS[$hundred] . ' trăm';
        } elseif ($full) {
            $s .= 'không trăm';
        }

        if ($ten > 1) {
            $s .= ' ' . self::DIGITS[$ten] . ' mươi';
            if ($unit === 1) {
                $s .= ' mốt';
            } elseif ($unit === 5) {
                $s .= ' lăm';
            } elseif ($unit > 0) {
                $s .= ' ' . self::DIGITS[$unit];
            }
        } elseif ($ten === 1) {
            $s .= ' mười';
            if ($unit === 5) {
                $s .= ' lăm';
            } elseif ($unit > 0) {
                $s .= ' ' . self::DIGITS[$unit];
            }
        } else { // hàng chục = 0
            if ($unit > 0) {
                if ($hundred > 0 || $full) {
                    $s .= ' lẻ';
                }
                $s .= ' ' . self::DIGITS[$unit];
            }
        }

        return trim($s);
    }
}
