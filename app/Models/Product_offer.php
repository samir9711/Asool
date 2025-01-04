<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Product_offer extends Model
{
    use HasFactory;


    protected $fillable =
    [
        'final_price',
        'poster_image',
        'product_id',
        'percentage',
    ] ;



    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
