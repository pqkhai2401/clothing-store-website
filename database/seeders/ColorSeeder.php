<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Color::defaultHexMap() as $name => $hexCode) {
            Color::updateOrCreate(
                ['name' => $name],
                ['hex_code' => $hexCode]
            );
        }
    }
}
