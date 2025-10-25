<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatRoom;       // チャットルームモデル
use App\Models\Evaluation;     // 評価モデル（作成してください）
use Illuminate\Support\Facades\Auth;
use App\Mail\TransactionCompletedMail;
use Illuminate\Support\Facades\Mail;

class EvaluationController extends Controller
{
    public function store(Request $request, $chatRoomId)
    {
        $request->validate([
            'score' => 'required|integer|min:1|max:5',
        ]);

        $chatRoom = ChatRoom::findOrFail($chatRoomId);
        $user = Auth::user();
        $transaction = $chatRoom->transaction;

        // 評価対象ユーザーを決定
        $buyerId = $chatRoom->buyer_id;
        $sellerId = $chatRoom->item->user_id;
        $targetId = $user->id === $buyerId ? $sellerId : $buyerId;

        // 評価を保存または更新
        Evaluation::updateOrCreate(
            [
                'chat_room_id' => $chatRoom->id,
                'evaluator_id' => $user->id,
                'target_user_id' => $targetId,
            ],
            [
                'score' => $request->score,
            ]
        );

        // Transactionを更新
        if ($transaction) {
            $transaction->markEvaluatedBy($user->id, $buyerId, $sellerId);

            // 購入者なら出品者にメール送信
            if ($user->id === $buyerId) {
                $seller = $chatRoom->seller;
                Mail::to($seller->email)->send(new TransactionCompletedMail($chatRoom));
            }
        }

        return redirect('/')->with('success', '評価を送信しました。ありがとうございます！');
    }
}