<?php

namespace App\Services\Autos;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\Price;
use Carbon\Carbon;

class ParkingCostCalculator
{
    /**
     * Calculate storage cost per parking across all Parking periods.
     *
     * @return array{total_days:int,total_cost:int,per_parkings:array<int,array{parking:array{id:int,name:?string},total_days:int,total_cost:int,days:array<int,array{date:string,price:int}>}>}
     */
    public function calculate(Auto $auto, ?Carbon $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?: now();

        $periods = $auto->locationPeriods()
            ->where('status', Statuses::Parking->value)
            ->with('location:id,name')
            ->orderBy('started_at')
            ->get();

        $grouped = [];
        foreach ($periods as $p) {
            $parkingId = (int) $p->location_id;
            $start = Carbon::parse($p->started_at)->startOfDay();
            $endExclusive = $p->ended_at ? Carbon::parse($p->ended_at)->startOfDay() : $asOfDate->copy()->startOfDay();
            if ($endExclusive->lessThanOrEqualTo($start)) {
                continue;
            }

            $days = [];
            $cursor = $start->copy();
            while ($cursor->lt($endExclusive)) {
                $priceRow = Price::query()
                    ->where('parking_id', $parkingId)
                    ->forDate($cursor)
                    ->orderByDesc('date_start')
                    ->first();
                $price = (int) ($priceRow?->price ?? 0);
                $days[] = [
                    'date' => $cursor->toDateString(),
                    'price' => $price,
                ];
                $cursor->addDay();
            }

            if (!isset($grouped[$parkingId])) {
                $grouped[$parkingId] = [
                    'parking' => [
                        'id' => $parkingId,
                        'name' => $p->location->name ?? null,
                    ],
                    'days' => [],
                    'total_days' => 0,
                    'total_cost' => 0,
                ];
            }
            $grouped[$parkingId]['days'] = array_merge($grouped[$parkingId]['days'], $days);
            $grouped[$parkingId]['total_days'] += count($days);
            $grouped[$parkingId]['total_cost'] += array_sum(array_column($days, 'price'));
        }

        $totalDays = 0;
        $totalCost = 0;
        foreach ($grouped as $g) {
            $totalDays += $g['total_days'];
            $totalCost += $g['total_cost'];
        }

        return [
            'total_days' => $totalDays,
            'total_cost' => $totalCost,
            'per_parkings' => array_values($grouped),
        ];
    }
}
