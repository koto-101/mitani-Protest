<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $now = Carbon::now();

        DB::table('users')->insert([
            [
                'name' => 'user1',
                'email' => 'user1@example.com',
                'password' => Hash::make('password'),
                'postal_code' => '123-4567',
                'address' => 'Tokyo, Shibuya',
                'building_name' => 'Building 1',
                'avatar_path' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'user2',
                'email' => 'user2@example.com',
                'password' => Hash::make('password'),
                'postal_code' => '234-5678',
                'address' => 'Osaka, Namba',
                'building_name' => 'Building 2',
                'avatar_path' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'user3',
                'email' => 'user3@example.com',
                'password' => Hash::make('password'),
                'postal_code' => '345-6789',
                'address' => 'Nagoya, Sakae',
                'building_name' => 'Building 3',
                'avatar_path' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
