<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\Category;

class CategoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Item::all()->each(function ($item) {
            $categoryIds = Category::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $item->categories()->syncWithoutDetaching($categoryIds);
        });
    }
}
