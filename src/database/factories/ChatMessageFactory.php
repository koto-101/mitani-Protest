<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\User;

class ChatMessageFactory extends Factory
{
    protected $model = ChatMessage::class;

    public function definition(): array
    {
        return [
            'chat_room_id' => ChatRoom::factory(),
            'sender_id' => User::factory(),
            'message' => $this->faker->realText(100),
            'image_path' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    // ✅ カスタム：既存のChatRoomを指定して作る
    public function forRoom(ChatRoom $chatRoom)
    {
        return $this->state(fn() => [
            'chat_room_id' => $chatRoom->id,
        ]);
    }

    // ✅ カスタム：送信者を指定して作る
    public function from(User $user)
    {
        return $this->state(fn() => [
            'sender_id' => $user->id,
        ]);
    }
}