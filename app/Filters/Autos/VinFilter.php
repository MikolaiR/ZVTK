<?php

namespace App\Filters\Autos;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class VinFilter implements Filter
{
    public function apply(Builder $query, mixed $value): void
    {
        $value = trim((string) $value);
        if ($value === '') {
            return;
        }

        $query->where('vin', 'like', "%{$value}%");
    }
}
