<?php

namespace App\Services;

use App\Models\Motor;
use Illuminate\Support\Collection;

/**
 * Het rijstijladvies, los van de wizardpagina, zodat de homepage (de rijstijlkiezer in de
 * hero) en /welke-motor-past-bij-mij exact dezelfde uitkomst tonen.
 */
class MotorAdvisor
{
    /**
     * Scorepunten per categorie op basis van elk antwoord. Geen exacte wetenschap, een redelijke
     * vertaling van rijstijl/gebruik naar het type motor dat daar doorgaans het best bij past.
     */
    private const SCORING = [
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

    /**
     * Voorkeur (rijstijl) weegt zwaarder dan terrein: het is het directe signaal voor wat voor
     * rijder iemand is, terrein is alleen de omgeving en kan via meerdere checkboxen optellen.
     * Zonder deze weging kon een gekozen terrein de voorkeur overstemmen, waardoor iemand die
     * "relax" aangaf toch een naked bike kreeg puur omdat die ook "binnendoor" reed.
     */
    private const VOORKEUR_WEIGHT = 2;

    /**
     * Met een vol rijbewijs zoekt vrijwel niemand een motor onder A2-niveau. Zonder deze grens
     * stond bij "rijden voor het plezier" een Mash 125 van 11 pk bovenaan voor ervaren rijders.
     * Gelijk aan de A2-grens van 35 kW.
     */
    private const ERVAREN_MIN_HP = 47;

    /**
     * Met A2 mag je tot 47 pk. Een 125cc van 15 pk valt daar ook onder, maar daar ben je als
     * A2-rijder snel op uitgekeken; die domineerden "bochten" omdat ze het lichtst zijn.
     */
    private const BEGINNER_MIN_HP = 20;

    /**
     * Matches voor een set antwoorden, gerangschikt op hoe goed elke motor past. Eén regel per
     * model: van hetzelfde model in meerdere bouwjaren blijft alleen het best passende bouwjaar
     * over, anders vulde bijvoorbeeld de Panigale V4 de halve top 6.
     *
     * @param  array<int, string>  $terrein
     * @return array{matches: Collection<int, Motor>, categories: array<int, string>}
     */
    public function advise(string $ervaring, ?string $voorkeur, array $terrein = []): array
    {
        $categories = $this->topCategories($this->scoreCategories($voorkeur, $terrein));
        $motors = $this->match($categories, $ervaring);

        return [
            'matches' => $this->rank($motors, $voorkeur, $ervaring),
            'categories' => $categories,
        ];
    }

    /**
     * @param  array<int, string>  $terrein
     * @return array<string, int>
     */
    private function scoreCategories(?string $voorkeur, array $terrein): array
    {
        $scores = array_fill_keys(array_keys(Motor::CATEGORIES), 0);

        foreach (self::SCORING['voorkeur'][$voorkeur] ?? [] as $category => $points) {
            $scores[$category] += $points * self::VOORKEUR_WEIGHT;
        }

        foreach ($terrein as $key) {
            foreach (self::SCORING['terrein'][$key] ?? [] as $category => $points) {
                $scores[$category] += $points;
            }
        }

        return $scores;
    }

    /**
     * De categorie(ën) binnen 1 punt van de hoogste score, max 2, zodat het advies niet
     * onnodig smal is bij een gelijkspel tussen twee logische richtingen.
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

        // Op score sorteren voor het afkappen tot 2, anders bepaalt de volgorde in
        // Motor::CATEGORIES wie er afvalt in plaats van wie het beste scoort.
        arsort($qualifying);

        return array_slice(array_keys($qualifying), 0, 2);
    }

    /**
     * @param  array<int, string>  $categories
     * @return Collection<int, Motor>
     */
    public function match(array $categories, string $ervaring): Collection
    {
        $query = Motor::query()->whereNotNull('category');

        if (! empty($categories)) {
            $query->whereIn('category', $categories);
        }

        $motors = $query->get();

        if ($ervaring === 'beginner') {
            $motors = $motors->filter(fn (Motor $motor) => $motor->isA2Eligible())->values();
        }

        $minHp = $ervaring === 'beginner' ? self::BEGINNER_MIN_HP : self::ERVAREN_MIN_HP;
        $strong = $motors->filter(fn (Motor $motor) => $motor->power_hp >= $minHp)->values();

        // Liever een smal advies dan een leeg advies: zijn er in deze categorie(ën) alleen
        // lichte motoren, dan tonen we die gewoon.
        return $strong->isNotEmpty() ? $strong : $motors->values();
    }

    /**
     * Rangschikt binnen de gematchte categorieën. Twee kenmerken, beide relatief binnen de
     * eigen categorie (een 125cc retro wordt niet tegen een cruiser met een V-twin afgezet):
     *
     * - sportiviteit: vermogen per kilo, 0 = meest ontspannen, 1 = meest direct;
     * - lichtheid: 1 = lichtste van de categorie.
     *
     * "Bochten" ging eerst alleen op vermogen per kilo, waardoor ervaren rijders uitsluitend
     * superbikes van 220 pk kregen. In bochten telt wendbaarheid, dus daar weegt lichtheid
     * het zwaarst, en piekt het vermogen in het sportieve midden (denk aan een middengewicht als
     * een Daytona 675) in plaats van bij het maximum. "Relax" zoekt het rustige midden in plaats
     * van het zwakste model.
     *
     * @return Collection<int, Motor>
     */
    private function rank(Collection $motors, ?string $voorkeur, string $ervaring): Collection
    {
        $bounds = $motors->groupBy('category')->map(function (Collection $group) {
            $ptw = $group->map(fn (Motor $motor) => $motor->powerToWeight());
            $kg = $group->pluck('weight_kg');

            return [
                'ptwMin' => $ptw->min(), 'ptwRange' => max($ptw->max() - $ptw->min(), 0.0001),
                'kgMin' => $kg->min(), 'kgRange' => max($kg->max() - $kg->min(), 0.0001),
            ];
        });

        return $motors
            ->map(function (Motor $motor) use ($voorkeur, $ervaring, $bounds) {
                $b = $bounds[$motor->category];
                $sport = ($motor->powerToWeight() - $b['ptwMin']) / $b['ptwRange'];
                $light = 1 - ($motor->weight_kg - $b['kgMin']) / $b['kgRange'];

                $voorkeurScore = match ($voorkeur) {
                    'snelheid' => $sport,
                    'bochten' => 0.6 * $light + 0.4 * (1 - abs($sport - 0.55) * 1.5),
                    'relax' => 1 - abs($sport - 0.4) * 1.6,
                    default => 0.5,
                };

                // Een beginner is geholpen met het vergevingsgezinde midden: niet de sportiefste
                // die nog net mag, maar ook niet de zwakste 125cc, waar je met A2 snel op
                // uitgekeken bent.
                $ervaringScore = $ervaring === 'beginner' ? 1 - abs($sport - 0.5) * 1.4 : 0.0;

                $motor->matchScore = ($voorkeurScore * 3) + ($ervaringScore * 2);

                return $motor;
            })
            ->sortByDesc('matchScore')
            ->unique(fn (Motor $motor) => mb_strtolower($motor->brand.'|'.$motor->model))
            ->values();
    }
}
