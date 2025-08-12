<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExhibitionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function listed_item_information_is_saved_correctly()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::factory()->create(['name' => '本']);

        $formData = [
            'title' => 'テスト商品',
            'categories' => [$category->id],
            'condition' => '未使用',
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
            'price' => 5000,
            'images' => [UploadedFile::fake()->image('test.jpg')],
        ];

        $response = $this->post('/sell', $formData);

        $response->assertRedirect('/');

        $item = Item::where('title', 'テスト商品')->first();

        $this->assertDatabaseHas('items', [
            'title' => 'テスト商品',
            'condition' => '未使用',
            'brand' => 'テストブランド',
            'description' => 'これはテスト用の商品です。',
            'price' => 5000,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('category_item', [
            'item_id' => $item->id,
            'category_id' => $category->id,
        ]);

        Storage::disk('public')->assertExists('items/' . $formData['images'][0]->hashName());
    }
}
