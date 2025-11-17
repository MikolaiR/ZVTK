<?php

namespace App\Services;

use App\Models\AutoModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AutoModelService
{
    /**
     * Build models list with filters (search by model or brand name).
     *
     * @param array{search?:string,show_deleted?:bool} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $showDeleted = (bool) ($filters['show_deleted'] ?? false);

        $query = AutoModel::query()
            ->with(['brand:id,name'])
            ->select(['id', 'name', 'auto_brand_id', 'deleted_at', 'created_at']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('brand', function ($b) use ($search) {
                      $b->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($showDeleted) {
            $query->withTrashed();
        }

        return $query->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Create new model.
     *
     * @param array{name:string,auto_brand_id:int} $data
     */
    public function create(array $data): AutoModel
    {
        return AutoModel::create([
            'name' => $data['name'],
            'auto_brand_id' => $data['auto_brand_id'],
        ]);
    }

    /**
     * Update model.
     *
     * @param array{name:string,auto_brand_id:int} $data
     */
    public function update(AutoModel $model, array $data): AutoModel
    {
        $model->update([
            'name' => $data['name'],
            'auto_brand_id' => $data['auto_brand_id'],
        ]);

        return $model;
    }

    /**
     * Soft delete model.
     */
    public function delete(AutoModel $model): void
    {
        if (! $model->trashed()) {
            $model->delete();
        }
    }

    /**
     * Restore soft-deleted model by id.
     */
    public function restore(int $id): AutoModel
    {
        $model = AutoModel::withTrashed()->findOrFail($id);
        $model->restore();
        return $model;
    }
}
