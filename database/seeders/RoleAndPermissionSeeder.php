<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Role::create([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        Role::create([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        Role::create([
            'name' => 'watchman',
            'guard_name' => 'web',
        ]);
        
        Permission::create([
            'name' => 'create_auto',
            'guard_name' => 'web',
        ]);
    }
}