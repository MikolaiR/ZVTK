<?php

namespace App\Services;

use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilderContract;
use Illuminate\Database\DatabaseManager as DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AutoLocationService
{
    public function __construct(private readonly DB $db)
    {
    }

    /**
     * Open a new location period for the auto.
     * Ensures there is no more than one active period by closing the previous one in a transaction.
     */
    public function moveToLocation(Auto $auto, Model $location, int $acceptedByUserId, ?string $note = null, ?Carbon $at = null): AutoLocationPeriod
    {
        $at = $at ?: now();

        return $this->db->transaction(function () use ($auto, $location, $acceptedByUserId, $note, $at) {
            $this->closeActivePeriod($auto, $at);

            return $auto->locationPeriods()->create([
                'location_type' => $location->getMorphClass(),
                'location_id' => $location->getKey(),
                'started_at' => $at,
                'accepted_by_user_id' => $acceptedByUserId,
                'acceptance_note' => $note,
            ]);
        });
    }

    /**
     * Close the currently active period, if any.
     */
    public function closeActivePeriod(Auto $auto, ?Carbon $endedAt = null): void
    {
        $endedAt = $endedAt ?: now();

        /** @var Builder $active */
        $active = $auto->locationPeriods()->whereNull('ended_at');
        /** @var AutoLocationPeriod|null $period */
        $period = $active->first();

        if ($period) {
            $period->update(['ended_at' => $endedAt]);
        }
    }

    /**
     * Get the active period for the auto.
     */
    public function getActivePeriod(Auto $auto): ?AutoLocationPeriod
    {
        /** @var AutoLocationPeriod|null $period */
        $period = $auto->locationPeriods()->whereNull('ended_at')->latest('started_at')->first();
        return $period;
    }

    /**
     * Query periods overlapping a given date range (inclusive, by calendar date).
     */
    public function queryOverlappingPeriods(Auto $auto, Carbon $from, Carbon $to): Builder|QueryBuilderContract
    {
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        return $auto->locationPeriods()
            ->whereDate('started_at', '<=', $toDate)
            ->where(function ($q) use ($fromDate) {
                $q->whereNull('ended_at')->orWhereDate('ended_at', '>=', $fromDate);
            });
    }
}
