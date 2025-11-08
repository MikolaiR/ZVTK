<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Parking;
use App\Models\Price;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parkings = Parking::all();

        foreach ($parkings as $parking) {
            Price::create([
                'title' => 'Price for ' . $parking->title,
                'price' => rand(2, 10),
                'parking_id' => $parking->id,
                'date_start' => now(),
            ]);
        }
    }
}
