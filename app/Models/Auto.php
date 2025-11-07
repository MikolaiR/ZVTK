<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Auto extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->useFallbackUrl('/images/not_photo.png');
        $this->addMediaCollection('videos');
        $this->addMediaCollection('documents');
    }
}
