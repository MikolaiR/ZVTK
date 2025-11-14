<?php

namespace App\Filters\Autos;

use Illuminate\Database\Eloquent\Builder;

class AutoFilters
{
    public static function apply(Builder $query, array $input): Builder
    {
        $map = [
            'vin' => new VinFilter(),
            'status' => new StatusFilter(),
        ];

        foreach ($map as $key => $filter) {
            if (array_key_exists($key, $input)) {
                $filter->apply($query, $input[$key]);
            }
        }

        return $query;
    }
}
