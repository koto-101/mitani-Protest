<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function createItemWithImage()
    {
        $item = Item::factory()->create();
        ItemImage::factory()->create(['item_id' => $item->id]);
        return $item;
    }

    /** @test */
    public function logged_in_user_can_post_comment()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = $this->createItemWithImage();

        $response = $this->post("/item/{$item->id}/comment", [
            'content' => 'これはテストコメントです。',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'item_id' => $item->id,
            'content' => 'これはテストコメントです。',
        ]);

        $this->assertEquals(1, $item->comments()->count());
    }

    /** @test */
    public function guest_cannot_post_comment()
    {
        $item = $this->createItemWithImage();

        $response = $this->post("/item/{$item->id}/comment", [
            'content' => '未ログインでのコメント',
        ]);

        $response->assertRedirect('/login');

        $this->assertDatabaseMissing('comments', [
            'content' => '未ログインでのコメント',
        ]);
    }

    /** @test */
    public function comment_must_not_be_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = $this->createItemWithImage();

        $response = $this->from("/item/{$item->id}")->post("/item/{$item->id}/comment", [
            'content' => '',
        ]);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors('content');
    }

    /** @test */
    public function comment_must_not_exceed_255_characters()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = $this->createItemWithImage();

        $longComment = str_repeat('あ', 256);

        $response = $this->from("/item/{$item->id}")->post("/item/{$item->id}/comment", [
            'content' => $longComment,
        ]);

        $response->assertRedirect("/item/{$item->id}");
        $response->assertSessionHasErrors('content');
    }
}
