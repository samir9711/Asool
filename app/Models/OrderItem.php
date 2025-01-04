<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class OrderItem extends Model
{
    use HasFactory;
    use HasApiTokens;
    protected $fillable =
    [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ] ;
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
