<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OrderSetting;

class OrderSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        OrderSetting::updateOrCreate(
            ['key' => 'premium_percentage'],
            ['value' => 0] 
        );
    }
}
