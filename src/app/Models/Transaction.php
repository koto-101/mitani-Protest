<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'status',
        'completed_at',
        'buyer_evaluated',
        'seller_evaluated',
        'buyer_unread_count',
        'seller_unread_count',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'buyer_evaluated' => 'boolean',
        'seller_evaluated' => 'boolean',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    } 

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function markEvaluatedBy($userId, $buyerId, $sellerId)
    {
        if ($userId === $buyerId) {
            $this->buyer_evaluated = true;
            $this->status = 'buyer_completed';
        } elseif ($userId === $sellerId) {
            $this->seller_evaluated = true;
        }

        // 両方が評価済みなら completed に変更
        if ($this->buyer_evaluated && $this->seller_evaluated) {
            $this->status = 'completed';
            $this->completed_at = now();
        }

        $this->save();
    }
}
