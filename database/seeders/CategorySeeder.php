<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');
        $items = [
            'Vegetables' => 'categories/vegetables.jpg', 'Fruits' => 'categories/fruits.jpg', 'Rice & Grains' => 'categories/rice-grains.jpg',
            'Dairy' => 'categories/dairy.jpg', 'Fish & Meat' => 'categories/fish-meat.jpg', 'Eggs' => 'categories/eggs.jpg', 'Organic' => 'categories/organic.jpg',
        ];

        foreach ($items as $name => $image) {
            DB::table('categories')->updateOrInsert(
                ['name' => $name],
                ['slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')), 'image' => $image, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
