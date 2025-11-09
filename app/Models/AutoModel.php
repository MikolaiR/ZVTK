<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoModel extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $with = ['brand'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(AutoBrand::class, 'auto_brand_id', 'id');
    }
}
