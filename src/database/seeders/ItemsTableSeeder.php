<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $categories = Category::all();

        // ここで出品するユーザーを固定
        $user1 = $users->where('name', 'user1')->first();
        $user2 = $users->where('name', 'user2')->first();

        $items = [
            [
                'title' => '腕時計',
                'price' => 15000,
                'brand' => 'Rolax',
                'description' => 'スタイリッシュなデザインのメンズ腕時計',
                'condition' => '良好',
                'image_path' => 'images/Armani+Mens+Clock.png',
                'user' => $user1,
            ],
            [
                'title' => 'HDD',
                'price' => 5000,
                'brand' => '西芝',
                'description' => '高速で信頼性の高いハードディスク',
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'images/HDD+Hard+Disk.png',
                'user' => $user1,
            ],
            [
                'title' => '玉ねぎ3束',
                'price' => 300,
                'brand' => null,
                'description' => '新鮮な玉ねぎ3束のセット',
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'images/iLoveIMG+d.png',
                'user' => $user1,
            ],
            [
                'title' => '革靴',
                'price' => 4000,
                'brand' => null,
                'description' => 'クラシックなデザインの革靴',
                'condition' => '状態が悪い',
                'image_path' => 'images/Leather+Shoes+Product+Photo (1).png',
                'user' => $user1,
            ],
            [
                'title' => 'ノートPC',
                'price' => 45000,
                'brand' => null,
                'description' => '高性能なノートパソコン',
                'condition' => '良好',
                'image_path' => 'images/Living+Room+Laptop.png',
                'user' => $user1,
            ],
            [
                'title' => 'マイク',
                'price' => 8000,
                'brand' => null,
                'description' => '高音質のレコーディング用マイク',
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'images/Music+Mic+4632231.png',
                'user' => $user2,
            ],
            [
                'title' => 'ショルダーバッグ',
                'price' => 3500,
                'brand' => null,
                'description' => 'おしゃれなショルダーバッグ',
                'condition' => 'やや傷や汚れあり',
                'image_path' => 'images/Purse+fashion+pocket.png',
                'user' => $user2,
            ],
            [
                'title' => 'タンブラー',
                'price' => 500,
                'brand' => null,
                'description' => '使いやすいタンブラー',
                'condition' => '状態が悪い',
                'image_path' => 'images/Tumbler+souvenir.png',
                'user' => $user2,
            ],
            [
                'title' => 'コーヒーミル',
                'price' => 4000,
                'brand' => 'Starbacks',
                'description' => '手動のコーヒーミル',
                'condition' => '良好',
                'image_path' => 'images/Waitress+with+Coffee+Grinder.png',
                'user' => $user2,
            ],
            [
                'title' => 'メイクセット',
                'price' => 2500,
                'brand' => null,
                'description' => '便利なメイクアップセット',
                'condition' => '目立った傷や汚れなし',
                'image_path' => 'images/外出メイクアップセット.png',
                'user' => $user2,
            ],
        ];

        foreach ($items as $itemData) {
            $item = Item::create([
                'user_id' => $itemData['user']->id,
                'title' => $itemData['title'],
                'brand' => $itemData['brand'],
                'description' => $itemData['description'],
                'price' => $itemData['price'],
                'condition' => $itemData['condition'],
                'status' => '出品中',
            ]);

            $sourcePath = public_path($itemData['image_path']);
            $filename = Str::random(40) . '.' . pathinfo($sourcePath, PATHINFO_EXTENSION);
            $storagePath = 'items/' . $filename;

            if (file_exists($sourcePath)) {
                Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));
                $item->images()->create([
                    'image_path' => $storagePath,
                ]);
            } else {
                logger("Image not found: {$sourcePath}");
            }

            // カテゴリはランダムで2つ
            $existingCategoryIds = $item->categories()->pluck('categories.id')->toArray();
            $availableCategoryIds = $categories->pluck('id')->diff($existingCategoryIds)->shuffle()->take(2)->toArray();
            if (!empty($availableCategoryIds)) {
                $item->categories()->attach($availableCategoryIds);
            }
        }
    }
}
