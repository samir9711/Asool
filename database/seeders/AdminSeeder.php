<?php

namespace Database\Seeders;

use App\Models\WorkUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        WorkUser::create([
            'username' => 'asool@syria.net',
            'password'=>Hash::make('q1w2e3r4t5'),
            'type'=> 'super',
        ]);
    }
}
