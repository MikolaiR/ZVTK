<?php

namespace App\Services\Autos\Transitions;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Parking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MoveToParkingTransition implements AutoTransition
{
    public function handle(Auto $auto, array $data, User $actor): void
    {
        $parkingId = (int) $data['parking_id'];
        Parking::query()->whereKey($parkingId)->firstOrFail();
        $now = now();

        DB::transaction(function () use ($auto, $parkingId, $actor, $data, $now) {
            // close active period
            $active = $auto->locationPeriods()->whereNull('ended_at')->latest('started_at')->first();
            if ($active) {
                $active->update(['ended_at' => $now]);
            }

            // new period delivery to parking (destination known)
            AutoLocationPeriod::create([
                'auto_id' => $auto->id,
                'location_type' => Parking::class,
                'location_id' => $parkingId,
                'started_at' => $now,
                'ended_at' => null,
                'accepted_by_user_id' => $actor->id,
                'acceptance_note' => $data['note'] ?? null,
                'status' => Statuses::DeliveryToParking->value,
            ]);

            $auto->update(['status' => Statuses::DeliveryToParking->value]);

            foreach ((array)($data['photos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('photos'); }
            foreach ((array)($data['videos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('videos'); }
            foreach ((array)($data['documents'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('documents'); }
        });
    }
}
