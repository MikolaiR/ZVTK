<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * List permissions with attached roles.
     */
    public function list(): LengthAwarePaginator
    {
        return Permission::query()
            ->with(['roles:id,name'])
            ->select(['id', 'name', 'guard_name', 'created_at'])
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Return all role names.
     *
     * @return Collection<string>
     */
    public function getAllRoleNames(): Collection
    {
        return \Spatie\Permission\Models\Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->pluck('name');
    }

    /**
     * Create a permission and optionally attach to roles.
     *
     * @param array{name:string,roles?:array<int,string>} $data
     */
    public function create(array $data): Permission
    {
        $permission = Permission::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($data['roles'])) {
            $permission->syncRoles($data['roles']);
        }

        return $permission;
    }

    /**
     * Update permission name and attached roles.
     */
    public function update(Permission $permission, array $data): Permission
    {
        $permission->update(['name' => $data['name']]);

        if (array_key_exists('roles', $data)) {
            $permission->syncRoles($data['roles'] ?? []);
        }

        return $permission;
    }

    /**
     * Delete a permission.
     */
    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
