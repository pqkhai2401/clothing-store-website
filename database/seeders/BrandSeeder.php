<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Nike',
            'Adidas',
            'Zara',
            'H&M',
            'Uniqlo',
            "Levi's",
            'Champion',
            'The North Face',
            'Puma',
            'New Balance',
            'MLB',
            'Converse',
        ];

        foreach ($brands as $name) {
            Brand::firstOrCreate(['name' => $name]);
        }
    }
}
