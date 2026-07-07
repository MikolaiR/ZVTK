<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\Admin\Permission\StorePermissionRequest;
use App\Http\Requests\Admin\Permission\UpdatePermissionRequest;

class PermissionController extends Controller
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function index(Request $request): View
    {
        $paginator = $this->permissions->list();

        return view('admin.permissions.index', [
            'permissions' => $paginator->through(fn (Permission $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'roles' => $p->roles->pluck('name')->values(),
            ]),
        ]);
    }

    public function create(): View
    {
        $roles = $this->permissions->getAllRoleNames();
        return view('admin.permissions.create', [
            'roles' => $roles,
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $this->permissions->create($request->validated());
        return redirect()->route('admin.permissions.index')->with('success', __('Permission created.'));
    }

    public function edit(Permission $permission): View
    {
        $roles = $this->permissions->getAllRoleNames();
        return view('admin.permissions.edit', [
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
