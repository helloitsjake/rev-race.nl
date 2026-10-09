<?php

namespace App\Services;

use App\Mail\AiBudgetWarning;
use App\Models\AiUsageLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Alles wat de OpenAI API kost gaat hier langs, zowel vooraf (mag deze call?) als
 * achteraf (wat heeft hij gekost?). Twee limieten, in deze volgorde:
 *
 *  1. Het globale dagbudget. Dit is de noodrem: is die op, dan gaat er niets meer uit,
 *     ongeacht wie het vraagt. Zonder deze limiet is per-IP throttling zinloos, want een
 *     aanvaller met tien IP's heeft simpelweg tien keer zo veel ruimte.
 *  2. De limiet per account per 24 uur.
 *
 * De kosten komen uit de echte token-aantallen in het API-antwoord, niet uit een schatting,
 * zodat het dagbudget klopt met wat OpenAI in rekening brengt.
 */
class AiSpendGuard
{
    public const PURPOSE_MOTOR_LOOKUP = 'motor_lookup';
    public const PURPOSE_NEWS_ARTICLE = 'news_article';

    public const BLOCKED_BUDGET = 'budget';
    public const BLOCKED_USER_LIMIT = 'user_limit';

    /**
     * Mag er nu een call uit voor dit doel? Geeft null als het mag, anders de reden.
     */
    public function blockedReason(string $purpose, ?User $user = null): ?string
    {
        if ($this->spentToday() >= $this->dailyBudget()) {
            return self::BLOCKED_BUDGET;
        }

        if ($purpose === self::PURPOSE_MOTOR_LOOKUP && $this->userLookupsToday($user) >= $this->userLimit()) {
            return self::BLOCKED_USER_LIMIT;
        }

        return null;
    }

    public function spentToday(): float
    {
        return (float) AiUsageLog::query()
            ->where('created_at', '>=', now()->startOfDay())
            ->sum('cost_usd');
    }

    public function dailyBudget(): float
    {
        return (float) config('ai.daily_budget_usd');
    }

    public function userLimit(): int
    {
        return (int) config('ai.lookups_per_user_per_day');
    }

    /**
     * Alleen calls die echt geld hebben gekost tellen mee voor de persoonlijke limiet.
     * Een afwijzing uit de negatieve cache kost niets en mag iemand dus niet opzadelen
     * met een verbruikte poging.
     */
    public function userLookupsToday(?User $user): int
    {
        if ($user === null) {
            return 0;
        }

        return AiUsageLog::query()
            ->where('user_id', $user->id)
            ->where('purpose', self::PURPOSE_MOTOR_LOOKUP)
            ->whereIn('outcome', [
                AiUsageLog::OUTCOME_SUCCESS,
                AiUsageLog::OUTCOME_REJECTED,
                AiUsageLog::OUTCOME_ERROR,
            ])
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    /**
     * Legt een geblokkeerde poging vast. Kost niets, maar is wel het signaal waaraan je
     * misbruik herkent: honderden geblokkeerde pogingen van één IP is geen bezoeker.
     */
    public function recordBlocked(string $purpose, string $reason, ?User $user, ?string $ip, ?string $query = null): AiUsageLog
    {
        return AiUsageLog::query()->create([
            'purpose' => $purpose,
            'outcome' => AiUsageLog::OUTCOME_BLOCKED,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'model' => $reason,
            'query' => $query === null ? null : Str::limit($query, 200, ''),
            'input_tokens' => 0,
            'output_tokens' => 0,
            'cost_usd' => 0,
        ]);
    }

    /**
     * Legt een afwijzing uit de negatieve cache vast. Geen API-call, geen kosten.
     */
    public function recordCached(string $purpose, ?User $user, ?string $ip, string $query): AiUsageLog
    {
        return AiUsageLog::query()->create([
            'purpose' => $purpose,
            'outcome' => AiUsageLog::OUTCOME_CACHED,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'model' => null,
            'query' => Str::limit($query, 200, ''),
            'input_tokens' => 0,
            'output_tokens' => 0,
            'cost_usd' => 0,
        ]);
    }

    /**
     * Legt een echt gedane call vast, met de token-aantallen uit het API-antwoord.
     *
     * @param  array<string, mixed>|null  $usage  input_tokens en output_tokens (OpenAiClient vertaalt de OpenAI-velden)
     */
    public function recordCall(
        string $purpose,
        string $outcome,
        string $model,
        ?array $usage,
        ?User $user = null,
        ?string $ip = null,
        ?string $query = null,
    ): AiUsageLog {
        $inputTokens = (int) ($usage['input_tokens'] ?? 0);
        $outputTokens = (int) ($usage['output_tokens'] ?? 0);

        $log = AiUsageLog::query()->create([
            'purpose' => $purpose,
            'outcome' => $outcome,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'model' => $model,
            'query' => $query === null ? null : Str::limit($query, 200, ''),
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'cost_usd' => $this->cost($model, $inputTokens, $outputTokens),
        ]);

        $this->warnIfBudgetRunningOut();

        return $log;
    }

    /**
     * Kosten in dollars. Een model dat niet in config/ai.php staat wordt bewust tegen de
     * duurste tarieven gerekend, zodat een onbekend model het dagbudget niet stil oprekt.
     */
    public function cost(string $model, int $inputTokens, int $outputTokens): float
    {
        $prices = config('ai.prices');
        $price = $prices[$model] ?? $prices['unknown'];

        return round(
            ($inputTokens / 1_000_000) * $price['input']
            + ($outputTokens / 1_000_000) * $price['output'],
            6,
        );
    }

    /**
     * Eén mail per dag zodra het budget over de waarschuwingsdrempel gaat. Het cache-slot
     * voorkomt dat een aanval een mailstorm wordt.
     */
    private function warnIfBudgetRunningOut(): void
    {
        $budget = $this->dailyBudget();

        if ($budget <= 0) {
            return;
        }

        $spent = $this->spentToday();
        $share = $spent / $budget;

        if ($share < (float) config('ai.budget_warning_at')) {
            return;
        }

        $key = 'ai-budget-warning:'.now()->toDateString();

        if (! Cache::add($key, true, now()->endOfDay())) {
            return;
        }

        Log::warning(sprintf(
            'AI-dagbudget staat op $%.4f van $%.2f (%.0f%%).',
            $spent,
            $budget,
            $share * 100,
        ));

        // Een mislukte mail mag een lookup nooit laten klappen: de limiet zelf is de
        // bescherming, de mail is alleen de melding.
        try {
            Mail::to(config('ai.alert_email'))->send(new AiBudgetWarning($spent, $budget));
        } catch (\Throwable $exception) {
            Log::error('Waarschuwingsmail AI-budget kon niet verstuurd worden: '.$exception->getMessage());
        }
    }
}
