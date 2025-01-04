<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    $profitPercentage = $this->profit_percentage;
    $finalPrice = $this->price + ($this->price * $profitPercentage / 100);

    return [
        'id' => $this->id,
        'name' => $this->getTranslation('name', app()->getLocale()),
        'image' => $this->image,
        'is_hot' => $this->is_hot,
        'price' => $this->price,
        'profit_percentage' => $profitPercentage,
        'final_price' => round($finalPrice, 2),
        'category_id' => $this->category->getTranslation('name', app()->getLocale()),
    ];
}

}
