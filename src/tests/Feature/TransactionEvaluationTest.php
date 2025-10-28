<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Item;
use App\Models\ChatRoom;
use App\Models\Evaluation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionEvaluationTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $seller;
    private Item $item;
    private ChatRoom $chatRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();
        $this->seller = User::factory()->create();

        $this->item = Item::factory()->create([
            'user_id' => $this->seller->id,
        ]);

        $this->chatRoom = ChatRoom::factory()->create([
            'item_id' => $this->item->id,
            'buyer_id' => $this->buyer->id,
        ]);
    }

    /** 1️ 購入者が取引完了モーダルで評価を送信 → 評価が保存される */
    public function test_buyer_can_submit_evaluation(): void
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('evaluation.store', $this->chatRoom->id), [
            'score' => 5,
            'comment' => 'Great seller, fast transaction!',
        ]);

        $response->assertRedirect(); 
        $this->assertDatabaseHas('evaluations', [
            'chat_room_id' => $this->chatRoom->id,
            'evaluator_id' => $this->buyer->id,
            'score' => 5,
            'comment' => 'Great seller, fast transaction!',
        ]);
    }

    /** 2️ 出品者が購入者を評価 → 評価が保存される */
    public function test_seller_can_submit_evaluation(): void
    {
        $this->actingAs($this->seller);

        $response = $this->post(route('evaluation.store', $this->chatRoom->id), [
            'score' => 4,
            'comment' => 'Smooth transaction, thank you!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('evaluations', [
            'chat_room_id' => $this->chatRoom->id,
            'evaluator_id' => $this->seller->id,
            'score' => 4,
            'comment' => 'Smooth transaction, thank you!',
        ]);
    }

    /** 3️ 評価送信後、商品一覧画面に遷移する */
    public function test_redirects_after_evaluation_submission(): void
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('evaluation.store', $this->chatRoom->id), [
            'score' => 5,
            'comment' => 'Nice deal!',
        ]);

        $response->assertRedirect('/'); 
    }

    /**コメントが400文字を超えるとバリデーションエラー */
    public function test_validation_error_when_comment_exceeds_400_characters(): void
    {
        $this->actingAs($this->buyer);

        $longComment = str_repeat('a', 401);

        $response = $this->from(route('evaluation.store', $this->chatRoom->id))
            ->post(route('evaluation.store', $this->chatRoom->id), [
                'score' => 5,
                'comment' => $longComment,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['comment']);
        $this->assertDatabaseCount('evaluations', 0);
    }

    /** スコア未入力だとエラー */
    public function test_validation_error_when_score_is_missing(): void
    {
        $this->actingAs($this->buyer);

        $response = $this->from(route('evaluation.store', $this->chatRoom->id))
            ->post(route('evaluation.store', $this->chatRoom->id), [
                'comment' => 'Forgot to rate...',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['score']);
        $this->assertDatabaseCount('evaluations', 0);
    }
}
