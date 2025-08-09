<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;

class PurchaseFactory extends Factory
{
    protected $model = Purchase::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'item_id' => Item::factory(),
            'payment_method' => $this->faker->randomElement(['card', 'convenience']),
            'purchase_postal_code' => $this->faker->postcode(),
            'purchase_address' => $this->faker->address(),
            'purchase_building_name' => $this->faker->secondaryAddress(),
        ];
    }
}


