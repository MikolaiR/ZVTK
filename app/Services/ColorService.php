<?php

namespace App\Services;

use App\Models\Color;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ColorService
{
    /**
     * Build colors list with filters.
     *
     * @param array{search?:string,show_deleted?:bool} $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $showDeleted = (bool) ($filters['show_deleted'] ?? false);

        $query = Color::query()
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
     * Create color.
     *
     * @param array{name:string} $data
     */
    public function create(array $data): Color
    {
        return Color::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * Update color.
     *
     * @param array{name:string} $data
     */
    public function update(Color $color, array $data): Color
    {
        $color->update([
            'name' => $data['name'],
        ]);

        return $color;
    }

    /**
     * Soft delete color.
     */
    public function delete(Color $color): void
    {
        if (! $color->trashed()) {
            $color->delete();
        }
    }

    /**
     * Restore soft-deleted color by id.
     */
    public function restore(int $id): Color
    {
        $color = Color::withTrashed()->findOrFail($id);
        $color->restore();
        return $color;
    }
}
