<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::where('name', 'admin')->first();
        $roleMeneger = Role::where('name', 'meneger')->first();
        $roleWatchman = Role::where('name', 'watchman')->first();

        User::create([
            'name' => 'Admin',
            'email' => 'admin@tutweb.by',
            'password' => '1234qwer',
        ])->assignRole($roleAdmin);

        User::create([
            'name' => 'Meneger',
            'email' => 'meneger@tutweb.by',
            'password' => '1234qwer',
        ])->assignRole($roleMeneger);

        User::create([
            'name' => 'Watchman',
            'email' => 'watchman@tutweb.by',
            'password' => '1234qwer',
        ])->assignRole($roleWatchman);
    }
}
