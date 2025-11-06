<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Price extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    public function parking(): BelongsTo
    {
        return $this->belongsTo(Parking::class);
    }

    public function scopeForDate($query, $date)
    {
        $d = $date instanceof Carbon ? $date->toDateString() : (string) $date;
        return $query->whereDate('date_start', '<=', $d)
            ->where(function ($q) use ($d) {
                $q->whereNull('date_end')->orWhereDate('date_end', '>=', $d);
            });
    }
}
