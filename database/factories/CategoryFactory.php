<?php

namespace Database\Factories;
use App\Models\Shop;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->name, // اسم الفئة بالإنجليزية
                'ar' => $this->faker->name, // اسم الفئة بالعربية
            ],
            'image' => $this->faker->imageUrl(640, 480, 'category', true, 'categories'), // صورة عشوائية
            'is_interested' => $this->faker->boolean, // قيمة true أو false
            'shop_id' => Shop::inRandomOrder()->first()->id ?? 1, // ID متجر عشوائي
        ];
    }
}
