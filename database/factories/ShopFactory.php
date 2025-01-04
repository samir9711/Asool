<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'name' => [
                'en' => $this->faker->name(),
                'ar' => $this->faker->name(),
            ],
            'image' => $this->faker->imageUrl(640, 480, 'business', true, 'shop'),
            'is_interested' => $this->faker->boolean,

        ];
    }
}
