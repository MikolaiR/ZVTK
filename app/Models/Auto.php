<?php

namespace App\Models;

use App\Enums\Statuses;
use App\Observers\AutoObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Support\MediaLibrary\AutoMediaConversions;

#[ObservedBy([AutoObserver::class])]
class Auto extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $guarded = [];

    protected function casts()
    {
        return [
            'status' => Statuses::class
        ];
    }
    protected static function booted(): void
    {
        static::addGlobalScope('company_visibility', function (Builder $builder) {
            $user = Auth::user();
            if ($user && ! $user->hasRole('admin')) {
                $builder->where('company_id', $user->company_id);
            }
        });
    }

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

    public function model(): BelongsTo
    {
        return $this->belongsTo(AutoModel::class, 'auto_model_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(Sender::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos')->useFallbackUrl('/images/not_photo.png')->useDisk('local');
        $this->addMediaCollection('videos')->useDisk('local');
        $this->addMediaCollection('documents')->useDisk('local');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        (new AutoMediaConversions())->register($this);
    }

}
