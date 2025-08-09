<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => mb_strimwidth($this->faker->name(), 0, 20, '', 'UTF-8'),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'), // パスワードは固定でもOK
            'postal_code' => $this->faker->postcode(),
            'address' => $this->faker->address(),
            'building_name' => $this->faker->optional()->secondaryAddress(),
            'avatar_path' => 'images/default-avatar.png', 
            'created_at' => now(),
            'updated_at' => now(),

            // 'remember_token' => Str::random(10),
        ];
    }
}
