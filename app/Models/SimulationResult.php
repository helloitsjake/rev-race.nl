<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationResult extends Model
{
    protected $fillable = [
        'share_code',
        'user_id',
        'motor_a_id',
        'motor_b_id',
        'road_type',
        'road_condition',
        'distance_m',
        'rider_a_kg',
        'rider_b_kg',
        'time_a_s',
        'time_b_s',
        'winner',
        'samples',
    ];

    protected function casts(): array
    {
        return [
            'distance_m' => 'integer',
            'rider_a_kg' => 'integer',
            'rider_b_kg' => 'integer',
            'time_a_s' => 'float',
            'time_b_s' => 'float',
            'samples' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function motorA(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'motor_a_id');
    }

    public function motorB(): BelongsTo
    {
        return $this->belongsTo(Motor::class, 'motor_b_id');
    }
}
