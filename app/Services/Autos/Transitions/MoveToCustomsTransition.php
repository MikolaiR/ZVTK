<?php

namespace App\Services\Autos\Transitions;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoLocationPeriod;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MoveToCustomsTransition implements AutoTransition
{
    public function handle(Auto $auto, array $data, User $actor): void
    {
        $arrival = !empty($data['arrival_date']) ? Carbon::parse($data['arrival_date']) : now();
        $customerId = (int) $data['customer_id'];
        Customer::query()->whereKey($customerId)->firstOrFail();

        DB::transaction(function () use ($auto, $arrival, $customerId, $actor, $data) {
            // close active period (if exists)
            $active = $auto->locationPeriods()->whereNull('ended_at')->latest('started_at')->first();
            if ($active) {
                $active->update([
                    'ended_at' => $arrival,
                ]);
            }

            // new period at customs
            AutoLocationPeriod::create([
                'auto_id' => $auto->id,
                'location_type' => Customer::class,
                'location_id' => $customerId,
                'started_at' => $arrival,
                'ended_at' => null,
                'accepted_by_user_id' => $actor->id,
                'acceptance_note' => $data['note'] ?? null,
                'status' => Statuses::Customer->value,
            ]);

            $auto->update(['status' => Statuses::Customer->value]);

            foreach ((array)($data['photos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('photos'); }
            foreach ((array)($data['videos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('videos'); }
            foreach ((array)($data['documents'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('documents'); }
        });
    }
}
