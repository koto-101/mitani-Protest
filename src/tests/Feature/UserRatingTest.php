<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Evaluation;
use App\Models\ChatRoom; 
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRatingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 他ユーザーから評価を受けている場合、平均評価が四捨五入して表示される
     */
    public function test_user_with_ratings_sees_rounded_average_score()
    {
        // 出品者（評価される側）
        $seller = User::create([
            'name' => '出品者',
            'email' => 'seller@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // 購入者（評価する側）
        $buyer = User::create([
            'name' => '購入者',
            'email' => 'buyer@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // 商品
        $item = \App\Models\Item::create([
            'user_id' => $seller->id,
            'title' => 'テスト商品',
            'price' => 1000,
            'condition' => 'new',
            'status' => 'available',
        ]);

        // チャットルーム（評価と紐づく）
        $chatRoom = ChatRoom::create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        // 評価（直接 create）
        Evaluation::create([
            'chat_room_id' => $chatRoom->id,
            'evaluator_id' => $buyer->id,
            'target_user_id' => $seller->id,
            'score' => 4,
        ]);

        // テスト実行
        $seller->markEmailAsVerified();
        $response = $this->actingAs($seller, 'web')->get('/mypage');

        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star">★</span>',
        ], false); // 平均が4なら星4つが表示される想定
    }

    /**
     * 評価が存在しないユーザーの場合、評価は表示されない
     */
    public function test_user_with_no_ratings_does_not_see_average_score()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/mypage');

        $response->assertDontSee('★');// 評価がないので星が出ない
    }

    /**
     * 評価の平均値が境界値のときに正しく四捨五入される
     */
    public function test_average_rating_is_rounded_correctly()
    {
        $targetUser = User::factory()->create();
        $evaluator1 = User::factory()->create();
        $evaluator2 = User::factory()->create();

        $chatRoom1 = ChatRoom::factory()->create();
        $chatRoom2 = ChatRoom::factory()->create();

        // 平均 3.49 → 四捨五入で3
        Evaluation::create([
            'chat_room_id' => $chatRoom1->id,
            'evaluator_id' => $evaluator1->id,
            'target_user_id' => $targetUser->id,
            'score' => 3,
        ]);
        Evaluation::create([
            'chat_room_id' => $chatRoom2->id,
            'evaluator_id' => $evaluator2->id,
            'target_user_id' => $targetUser->id,
            'score' => 3.98,
        ]);

        $this->actingAs($targetUser);
        $response = $this->get('/mypage');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star">★</span>',
            '<span class="star">★</span>',
        ], false);

        // 次のケース：平均3.51 → 四捨五入で4
        Evaluation::query()->delete();

        $chatRoom3 = ChatRoom::factory()->create();
        $chatRoom4 = ChatRoom::factory()->create();

        Evaluation::create([
            'chat_room_id' => $chatRoom3->id,
            'evaluator_id' => $evaluator1->id,
            'target_user_id' => $targetUser->id,
            'score' => 3.5,
        ]);
        Evaluation::create([
            'chat_room_id' => $chatRoom4->id,
            'evaluator_id' => $evaluator2->id,
            'target_user_id' => $targetUser->id,
            'score' => 3.52,
        ]);

        $response = $this->get('/mypage');
        $response->assertStatus(200);
        $response->assertSeeInOrder([
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star filled">★</span>',
            '<span class="star">★</span>',
        ], false);
    }
}