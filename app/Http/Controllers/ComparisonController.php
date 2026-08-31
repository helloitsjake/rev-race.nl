<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Services\SimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function show(string $slug, SimulationService $simulations): View|RedirectResponse
    {
        [$slugA, $slugB] = $this->splitSlug($slug);

        $motors = Motor::query()->get();
        $motorA = $motors->first(fn (Motor $motor) => $motor->slug() === $slugA);
        $motorB = $motors->first(fn (Motor $motor) => $motor->slug() === $slugB);

        abort_if(! $motorA || ! $motorB || $motorA->is($motorB), 404);

        // Elk paar is via twee URL's bereikbaar (A-vs-B en B-vs-A): modelpagina's linken naar
        // "zichzelf-vs-de-ander" (zie BrandController::modelComparisons()), dus beide richtingen
        // krijgen echte inkomende links. pairs() (de sitemap) neemt per paar maar één richting op,
        // dus de andere bleef zonder redirect gewoon indexeerbaar: Ahrefs zag dit als 706
        // "indexable page not in sitemap" (18 aug). 301 naar de canonieke richting bundelt ook de
        // linkwaarde van beide modelpagina's op één URL i.p.v. die te versnipperen.
        if (self::sortKey($motorA) > self::sortKey($motorB)) {
            return redirect()->route('compare.show', "{$motorB->slug()}-vs-{$motorA->slug()}", 301);
        }

        // De uitkomst hangt alleen af van motorA/motorB (vaste afstand, geen rijdersgewicht op
        // deze statische vergelijkpagina), dus cachen op basis van hun id's + updated_at is
        // veilig en ontlast de simulatieloop onder crawllast (Ahrefs zag hier TTFB tot 25s en
        // enkele volledige timeouts, vermoedelijk PHP-FPM-verzadiging op shared hosting).
        // updated_at in de key voorkomt verstopte cache na een correctie op "klopt dit niet?".
        $cacheKey = sprintf(
            'compare:%d:%d:%s:%s',
            $motorA->id,
            $motorB->id,
            $motorA->updated_at?->timestamp,
            $motorB->updated_at?->timestamp,
        );

        $results = Cache::remember($cacheKey, now()->addDays(30), function () use ($motorA, $motorB, $simulations) {
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

            return $results;
        });

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
     * Levert per paar ook een lastmod op: de laatste wijziging van de twee betrokken motoren.
     * De vergelijkingspagina bevat geen eigen tekst die los van de motordata verandert, dus dat
     * is precies het moment waarop de inhoud van de pagina daadwerkelijk anders werd. Google
     * gebruikt <lastmod> om te bepalen of hercrawlen zin heeft; <priority> en <changefreq>
     * worden genegeerd en staan daarom niet meer in de sitemap.
     *
     * @return Collection<int, array{slug: string, lastmod: \Illuminate\Support\Carbon|null}>
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

                $pairs->push([
                    'slug' => self::canonicalSlug($motorA, $motorB),
                    'lastmod' => collect([$motorA->updated_at, $motorB->updated_at])->filter()->max(),
                ]);
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

    /**
     * Zelfde sorteervolgorde als pairs() (category, brand, model): bepaalt welke van de twee
     * richtingen canoniek is.
     *
     * @return array{0: ?string, 1: string, 2: string}
     */
    private static function sortKey(Motor $motor): array
    {
        return [$motor->category, $motor->brand, $motor->model];
    }

    /**
     * De canonieke slug voor een paar, ongeacht in welke volgorde de twee motoren aangeleverd
     * worden. Elke plek in de app die naar een vergelijking linkt hoort hier langs: de niet-
     * canonieke richting krijgt in show() een 301, en een interne link naar een redirect is
     * verspilde linkwaarde plus een extra hop voor de crawler.
     */
    public static function canonicalSlug(Motor $first, Motor $second): string
    {
        [$a, $b] = self::sortKey($first) > self::sortKey($second)
            ? [$second, $first]
            : [$first, $second];

        return "{$a->slug()}-vs-{$b->slug()}";
    }
}
