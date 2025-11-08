<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AutoBrandAndModelSeeder;
use Database\Seeders\ColorSeeder;
use Database\Seeders\ProviderSeeder;
use Database\Seeders\SenderSeeder;
use Database\Seeders\ParkingSeeder;
use Database\Seeders\PriceSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanySeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            AutoBrandAndModelSeeder::class,
            ColorSeeder::class,
            ProviderSeeder::class,
            SenderSeeder::class,
            ParkingSeeder::class,
            PriceSeeder::class,
            CustomerSeeder::class,
            AutoSeeder::class,
        ]);
    }
}
