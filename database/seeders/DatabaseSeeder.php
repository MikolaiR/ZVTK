<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CompanySeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\AutoBrandAndModelSeeder;
use Database\Seeders\ColorSeeder;

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
        ]);
    }
}
