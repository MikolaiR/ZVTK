<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AutoSale extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function auto(): BelongsTo
    {
        return $this->belongsTo(Auto::class);
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by_user_id');
    }
}
