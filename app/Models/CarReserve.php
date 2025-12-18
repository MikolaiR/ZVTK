<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarReserve extends Model
{
    /** @use HasFactory<\Database\Factories\CarReserveFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'car_reserves';

    protected $fillable = [
        'auto_id',
        'user_id',
    ];

    public function Auto()
    {
        return $this->belongsTo(Auto::class);
    }

    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
