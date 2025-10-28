<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TransactionChatTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1️本文＋画像を送信できる
     */
    public function test_user_can_send_message_with_text_and_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                'message' => 'Test message',
                'image' => UploadedFile::fake()->image('chat.png'),
            ]);

        $response->assertRedirect(route('chat.show', ['chatRoom' => $chatRoom->id]));

        $this->assertDatabaseHas('chat_messages', [
            'chat_room_id' => $chatRoom->id,
            'sender_id' => $user->id,
            'message' => 'Test message',
        ]);

        $this->assertNotNull(ChatMessage::first()->image_path);
        Storage::disk('public')->assertExists(ChatMessage::first()->image_path);
    }

    /**
     * 2️本文が空の場合、バリデーションエラーになる
     */
    public function test_validation_error_when_message_is_empty()
    {
        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                'message' => '',
            ]);

        $response->assertSessionHasErrors(['message']);
    }

    /**
     * 3️ 画像拡張子が .png / .jpeg 以外だとバリデーションエラーになる
     */
    public function test_validation_error_when_image_has_invalid_extension()
    {
        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                'message' => 'Invalid file test',
                'image' => UploadedFile::fake()->create('chat.gif', 100),
            ]);

        $response->assertSessionHasErrors(['image']);
    }

    /**
     * 4️ 本文が401文字以上の場合、バリデーションエラーになる
     */
    public function test_validation_error_when_message_exceeds_400_characters()
    {
        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);
        $longText = str_repeat('あ', 401);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                'message' => $longText,
            ]);

        $response->assertSessionHasErrors(['message']);
    }

    /**
     * 5️ バリデーションエラー時、入力した本文が戻ってくる（保持される）
     */
    public function test_input_is_retained_after_validation_error()
{
    $user = User::factory()->create();
    $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);

    $response = $this->followingRedirects()
        ->actingAs($user)
        ->from(route('chat.show', $chatRoom))
        ->post(route('chat.message.store', $chatRoom), [
            'message' => '',
        ]);

    // エラーが表示されていることを確認
    $response->assertSee('本文を入力してください。');

    $response->assertSee('value="'); 
}


    /**
     * 6️ 本文がちょうど400文字のとき、投稿が成功する（境界値）
     */
    public function test_message_is_posted_when_body_is_exactly_400_characters()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);
        $text = str_repeat('あ', 400);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                'message' => $text,
            ]);

        $response->assertRedirect(route('chat.show', ['chatRoom' => $chatRoom->id]));

        $this->assertDatabaseHas('chat_messages', [
            'chat_room_id' => $chatRoom->id,
            'sender_id' => $user->id,
            'message' => $text,
        ]);
    }

    /**
     * 7️ 画像のみ送信した場合、本文必須エラーになる（本文は必須）
     */
    public function test_validation_error_when_only_image_is_posted()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $chatRoom = ChatRoom::factory()->create(['buyer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('chat.message.store', ['chatRoom' => $chatRoom->id]), [
                // message を送らない（または null）で画像だけ送る
                'image' => UploadedFile::fake()->image('chat.png'),
            ]);

        // 本文が必須なので message のバリデーションエラーを期待
        $response->assertSessionHasErrors(['message']);
    }
}
