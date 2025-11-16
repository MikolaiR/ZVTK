<?php

namespace App\Models;

use App\Observers\ParkingObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ParkingObserver::class])]
class Parking extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function locationPeriods(): MorphMany
    {
        return $this->morphMany(AutoLocationPeriod::class, 'location');
    }
}
