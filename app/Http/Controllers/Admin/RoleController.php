<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Admin\Role\StoreRoleRequest;
use App\Http\Requests\Admin\Role\UpdateRoleRequest;

class RoleController extends Controller
{
    public function __construct(private readonly RoleService $roles)
    {
    }

    public function index(Request $request): Response
    {
        $paginator = $this->roles->list();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $paginator->through(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'permissions' => $r->permissions->pluck('name')->values(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $permissions = $this->roles->getAllPermissionNames();
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());
        return redirect()->route('admin.roles.index')->with('success', __('Role created.'));
    }

    public function edit(Role $role): Response
    {
        $permissions = $this->roles->getAllPermissionNames();
        return Inertia::render('Admin/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permissions' => $role->permissions()->pluck('name')->values(),
            ],
            'permissions' => $permissions,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());
        return redirect()->route('admin.roles.index')->with('success', __('Role updated.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->roles->delete($role);
        return back()->with('success', __('Role deleted.'));
    }
}
