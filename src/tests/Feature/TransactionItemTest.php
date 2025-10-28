<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\ChatRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionItemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_user_sees_transaction_items_on_mypage()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $item = Item::factory()->create([
            'user_id' => $user->id,
        ]);

        $chatRoom = ChatRoom::factory()->create([
            'buyer_id' => $user->id, 
            'item_id' => $item->id,
        ]);

        $purchase = Purchase::factory()->create([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ]);

        $transaction = Transaction::create([
            'purchase_id' => $purchase->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        $response = $this->get(route('mypage.show', ['page' => 'transaction']));
        $response->assertStatus(200); 
        $response->assertSee($item->title); 
        $response->assertSee('取引中の商品'); 
    }

    /** @test */
    public function test_completed_transactions_do_not_appear_in_transaction_list()
    {
        $buyer = \App\Models\User::factory()->create();
        $seller = \App\Models\User::factory()->create();
        $this->actingAs($buyer);

        $itemInProgress = \App\Models\Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '取引中の商品',
        ]);
        $itemCompleted = \App\Models\Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '完了済みの商品',
        ]);

        $chatInProgress = \App\Models\ChatRoom::factory()->create([
            'item_id' => $itemInProgress->id,
            'buyer_id' => $buyer->id,
        ]);
        $chatCompleted = \App\Models\ChatRoom::factory()->create([
            'item_id' => $itemCompleted->id,
            'buyer_id' => $buyer->id,
        ]);

        $purchaseInProgress = \App\Models\Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemInProgress->id,
        ]);
        $purchaseCompleted = \App\Models\Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemCompleted->id,
        ]);

        \App\Models\Transaction::create([
            'chat_room_id' => $chatInProgress->id,
            'purchase_id' => $purchaseInProgress->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        \App\Models\Transaction::create([
            'chat_room_id' => $chatCompleted->id,
            'purchase_id' => $purchaseCompleted->id,
            'status' => 'completed',
            'buyer_evaluated' => true,
            'seller_evaluated' => true,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        $response = $this->get(route('mypage.show', ['page' => 'transaction']));
        $response->assertStatus(200);

        $response->assertSee('取引中の商品');
        $response->assertDontSee('完了済みの商品');
    }

    /** @test */
    public function test_user_sees_transaction_items_and_unread_message_counts_on_mypage()
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $this->actingAs($buyer);

        // 商品A（未読2件）
        $itemA = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '取引A',
        ]);
        $chatRoomA = ChatRoom::factory()->create([
            'item_id' => $itemA->id,
            'buyer_id' => $buyer->id,
        ]);
        $purchaseA = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemA->id,
        ]);
        Transaction::create([
            'chat_room_id' => $chatRoomA->id,
            'purchase_id' => $purchaseA->id,
            'status' => 'in_progress',
            'buyer_unread_count' => 2,
            'seller_unread_count' => 0,
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
        ]);

        // 商品B（未読5件）
        $itemB = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '取引B',
        ]);
        $chatRoomB = ChatRoom::factory()->create([
            'item_id' => $itemB->id,
            'buyer_id' => $buyer->id,
        ]);
        $purchaseB = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemB->id,
        ]);
        Transaction::create([
            'chat_room_id' => $chatRoomB->id,
            'purchase_id' => $purchaseB->id,
            'status' => 'in_progress',
            'buyer_unread_count' => 5,
            'seller_unread_count' => 0,
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
        ]);

        // 完了済み商品（表示されない想定）
        $completedItem = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '完了済み商品C',
        ]);
        $completedChatRoom = ChatRoom::factory()->create([
            'item_id' => $completedItem->id,
            'buyer_id' => $buyer->id,
        ]);
        $completedPurchase = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $completedItem->id,
        ]);
        Transaction::create([
            'chat_room_id' => $completedChatRoom->id,
            'purchase_id' => $completedPurchase->id,
            'status' => 'completed',
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
            'buyer_evaluated' => true,
            'seller_evaluated' => true,
        ]);

        // --- 実行 ---
        $response = $this->get(route('mypage.show', ['page' => 'transaction']));

        // --- 検証 ---
        $response->assertStatus(200);

        // 取引中タブ表示
        $response->assertSee('取引中の商品');

        // 個別の商品タイトルが表示される
        $response->assertSee('取引A');
        $response->assertSee('取引B');
        $response->assertDontSee('完了済み商品C');

        // 個別未読件数が表示されている
        $response->assertSee('2');
        $response->assertSee('5');

        // 総合未読数（2 + 5 = 7）が表示されている
        $response->assertSee('7');
    }

    /** @test */
    public function test_user_can_access_chat_room_from_transaction_item()
    {
        // 購入者と出品者を作成
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        // ログイン（購入者として）
        $this->actingAs($buyer);

        // 商品作成
        $item = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => 'チャット遷移テスト商品',
        ]);

        // チャットルーム作成
        $chatRoom = ChatRoom::factory()->create([
            'item_id' => $item->id,
            'buyer_id' => $buyer->id,
        ]);

        // 購入・取引作成
        $purchase = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $item->id,
        ]);

        $transaction = Transaction::create([
            'chat_room_id' => $chatRoom->id,
            'purchase_id' => $purchase->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        // 「取引中の商品」ページにアクセス
        $response = $this->get(route('mypage.show', ['page' => 'transaction']));
        $response->assertStatus(200);

        // 商品タイトルとリンク先を確認
        $response->assertSee('チャット遷移テスト商品');
        $response->assertSee('/mypage/chat/' . $chatRoom->id);

        // 実際にリンク先へアクセスできるか
        $chatResponse = $this->get('/mypage/chat/' . $chatRoom->id);
        $chatResponse->assertStatus(200);
    }

    /** @test */
    public function test_user_can_switch_chat_rooms_from_sidebar()
    {
        // ユーザー作成
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        // ログイン（購入者として）
        $this->actingAs($buyer);

        // 商品を2つ作成（出品者は同じ）
        $itemA = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '商品A',
        ]);
        $itemB = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '商品B',
        ]);

        // それぞれにチャットルームを作成
        $chatRoomA = ChatRoom::factory()->create([
            'item_id' => $itemA->id,
            'buyer_id' => $buyer->id,
        ]);
        $chatRoomB = ChatRoom::factory()->create([
            'item_id' => $itemB->id,
            'buyer_id' => $buyer->id,
        ]);

        // 購入・取引データ作成
        $purchaseA = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemA->id,
        ]);
        $purchaseB = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemB->id,
        ]);

        Transaction::create([
            'chat_room_id' => $chatRoomA->id,
            'purchase_id' => $purchaseA->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        Transaction::create([
            'chat_room_id' => $chatRoomB->id,
            'purchase_id' => $purchaseB->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
        ]);

        // 最初に商品Aのチャット画面へアクセス
        $responseA = $this->get('/mypage/chat/' . $chatRoomA->id);
        $responseA->assertStatus(200);
        $responseA->assertSee('商品A');

        // サイドバーで商品Bをクリック → 商品Bのチャット画面へ遷移
        $responseB = $this->get('/mypage/chat/' . $chatRoomB->id);
        $responseB->assertStatus(200);
        $responseB->assertSee('商品B');
    }

    /** @test */
    public function test_transaction_items_are_sorted_by_latest_message()
    {
        // 購入者と出品者
        $buyer = User::factory()->create();
        $seller = User::factory()->create();

        $this->actingAs($buyer);

        // 商品を2つ作成
        $itemOld = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '古いメッセージの商品',
        ]);
        $itemNew = Item::factory()->create([
            'user_id' => $seller->id,
            'title' => '新しいメッセージの商品',
        ]);

        // チャットルーム作成
        $chatOld = ChatRoom::factory()->create([
            'item_id' => $itemOld->id,
            'buyer_id' => $buyer->id,
            'created_at' => now()->subDay(),
        ]);
        $chatNew = ChatRoom::factory()->create([
            'item_id' => $itemNew->id,
            'buyer_id' => $buyer->id,
            'created_at' => now(),
        ]);

        // 購入・取引作成
        $purchaseOld = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemOld->id,
        ]);
        $purchaseNew = Purchase::factory()->create([
            'user_id' => $buyer->id,
            'item_id' => $itemNew->id,
        ]);

        Transaction::create([
            'chat_room_id' => $chatOld->id,
            'purchase_id' => $purchaseOld->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
            'created_at' => now()->subDay(),
        ]);

        Transaction::create([
            'chat_room_id' => $chatNew->id,
            'purchase_id' => $purchaseNew->id,
            'status' => 'in_progress',
            'buyer_evaluated' => false,
            'seller_evaluated' => false,
            'buyer_unread_count' => 0,
            'seller_unread_count' => 0,
            'created_at' => now(),
        ]);

        // チャットメッセージを作成（古い方が先、新しい方が後）
        \App\Models\ChatMessage::factory()->create([
            'chat_room_id' => $chatOld->id,
            'sender_id' => $seller->id,
            'message' => '古いメッセージ',
            'created_at' => now()->subHours(2),
        ]);

        \App\Models\ChatMessage::factory()->create([
            'chat_room_id' => $chatNew->id,
            'sender_id' => $seller->id,
            'message' => '新しいメッセージ',
            'created_at' => now(),
        ]);

        // マイページ取引中画面にアクセス
        $response = $this->get(route('mypage.show', ['page' => 'transaction']));
        $response->assertStatus(200);

        // 表示順をテキスト位置で確認
        $content = $response->getContent();

        $posNew = strpos($content, '新しいメッセージの商品');
        $posOld = strpos($content, '古いメッセージの商品');

        $this->assertTrue($posNew < $posOld, '新しいメッセージの商品が上に表示されていません。');
    }
}
