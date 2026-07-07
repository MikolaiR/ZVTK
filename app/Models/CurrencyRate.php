<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = [
        'currency_code',
        'rate',
        'rate_date',
    ];

    protected $casts = [
        'rate' => 'float',
        'rate_date' => 'date',
    ];

    public function scopeForDate(Builder $query, string $currencyCode, Carbon $date): Builder
    {
        return $query
            ->where('currency_code', $currencyCode)
            ->whereDate('rate_date', $date->toDateString());
    }

    public static function getRate(string $currencyCode, ?Carbon $date = null): ?float
    {
        $date = $date ?: now();

        $rate = static::query()
            ->forDate($currencyCode, $date)
            ->value('rate');

        return $rate !== null ? (float) $rate : null;
    }
}
