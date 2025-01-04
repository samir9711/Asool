<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $fillable =
    [
        'name',
        'image',
        'is_interested',
        'shop_id'
    ] ;
    public $translatable = ['name'];
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
