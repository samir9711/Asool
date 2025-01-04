<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'final_price' => $this->final_price,
            'poster_image' => $this->poster_image,
            'percentage' => $this->percentage,
            'product' => new ProductResource($this->product),

        ];
    }
}
