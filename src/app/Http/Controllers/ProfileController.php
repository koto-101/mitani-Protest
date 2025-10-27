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

        $chatRooms = ChatRoom::where(function($q) use ($user) {
            $q->where('buyer_id', $user->id)
            ->orWhereHas('item', fn($q2) => $q2->where('user_id', $user->id));
        })->with(['messages', 'reads'])->get();

        foreach ($chatRooms as $chatRoom) {
            if ($chatRoom->messages->isEmpty()) {
                continue; // メッセージがない場合は未読計算しない
            }

            $lastReadId = (int) optional($chatRoom->reads->firstWhere('user_id', $user->id))->last_read_message_id ?? 0;

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
                    ->whereHas('purchase', function ($q) use ($user) {
                        $q->whereHas('chatRoom', function ($q2) use ($user) {
                            $q2->where('buyer_id', $user->id)
                                ->orWhereHas('item', function ($q3) use ($user) {
                                    $q3->where('user_id', $user->id);
                                });
                        });
                    })
                    ->with([
                        'purchase.chatRoom.item.item_images',
                        'purchase.chatRoom.messages',
                        'purchase.chatRoom.reads'
                    ])
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
