<?php

namespace App\Support;

use App\Http\Controllers\ComparisonController;
use App\Models\Motor;
use Illuminate\Support\Collection;

/**
 * Alternatieven voor één model (24-11-2026), voor /merken/{merk}/{model}/alternatieven.
 *
 * Een alternatief zit in hetzelfde segment (anders bestaat er geen vergelijkingspagina, zie
 * ComparisonController::pairs()), ligt binnen 35% van het vermogen en lijkt verder zo veel
 * mogelijk op vermogen, gewicht en cilinderinhoud. Is het origineel A2-geschikt, dan alleen
 * A2-alternatieven: wie een A2-motor zoekt, heeft niets aan een motor die hij niet mag rijden.
 * Hetzelfde model in een ander bouwjaar telt niet, elk alternatief staat er met één bouwjaar
 * in, oudere modellen wegen zwaarder, en per merk maximaal twee, zodat de lijst
 * echt keuze geeft en niet zes keer hetzelfde merk.
 *
 * Alleen modellen in config/alternatieven.php krijgen een pagina: dat zijn de modellen met
 * aantoonbare zoekvraag in Search Console. Voor alle 542 zou het weer duizenden dunne pagina's
 * opleveren, het probleem van de vergelijkingspagina's.
 */
class Alternatives
{
    public const MIN = 3;

    public const MAX = 6;

    public static function isListed(Motor $motor): bool
    {
        return in_array($motor->slug(), config('alternatieven.models', []), true);
    }

    /**
     * @param  Collection<int, Motor>  $motors
     * @return Collection<int, array{motor: Motor, score: float, slug: string, hp: int, kg: int}>
     */
    public static function for(Motor $motor, Collection $motors): Collection
    {
        if (! $motor->category || $motor->power_hp <= 0 || $motor->weight_kg <= 0) {
            return collect();
        }

        $a2 = $motor->isA2Eligible();

        $perBrand = [];
        $chosen = [];

        return $motors
            ->filter(fn (Motor $other) => $other->isNot($motor)
                && $other->category === $motor->category
                && $other->power_hp > 0 && $other->weight_kg > 0
                && ! self::sameFamily($other, $motor)
                && abs($other->power_hp / $motor->power_hp - 1) <= 0.35
                && (! $a2 || $other->isA2Eligible()))
            ->map(fn (Motor $other) => [
                'motor' => $other,
                'score' => abs(log($other->power_hp / $motor->power_hp))
                    + 0.6 * abs(log($other->weight_kg / $motor->weight_kg))
                    + 0.4 * abs(log(max(1, $other->displacement_cc) / max(1, $motor->displacement_cc)))
                    // Ouder dan het origineel weegt zwaarder: wie alternatieven zoekt, kijkt naar
                    // wat er nu te koop is, niet naar een model van tien jaar eerder.
                    + 0.025 * max(0, (int) $motor->year - (int) $other->year),
            ])
            ->sortBy('score')
            // Per modelfamilie één (de best passende), daarna maximaal twee per merk.
            ->filter(function (array $row) use (&$perBrand, &$chosen) {
                foreach ($chosen as $picked) {
                    if (self::sameFamily($row['motor'], $picked)) {
                        return false;
                    }
                }
                $brand = $row['motor']->brand;
                if (($perBrand[$brand] ?? 0) >= 2) {
                    return false;
                }
                $perBrand[$brand] = ($perBrand[$brand] ?? 0) + 1;
                $chosen[] = $row['motor'];

                return true;
            })
            ->take(self::MAX)
            ->map(fn (array $row) => $row + [
                'slug' => ComparisonController::canonicalSlug($motor, $row['motor']),
                'hp' => $row['motor']->power_hp - $motor->power_hp,
                'kg' => $row['motor']->weight_kg - $motor->weight_kg,
            ])
            ->values();
    }

    /**
     * Zelfde merk en de ene modelnaam begint met de andere: "Ténéré 700" en "Ténéré 700 World
     * Raid", "SV650" en "SV650X", "Tiger 1200 GT Pro" en "Tiger 1200 GT Explorer", of hetzelfde
     * model in een ander bouwjaar.
     */
    public static function sameFamily(Motor $a, Motor $b): bool
    {
        if (mb_strtolower($a->brand) !== mb_strtolower($b->brand)) {
            return false;
        }
        $norm = fn (Motor $m) => preg_replace('/[^a-z0-9]/', '', mb_strtolower(preg_replace('/\s*\([^)]*\)/', '', $m->model)));
        [$x, $y] = [$norm($a), $norm($b)];

        if ($x !== '' && $y !== '' && (str_starts_with($x, $y) || str_starts_with($y, $x))) {
            return true;
        }
        // Zelfde eerste twee woorden: "Tiger 1200 GT Pro" en "Tiger 1200 GT Explorer".
        $two = fn (Motor $m) => implode(' ', array_slice(preg_split('/\s+/', mb_strtolower(trim($m->model))), 0, 2));

        return str_contains(trim($a->model), ' ') && $two($a) === $two($b);
    }

    /** "12 pk meer, 8 kg lichter": het verschil met het origineel, in gewone taal. */
    public static function delta(array $row): string
    {
        $hp = $row['hp'];
        $kg = $row['kg'];

        $parts = [
            $hp === 0 ? 'evenveel pk' : abs($hp) . ' pk ' . ($hp > 0 ? 'meer' : 'minder'),
            $kg === 0 ? 'even zwaar' : abs($kg) . ' kg ' . ($kg > 0 ? 'zwaarder' : 'lichter'),
        ];

        return implode(', ', $parts);
    }
}
