<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GarageMotor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'motor_id',
        'nickname',
        'sort_order',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
