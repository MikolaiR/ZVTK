<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoLocationPeriod extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function auto(): BelongsTo
    {
        return $this->belongsTo(Auto::class);
    }

    public function location(): MorphTo
    {
        return $this->morphTo();
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by_user_id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at');
    }
}
