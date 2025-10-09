<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = [
            'Hot Coffee',
            'Cold Coffee',
            'Espresso',
            'Latte',
            'Cappuccino',
            'Mocha',
            'Tea',
            'Non-Coffee',
        ];

        foreach ($defaultCategories as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
