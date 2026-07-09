<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->catalogSizes() as $size) {
            Size::updateOrCreate(
                ['name' => $size['name']],
                [
                    'sort_weight' => $size['sort_weight'],
                    'status' => 1,
                ]
            );
        }
    }

    private function catalogSizes(): array
    {
        return [
            ['name' => 'XXS', 'sort_weight' => 5],
            ['name' => 'XS', 'sort_weight' => 10],
            ['name' => 'S', 'sort_weight' => 20],
            ['name' => 'M', 'sort_weight' => 30],
            ['name' => 'L', 'sort_weight' => 40],
            ['name' => 'XL', 'sort_weight' => 50],
            ['name' => 'XXL', 'sort_weight' => 60],
            ['name' => 'XXXL', 'sort_weight' => 70],
            ['name' => '4XL', 'sort_weight' => 80],
            ['name' => '5XL', 'sort_weight' => 90],
            
            ['name' => '38', 'sort_weight' => 380],
            ['name' => '39', 'sort_weight' => 390],
            ['name' => '40', 'sort_weight' => 400],
            ['name' => '41', 'sort_weight' => 410],
            ['name' => '42', 'sort_weight' => 420],
            ['name' => '43', 'sort_weight' => 430],
            ['name' => '44', 'sort_weight' => 440],
            ['name' => '45', 'sort_weight' => 450],
            
        ];
    }
}