<?php

namespace App\Filters\Autos;

use App\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements Filter
{
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '' ) {
            return;
        }
        $int = (int) $value;
        if ($int <= 0) {
            return;
        }
        $query->where('status', $int);
    }
}
