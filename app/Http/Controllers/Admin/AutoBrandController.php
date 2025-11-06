<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoBrand;
use App\Services\AutoBrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Admin\AutoBrand\StoreAutoBrandRequest;
use App\Http\Requests\Admin\AutoBrand\UpdateAutoBrandRequest;

class AutoBrandController extends Controller
{
    public function __construct(private readonly AutoBrandService $brands)
    {
    }

    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $showDeleted = (bool) $request->boolean('show_deleted', false);

        $brands = $this->brands->list([
            'search' => $search,
            'show_deleted' => $showDeleted,
        ]);

        return Inertia::render('Admin/AutoBrands/Index', [
            'filters' => [
                'search' => $search,
                'show_deleted' => $showDeleted,
            ],
            'brands' => $brands->through(fn (AutoBrand $b) => [
                'id' => $b->id,
                'name' => $b->name,
                'deleted_at' => $b->deleted_at,
                'models_count' => $b->models_count ?? 0,
                'created_at' => $b->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/AutoBrands/Create');
    }

    public function store(StoreAutoBrandRequest $request): RedirectResponse
    {
        $this->brands->create($request->validated());
        return redirect()->route('admin.auto.brands.index')->with('success', __('Brand created.'));
    }

    public function edit(AutoBrand $brand): Response
    {
        return Inertia::render('Admin/AutoBrands/Edit', [
            'brand' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'deleted_at' => $brand->deleted_at,
            ],
        ]);
    }

    public function update(UpdateAutoBrandRequest $request, AutoBrand $brand): RedirectResponse
    {
        $this->brands->update($brand, $request->validated());
        return redirect()->route('admin.auto.brands.index')->with('success', __('Brand updated.'));
    }

    public function destroy(AutoBrand $brand): RedirectResponse
    {
        $this->brands->delete($brand);
        return back()->with('success', __('Brand deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->brands->restore($id);
        return redirect()->route('admin.auto.brands.index')->with('success', __('Brand restored.'));
    }
}
