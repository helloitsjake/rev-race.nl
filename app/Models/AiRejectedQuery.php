<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AiRejectedQuery extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'normalized_query',
        'original_query',
        'hits',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'hits' => 'integer',
        ];
    }

    /**
     * Kleine letters, samengevouwen witruimte en interpunctie eraf, zodat "Fiets!!",
     * "fiets" en "  FIETS " als dezelfde afwijzing gelden en een aanvaller de cache niet
     * omzeilt met een hoofdletter.
     */
    public static function normalize(string $query): string
    {
        $normalized = Str::lower(trim($query));
        $normalized = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $normalized) ?? $normalized;
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return Str::limit(trim($normalized), 200, '');
    }
}
