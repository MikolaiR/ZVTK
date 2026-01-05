<?php

namespace Database\Seeders;

use App\Enums\Statuses;
use App\Services\Autos\AutoActionsResolver;
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

        $transitionPermissions = [
            AutoActionsResolver::PERMISSION_TRANSITION_MOVE_TO_CUSTOMS,
            AutoActionsResolver::PERMISSION_TRANSITION_MOVE_TO_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_ACCEPT_AT_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_MOVE_TO_CUSTOMS_FROM_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_MOVE_TO_OTHER_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_SELL,
            AutoActionsResolver::PERMISSION_TRANSITION_SAVE_FILES,
            AutoActionsResolver::PERMISSION_VIEW_STORAGE_COST,
        ];

        foreach ($transitionPermissions as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        // Create permissions for each status
        foreach (Statuses::cases() as $status) {
            Permission::firstOrCreate([
                'name' => $status->permissionName(),
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to admin
        $adminRole->syncPermissions(array_merge(
            ['create_auto'],
            Statuses::allPermissionNames(),
            $transitionPermissions,
        ));

        // Assign permissions to manager
        $managerRole->syncPermissions(array_merge(
            ['create_auto'],
            Statuses::allPermissionNames(),
            $transitionPermissions,
        ));

        // Assign limited permissions to watchman (DeliveryToParking, Parking, Sale)
        $watchmanRole->syncPermissions([
            Statuses::DeliveryToParking->permissionName(),
            Statuses::Parking->permissionName(),
            Statuses::Sale->permissionName(),
            AutoActionsResolver::PERMISSION_TRANSITION_ACCEPT_AT_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_MOVE_TO_OTHER_PARKING,
            AutoActionsResolver::PERMISSION_TRANSITION_SAVE_FILES,
            AutoActionsResolver::PERMISSION_VIEW_STORAGE_COST,
        ]);
    }
}