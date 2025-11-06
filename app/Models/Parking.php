<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parking extends Model
{
    use SoftDeletes;

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function locationPeriods(): MorphMany
    {
        return $this->morphMany(AutoLocationPeriod::class, 'location');
    }
}
