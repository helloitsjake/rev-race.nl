<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimulationLog extends Model
{
    public const LIMIT = 10;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip_address',
        'identifier',
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

    public static function identifier(?User $user, string $ip): string
    {
        return $user ? "user:{$user->id}" : "ip:{$ip}";
    }
}
