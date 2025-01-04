<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class WorkUser extends Model
{
    use HasFactory;
    use HasApiTokens;
    protected $fillable =
    [
        'username', 'password', 'type'
    ] ;

    protected $hidden = ['password'];
}
