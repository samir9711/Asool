<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
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
                'en' => $this->faker->word, 
                'ar' => $this->faker->word, // اسم المنتج بالعربية
            ],
            'category_id' => Category::inRandomOrder()->first()->id ?? 1, // ID فئة عشوائية
            'is_hot' => $this->faker->boolean, // قيمة true أو false
            'image' => $this->faker->imageUrl(640, 480, 'product', true, 'products'), // صورة عشوائية
            'price' => $this->faker->randomFloat(2, 10, 1000), // سعر عشوائي
            'profit_percentage' => $this->faker->numberBetween(5, 50), // نسبة ربح عشوائية بين 5% و50%
        ];

    }
}
