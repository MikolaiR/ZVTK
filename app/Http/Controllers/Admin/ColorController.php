<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Services\ColorService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Admin\Color\StoreColorRequest;
use App\Http\Requests\Admin\Color\UpdateColorRequest;

class ColorController extends Controller
{
    public function __construct(private readonly ColorService $colors)
    {
    }

    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $showDeleted = (bool) $request->boolean('show_deleted', false);

        $colors = $this->colors->list([
            'search' => $search,
            'show_deleted' => $showDeleted,
        ]);

        return Inertia::render('Admin/Colors/Index', [
            'filters' => [
                'search' => $search,
                'show_deleted' => $showDeleted,
            ],
            'colors' => $colors->through(fn (Color $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'deleted_at' => $c->deleted_at,
                'created_at' => $c->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Colors/Create');
    }

    public function store(StoreColorRequest $request): RedirectResponse
    {
        $this->colors->create($request->validated());
        return redirect()->route('admin.colors.index')->with('success', __('Color created.'));
    }

    public function edit(Color $color): Response
    {
        return Inertia::render('Admin/Colors/Edit', [
            'color' => [
                'id' => $color->id,
                'name' => $color->name,
                'deleted_at' => $color->deleted_at,
            ],
        ]);
    }

    public function update(UpdateColorRequest $request, Color $color): RedirectResponse
    {
        $this->colors->update($color, $request->validated());
        return redirect()->route('admin.colors.index')->with('success', __('Color updated.'));
    }

    public function destroy(Color $color): RedirectResponse
    {
        $this->colors->delete($color);
        return back()->with('success', __('Color deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->colors->restore($id);
        return redirect()->route('admin.colors.index')->with('success', __('Color restored.'));
    }
}
