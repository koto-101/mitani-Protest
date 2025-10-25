<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ChatRead;
use App\Models\Transaction;

    class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        return view('profiles.edit', compact('user'));
    }

    public function update(ProfileRequest $request)
    {
        $user = Auth::user();

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar_path = $path;
        }

        $user->name = $request->input('name');
        $user->postal_code = $request->input('postal_code');
        $user->address = $request->input('address');
        $user->building_name = $request->input('building_name');
        $user->save();

        return redirect()->route('mypage.show');
    }

    public function show(Request $request)
    {
        $user = Auth::user();
        $page = $request->query('page', 'sell'); // デフォルトは「出品した商品」

        $items = collect(); // 初期化
        $totalUnreadCount = 0;

        // 未読メッセージ合計をタブに関係なく計算
        $chatRooms = ChatRoom::where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)
            ->orWhereHas('item', fn($q2) => $q2->where('user_id', $user->id));
        })->with(['messages', 'reads'])->get();

        foreach ($chatRooms as $chatRoom) {
            // 自分が最後に読んだメッセージIDを取得
            $lastRead = $chatRoom->reads->firstWhere('user_id', $user->id);
            $lastReadId = $lastRead ? $lastRead->last_read_message_id : 0; // まだ読んでなければ0

            // 未読は「自分以外が送ったメッセージで、最後に読んだIDより大きいもの」
            $unreadCount = ChatMessage::where('chat_room_id', $chatRoom->id)
                ->where('sender_id', '!=', $user->id)
                ->where('id', '>', $lastReadId)
                ->count();

            $totalUnreadCount += $unreadCount;
        }

        // switchは今まで通り商品リスト作成に使う
        switch ($page) {
            case 'buy':
                $items = $user->purchases()
                    ->with('item.item_images')
                    ->get()
                    ->pluck('item');
                break;

            case 'transaction':
                $transactions = Transaction::whereIn('status', ['in_progress', 'buyer_completed'])
                    ->whereHas('purchase.chatRoom', function ($query) use ($user) {
                        $query->where('buyer_id', $user->id)
                            ->orWhereHas('item', function ($q) use ($user) {
                                $q->where('user_id', $user->id);
                            });
                    })
                    ->with(['purchase.chatRoom.item.item_images', 'purchase.chatRoom.messages', 'purchase.chatRoom.reads'])
                    ->get();

                $items = $transactions->map(function ($transaction) use ($user) {
                    $chatRoom = $transaction->purchase->chatRoom;
                    $item = $chatRoom->item;
                    $item->chat_room_id = $chatRoom->id;

                    // 個別商品の未読数
                    $lastReadId = optional($chatRoom->reads->firstWhere('user_id', $user->id))->last_read_message_id ?? 0;

                    $unreadCount = $chatRoom->messages
                        ->where('sender_id', '!=', $user->id)
                        ->where('id', '>', $lastReadId)
                        ->count();

                    $item->unread_count = $unreadCount;

                    // 最新メッセージ日時
                    $item->latest_message_at = optional($chatRoom->messages()->latest()->first())->created_at;

                    return $item;
                });

                // 最新メッセージ順にソート
                $items = $items->sortByDesc('latest_message_at')->values();
                break;

            case 'sell':
            default:
                $items = $user->items()
                    ->with('item_images')
                    ->get();
                break;
        }

        // 評価平均（受けた評価）
        $averageRating = $user->receivedEvaluations()->avg('score');

        return view('profiles.show', [
            'user' => $user->setAttribute('average_rating', $averageRating),
            'items' => $items,
            'totalUnreadCount' => $totalUnreadCount,
            'currentPage' => $page,
        ]);
    }
}
