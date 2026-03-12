<?php

namespace App\Filters\Autos;

use App\Enums\Statuses;
use App\Filters\Filter;
use App\Models\Parking;
use Illuminate\Database\Eloquent\Builder;

class ParkingFilter implements Filter
{
    public function apply(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $parkingId = (int) $value;
        if ($parkingId <= 0) {
            return;
        }

        $query
            ->where('status', Statuses::Parking)
            ->whereHas('currentLocation', function (Builder $locationQuery) use ($parkingId) {
                $locationQuery
                    ->where('location_type', Parking::class)
                    ->where('location_id', $parkingId)
                    ->whereNull('ended_at');
            });
    }
}
