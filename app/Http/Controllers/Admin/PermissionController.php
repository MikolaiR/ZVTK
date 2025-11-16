<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\Permission\StorePermissionRequest;
use App\Http\Requests\Admin\Permission\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function index(Request $request): Response
    {
        $paginator = $this->permissions->list();

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $paginator->through(fn (Permission $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'roles' => $p->roles->pluck('name')->values(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $roles = $this->permissions->getAllRoleNames();
        return Inertia::render('Admin/Permissions/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissions->create($request->validated());
        return redirect()->route('admin.permissions.index')->with('success', __('Permission created.'));
    }

    public function edit(Permission $permission): Response
    {
        $roles = $this->permissions->getAllRoleNames();
        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'roles' => $permission->roles()->pluck('name')->values(),
            ],
            'roles' => $roles,
        ]);
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $this->permissions->update($permission, $request->validated());
        return redirect()->route('admin.permissions.index')->with('success', __('Permission updated.'));
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $this->permissions->delete($permission);
        return back()->with('success', __('Permission deleted.'));
    }
}
