<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotorReport extends Model
{
    protected $fillable = [
        'motor_id',
        'reporter_email',
        'message',
        'status',
    ];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
