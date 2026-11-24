<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Motor;
use App\Models\Partner;
use App\Support\Alternatives;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Sitemap-index met deelsitemaps (24-11-2026).
 *
 * Waarom: de oude /sitemap.xml zette ruim 27.000 URL's in één Blade-view en gebruikte daarvoor
 * ongeveer 119 MB van de 128 MB PHP-geheugen. Met meer motoren groeit het aantal vergelijkingen
 * kwadratisch, dus die ene sitemap zou vanzelf stukgaan. Nu: een index met kleine delen, en de
 * vergelijkingen worden met platte arrays opgebouwd in plaats van met duizenden modelobjecten.
 *
 * Geen <priority> en <changefreq>: Google negeert beide. Wel <lastmod> waar een betrouwbare
 * datum bestaat.
 */
class SitemapController extends Controller
{
    public const PER_FILE = 5000;

    /** @var array<string, list<array{0: string, 1: ?string}>>|null */
    private ?array $groups = null;

    public function index(): Response
    {
        $lastmod = Motor::query()->max('updated_at');
        $files = [
            ['loc' => route('sitemaps.pages'), 'lastmod' => $lastmod],
            ['loc' => route('sitemaps.models'), 'lastmod' => $lastmod],
        ];
        for ($n = 1; $n <= $this->comparisonFileCount(); $n++) {
            $files[] = ['loc' => route('sitemaps.comparisons', $n), 'lastmod' => $lastmod];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($files as $file) {
            $xml .= '<sitemap><loc>' . e($file['loc']) . '</loc>' . $this->lastmod($file['lastmod']) . "</sitemap>\n";
        }

        return $this->xml($xml . '</sitemapindex>');
    }

    /** Vaste pagina's, merken, segmenten, A2, alternatieven, partners, artikelen en toplijsten. */
    public function pages(): Response
    {
        $motors = Motor::query()->get();
        $motorsLastmod = $motors->max('updated_at');

        $urls = collect([
            'home', 'wizard.index', 'simulation.index', 'most-searched.index', 'partners.index',
            'partners.apply', 'how-it-works', 'kennis.index', 'about', 'privacy', 'contact', 'yearly-report.show',
        ])->map(fn ($name) => [route($name), null]);

        foreach (['brands.index', 'segments.index', 'a2-motoren'] as $name) {
            $urls->push([route($name), $motorsLastmod]);
        }
        foreach (SegmentController::a2Segments($motors)->keys() as $key) {
            $urls->push([route('a2-motoren.segment', $key), $motorsLastmod]);
        }
        foreach ($motors->groupBy('brand') as $brand => $group) {
            $urls->push([route('brands.show', Str::slug($brand)), $group->max('updated_at')]);
        }
        foreach (array_keys(Motor::CATEGORIES) as $key) {
            $urls->push([route('segments.show', $key), $motors->where('category', $key)->max('updated_at')]);
        }
        foreach ($motors as $motor) {
            // Alleen modellen die echt een pagina hebben (genoeg alternatieven), anders 404's.
            if (Alternatives::isListed($motor) && Alternatives::for($motor, $motors)->count() >= Alternatives::MIN) {
                $urls->push([route('brands.alternatives', [Str::slug($motor->brand), $motor->slug()]), $motorsLastmod]);
            }
        }
        foreach (Partner::query()->verified()->get() as $partner) {
            $urls->push([route('partners.show', $partner), $partner->updated_at]);
        }
        foreach (Article::query()->published()->get() as $article) {
            $urls->push([route('kennis.show', $article), $article->updated_at]);
        }
        foreach (array_keys(ToplijstController::lists()) as $slug) {
            $urls->push([route('toplijst.show', $slug), $motorsLastmod]);
        }

        return $this->urlset($urls->all());
    }

    public function models(): Response
    {
        $urls = Motor::query()->get()
            ->map(fn (Motor $motor) => [route('brands.model', [Str::slug($motor->brand), $motor->slug()]), $motor->updated_at])
            ->all();

        return $this->urlset($urls);
    }

    public function comparisons(int $n): Response
    {
        abort_if($n < 1 || $n > $this->comparisonFileCount(), 404);

        $from = ($n - 1) * self::PER_FILE;
        $to = $from + self::PER_FILE;
        $base = rtrim(route('home'), '/') . '/vergelijk/';
        $urls = [];
        $i = 0;

        // Zelfde volgorde en canonieke richting als ComparisonController::pairs().
        foreach ($this->groupedMotors() as $group) {
            $count = count($group);
            for ($a = 0; $a < $count; $a++) {
                for ($b = $a + 1; $b < $count; $b++, $i++) {
                    if ($i < $from) {
                        continue;
                    }
                    if ($i >= $to) {
                        break 3;
                    }
                    $urls[] = [$base . $group[$a][0] . '-vs-' . $group[$b][0], max($group[$a][1], $group[$b][1])];
                }
            }
        }

        return $this->urlset($urls);
    }

    private function comparisonFileCount(): int
    {
        $total = 0;
        foreach ($this->groupedMotors() as $group) {
            $total += intdiv(count($group) * (count($group) - 1), 2);
        }

        return max(1, (int) ceil($total / self::PER_FILE));
    }

    /** @return array<string, list<array{0: string, 1: ?string}>> categorie => [[slug, updated_at], ...] */
    private function groupedMotors(): array
    {
        if ($this->groups === null) {
            $groups = [];
            $rows = Motor::query()->whereNotNull('category')
                ->orderBy('category')->orderBy('brand')->orderBy('model')
                ->toBase()->get(['category', 'brand', 'model', 'year', 'updated_at']);
            foreach ($rows as $row) {
                $groups[$row->category][] = [Str::slug("{$row->brand} {$row->model} {$row->year}"), $row->updated_at];
            }
            $this->groups = $groups;
        }

        return $this->groups;
    }

    /** @param  list<array{0: string, 1: mixed}>  $urls */
    private function urlset(array $urls): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as [$loc, $date]) {
            $xml .= '<url><loc>' . e($loc) . '</loc>' . $this->lastmod($date) . "</url>\n";
        }

        return $this->xml($xml . '</urlset>');
    }

    private function lastmod(mixed $date): string
    {
        return $date ? '<lastmod>' . Carbon::parse($date)->toAtomString() . '</lastmod>' : '';
    }

    private function xml(string $xml): Response
    {
        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
