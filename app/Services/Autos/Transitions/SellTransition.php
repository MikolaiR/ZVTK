<?php

namespace App\Services\Autos\Transitions;

use App\Enums\Statuses;
use App\Models\Auto;
use App\Models\AutoSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SellTransition implements AutoTransition
{
    public function handle(Auto $auto, array $data, User $actor): void
    {
        $soldAt = !empty($data['sold_at']) ? Carbon::parse($data['sold_at']) : now();
        DB::transaction(function () use ($auto, $actor, $data, $soldAt) {
            // Close active period at sold time if exists
            $active = $auto->locationPeriods()->whereNull('ended_at')->latest('started_at')->first();
            if ($active) {
                $active->update(['ended_at' => $soldAt]);
            }

            AutoSale::create([
                'auto_id' => $auto->id,
                'sold_at' => $soldAt,
                'sold_by_user_id' => $actor->id,
                'note' => $data['note'] ?? null,
            ]);

            $auto->update(['status' => Statuses::Sale->value]);

            foreach ((array)($data['photos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('photos'); }
            foreach ((array)($data['videos'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('videos'); }
            foreach ((array)($data['documents'] ?? []) as $file) { $auto->addMedia($file)->toMediaCollection('documents'); }
        });
    }
}
