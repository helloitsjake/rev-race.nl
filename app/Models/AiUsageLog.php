<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    public const UPDATED_AT = null;

    /** Er is daadwerkelijk een API-call gedaan en die leverde een motor op. */
    public const OUTCOME_SUCCESS = 'success';

    /** Er is een API-call gedaan, Claude wees de invoer af als niet-motorfiets. */
    public const OUTCOME_REJECTED = 'rejected';

    /** Afgewezen op basis van de negatieve cache: geen API-call, geen kosten. */
    public const OUTCOME_CACHED = 'cached';

    /** Tegen een limiet aangelopen: geen API-call, geen kosten. */
    public const OUTCOME_BLOCKED = 'blocked';

    /** De API-call ging de deur uit maar mislukte. */
    public const OUTCOME_ERROR = 'error';

    protected $fillable = [
        'purpose',
        'outcome',
        'user_id',
        'ip_address',
        'model',
        'query',
        'input_tokens',
        'output_tokens',
        'cost_usd',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'cost_usd' => 'decimal:6',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Outcomes waarbij er echt een call de deur uit is gegaan en er dus geld is besteed.
     */
    public function billed(): bool
    {
        return in_array($this->outcome, [self::OUTCOME_SUCCESS, self::OUTCOME_REJECTED, self::OUTCOME_ERROR], true);
    }
}
