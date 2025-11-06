<?php

namespace App\Services;

use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Parking;
use App\Models\Price;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ParkingCostService
{
    public function calculateForAuto(Auto $auto, Carbon $from, Carbon $to): int
    {
        $total = 0;
        $parkingMorph = (new Parking())->getMorphClass();

        /** @var Builder $q */
        $q = $auto->locationPeriods()
            ->where('location_type', $parkingMorph)
            ->whereDate('started_at', '<=', $to->toDateString())
            ->where(function ($w) use ($from) {
                $w->whereNull('ended_at')->orWhereDate('ended_at', '>=', $from->toDateString());
            })
            ->orderBy('started_at');

        /** @var AutoLocationPeriod[] $periods */
        $periods = $q->get();

        foreach ($periods as $period) {
            $pFrom = Carbon::parse(max($period->started_at->toDateString(), $from->toDateString()));
            $pTo = Carbon::parse(min(($period->ended_at?->toDateString() ?? now()->toDateString()), $to->toDateString()));
            if ($pFrom->gt($pTo)) {
                continue;
            }
            $total += $this->calculateForParkingBetween((int) $period->location_id, $pFrom, $pTo);
        }

        return $total;
    }

    public function calculateForPeriod(AutoLocationPeriod $period): int
    {
        $from = Carbon::parse($period->started_at->toDateString());
        $to = Carbon::parse(($period->ended_at?->toDateString() ?? now()->toDateString()));
        return $this->calculateForParkingBetween((int) $period->location_id, $from, $to);
    }

    protected function calculateForParkingBetween(int $parkingId, Carbon $from, Carbon $to): int
    {
        $sum = 0;
        $d = $from->copy();
        while ($d->lte($to)) {
            $price = Price::query()
                ->where('parking_id', $parkingId)
                ->whereDate('date_start', '<=', $d->toDateString())
                ->where(function ($q) use ($d) {
                    $q->whereNull('date_end')->orWhereDate('date_end', '>=', $d->toDateString());
                })
                ->orderByDesc('date_start')
                ->value('price');

            $sum += (int) ($price ?? 0);
            $d->addDay();
        }
        return $sum;
    }
}
