<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
        'total_price' => $this->total_price,
        'date' => $this->date instanceof \DateTimeInterface ? $this->date->format('Y-m-d H:i:s') : $this->date,
        'status' => $this->status,
        'reciver_name' => $this->reciver_name,
        'reciver_phone' => $this->reciver_phone,
        'user' => [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ],
        'items' => $this->orderItems->map(function ($item) {
            return [
                'product_name' => $item->product->getTranslation('name', app()->getLocale()),
                'product_price' => $item->price,
                'quantity' => $item->quantity,
            ];
        }),
        'category' => $this->orderItems->first()?->product->category->getTranslation('name', app()->getLocale()),
    ];
}

}
