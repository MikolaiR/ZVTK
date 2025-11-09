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
            'name' => 'Parking 1',
            'address' => 'Parking 1',
            'company_id' => 1,
        ]);

        Parking::create([
            'name' => 'Parking 2',
            'address' => 'Parking 2',
            'company_id' => 1,
        ]);

        Parking::create([
            'name' => 'Parking 3',
            'address' => 'Parking 3',
            'company_id' => 1,
        ]);
    }
}
