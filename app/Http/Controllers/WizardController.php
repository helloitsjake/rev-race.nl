<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Services\MotorAdvisor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WizardController extends Controller
{
    /**
     * @return array<string, string>
     */
    public static function experienceLevels(): array
    {
        return [
            'beginner' => 'Net rijbewijs / A2',
            'ervaren' => 'Vol rijbewijs / ervaren',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function voorkeuren(): array
    {
        return [
            'bochten' => 'Ik houd van bochten en leunhoek',
            'snelheid' => 'Ik wil vooral snel zijn op het rechte stuk',
            'relax' => 'Geen voorkeur, ik rij voor het plezier',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function terreinen(): array
    {
        return [
            'snelweg' => 'Snelweg, lange afstanden',
            'binnendoor' => 'Binnendoor, kronkelwegen',
            'bergen' => 'Bergen, pashaarspeldbochten',
        ];
    }

    /**
     * Hoeveel gescoorde motoren er maximaal als "aanbevolen voor jou" getoond worden.
     * De rest van de matches blijft opvraagbaar via "bekijk alle modellen".
     */
    private const TOP_MATCH_COUNT = 6;

    public function index(Request $request, MotorAdvisor $advisor): View
    {
        $ervaring = $request->query('ervaring');
        $ervaring = array_key_exists((string) $ervaring, self::experienceLevels()) ? $ervaring : null;

        $voorkeur = $request->query('voorkeur');
        $voorkeur = array_key_exists((string) $voorkeur, self::voorkeuren()) ? $voorkeur : null;

        $terrein = array_values(array_intersect((array) $request->query('terrein', []), array_keys(self::terreinen())));

        $leeftijd = $request->query('leeftijd');
        $lengte = $request->query('lengte');
        $gewicht = $request->query('gewicht');

        $merk = $request->query('merk');
        $merk = is_string($merk) && trim($merk) !== '' ? trim($merk) : null;

        $hasAnswers = $ervaring && ($voorkeur || $terrein);

        $anyMatches = null;
        $topMatches = collect();
        $moreMatches = collect();
        $availableBrands = collect();
        $merkFallbackUsed = false;
        $fallback = null;
        $topCategories = [];
        $reasonParts = [];

        if ($hasAnswers) {
            $advice = $advisor->advise($ervaring, $voorkeur, $terrein);
            $topCategories = $advice['categories'];
            $anyMatches = $advice['matches'];

            if ($anyMatches->isEmpty() && $ervaring === 'beginner') {
                $fallback = $advisor->match([], 'beginner');
            }

            if ($anyMatches->isNotEmpty()) {
                $availableBrands = $anyMatches->pluck('brand')->unique()->sort()->values();

                $selected = $merk ? $anyMatches->filter(fn (Motor $motor) => $motor->brand === $merk)->values() : $anyMatches;

                if ($merk && $selected->isEmpty()) {
                    $merkFallbackUsed = true;
                    $selected = $anyMatches;
                }

                $topMatches = $selected->take(self::TOP_MATCH_COUNT)->values();
                $moreMatches = $selected->slice(self::TOP_MATCH_COUNT)
                    ->sortBy([['brand', 'asc'], ['model', 'asc']])
                    ->values();
            }

            if ($voorkeur) {
                $reasonParts[] = Str::lower(self::voorkeuren()[$voorkeur]);
            }
            foreach ($terrein as $key) {
                $reasonParts[] = Str::lower(self::terreinen()[$key]);
            }
        }

        return view('wizard', [
            'voorkeuren' => self::voorkeuren(),
            'terreinen' => self::terreinen(),
            'experienceLevels' => self::experienceLevels(),
            'selectedErvaring' => $ervaring,
            'selectedVoorkeur' => $voorkeur,
            'selectedTerrein' => $terrein,
            'leeftijd' => $leeftijd,
            'lengte' => $lengte,
            'gewicht' => $gewicht,
            'selectedMerk' => $merk,
            'anyMatches' => $anyMatches,
            'topMatches' => $topMatches,
            'moreMatches' => $moreMatches,
            'availableBrands' => $availableBrands,
            'merkFallbackUsed' => $merkFallbackUsed,
            'fallback' => $fallback,
            'topCategories' => $topCategories,
            'reasonParts' => $reasonParts,
        ]);
    }
}
