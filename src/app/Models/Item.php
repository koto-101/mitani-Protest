<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ItemImage; 

class Item extends Model
{
    use HasFactory;

    const STATUS_LISTED = '出品中';
    const STATUS_SOLD = '売却済み';

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

    protected $fillable = ['title', 'brand', 'description', 'price', 'condition', 'status'];
}
