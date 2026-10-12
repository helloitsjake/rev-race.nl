<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutboundClick extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['motor_id', 'destination', 'source'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
