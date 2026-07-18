<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
     * Scorepunten per categorie op basis van elk antwoord. Geen exacte wetenschap, een redelijke
     * vertaling van rijstijl/gebruik naar het type motor dat daar doorgaans het best bij past.
     *
     * @return array<string, array<string, array<string, int>>>
     */
    private static function scoring(): array
    {
        return [
            'voorkeur' => [
                'bochten' => ['sport' => 3, 'naked' => 2, 'adventure' => 1],
                'snelheid' => ['sport' => 2, 'naked' => 2, 'tourer' => 1],
                'relax' => ['cruiser' => 2, 'retro' => 2, 'tourer' => 1],
            ],
            'terrein' => [
                'snelweg' => ['tourer' => 3, 'adventure' => 1, 'cruiser' => 1],
                'binnendoor' => ['naked' => 3, 'sport' => 1, 'retro' => 1],
                'bergen' => ['adventure' => 2, 'sport' => 2, 'naked' => 1],
            ],
        ];
    }

    /**
     * Hoeveel gescoorde motoren er maximaal als "aanbevolen voor jou" getoond worden.
     * De rest van de matches blijft opvraagbaar via "bekijk alle modellen".
     */
    private const TOP_MATCH_COUNT = 6;

    public function index(Request $request): View
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
            $scores = $this->scoreCategories($voorkeur, $terrein);
            $topCategories = $this->topCategories($scores);
            $anyMatches = $this->match($topCategories, $ervaring);

            if ($anyMatches->isEmpty() && $ervaring === 'beginner') {
                $fallback = $this->match([], 'beginner');
            }

            if ($anyMatches->isNotEmpty()) {
                $availableBrands = $anyMatches->pluck('brand')->unique()->sort()->values();

                $scored = $this->scoreMotors($anyMatches, $voorkeur, $ervaring);

                $selected = $merk ? $scored->filter(fn (Motor $motor) => $motor->brand === $merk)->values() : $scored;

                if ($merk && $selected->isEmpty()) {
                    $merkFallbackUsed = true;
                    $selected = $scored;
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

    /**
     * Voorkeur (rijstijl) weegt zwaarder dan terrein: het is het directe signaal voor wat voor
     * rijder iemand is (bijv. chopper- vs naked-karakter), terrein is alleen de omgeving waarin
     * je rijdt en kan bovendien via meerdere checkboxen tegelijk optellen. Zonder deze weging kon
     * een gekozen terrein de voorkeur simpelweg overstemmen, waardoor bijv. iemand die "relax"
     * (cruiser/chopper-achtig) aangaf toch een naked bike geadviseerd kreeg puur omdat die ook
     * "binnendoor" reed.
     */
    private const VOORKEUR_WEIGHT = 2;

    /**
     * @param  array<int, string>  $terrein
     * @return array<string, int>
     */
    private function scoreCategories(?string $voorkeur, array $terrein): array
    {
        $scoring = self::scoring();
        $scores = array_fill_keys(array_keys(Motor::CATEGORIES), 0);

        if ($voorkeur && isset($scoring['voorkeur'][$voorkeur])) {
            foreach ($scoring['voorkeur'][$voorkeur] as $category => $points) {
                $scores[$category] += $points * self::VOORKEUR_WEIGHT;
            }
        }

        foreach ($terrein as $key) {
            if (! isset($scoring['terrein'][$key])) {
                continue;
            }

            foreach ($scoring['terrein'][$key] as $category => $points) {
                $scores[$category] += $points;
            }
        }

        return $scores;
    }

    /**
     * De categorie(ën) die het dichtst bij de hoogste score liggen (binnen 1 punt), max 2, zodat
     * het advies niet onnodig smal is bij een gelijkspel tussen twee logische richtingen.
     *
     * @param  array<string, int>  $scores
     * @return array<int, string>
     */
    private function topCategories(array $scores): array
    {
        $max = max($scores);

        if ($max <= 0) {
            return [];
        }

        $qualifying = array_filter($scores, fn ($score) => $score >= $max - 1 && $score > 0);

        // Op score sorteren voor het afkappen tot 2: anders bepaalt de toevallige volgorde waarin
        // categorieën in Motor::CATEGORIES staan wie er afvalt bij meer dan 2 kandidaten, in plaats
        // van wie er daadwerkelijk het beste scoort.
        arsort($qualifying);

        return array_slice(array_keys($qualifying), 0, 2);
    }

    /**
     * @param  array<int, string>  $categories
     */
    private function match(array $categories, string $ervaring): Collection
    {
        $query = Motor::query()->whereNotNull('category');

        if (! empty($categories)) {
            $query->whereIn('category', $categories);
        }

        return $query->get()
            ->when($ervaring === 'beginner', fn ($motors) => $motors->filter(fn (Motor $motor) => $motor->isA2Eligible()))
            ->sortBy([['brand', 'asc'], ['model', 'asc']])
            ->values();
    }

    /**
     * Rangschikt de gematchte motoren op hoe goed ze individueel passen, in plaats van iedere
     * motor binnen de gematchte categorie(ën) als gelijkwaardig te behandelen. Een categoriematch
     * alleen zegt niks over hoe sportief, licht of vergevingsgezind een specifieke motor is: dat
     * bepaalt hier of hij bovenaan of onderaan de lijst belandt.
     *
     * De categoriescore zelf telt hier bewust niet mee: die bepaalt in topCategories() al wélke
     * categorieën in aanmerking komen (soms 2, bij een gelijkspel). Zou die score hier ook worden
     * opgeteld, dan verdringt de categorie met net iets meer punten de andere volledig uit de
     * top-resultaten, ook al waren beide categorieën expliciet als evenwaardig aangemerkt.
     */
    private function scoreMotors(Collection $motors, ?string $voorkeur, string $ervaring): Collection
    {
        // Per categorie normaliseren, niet over de hele resultatenset heen: een kleine 125cc
        // retro-klassieker heeft van nature een veel lagere power-to-weight dan een cruiser met
        // een flinke V-twin, ook al voelen beide "relaxed". Sportiviteit is dus alleen zinvol
        // relatief ten opzichte van andere motoren in dezelfde categorie.
        $ptwBoundsByCategory = $motors->groupBy('category')->map(function (Collection $group) {
            $values = $group->map(fn (Motor $motor) => $motor->powerToWeight());

            return ['min' => $values->min(), 'range' => max($values->max() - $values->min(), 0.0001)];
        });

        return $motors->map(function (Motor $motor) use ($voorkeur, $ervaring, $ptwBoundsByCategory) {
            $bounds = $ptwBoundsByCategory[$motor->category];

            // 0 = meest ontspannen krachtafgifte binnen zijn eigen categorie, 1 = meest sportief/direct.
            $sportiviteit = ($motor->powerToWeight() - $bounds['min']) / $bounds['range'];

            $voorkeurScore = match ($voorkeur) {
                'snelheid' => $sportiviteit,
                'bochten' => $sportiviteit,
                'relax' => 1 - $sportiviteit,
                default => 0.0,
            };

            // Ook binnen de A2-toegestane motoren is er spreiding: een beginner is doorgaans
            // geholpen met de meest vergevingsgezinde optie, niet de sportiefste die nog net mag.
            $ervaringScore = $ervaring === 'beginner' ? 1 - $sportiviteit : 0.0;

            $motor->matchScore = ($voorkeurScore * 3) + ($ervaringScore * 2);

            return $motor;
        })->sortByDesc('matchScore')->values();
    }
}
