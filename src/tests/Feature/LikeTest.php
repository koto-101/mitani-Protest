<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use \App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_like_an_item()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($user);

        $this->assertEquals(0, $item->likes()->count());

        $response = $this->post("/item/{$item->id}/like-toggle");

        $response->assertStatus(302);

        $item->refresh();

        $this->assertEquals(1, $item->likes()->count());

        $item->load('likes');

        $response = $this->get("/item/{$item->id}");
        $response->assertSee((string) $item->likes->count());

        $this->assertStringContainsString('liked', $response->getContent());

    }

    /** @test */
    public function user_can_unlike_an_item()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $otherUser->id]);
        ItemImage::factory()->create([
            'item_id' => $item->id,
            'image_path' => 'items/test.jpg',
        ]);

        $item->likes()->create(['user_id' => $user->id]);

        $this->assertEquals(1, $item->likes()->count());

        $this->actingAs($user);

        $response = $this->post("/item/{$item->id}/like-toggle");
        $response->assertStatus(302);

        $this->assertEquals(0, $item->likes()->count());

        $item->refresh();

        $response = $this->get("/item/{$item->id}");
        $response->assertSee('0');

        $response->assertDontSee('class="liked"', false);
    }
}
