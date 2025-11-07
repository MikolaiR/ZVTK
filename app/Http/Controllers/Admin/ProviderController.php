<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Services\ProviderService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Admin\Provider\StoreProviderRequest;
use App\Http\Requests\Admin\Provider\UpdateProviderRequest;

class ProviderController extends Controller
{
    public function __construct(private readonly ProviderService $providers)
    {
    }

    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $showDeleted = (bool) $request->boolean('show_deleted', false);

        $providers = $this->providers->list([
            'search' => $search,
            'show_deleted' => $showDeleted,
        ]);

        return Inertia::render('Admin/Providers/Index', [
            'filters' => [
                'search' => $search,
                'show_deleted' => $showDeleted,
            ],
            'providers' => $providers->through(fn (Provider $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'user' => $p->user ? [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'email' => $p->user->email,
                ] : null,
                'deleted_at' => $p->deleted_at,
                'created_at' => $p->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function create(): Response
    {
        $users = $this->providers->getUsersForSelect();
        return Inertia::render('Admin/Providers/Create', [
            'users' => $users,
        ]);
    }

    public function store(StoreProviderRequest $request): RedirectResponse
    {
        $this->providers->create($request->validated());
        return redirect()->route('admin.providers.index')->with('success', __('Provider created.'));
    }

    public function edit(Provider $provider): Response
    {
        $users = $this->providers->getUsersForSelect();
        return Inertia::render('Admin/Providers/Edit', [
            'provider' => [
                'id' => $provider->id,
                'name' => $provider->name,
                'user_id' => $provider->user_id,
                'deleted_at' => $provider->deleted_at,
            ],
            'users' => $users,
        ]);
    }

    public function update(UpdateProviderRequest $request, Provider $provider): RedirectResponse
    {
        $this->providers->update($provider, $request->validated());
        return redirect()->route('admin.providers.index')->with('success', __('Provider updated.'));
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        $this->providers->delete($provider);
        return back()->with('success', __('Provider deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->providers->restore($id);
        return redirect()->route('admin.providers.index')->with('success', __('Provider restored.'));
    }
}
