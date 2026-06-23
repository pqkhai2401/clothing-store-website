<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            'Đen',
            'Trắng',
            'Xám',
            'Xanh navy',
            'Xanh dương',
            'Đỏ',
            'Xanh lá',
            'Vàng',
            'Cam',
            'Hồng',
            'Tím',
            'Nâu',
            'Be',
            'Xanh rêu',
        ];

        foreach ($colors as $name) {
            Color::firstOrCreate(['name' => $name]);
        }
    }
}
