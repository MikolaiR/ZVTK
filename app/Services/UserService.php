<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Build users list with filters and return paginator + role names.
     *
     * @param array{search?:string,status?:string,role?:string} $filters
     * @return array{0: LengthAwarePaginator, 1: Collection<string>}
     */
    public function list(array $filters): array
    {
        $search = (string) ($filters['search'] ?? '');
        $status = (string) ($filters['status'] ?? 'all'); // all|active|inactive|deleted
        $role = (string) ($filters['role'] ?? '');

        $query = User::query()
            ->with(['roles:id,name', 'permissions:id,name', 'company:id,name'])
            ->select(['id', 'name', 'email', 'company_id', 'is_active', 'deleted_at', 'created_at']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } else {
            $query->withTrashed();
        }

        if ($role !== '') {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        $users = $query->orderByDesc('id')->paginate(15)->withQueryString();
        $roles = $this->getAllRoleNames();

        return [$users, $roles];
    }

    /**
     * Return all role names.
     *
     * @return Collection<string>
     */
    public function getAllRoleNames(): Collection
    {
        return Role::query()->orderBy('name')->get(['id', 'name'])->pluck('name');
    }

    /**
     * Create user and optionally assign roles.
     *
     * @param array{name:string,email:string,password:string,is_active?:bool,roles?:array<int,string>} $data
     */
    public function create(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'company_id' => $data['company_id'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        if (!empty($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }

        return $user;
    }

    /**
     * Update user and roles. Prevent self-deactivation.
     *
     * @param User $user
     * @param array{name:string,email:string,password?:string,is_active:bool,roles?:array<int,string>} $data
     */
    public function update(User $user, array $data, User $actingUser): User
    {
        if ($user->trashed()) {
            abort(422, __('Cannot update deleted user.'));
        }

        if ($actingUser->is($user) && isset($data['is_active']) && $data['is_active'] === false) {
            abort(422, __('You cannot deactivate your own account.'));
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'company_id' => $data['company_id'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        if (array_key_exists('roles', $data)) {
            $user->syncRoles($data['roles'] ?? []);
        }

        if (array_key_exists('permissions', $data)) {
            $user->syncPermissions($data['permissions'] ?? []);
        }

        return $user;
    }

    /**
     * Toggle user active flag. Prevent toggling for acting user and deleted users.
     */
    public function toggleActive(User $user, User $actingUser): void
    {
        if ($actingUser->is($user)) {
            abort(422, __('You cannot change your own active status.'));
        }
        if ($user->trashed()) {
            abort(422, __('Cannot toggle inactive status for deleted user.'));
        }
        $user->update(['is_active' => ! (bool) $user->is_active]);
    }

    /**
     * Delete a user (soft delete). Prevent deleting yourself.
     */
    public function delete(User $user, User $actingUser): void
    {
        if ($actingUser->is($user)) {
            abort(422, __('You cannot delete your own account.'));
        }
        if (!$user->trashed()) {
            $user->delete();
        }
    }

    /**
     * Restore soft-deleted user by id.
     */
    public function restore(int $id): User
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return $user;
    }

    /**
     * Sync user roles with provided names.
     */
    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    /**
     * Return all permission names.
     *
     * @return Collection<string>
     */
    public function getAllPermissionNames(): Collection
    {
        return \Spatie\Permission\Models\Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->pluck('name');
    }
}
