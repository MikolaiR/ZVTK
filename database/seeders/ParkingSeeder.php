<?php

namespace Database\Seeders;

use App\Models\Parking;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParkingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Parking::create([
            'address' => 'Parking 1',
            'company_id' => 1,
        ]);

        Parking::create([
            'address' => 'Parking 2',
            'company_id' => 1,
        ]);

        Parking::create([
            'address' => 'Parking 3',
            'company_id' => 1,
        ]);
    }
}
