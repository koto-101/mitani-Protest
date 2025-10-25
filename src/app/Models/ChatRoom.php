<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'buyer_id',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'item_id', 'item_id');
    }
    
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function reads()
    {
        return $this->hasMany(ChatRead::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function transaction()
    {
        return $this->hasOneThrough(Transaction::class, Purchase::class, 'item_id', 'purchase_id', 'item_id', 'id');
    }

    public function seller()
    {
        // item テーブルの user_id に紐づくユーザーを取得する想定
        return $this->hasOneThrough(
            User::class, // 最終的に取得したいモデル（seller=User）
            Item::class, // 中間のモデル（item）
            'id',        // 中間モデル(Item)の主キー（ローカルキー）
            'id',        // 最終モデル(User)の主キー（ローカルキー）
            'item_id',   // 現モデル(ChatRoom)の外部キー
            'user_id'    // 中間モデル(Item)の外部キー
        );
    }
}