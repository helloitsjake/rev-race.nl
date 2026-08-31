<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SegmentController extends Controller
{
    /**
     * Korte, feitelijke duiding per segment. Geen marketingtaal, sluit aan bij hoe de wizard
     * dezelfde categorieën al aan rijstijl/terrein koppelt.
     */
    public const DESCRIPTIONS = [
        'naked' => 'Een kale motor zonder kuip: rechtop zitten, direct sturen, veelzijdig voor zowel dagelijks gebruik als een stevige bocht.',
        'sport' => 'Gebouwd voor snelheid en leunhoek, met een sportieve, voorovergebogen zithouding en veel vermogen ten opzichte van het gewicht.',
        'tourer' => 'Comfort en actieradius staan voorop: rustige zithouding, gemaakt voor lange afstanden op de snelweg.',
        'adventure' => 'De crossover tussen asfalt en onverhard terrein, herkenbaar aan het hoge zicht, de robuuste bouw en het langere veerpakket.',
        'cruiser' => 'Lage zit, relaxte houding en veel nadruk op karakter en gevoel in plaats van pure topsnelheid of vermogen.',
        'retro' => 'Klassieke vormgeving met moderne techniek eronder, vaak gekozen om de uitstraling, niet om de specificaties.',
    ];

    /**
     * De term zoals mensen 'm daadwerkelijk zoeken (Ahrefs Rank Tracker, augustus 2026), niet
     * altijd hetzelfde als het segmentlabel zelf ("Toermotor" i.p.v. "Tourer").
     */
    public const BUYING_LABEL = [
        'naked' => 'naked bike',
        'sport' => 'sportmotor',
        'tourer' => 'toermotor',
        'adventure' => 'adventure motor',
        'cruiser' => 'cruiser',
        'retro' => 'retro motor',
    ];

    /**
     * Korte alinea gericht op de koopvraag ("beste X", "X kopen"), als aanvulling op de
     * feitelijke DESCRIPTIONS hierboven. Geen prijzen: RevRace heeft geen prijsdata (zie ook de
     * wizard, die budget bewust buiten de match houdt).
     */
    public const BUYING_INTRO = [
        'naked' => 'Twijfel je tussen meerdere naked bikes? Vergelijk vermogen, gewicht en de pk/kg-verhouding van elk model hieronder, of doe de :wizard voor een advies op basis van je rijstijl.',
        'sport' => 'Een sportmotor kopen begint met de vraag hoeveel vermogen je écht gebruikt. Vergelijk de modellen hieronder op vermogen, gewicht en pk/kg, of doe de :wizard voor advies op maat.',
        'tourer' => 'Bij een toermotor kopen telt comfort over lange afstanden net zo zwaar als vermogen. Vergelijk de modellen hieronder, of laat de :wizard meewegen wat voor jou telt.',
        'adventure' => 'Een adventure motor kopen betekent kiezen tussen puur asfaltcomfort en echte offroad-capaciteit. Vergelijk de modellen hieronder op vermogen en gewicht, of doe de :wizard voor een gericht advies.',
        'cruiser' => 'Cruisers worden in Nederland ook vaak choppers genoemd: lage zit, relaxte houding, veel karakter. Vergelijk de modellen hieronder, of doe de :wizard voor advies op basis van je rijstijl.',
        'retro' => 'Een retro motor kopen is vaak een keuze voor uitstraling, niet alleen voor specificaties. Vergelijk de modellen hieronder, of doe de :wizard als de techniek net zo zwaar moet wegen als het uiterlijk.',
    ];

    public function index(): View
    {
        $counts = Motor::query()
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as motor_count')
            ->groupBy('category')
            ->pluck('motor_count', 'category');

        $segments = collect(Motor::CATEGORIES)->map(fn ($label, $key) => [
            'key' => $key,
            'label' => $label,
            'description' => self::DESCRIPTIONS[$key] ?? null,
            'count' => $counts[$key] ?? 0,
        ])->values();

        return view('segments', ['segments' => $segments]);
    }

    public function show(string $categorie): View
    {
        abort_unless(array_key_exists($categorie, Motor::CATEGORIES), 404);

        $motors = Motor::query()
            ->where('category', $categorie)
            ->get()
            ->sortByDesc(fn (Motor $motor) => $motor->powerToWeight())
            ->values();

        abort_if($motors->isEmpty(), 404);

        /*
         * Elke motor in het segment koppelen aan zijn directe buur in de pk/kg-ranglijst, in
         * plaats van alleen de zes zwaarste.
         *
         * Waarom: Ahrefs zag op 18 augustus 9.597 indexeerbare pagina's met precies één
         * inkomende interne link, en Search Console laat over de drie maanden t/m 31 augustus
         * zien dat maar 877 van de ruim 10.000 vergelijkingspagina's ooit vertoond zijn. De
         * pagina's zijn identiek van opbouw, dus dat verschil zit in het aantal interne
         * verwijzingen. Zes links per segmentpagina dekten samen 36 van de 10.000 pagina's.
         *
         * De buur in de pk/kg-ranglijst is bovendien inhoudelijk de nuttigste vergelijking: dat
         * zijn de twee modellen die daadwerkelijk tegen elkaar afgewogen worden. De eerste zes
         * blijven als kaarten in beeld, de rest staat in een uitklapblok (zelfde patroon als de
         * modelpagina), zodat de pagina niet omslaat in een linklijst.
         */
        $comparisons = $motors
            ->map(function (Motor $motor, int $i) use ($motors) {
                $partner = $motors->get($i + 1);

                return $partner && ! $partner->is($motor)
                    ? [
                        'motorA' => $motor,
                        'motorB' => $partner,
                        'slug' => ComparisonController::canonicalSlug($motor, $partner),
                    ]
                    : null;
            })
            ->filter()
            ->values();

        return view('segment-show', [
            'categorie' => $categorie,
            'label' => Motor::CATEGORIES[$categorie],
            'description' => self::DESCRIPTIONS[$categorie] ?? null,
            'buyingLabel' => self::BUYING_LABEL[$categorie] ?? Str::lower(Motor::CATEGORIES[$categorie]),
            'buyingIntro' => self::BUYING_INTRO[$categorie] ?? null,
            'motors' => $motors,
            'comparisons' => $comparisons,
        ]);
    }
}
