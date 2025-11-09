<?php

namespace App\Models;

use App\Observers\ProviderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ProviderObserver::class])]
class Provider extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function autos(): HasMany
    {
        return $this->hasMany(Auto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
