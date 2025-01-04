<?php
namespace App\Http\Traits;
use Illuminate\Support\Str;
use Illuminate\Http\Database\Eloquent\Model;

trait Uuid{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->uuid=Str::uuid()->toString();});
    }
}