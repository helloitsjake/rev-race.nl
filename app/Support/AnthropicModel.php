<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Eén plek waar bepaald wordt met welk Claude-model dit project de Anthropic API
 * aanroept. Services vragen het model hier op en nooit direct via config(), zodat
 * een verkeerd ingevulde ANTHROPIC_MODEL in .env nooit tot een geblokkeerd model
 * kan leiden.
 */
class AnthropicModel
{
    /**
     * Het model dat gebruikt wordt als ANTHROPIC_MODEL leeg is of geblokkeerd is.
     */
    public const FALLBACK = 'claude-sonnet-4-6';

    /**
     * Modelfamilies die dit project nooit mag aanroepen. Fable 5 en Mythos 5 zijn de
     * duurste modellen ($10 in / $50 out per miljoen tokens, tegenover $3/$15 voor
     * Sonnet) en voegen voor het herschrijven van nieuwsberichten en het opzoeken van
     * motorspecificaties niets toe. Match is op substring, dus ook 'claude-fable-6' of
     * een variant met datumsuffix wordt geblokkeerd.
     */
    private const BLOCKED = ['fable', 'mythos'];

    public static function resolve(): string
    {
        $model = trim((string) config('services.anthropic.model'));

        if ($model === '') {
            return self::FALLBACK;
        }

        foreach (self::BLOCKED as $blocked) {
            if (str_contains(strtolower($model), $blocked)) {
                Log::warning(sprintf(
                    'ANTHROPIC_MODEL staat op "%s". Dat model is geblokkeerd voor RevRace; er wordt %s gebruikt.',
                    $model,
                    self::FALLBACK,
                ));

                return self::FALLBACK;
            }
        }

        return $model;
    }
}
