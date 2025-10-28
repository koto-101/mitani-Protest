<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ItemImage; 
use App\Models\User;

class Item extends Model
{
    use HasFactory;

    const STATUS_LISTED = '出品中';
    const STATUS_SOLD = '売却済み';
    const STATUS_TRANSACTION = '取引中'; 

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class ,'category_item');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function isSold(): bool
    {
        return $this->purchases()->exists();
    }
    public function item_images()
    {
        return $this->hasMany(ItemImage::class);
    }
    
    public function chatRoom()
    {
        return $this->hasOne(ChatRoom::class);
    }

    protected $fillable = [
        'user_id',
        'title',
        'brand',
        'description',
        'price',
        'condition',
        'status',
    ];
}
