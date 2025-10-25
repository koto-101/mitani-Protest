<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Item;

class Purchase extends Model
{
    use HasFactory;

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    public function chatRoom()
    {
        return $this->hasOne(ChatRoom::class, 'item_id', 'item_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'purchase_id', 'id');
    }

    protected $fillable = [
        'user_id',
        'item_id',
        'payment_method',
        'purchase_postal_code',
        'purchase_address',
        'purchase_building_name',
        'shipping_address_id',        
    ];
}
