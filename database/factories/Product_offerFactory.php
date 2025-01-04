<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product_offer>
 */
class Product_offerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::inRandomOrder()->first();

        
        $productPrice = $product ? $product->price : 100;
        $percentage = $this->faker->randomFloat(2, 0, 50);
        $finalPrice = $productPrice - ($productPrice * ($percentage / 100));

        return [
            'product_id' => $product->id ?? 1,
            'percentage' => $percentage,
            'final_price' => round($finalPrice, 2),
            'poster_image' => $this->faker->imageUrl(640, 480, 'poster', true, 'offer')
        ];
    }
}
