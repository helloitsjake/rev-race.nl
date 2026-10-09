<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Eén plek waar bepaald wordt met welk OpenAI-model RevRace werkt. Services vragen het model
 * hier op en nooit direct via config(), zodat een verkeerd ingevulde OPENAI_MODEL in .env
 * nooit tot een duur model kan leiden.
 */
class AiModel
{
    /**
     * Het model dat gebruikt wordt als OPENAI_MODEL leeg of geblokkeerd is. Gekozen op
     * 9 okt 2026: $0,75 in / $4,50 uit per miljoen tokens, ruim goed genoeg voor het
     * herschrijven van nieuws en het opzoeken van specificaties.
     */
    public const FALLBACK = 'gpt-5.4-mini';

    /**
     * Modelfamilies die dit project nooit mag aanroepen: de pro- en topmodellen kosten een
     * veelvoud en voegen voor deze taken niets toe. Match is op substring.
     */
    private const BLOCKED = ['-pro', 'gpt-5.5', 'gpt-5.6', 'deep-research', 'o3'];

    public static function resolve(): string
    {
        $model = strtolower(trim((string) config('services.openai.model')));

        if ($model === '') {
            return self::FALLBACK;
        }

        foreach (self::BLOCKED as $blocked) {
            if (str_contains($model, $blocked)) {
                Log::warning(sprintf(
                    'OPENAI_MODEL staat op "%s". Dat model is geblokkeerd voor RevRace; er wordt %s gebruikt.',
                    $model,
                    self::FALLBACK,
                ));

                return self::FALLBACK;
            }
        }

        return $model;
    }
}
