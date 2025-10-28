<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Item;
use App\Models\ChatRoom;
use App\Mail\TransactionCompletedMail;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionMailTest extends TestCase
{
    use RefreshDatabase;

    /** 1️ 購入者が取引完了 → 出品者に通知メールが送信される */
    public function test_mail_is_sent_to_seller_when_buyer_completes_transaction()
    {
        Mail::fake();

        // ユーザー・商品・チャットルーム作成
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);

        $chatRoom = ChatRoom::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        // 購入者が取引完了 → メール送信
        Mail::to($seller->email)->send(new TransactionCompletedMail($chatRoom));

        // メールが送信されたことを確認
        Mail::assertSent(TransactionCompletedMail::class, function ($mail) use ($seller) {
            return $mail->hasTo($seller->email);
        });
    }

    /** 2️ メールの内容確認 → 商品名・購入者名・取引完了の文言が含まれている */
    public function test_mail_contains_item_and_buyer_information()
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $item = Item::factory()->create(['user_id' => $seller->id]);
        $chatRoom = ChatRoom::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        $mailable = new TransactionCompletedMail($chatRoom);
        $rendered = $mailable->render();

        $this->assertStringContainsString($item->title, $rendered);
        $this->assertStringContainsString($buyer->name, $rendered);
        $this->assertStringContainsString('取引が完了しました', $rendered);
    }
}
