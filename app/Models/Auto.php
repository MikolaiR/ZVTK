<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Auto extends Model
{
    use SoftDeletes;

    public function locationPeriods(): HasMany
    {
        return $this->hasMany(AutoLocationPeriod::class);
    }

    public function currentLocation(): HasOne
    {
        return $this->hasOne(AutoLocationPeriod::class)
            ->whereNull('ended_at')
            ->latestOfMany('started_at');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(AutoSale::class);
    }
}
