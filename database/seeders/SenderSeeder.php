<?php

namespace Database\Seeders;

use App\Models\Sender;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Sender::create([
                'name' => $user->name,
                'user_id' => $user->id,
                'company_id' => $user->company_id,
            ]);
        }
    }
}
