<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Customer::create([
            'name' => 'Customer 1',
            'phone' => '1234567890',
            'email' => 'customer@example.com',
            'document_number' => '1234567890',
        ]);

        Customer::create([
            'name' => 'Customer 2',
            'phone' => '1234567890',
            'email' => 'customer@example.com',
            'document_number' => '1234567890',
        ]);
    }
}
