<?php

namespace Database\Seeders;

use App\Enums\Statuses;
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
        // Create roles
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $managerRole = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);

        $watchmanRole = Role::firstOrCreate([
            'name' => 'watchman',
            'guard_name' => 'web',
        ]);

        // Create base permissions
        Permission::firstOrCreate([
            'name' => 'create_auto',
            'guard_name' => 'web',
        ]);

        // Create permissions for each status
        foreach (Statuses::cases() as $status) {
            Permission::firstOrCreate([
                'name' => $status->permissionName(),
                'guard_name' => 'web',
            ]);
        }

        // Assign all status permissions to admin
        $adminRole->syncPermissions(Statuses::allPermissionNames());

        // Assign all status permissions to manager
        $managerRole->syncPermissions(Statuses::allPermissionNames());

        // Assign limited status permissions to watchman (DeliveryToParking, Parking, Sale)
        $watchmanRole->syncPermissions([
            Statuses::DeliveryToParking->permissionName(),
            Statuses::Parking->permissionName(),
            Statuses::Sale->permissionName(),
        ]);
    }
}