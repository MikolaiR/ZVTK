<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $status = (string) $request->query('status', 'all'); // all|active|inactive|deleted
        $role = (string) $request->query('role', '');

        $query = User::query()
            ->with(['roles:id,name'])
            ->select(['id', 'name', 'email', 'is_active', 'deleted_at', 'created_at']);

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

        $roles = Role::query()->orderBy('name')->get(['id', 'name'])->pluck('name');

        return Inertia::render('Admin/Users/Index', [
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role,
            ],
            'users' => $users->through(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'is_active' => (bool) $u->is_active,
                    'deleted_at' => $u->deleted_at,
                    'roles' => $u->roles->pluck('name')->values(),
                    'created_at' => $u->created_at?->toDateTimeString(),
                ];
            }),
            'roles' => $roles,
        ]);
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors(['message' => __('You cannot change your own active status.')]);
        }
        if ($user->trashed()) {
            return back()->withErrors(['message' => __('Cannot toggle inactive status for deleted user.')]);
        }
        $user->update(['is_active' => ! (bool) $user->is_active]);
        return back()->with('success', __('User status updated.'));
    }

    public function syncRoles(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string'],
        ]);
        $roles = $data['roles'] ?? [];
        $user->syncRoles($roles);
        return back()->with('success', __('Roles updated.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->trashed()) {
            return back();
        }
        if ($request->user() && $request->user()->id === $user->id) {
            return back()->withErrors(['message' => __('You cannot delete your own account.')]);
        }
        $user->delete();
        return back()->with('success', __('User deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        return redirect()->route('admin.users.index')->with('success', __('User restored.'));
    }

    public function create(): Response
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name'])->pluck('name');
        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_active' => ['sometimes', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', __('User created.'));
    }

    public function edit(User $user): Response
    {
        $roles = Role::query()->orderBy('name')->get(['id', 'name'])->pluck('name');
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

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->trashed()) {
            return back()->withErrors(['message' => __('Cannot update deleted user.')]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'is_active' => ['required', 'boolean'],
            'roles' => ['array'],
            'roles.*' => ['string'],
        ]);

        if ($request->user()->id === $user->id && isset($data['is_active']) && $data['is_active'] === false) {
            return back()->withErrors(['message' => __('You cannot deactivate your own account.')]);
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'is_active' => (bool) $data['is_active'],
        ];
        if (!empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        return redirect()->route('admin.users.index')->with('success', __('User updated.'));
    }
}
