<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Motor;
use App\Models\Partner;
use App\Models\SimulationResult;
use App\Services\MotorAdvisor;
use App\Services\SimulationLimitService;
use App\Services\SimulationService;
use App\Http\Controllers\ComparisonController;
use App\Http\Controllers\ToplijstController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Onder deze drempel is "populair" een misleidend label: 1 of 2 races zegt niets.
     * Motoren die er niet aan voldoen worden niet aangevuld met verzonnen 0-tellingen,
     * de sectie verdwijnt dan gewoon van de homepage (zie home.blade.php).
     */
    private const MIN_WEEKLY_POPULAR_USES = 3;

    public function home(SimulationService $simulations, MotorAdvisor $advisor): View
    {
        $motors = Motor::query()->orderBy('brand')->orderBy('model')->get();

        $weeklyPopular = $this->topSearchedMotors(days: 7, limit: 3)
            ->filter(fn (array $row) => $row['uses'] >= self::MIN_WEEKLY_POPULAR_USES)
            ->values();

        $articles = Article::query()->published()
            ->where('category', '!=', Article::NEWS_CATEGORY)
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return view('home', [
            'motors' => $motors,
            'a2Count' => $motors->filter(fn (Motor $motor) => $motor->isA2Eligible())->count(),
            'segmentCount' => count(Motor::CATEGORIES),
            'weeklyPopular' => $weeklyPopular,
            'heroRace' => $this->heroRace($motors, $simulations),
            'styleAdvice' => $this->styleAdvice($advisor),
            'articles' => $articles,
        ]);
    }

    /**
     * De top 3 per rijstijl en rijbewijs voor de rijstijlkiezer in de hero. Komt uit dezelfde
     * MotorAdvisor als de wizard, zodat "Volledig advies" daarna dezelfde motoren bovenaan toont.
     *
     * @return array<string, array<string, array<int, array{label: string, url: string, hp: int, kg: int, a2: bool}>>>
     */
    private function styleAdvice(MotorAdvisor $advisor): array
    {
        $advice = [];

        foreach (array_keys(WizardController::experienceLevels()) as $ervaring) {
            foreach (array_keys(WizardController::voorkeuren()) as $voorkeur) {
                $advice[$ervaring][$voorkeur] = $advisor->advise($ervaring, $voorkeur)['matches']
                    ->take(3)
                    ->map(fn (Motor $motor) => [
                        'label' => $motor->label(),
                        'url' => route('brands.model', [Str::slug($motor->brand), $motor->slug()]),
                        'hp' => (int) $motor->power_hp,
                        'kg' => (int) $motor->weight_kg,
                        'a2' => $motor->isA2Eligible(),
                    ])
                    ->values()
                    ->all();
            }
        }

        return $advice;
    }

    /**
     * Vaste, herkenbare hero-matchup op de homepage. Echt door de fysica-engine
     * uitgerekend (geen verzonnen tijden) zodat de "eerlijke rekensom"-belofte ook
     * hier klopt. Als een van de twee motoren ooit uit de database verdwijnt,
     * verdwijnt het hero-voorbeeld gewoon mee i.p.v. te crashen.
     *
     * @return array{motor_a: Motor, motor_b: Motor, time_a_s: float, time_b_s: float, width_a: float, width_b: float}|null
     */
    private function heroRace($motors, SimulationService $simulations): ?array
    {
        $motorA = $motors->first(fn (Motor $motor) => $motor->brand === 'BMW' && $motor->model === 'F900R');
        $motorB = $motors->first(fn (Motor $motor) => $motor->brand === 'KTM' && $motor->model === '1190 Adventure');

        if (! $motorA || ! $motorB) {
            return null;
        }

        $result = $simulations->race($motorA, $motorB, [
            'road_type' => 'straight',
            'road_condition' => 'dry',
            'distance_m' => 500,
        ]);

        $slowest = max($result['time_a_s'], $result['time_b_s']);

        return [
            'motor_a' => $motorA,
            'motor_b' => $motorB,
            'time_a_s' => $result['time_a_s'],
            'time_b_s' => $result['time_b_s'],
            'width_a' => ($result['time_a_s'] / $slowest) * 100,
            'width_b' => ($result['time_b_s'] / $slowest) * 100,
        ];
    }

    public function about(): View
    {
        return view('about', [
            'motors' => Motor::query()->get(),
        ]);
    }

    public function howItWorks(): View
    {
        return view('how-it-works');
    }

    public function partnerApply(): View
    {
        return view('partner-apply');
    }

    public function mostSearched(): View
    {
        return view('most-searched', [
            'ranking' => $this->topSearchedMotors(days: 30, limit: 20),
            'partners' => Partner::query()->verified()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{motor: Motor, uses: int}>
     */
    private function topSearchedMotors(int $days, int $limit)
    {
        $since = now()->subDays($days);

        $counts = DB::table('simulation_results')
            ->select('motor_id', DB::raw('COUNT(*) as uses'))
            ->fromSub(function ($query) use ($since) {
                $query->from('simulation_results')->where('created_at', '>=', $since)
                    ->select('motor_a_id as motor_id')
                    ->unionAll(
                        DB::table('simulation_results')->where('created_at', '>=', $since)
                            ->select('motor_b_id as motor_id')
                    );
            }, 'combined')
            ->groupBy('motor_id')
            ->orderByDesc('uses')
            ->limit($limit)
            ->pluck('uses', 'motor_id');

        if ($counts->isEmpty()) {
            return collect();
        }

        $motors = Motor::query()->whereIn('id', $counts->keys())->get()->keyBy('id');

        return $counts
            ->map(fn ($uses, $motorId) => ['motor' => $motors->get($motorId), 'uses' => $uses])
            ->filter(fn (array $row) => $row['motor'] !== null)
            ->values();
    }

    public function a2Motoren(): View
    {
        $motors = Motor::query()
            ->get()
            ->filter(fn (Motor $motor) => $motor->isA2Eligible())
            ->sortBy([['category', 'asc'], ['brand', 'asc'], ['model', 'asc']])
            ->values();

        $byCategory = $motors->groupBy('category');

        return view('a2-motoren', [
            'motors' => $motors,
            'byCategory' => $byCategory,
        ]);
    }

    public function partners(): View
    {
        $partners = Partner::query()->verified()->orderBy('sort_order')->get();

        return view('partners', [
            'partners' => $partners,
            'categories' => $partners->pluck('category')->unique()->values(),
        ]);
    }

    public function partnerShow(Partner $partner): View
    {
        abort_unless($partner->status === 'verified', 404);

        return view('partner-show', ['partner' => $partner]);
    }

    public function kennis(): View
    {
        $articles = Article::query()->published()->orderByDesc('published_at')->get();

        return view('kennis', [
            'articles' => $articles,
            'categories' => $articles->pluck('category')->unique()->values(),
        ]);
    }

    public function kennisShow(Article $article): View
    {
        abort_unless($article->is_published && $article->published_at?->isPast(), 404);

        $related = Article::query()->published()
            ->where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('kennis-show', [
            'article' => $article,
            'related' => $related,
            'crossLinks' => $this->articleCrossLinks($article),
        ]);
    }

    /**
     * Statische kruislinks per kennis-categorie naar de vergelijk-/segment-/A2-kant van de site,
     * i.p.v. verzonnen onderwerp-matching. "Nieuwe releases" krijgt een merkpagina als de titel
     * een merk uit de motordatabase noemt, anders het merkenoverzicht.
     *
     * @return array<int, array{label: string, route: string}>
     */
    private function articleCrossLinks(Article $article): array
    {
        return match ($article->category) {
            'Beginnend motorrijder' => [
                ['label' => 'Alle A2 motoren', 'route' => route('a2-motoren')],
                ['label' => 'Welke motor past bij mij', 'route' => route('wizard.index')],
            ],
            'Ervaren motorrijder' => [
                ['label' => 'Alle segmenten', 'route' => route('segments.index')],
                ['label' => 'Start simulatie', 'route' => route('simulation.index')],
            ],
            'Nieuwe releases' => [$this->brandLinkForArticle($article)],
            default => [['label' => 'Welke motor past bij mij', 'route' => route('wizard.index')]],
        };
    }

    /**
     * @return array{label: string, route: string}
     */
    private function brandLinkForArticle(Article $article): array
    {
        $brands = Motor::query()->pluck('brand')->unique();

        foreach ($brands as $brand) {
            if (Str::contains($article->title, $brand, true)) {
                return ['label' => "Alle {$brand}-modellen", 'route' => route('brands.show', Str::slug($brand))];
            }
        }

        return ['label' => 'Alle merken', 'route' => route('brands.index')];
    }

    public function privacy(): View
    {
        return view('privacy');
    }

    public function contact(): View
    {
        return view('contact');
    }

    public function embed(Request $request, SimulationLimitService $limits): View
    {
        return view('embed', [
            'motors' => Motor::query()->orderBy('brand')->orderBy('model')->get(),
            'limit' => $limits->status($request->user(), $request->ip()),
            'embedded' => true,
        ]);
    }

    /**
     * llms.txt volgens de specificatie op llmstxt.org: een gecureerde markdown-index voor LLM's
     * en agents. Bewust geen dump van alle ~11.000 URL's, dat is precies wat sitemap.xml al doet.
     * Dynamisch in plaats van een statisch bestand in public/, zodat de aantallen en de
     * artikellijst niet gaan afwijken van de site zelf (zelfde reden als bij sitemap.xml).
     *
     * Alleen eigen kennisartikelen; de uit nieuwsbronnen herschreven releaseberichten wisselen
     * te snel en horen niet in een gecureerde index. Het onderscheid loopt via de categorie en
     * niet via source_url: eigen artikelen citeren ook bronnen (Rijksoverheid, CBR).
     */
    public function llms()
    {
        return response()
            ->view('llms', [
                'motorCount' => Motor::query()->count(),
                'brandCount' => Motor::query()->distinct()->count('brand'),
                'segments' => Motor::CATEGORIES,
                'segmentDescriptions' => SegmentController::DESCRIPTIONS,
                'toplijsten' => ToplijstController::lists(),
                'guides' => Article::query()->published()
                    ->where('category', '!=', Article::NEWS_CATEGORY)
                    ->orderBy('title')->get(),
                'newsCount' => Article::query()->published()
                    ->where('category', Article::NEWS_CATEGORY)->count(),
            ])
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    /**
     * De sitemap draagt per URL een <lastmod> en geen <priority>/<changefreq> meer: die laatste
     * twee worden door Google genegeerd, <lastmod> is het enige veld dat meeweegt bij de vraag
     * of hercrawlen zin heeft. De datum komt steeds van de onderliggende data (motor, artikel,
     * partner), niet van de deploy, zodat 'ie alleen verschuift als de pagina echt anders werd.
     * Vaste pagina's krijgen bewust geen lastmod: daar is geen betrouwbare wijzigingsdatum voor,
     * en een verzonnen datum is schadelijker dan geen datum.
     */
    public function sitemap()
    {
        $motors = Motor::query()->get();

        $brandSlugs = $motors->groupBy('brand')->map(fn ($group, $brand) => [
            'slug' => Str::slug($brand),
            'lastmod' => $group->max('updated_at'),
        ])->values();

        $modelSlugs = $motors->map(fn (Motor $motor) => [
            'brand' => Str::slug($motor->brand),
            'model' => $motor->slug(),
            'lastmod' => $motor->updated_at,
        ]);

        $segments = collect(array_keys(Motor::CATEGORIES))->map(fn (string $key) => [
            'key' => $key,
            'lastmod' => $motors->where('category', $key)->max('updated_at'),
        ]);

        // Toplijsten zijn afgeleid van de hele database, dus elke motorwijziging kan de volgorde
        // veranderen. De nieuwste motorwijziging is daarmee de juiste lastmod.
        $motorsLastmod = $motors->max('updated_at');

        return response()
            ->view('sitemap', [
                'partners' => Partner::query()->verified()->get(),
                'articles' => Article::query()->published()->get(),
                'toplijsten' => array_keys(ToplijstController::lists()),
                'pairs' => ComparisonController::pairs(),
                'brandSlugs' => $brandSlugs,
                'modelSlugs' => $modelSlugs,
                'segments' => $segments,
                'motorsLastmod' => $motorsLastmod,
            ])
            ->header('Content-Type', 'application/xml');
    }
}
