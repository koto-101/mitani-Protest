<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\Item;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Requests\ChatMessageRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatRead;

class ChatController extends Controller
{
    public function show(ChatRoom $chatRoom)
    {
        // 認可：このユーザーがチャット参加者か確認
        $this->authorize('view', $chatRoom);

        $user = Auth::user();

        $read = ChatRead::firstOrCreate(
            [
                'chat_room_id' => $chatRoom->id,
                'user_id' => $user->id,
            ],
            [
                'last_read_message_id' => null,
                'updated_at' => now(),
            ]
        );

        $lastMessage = $chatRoom->messages()->latest()->first();
        if ($lastMessage) {
            $read->last_read_message_id = $lastMessage->id;
            $read->updated_at = now();
            $read->save();
        }

        // チャットルームに関連する商品・相手ユーザー
        $item = $chatRoom->item;

        if ($chatRoom->buyer_id === $user->id) {
            // 自分が買い手なら出品者が相手
            $partner = $chatRoom->item->user;
        } else {
            // 自分が出品者なら買い手が相手
            $partner = $chatRoom->buyer;
        }

        $messages = $chatRoom->messages()
        ->with('sender')
        ->orderBy('created_at', 'asc')
        ->get();

        // メッセージ一覧（新しい順 or 古い順）
        $otherChatItems = ChatRoom::where(function ($q) use ($user) {
            // 自分が出品者の商品に紐づくチャット
            $q->whereHas('item', function ($sub) use ($user) {
                $sub->where('user_id', $user->id);
            })
            // または自分が買い手のチャット
            ->orWhere('buyer_id', $user->id);
        })
        ->where('id', '!=', $chatRoom->id)
        ->with('item')
        ->get();

        return view('profiles.chat', compact('chatRoom', 'messages', 'item', 'partner', 'otherChatItems'));
    }

    /**
     * メッセージ送信
     */
    public function store(ChatMessageRequest $request, ChatRoom $chatRoom)
    {
        $this->authorize('view', $chatRoom);

        $message = new ChatMessage();
        $message->chat_room_id = $chatRoom->id;
        $message->sender_id = Auth::id();
        $message->message = $request->input('message');

        // 画像アップロード（任意）
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $message->image_path = $path;
        }

        $message->save();
// dd(request('message'));
        return redirect()->route('chat.show', ['chatRoom' => $chatRoom->id])
                         ->with('success', 'メッセージを送信しました。');
    }

    /**
     * メッセージ編集
     */
    public function update(ChatMessageRequest $request, ChatMessage $chatMessage)
    {
        $this->authorize('update', $chatMessage);

        $chatMessage->message = $request->input('message');

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('chat_images', 'public');
            $chatMessage->image_path = $path;
        }

        $chatMessage->save();

        return back()->with('success', 'メッセージを更新しました。');
    }

    /**
     * メッセージ削除
     */
    public function destroy(ChatMessage $chatMessage)
    {
        $this->authorize('delete', $chatMessage);

        $chatMessage->delete();

        return redirect()->back()->with('success', 'メッセージを削除しました。');
    }
}
