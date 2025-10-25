<?php

namespace App\Policies;

use App\Models\ChatRoom;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChatRoomPolicy
{
    use HandlesAuthorization;

    /**
     * チャットルームを閲覧できるか（認可の例）
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\ChatRoom  $chatRoom
     * @return bool
     */
    public function view(User $user, ChatRoom $chatRoom)
    {
        // チャットルームに参加しているユーザーだけ許可する例
        return $chatRoom->buyer_id === $user->id || $chatRoom->item->user_id === $user->id;
    }

    /**
     * チャットルームを更新できるか（例）
     */
    public function update(User $user, ChatRoom $chatRoom)
    {
        // 必要に応じて権限判定を追加
        return $chatRoom->buyer_id === $user->id || $chatRoom->item->user_id === $user->id;
    }

    /**
     * チャットルームを削除できるか（例）
     */
    public function delete(User $user, ChatRoom $chatRoom)
    {
        //
    }
}
