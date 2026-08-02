<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Services\SimulationService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function show(string $slug, SimulationService $simulations): View
    {
        [$slugA, $slugB] = $this->splitSlug($slug);

        $motors = Motor::query()->get();
        $motorA = $motors->first(fn (Motor $motor) => $motor->slug() === $slugA);
        $motorB = $motors->first(fn (Motor $motor) => $motor->slug() === $slugB);

        abort_if(! $motorA || ! $motorB || $motorA->is($motorB), 404);

        $conditions = ['dry' => 'Droog', 'wet' => 'Vochtig', 'rain' => 'Nat'];
        $results = [];

        foreach ($conditions as $key => $label) {
            $results[$key] = [
                'label' => $label,
                'result' => $simulations->race($motorA, $motorB, [
                    'road_type' => 'straight',
                    'road_condition' => $key,
                    'distance_m' => 500,
                ]),
            ];
        }

        $related = $this->relatedComparisons($motors, $motorA, $motorB);

        return view('compare', [
            'motorA' => $motorA,
            'motorB' => $motorB,
            'results' => $results,
            'related' => $related,
        ]);
    }

    /**
     * Interne links naar vergelijkingen met motoren uit dezelfde categorie, zodat een
     * vergelijkingspagina niet langer een doodlopend eiland is. Motoren van hetzelfde merk als
     * $motorA komen eerst, daarna de rest van de categorie, gesorteerd op merk en model.
     *
     * @param  Collection<int, Motor>  $motors
     * @return Collection<int, array{motor: Motor, slug: string}>
     */
    private function relatedComparisons(Collection $motors, Motor $motorA, Motor $motorB): Collection
    {
        return $motors
            ->filter(fn (Motor $motor) => $motor->category !== null
                && $motor->category === $motorA->category
                && $motor->isNot($motorA)
                && $motor->isNot($motorB))
            ->sort(fn (Motor $a, Motor $b) => [$a->brand === $motorA->brand ? 0 : 1, $a->brand, $a->model]
                <=> [$b->brand === $motorA->brand ? 0 : 1, $b->brand, $b->model])
            ->take(6)
            ->map(fn (Motor $motor) => [
                'motor' => $motor,
                'slug' => "{$motorA->slug()}-vs-{$motor->slug()}",
            ])
            ->values();
    }

    /**
     * Alleen vergelijkingen binnen dezelfde categorie, voor de sitemap. Alle motorcombinaties
     * uitschrijven zou bij een grotere database (300+) ver boven de sitemap limiet van Google
     * (50.000 URL's) uitkomen, en zou vooral onzinnige combinaties bevatten (bv. cruiser tegen
     * supersport) die niemand daadwerkelijk zoekt. De /vergelijk/{slug} route zelf blijft open voor
     * elke twee motoren, dit beperkt alleen wat er in de sitemap gepubliceerd wordt.
     *
     * @return array<int, string>
     */
    public static function pairs(): Collection
    {
        $motors = Motor::query()->whereNotNull('category')->orderBy('category')->orderBy('brand')->orderBy('model')->get();
        $pairs = collect();

        foreach ($motors as $i => $motorA) {
            foreach ($motors->slice($i + 1) as $motorB) {
                if ($motorA->category !== $motorB->category) {
                    continue;
                }

                $pairs->push("{$motorA->slug()}-vs-{$motorB->slug()}");
            }
        }

        return $pairs;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitSlug(string $slug): array
    {
        $parts = explode('-vs-', $slug, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
