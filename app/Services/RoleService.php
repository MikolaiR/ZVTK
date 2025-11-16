<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * List roles with attached permissions.
     */
    public function list(): LengthAwarePaginator
    {
        return Role::query()
            ->with(['permissions:id,name'])
            ->select(['id', 'name', 'guard_name', 'created_at'])
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Return all permission names.
     *
     * @return Collection<string>
     */
    public function getAllPermissionNames(): Collection
    {
        return \Spatie\Permission\Models\Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->pluck('name');
    }

    /**
     * Create a role and optionally sync permissions.
     *
     * @param array{name:string,permissions?:array<int,string>} $data
     */
    public function create(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    /**
     * Update role name and permissions.
     */
    public function update(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (array_key_exists('permissions', $data)) {
            $role->syncPermissions($data['permissions'] ?? []);
        }

        return $role;
    }

    /**
     * Delete a role.
     */
    public function delete(Role $role): void
    {
        $role->delete();
    }
}
