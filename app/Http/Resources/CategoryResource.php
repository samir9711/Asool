<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       // app/Http/Resources/CategoryResource.php
return [
    'name' => $this->getTranslation('name', app()->getLocale()) ?? $this->name, // استخدام القيمة الافتراضية إذا لم توجد ترجمة
    'image' => $this->image,
    'is_interested' => $this->is_interested,
    'shop' => $this->shop ? $this->shop->name : null, // تجنب الأخطاء عند عدم وجود Shop
];

    }
}
