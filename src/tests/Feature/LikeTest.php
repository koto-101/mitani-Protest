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

        // ログイン状態にする
        $this->actingAs($user);

        // 初期のいいね数は0
        $this->assertEquals(0, $item->likes()->count());

        // いいねを送信 (like-toggleがPOSTでいいねの追加・解除を切り替える想定)
        $response = $this->post("/item/{$item->id}/like-toggle");

        $response->assertStatus(302); // リダイレクトを想定

        // いいねが増えているかDB確認
        $this->assertEquals(1, $item->likes()->count());

        // アイテムをリフレッシュして関連データを最新化
        $item->refresh();

        // いいね数が増えていることをビューで確認（数字だけ）
        $response = $this->get("/item/{$item->id}");
        $response->assertSee((string) $item->likes->count());

        // いいね済みなのでボタンにlikedクラスがついているかも確認したい場合
        $response->assertSee('class="liked"', false);
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

        // 最初にいいねを作成（既にいいね状態）
        $item->likes()->create(['user_id' => $user->id]);

        // いいね数は1
        $this->assertEquals(1, $item->likes()->count());

        // ログイン状態
        $this->actingAs($user);

        // いいね解除をPOST
        $response = $this->post("/item/{$item->id}/like-toggle");
        $response->assertStatus(302);

        // DBのいいね数は0になる
        $this->assertEquals(0, $item->likes()->count());

        // アイテムの最新状態を取得
        $item->refresh();

        // ビューでもいいね数が0になっているか
        $response = $this->get("/item/{$item->id}");
        $response->assertSee('0');

        // likedクラスがついていないことも確認
        $response->assertDontSee('class="liked"', false);
    }
}
