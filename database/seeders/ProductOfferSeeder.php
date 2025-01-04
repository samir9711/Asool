<?php

namespace Database\Seeders;

use App\Models\Product_offer;
use Database\Factories\ProductOfferFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Product_offer::factory(20)->create();
    }
}
