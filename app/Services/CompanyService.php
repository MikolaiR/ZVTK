<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CompanyService
{
    /**
     * Build companies list with filters.
     *
     * @param array{search?:string,show_deleted?:bool} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $showDeleted = (bool) ($filters['show_deleted'] ?? false);

        $query = Company::query()
            ->select(['id', 'name', 'deleted_at', 'created_at']);

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
     * Create company.
     *
     * @param array{name:string} $data
     */
    public function create(array $data): Company
    {
        return Company::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Update company.
     *
     * @param array{name:string} $data
     */
    public function update(Company $company, array $data): Company
    {
        $company->update([
            'name' => $data['name'],
        ]);

        return $company;
    }

    /**
     * Soft delete company.
     */
    public function delete(Company $company): void
    {
        if (! $company->trashed()) {
            $company->delete();
        }
    }

    /**
     * Restore soft-deleted company by id.
     */
    public function restore(int $id): Company
    {
        $company = Company::withTrashed()->findOrFail($id);
        $company->restore();
        return $company;
    }
}
