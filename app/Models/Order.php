<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Order extends Model
{
    use HasFactory;
    use HasApiTokens;
    protected $fillable =
     [
        'total_price',
        'lat',
        'lon',
        'date',
        'user_id',
        'reciver_name',
        'reciver_phone',
        'status',
        'is_premium',
        'reject_reason',
     ] ;
     protected $casts = [
        'date' => 'datetime',
    ];
     public function user()
     {
        return $this->belongsTo(User::class);
     }
     public function orderItems()
     {
        return $this->hasMany(OrderItem::class);
     }
}
