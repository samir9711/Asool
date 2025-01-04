<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $fillable =
     [
        'name',
        'image',
        'is_hot',
        'category_id',
        'price',
        'profit_percentage'
     ] ;

     public $translatable = ['name'];
    public function orderItem()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function Product_offers()
    {
        return $this->hasMany(Product_offer::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
