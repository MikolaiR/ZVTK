<?php

namespace App\Services;

use App\Models\AutoBrand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AutoBrandService
{
    /**
     * Build brands list with filters.
     *
     * @param array{search?:string,show_deleted?:bool} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $showDeleted = (bool) ($filters['show_deleted'] ?? false);

        $query = AutoBrand::query()
            ->select(['id', 'name', 'deleted_at', 'created_at'])
            ->withCount(['models' => function ($q) {
                $q->whereNull('deleted_at');
            }]);

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($showDeleted) {
            $query->withTrashed();
        }

        return $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Create new brand.
     *
     * @param array{name:string} $data
     */
    public function create(array $data): AutoBrand
    {
        return AutoBrand::create([
            'id' => strtoupper($data['name']),
            'name' => $data['name'],
        ]);
    }

    /**
     * Update brand name.
     *
     * @param array{name:string} $data
     */
    public function update(AutoBrand $brand, array $data): AutoBrand
    {
        $brand->update([
            'id' => strtoupper($data['name']),
            'name' => $data['name'],
        ]);

        return $brand;
    }

    /**
     * Soft delete brand. Forbid when it has active models.
     */
    public function delete(AutoBrand $brand): void
    {
        $hasAnyModels = $brand->models()->withTrashed()->exists();
        if ($hasAnyModels) {
            abort(422, __('Cannot delete brand with existing models.'));
        }

        if (! $brand->trashed()) {
            $brand->delete();
        }
    }

    /**
     * Restore soft-deleted brand by id.
     */
    public function restore(int $id): AutoBrand
    {
        $brand = AutoBrand::withTrashed()->findOrFail($id);
        $brand->restore();
        return $brand;
    }

    /**
     * Get all non-deleted brands for selects.
     */
    public function getAllActiveForSelect(): \Illuminate\Support\Collection
    {
        return AutoBrand::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();
    }
}
