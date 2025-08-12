<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function listed_items_are_displayed_on_profile_page()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $items = Item::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => '出品中',
        ]);

        $this->actingAs($user);

        $response = $this->get('/mypage?page=buy');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');

        foreach ($items as $item) {
            $response->assertSee($item->title);
        }
    }

    /** @test */
    public function purchased_items_are_displayed_on_profile_page()
    {
        $user = User::factory()->create([
            'name' => 'テスト太郎',
        ]);

        $purchasedItems = Item::factory()->count(2)->create([
            'status' => '売却済み',
        ]);

        foreach ($purchasedItems as $item) {
            Purchase::factory()->create([
                'user_id' => $user->id,
                'item_id' => $item->id,
            ]);
        }

        $this->actingAs($user);

        $response = $this->get('/mypage?page=sell');

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');

        foreach ($purchasedItems as $item) {
            $response->assertSee($item->title);
        }
    }

    /** @test */
    public function default_values_are_displayed_on_profile_edit_page()
    {
        $user = User::factory()->create([
            'name' => 'テスト花子',
            'postal_code' => '123-4567',
            'address' => '東京都千代田区1-2-3',
            'building_name' => 'テストビル 101',
            'avatar_path' => 'images/profile/test.jpg',
        ]);

        $this->actingAs($user);

        $response = $this->get('/mypage/profile');

        $response->assertStatus(200);
        $response->assertSee('テスト花子');
        $response->assertSee('123-4567');
        $response->assertSee('東京都千代田区1-2-3');
        $response->assertSee('テストビル 101');
        $response->assertSee($user->avatar_path);
    }
}
