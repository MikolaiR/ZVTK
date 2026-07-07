<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AutoModel;
use App\Services\AutoModelService;
use App\Services\AutoBrandService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Admin\AutoModel\StoreAutoModelRequest;
use App\Http\Requests\Admin\AutoModel\UpdateAutoModelRequest;

class AutoModelController extends Controller
{
    public function __construct(
        private readonly AutoModelService $models,
        private readonly AutoBrandService $brands
    ) {}

    public function index(Request $request): View
    {
        $search = (string) $request->query('search', '');
        $showDeleted = (bool) $request->boolean('show_deleted', false);

        $models = $this->models->list([
            'search' => $search,
            'show_deleted' => $showDeleted,
        ]);

        return view('admin.auto-models.index', [
            'filters' => [
                'search' => $search,
                'show_deleted' => $showDeleted,
            ],
            'models' => $models->through(fn (AutoModel $m) => [
                'id' => $m->id,
                'name' => $m->name,
                'brand' => $m->brand ? [
                    'id' => $m->brand->id,
                    'name' => $m->brand->name,
                ] : null,
                'deleted_at' => $m->deleted_at,
                'created_at' => $m->created_at?->toDateTimeString(),
            ]),
        ]);
    }

    public function create(): View
    {
        $brands = $this->brands->getAllActiveForSelect();
        return view('admin.auto-models.create', [
            'brands' => $brands,
        ]);
    }

    public function store(StoreAutoModelRequest $request): RedirectResponse
    {
        $this->models->create($request->validated());
        return redirect()->route('admin.auto.models.index')->with('success', __('Model created.'));
    }

    public function edit(AutoModel $model): View
    {
        $brands = $this->brands->getAllActiveForSelect();
        return view('admin.auto-models.edit', [
            'model' => [
                'id' => $model->id,
                'name' => $model->name,
                'auto_brand_id' => $model->auto_brand_id,
                'deleted_at' => $model->deleted_at,
            ],
            'brands' => $brands,
        ]);
    }

    public function update(UpdateAutoModelRequest $request, AutoModel $model): RedirectResponse
    {
        $this->models->update($model, $request->validated());
        return redirect()->route('admin.auto.models.index')->with('success', __('Model updated.'));
    }

    public function destroy(AutoModel $model): RedirectResponse
    {
        $this->models->delete($model);
        return back()->with('success', __('Model deleted.'));
    }

    public function restore(int $id): RedirectResponse
    {
        $this->models->restore($id);
        return redirect()->route('admin.auto.models.index')->with('success', __('Model restored.'));
    }
}
