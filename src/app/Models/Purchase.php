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
