<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Shop extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $fillable =
     [
        'name',
        'image',
        'is_interested',
     ] ;
     public $translatable = ['name'];
     public function categories()
     {
        return $this->hasMany(Category::class);
     }
}
