<?php

namespace App\Services;

use App\Models\SimulationLog;
use App\Models\User;
use Carbon\CarbonImmutable;

class SimulationLimitService
{
    /**
     * @return array{blocked: bool, used: int, limit: int, remaining: int, reset_at: string|null, seconds: int|null}
     */
    public function status(?User $user, string $ip): array
    {
        if ($user?->isPremium()) {
            return [
                'blocked' => false,
                'used' => 0,
                'limit' => SimulationLog::LIMIT,
                'remaining' => SimulationLog::LIMIT,
                'reset_at' => null,
                'seconds' => null,
            ];
        }

        $identifier = SimulationLog::identifier($user, $ip);
        $since = now()->subDay();
        $query = SimulationLog::query()
            ->where('identifier', $identifier)
            ->where('created_at', '>=', $since);

        $used = (int) $query->count();
        $oldest = $query->oldest('created_at')->first();
        $resetAt = $oldest ? CarbonImmutable::parse($oldest->created_at)->addDay() : null;

        return [
            'blocked' => $used >= SimulationLog::LIMIT,
            'used' => $used,
            'limit' => SimulationLog::LIMIT,
            'remaining' => max(0, SimulationLog::LIMIT - $used),
            'reset_at' => $resetAt?->toIso8601String(),
            'seconds' => $resetAt ? max(0, now()->diffInSeconds($resetAt, false)) : null,
        ];
    }

    public function record(?User $user, string $ip): void
    {
        SimulationLog::query()->create([
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'identifier' => SimulationLog::identifier($user, $ip),
            'created_at' => now(),
        ]);
    }
}
