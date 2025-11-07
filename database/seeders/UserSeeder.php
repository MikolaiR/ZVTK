<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::where('name', 'admin')->first()->syncPermissions(Permission::where('name', 'create_auto')->first());
        $roleManager = Role::where('name', 'manager')->first()->syncPermissions(Permission::where('name', 'create_auto')->first());
        $roleWatchman = Role::where('name', 'watchman')->first();

        $company1 = Company::where('name', 'Company 1')->first();
        $company2 = Company::where('name', 'Company 2')->first();
        $company3 = Company::where('name', 'Company 3')->first();

        

        User::create([
            'name' => 'Admin',
            'email' => 'admin@tutweb.by',
            'password' => '1234qwer',
            'company_id' => $company1->id,
        ])
        ->assignRole($roleAdmin);

        User::create([
            'name' => 'Manager',
            'email' => 'manager@tutweb.by',
            'password' => '1234qwer',
            'company_id' => $company2->id,
        ])
        ->assignRole($roleManager);

        User::create([
            'name' => 'Watchman',
            'email' => 'watchman@tutweb.by',
            'password' => '1234qwer',
            'company_id' => $company3->id,
        ])->assignRole($roleWatchman);
    }
}
