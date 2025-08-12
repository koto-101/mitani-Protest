<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_all_items_are_displayed()
    {
        $user = User::factory()->create();
        Item::factory()->count(3)->create([
            'user_id' => $user->id,
            'title' => 'テスト商品',
            'price' => 1000,
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('テスト商品');
    }

    /** @test */
    public function sold_items_are_labeled_with_sold()
    {
        $user = User::factory()->create();
        $soldItem = Item::factory()->create([
            'user_id' => $user->id,
            'price' => 1000,
            'status' => Item::STATUS_SOLD,
        ]);

        $response = $this->get('/');
        $response->assertSee('sold');
    }

    /** @test */
    public function user_does_not_see_their_own_items()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $ownItem = Item::factory()->create([
            'user_id' => $user->id,
            'title' => '自分の商品',
            'price' => 1000,
        ]);
        $otherUser = User::factory()->create();
        $otherItem = Item::factory()->create([
            'user_id' => $otherUser->id,
            'title' => '他人の商品',
            'price' => 1000,
        ]);

        $response = $this->get('/');
        $response->assertSee($otherItem->title);
        $response->assertDontSee($ownItem->title);
    }

    /** @test */
    public function it_can_search_items_by_title()
    {
        $user = User::factory()->create();
        Item::factory()->create([
            'title' => 'Apple Watch',
            'user_id' => $user->id,
            'price' => 1000,
        ]);
        Item::factory()->create([
            'title' => 'iPhone',
            'user_id' => $user->id,
            'price' => 1000,
        ]);
        Item::factory()->create([
            'title' => 'Samsung',
            'user_id' => $user->id,
            'price' => 1000,
        ]);

        $response = $this->get('/?keyword=Apple');
        $response->assertSee('Apple Watch');
        $response->assertDontSee('iPhone');
        $response->assertDontSee('Samsung');
    }

    /** @test */
    public function search_keyword_is_retained_on_mylist_page()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $keyword = 'Camera';
        $response = $this->get('/?tab=mylist&keyword=' . $keyword);

        $response->assertStatus(200);
        $response->assertSee('value="' . $keyword . '"', false); // 検索欄に値が入っていることを確認
    }

    /** @test */
    public function item_detail_page_displays_all_required_information()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = Item::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'テスト商品',
            'brand' => 'TestBrand',
            'price' => 9999,
            'description' => 'これはテスト商品です',
            'condition' => '新品',
            'status' => '出品中',
        ]);

        ItemImage::factory()->create([
            'item_id' => $item->id,
            'image_path' => 'items/test.jpg',
        ]);

        $categories = \App\Models\Category::factory()->count(2)->create();
        $item->categories()->attach($categories->pluck('id'));

        $item->likes()->create(['user_id' => $user->id]);

        $item->comments()->create([
            'user_id' => $user->id,
            'content' => 'これは素晴らしい商品ですね！',
        ]);

        $response = $this->get(route('items.show', $item->id));
        $response->assertStatus(200);

        $response->assertSee($item->title);
        $response->assertSee($item->brand);
        $response->assertSee(number_format($item->price));
        $response->assertSee($item->description);
        $response->assertSee($item->condition);
    

        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }

        $response->assertSee((string) $item->likes->count());
        $response->assertSee('コメント（' . $item->comments()->count() . '件）');

        $response->assertSee($user->name);
        $response->assertSee('これは素晴らしい商品ですね！');
    }
}
