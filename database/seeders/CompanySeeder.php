<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::create([
            'name' => 'Company 1',
        ]);
        Company::create([
            'name' => 'Company 2',
        ]);
        Company::create([
            'name' => 'Company 3',
        ]);
    }
}
