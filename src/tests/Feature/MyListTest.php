<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Like;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function liked_items_are_displayed_on_mylist_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $likedItem = Item::factory()->create([
            'user_id' => $user->id,
            'title' => 'これはいいねされた商品',
        ]);
        $unlikedItem = Item::factory()->create([
            'title' => 'これはいいねしていない商品',
        ]);

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $likedItem->id,
        ]);

        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('これはいいねされた商品');
        $response->assertDontSee('これはいいねしていない商品');
    }

    /** @test */
    public function sold_label_is_displayed_for_purchased_items_in_mylist()
    {
        $user = User::factory()->create();
        $item = Item::factory()->create([
            'status' => Item::STATUS_SOLD,
        ]);

        Like::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertSee('sold');
    }

    /** @test */
    public function guest_user_sees_no_items_on_mylist()
    {
        $response = $this->get('/?tab=mylist');
        $response->assertStatus(200);
        $response->assertDontSee('<div class="product-card">', false); // 商品カードが表示されていないことを確認
    }
}
