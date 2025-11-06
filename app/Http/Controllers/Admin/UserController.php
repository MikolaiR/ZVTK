<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;
use App\Services\UserService;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;

class UserController extends Controller
{
    public function __construct(private readonly UserService $users)
    {
    }
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', 'all'); // all|active|inactive|deleted
        $role = (string) $request->query('role', '');

        [$users, $roles] = $this->users->list([
            'search' => $search,
            'status' => $status,
            'role' => $role,
        ]);

        return Inertia::render('Admin/Users/Index', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role,
            ],
            'users' => $users->through(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'is_active' => (bool) $u->is_active,
                'deleted_at' => $u->deleted_at,
                'roles' => $u->roles->pluck('name')->values(),
                'created_at' => $u->created_at?->toDateTimeString(),
            ]),
            'roles' => $roles,
        ]);
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->users->toggleActive($user, $request->user());
        return back()->with('success', __('User status updated.'));
    }

    public function syncRoles(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string'],
        ]);
        $this->users->syncRoles($user, $data['roles'] ?? []);
        return back()->with('success', __('Roles updated.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->users->delete($user, $request->user());
        return back()->with('success', __('User deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->users->restore($id);
        return redirect()->route('admin.users.index')->with('success', __('User restored.'));
    }

    public function create(): Response
    {
        $roles = $this->users->getAllRoleNames();
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->users->create($data);
        return redirect()->route('admin.users.index')->with('success', __('User created.'));
    }

    public function edit(User $user): Response
    {
        $roles = $this->users->getAllRoleNames();
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_active' => (bool) $user->is_active,
                'roles' => $user->roles()->pluck('name')->values(),
            ],
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $this->users->update($user, $data, $request->user());
        return redirect()->route('admin.users.index')->with('success', __('User updated.'));
    }
}
