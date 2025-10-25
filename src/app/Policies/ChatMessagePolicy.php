<?php

namespace App\Policies;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatMessagePolicy
{
    use HandlesAuthorization;

    /**
     * メッセージを削除できるか
     */
    public function delete(User $user, ChatMessage $chatMessage)
    {
        return $chatMessage->sender_id === $user->id;
    }

    /**
     * メッセージを更新できるか
     */
    public function update(User $user, ChatMessage $chatMessage)
    {
        return $chatMessage->sender_id === $user->id;
    }
}
