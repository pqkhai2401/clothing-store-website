<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            // Clothing sizes
            'XS', 'S', 'M', 'L', 'XL', 'XXL',
            // Shoe sizes
            //'36', '37', '38', '39', '40', '41', '42', '43', '44',
        ];

        foreach ($sizes as $name) {
            Size::firstOrCreate(['name' => $name]);
        }
    }
}
