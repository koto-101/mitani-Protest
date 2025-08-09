<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use \App\Models\Item;
use \App\Models\ItemImage;

class ItemImageFactory extends Factory
{
    protected $model = ItemImage::class;

    public function definition()
    {
        return [
            'item_id' => Item::factory(),
            'image_path' => 'images/sample.jpg',
        ];
    }
}
