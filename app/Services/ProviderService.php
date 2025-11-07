<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProviderService
{
    /**
     * Build providers list with filters.
     *
     * @param array{search?:string,show_deleted?:bool} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $showDeleted = (bool) ($filters['show_deleted'] ?? false);

        $query = Provider::query()
            ->with(['user:id,name,email'])
            ->select(['id', 'name', 'user_id', 'deleted_at', 'created_at']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($showDeleted) {
            $query->withTrashed();
        }

        return $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();
    }

    /**
     * Create provider.
     *
     * @param array{name:string,user_id?:int|null} $data
     */
    public function create(array $data): Provider
    {
        return Provider::create([
            'name' => $data['name'],
            'user_id' => $data['user_id'] ?? null,
        ]);
    }

    /**
     * Update provider.
     *
     * @param array{name:string,user_id?:int|null} $data
     */
    public function update(Provider $provider, array $data): Provider
    {
        $provider->update([
            'name' => $data['name'],
            'user_id' => $data['user_id'] ?? null,
        ]);

        return $provider;
    }

    /**
     * Soft delete provider.
     */
    public function delete(Provider $provider): void
    {
        if (! $provider->trashed()) {
            $provider->delete();
        }
    }

    /**
     * Restore soft-deleted provider by id.
     */
    public function restore(int $id): Provider
    {
        $provider = Provider::withTrashed()->findOrFail($id);
        $provider->restore();
        return $provider;
    }

    /**
     * Get users for select.
     *
     * @return Collection<int, array{id:int,name:string,email:string}>
     */
    public function getUsersForSelect(): Collection
    {
        return User::query()
            ->select(['id', 'name', 'email'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ]);
    }
}
