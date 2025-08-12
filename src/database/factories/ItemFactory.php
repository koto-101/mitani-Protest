<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\Category;
use \App\Models\User;
use App\Models\Item;
use \App\Models\ItemImage;

class ItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->word(),
            'brand' => $this->faker->company(),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->numberBetween(1000, 10000),
            'condition' => $this->faker->word(),
            'status' => '出品中',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Item $item) {
            ItemImage::factory()->create([
                'item_id' => $item->id,
                'image_path' => 'items/test.jpg',
            ]);
        });
    }
}
