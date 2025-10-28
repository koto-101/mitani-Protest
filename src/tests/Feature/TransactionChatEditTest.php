<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionChatEditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private ChatRoom $chatRoom;
    private ChatMessage $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $this->chatRoom = ChatRoom::factory()->create([
            'buyer_id' => $this->user->id,
        ]);

        $this->message = ChatMessage::factory()->create([
            'chat_room_id' => $this->chatRoom->id,
            'sender_id' => $this->user->id,
            'message' => 'Original message',
        ]);
    }

    /** 1️投稿済みメッセージを編集 → 内容が更新される */
    public function test_user_can_edit_own_message(): void
    {
        $response = $this->patch(route('chat.message.update', $this->message->id), [
            'message' => 'Edited message',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', [
            'id' => $this->message->id,
            'message' => 'Edited message',
        ]);
    }

    /** 2️ 投稿済みメッセージを削除 → 一覧から消える */
    public function test_user_can_delete_own_message(): void
    {
        $response = $this->delete(route('chat.message.destroy', $this->message->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('chat_messages', [
            'id' => $this->message->id,
        ]);
    }

    /** 3️ 編集時にバリデーション（400文字超）でエラーが出る */
    public function test_validation_error_when_edit_message_exceeds_400_characters(): void
    {
        $longMessage = str_repeat('a', 401);

        $response = $this->from(route('chat.show', $this->chatRoom->id))
            ->patch(route('chat.message.update', $this->message->id), [
                'message' => $longMessage,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors(['message']);
        $this->assertDatabaseHas('chat_messages', [
            'id' => $this->message->id,
            'message' => 'Original message',
        ]);
    }

    /** 他人のメッセージを編集しようとしたらエラーになる */
    public function test_other_user_cannot_edit_message(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->patch(route('chat.message.update', $this->message->id), [
            'message' => 'Hacked!',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('chat_messages', [
            'id' => $this->message->id,
            'message' => 'Original message',
        ]);
    }

    /** 他人のメッセージを削除しようとしたらエラーになる */
    public function test_other_user_cannot_delete_message(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->delete(route('chat.message.destroy', $this->message->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('chat_messages', [
            'id' => $this->message->id,
        ]);
    }
}
