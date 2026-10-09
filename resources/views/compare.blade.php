@extends('layouts.app')

@php
    $dryResult = $results['dry']['result'];
    $dryWinner = $dryResult['winner'] === 'A' ? $motorA : $motorB;
    $rainWinner = $results['rain']['result']['winner'] === 'A' ? $motorA : $motorB;
    $powerDiff = abs($motorA->power_hp - $motorB->power_hp);
    $weightDiff = abs($motorA->weight_kg - $motorB->weight_kg);
    $strongerMotor = $motorA->power_hp >= $motorB->power_hp ? $motorA : $motorB;
    $lighterMotor = $motorA->weight_kg <= $motorB->weight_kg ? $motorA : $motorB;

    // Zelfde µ-waarden als App\Services\SimulationService::condition() — hier alleen voor
    // weergave in het paneelkopje, de daadwerkelijke uitslag komt uit $results.
    $muDisplay = [
        'dry' => ['t' => 1.00, 'b' => 1.00, 'c' => 1.00],
        'wet' => ['t' => 0.70, 'b' => 0.72, 'c' => 0.65],
        'rain' => ['t' => 0.45, 'b' => 0.50, 'c' => 0.40],
    ];

    /*
     * FAQ per vergelijking, gevuld uit de eigen simulatie- en specificatiedata.
     *
     * Waarom juist hier: dit sjabloon vult ruim 10.000 pagina's en levert volgens Search Console
     * (3 maanden t/m 31 augustus 2026) 100 van de 122 klikken van de hele site, met de hoogste
     * CTR van alle contenttypes op de homepage na. Eén wijziging aan dit sjabloon raakt dus
     * precies het deel van de site dat aantoonbaar werkt.
     *
     * Dezelfde collectie voedt het zichtbare blok en het FAQPage-schema, zodat de twee niet uit
     * elkaar kunnen lopen (Google eist dat het antwoord ook echt op de pagina staat).
     */
    $sameDryRainWinner = $rainWinner->is($dryWinner);
    $faq = collect([
        [
            'q' => "Welke is sneller, de {$motorA->label()} of de {$motorB->label()}?",
            'a' => "Op droog asfalt wint de {$dryWinner->label()}, met een verschil van "
                . number_format($dryResult['delta_s'], 2, ',', '.') . ' seconde over 500 meter. '
                . ($sameDryRainWinner
                    ? "Ook op nat wegdek blijft de {$rainWinner->label()} voorliggen."
                    : "Op nat wegdek draait dat om: dan wint de {$rainWinner->label()}."),
        ],
        [
            'q' => "Welke heeft meer vermogen, de {$motorA->label()} of de {$motorB->label()}?",
            'a' => $powerDiff > 0
                ? "De {$strongerMotor->label()} heeft met {$strongerMotor->power_hp} pk het meeste vermogen, {$powerDiff} pk meer dan de "
                    . ($strongerMotor->is($motorA) ? $motorB->label() : $motorA->label()) . '.'
                : "Beide leveren {$motorA->power_hp} pk, dus op vermogen ontlopen ze elkaar niet.",
        ],
        [
            'q' => "Welke is lichter, de {$motorA->label()} of de {$motorB->label()}?",
            'a' => $weightDiff > 0
                ? "De {$lighterMotor->label()} is met {$lighterMotor->weight_kg} kg de lichtste van de twee, {$weightDiff} kg minder dan de "
                    . ($lighterMotor->is($motorA) ? $motorB->label() : $motorA->label())
                    . '. Dat telt door in acceleratie en in hoe handelbaar de motor aanvoelt.'
                : "Beide wegen {$motorA->weight_kg} kg, dus op gewicht is er geen verschil.",
        ],
        [
            'q' => 'Wat is het verschil in pk per kilo?',
            'a' => "De {$motorA->label()} zit op " . number_format($motorA->powerToWeight(), 2, ',', '.')
                . " pk/kg, de {$motorB->label()} op " . number_format($motorB->powerToWeight(), 2, ',', '.')
                . ' pk/kg. Die verhouding zegt meer over hoe fel een motor optrekt dan het vermogen alleen.',
        ],
        [
            'q' => 'Zijn deze motoren geschikt voor een A2-rijbewijs?',
            'a' => match (true) {
                $motorA->isA2Eligible() && $motorB->isA2Eligible() => "Beide blijven binnen de A2-grenzen van maximaal 35 kW en maximaal 0,20 kW per kilo.",
                $motorA->isA2Eligible() => "Alleen de {$motorA->label()} blijft binnen de A2-grenzen. De {$motorB->label()} valt er met {$motorB->power_hp} pk buiten.",
                $motorB->isA2Eligible() => "Alleen de {$motorB->label()} blijft binnen de A2-grenzen. De {$motorA->label()} valt er met {$motorA->power_hp} pk buiten.",
                default => "Geen van beide is A2-geschikt: allebei zitten ze boven de grens van 35 kW of 0,20 kW per kilo.",
            },
        ],
    ]);

    /*
     * Motorcycle-schema voor beide motoren, hetzelfde subtype als op de modelpagina's. Bewust
     * geen 'Product': dat vraagt om offers/review/aggregateRating die RevRace niet heeft, en
     * levert dan een rich-results-validatiefout op.
     */
    $motorcycleSchema = fn (\App\Models\Motor $motor) => [
        '@type' => 'Motorcycle',
        'name' => $motor->label(),
        'brand' => ['@type' => 'Brand', 'name' => $motor->brand],
        'category' => $motor->categoryLabel(),
        'url' => route('brands.model', [\Illuminate\Support\Str::slug($motor->brand), $motor->slug()]),
        'additionalProperty' => array_values(array_filter([
            ['@type' => 'PropertyValue', 'name' => 'Vermogen', 'value' => $motor->power_hp, 'unitText' => 'pk'],
            ['@type' => 'PropertyValue', 'name' => 'Koppel', 'value' => $motor->torque_nm, 'unitText' => 'Nm'],
            ['@type' => 'PropertyValue', 'name' => 'Gewicht', 'value' => $motor->weight_kg, 'unitText' => 'kg'],
            ['@type' => 'PropertyValue', 'name' => 'Cilinderinhoud', 'value' => $motor->displacement_cc, 'unitText' => 'cc'],
            $motor->top_speed_kmh ? ['@type' => 'PropertyValue', 'name' => 'Topsnelheid', 'value' => $motor->top_speed_kmh, 'unitText' => 'km/u'] : null,
            ['@type' => 'PropertyValue', 'name' => 'Vermogen/gewicht', 'value' => number_format($motor->powerToWeight(), 2), 'unitText' => 'pk/kg'],
        ])),
    ];
@endphp

@section('title', "{$motorA->shortLabel()} vs {$motorB->shortLabel()} - RevRace")
@section('description', "Vergelijk de {$motorA->shortLabel()} met de {$motorB->shortLabel()} op vermogen, gewicht en grip in droog, vochtig en nat weer.")
@section('ogImage', asset('og-image-vergelijk.png'))
@if($motorA->category === null || $motorB->category === null || $motorA->category !== $motorB->category)
    {{--
        Cross-category vergelijkingen (bv. cruiser tegen supersport) blijven bereikbaar via de
        route, maar zijn bewust geen onderdeel van de sitemap (zie ComparisonController::pairs())
        omdat niemand die combinatie zoekt. Zonder noindex bleven ze toch "indexable" voor Google
        zodra ze via een directe link/share ontdekt werden, wat als "indexable page not in
        sitemap" in Ahrefs terugkwam. Same-category vergelijkingen blijven gewoon indexeerbaar.
    --}}
    @section('robots', 'noindex, follow')
@endif

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Simulatie', 'item' => route('simulation.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $motorA->label().' vs '.$motorB->label()],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $faq->map(fn (array $item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
    ])->all(),
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'ItemList',
    'name' => $motorA->label().' vs '.$motorB->label(),
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'item' => $motorcycleSchema($motorA)],
        ['@type' => 'ListItem', 'position' => 2, 'item' => $motorcycleSchema($motorB)],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a> <span class="sep">&rarr;</span>
                <a href="{{ route('simulation.index') }}">Simulatie</a> <span class="sep">&rarr;</span>
                {{ $motorA->label() }} vs {{ $motorB->label() }}
            </nav>
            <span class="eyebrow">Vergelijking</span>
            <h1>{{ $motorA->label() }} vs {{ $motorB->label() }}</h1>
            <p class="lede">Op droog asfalt wint de {{ $dryWinner->label() }}. Bekijk hieronder hoe dat verandert per wegconditie.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="panel">
                <p style="margin-bottom: 1.2em">
                    Wie is er nou sneller, de <b>{{ $motorA->label() }}</b> of de <b>{{ $motorB->label() }}</b>? Op papier heeft de
                    <b>{{ $strongerMotor->label() }}</b> met {{ $strongerMotor->power_hp }} pk het meeste vermogen
                    @if($powerDiff > 0)
                        ({{ $powerDiff }} pk meer dan de {{ $strongerMotor->is($motorA) ? $motorB->label() : $motorA->label() }}),
                    @else
                        (evenveel als de andere),
                    @endif
                    maar de <b>{{ $lighterMotor->label() }}</b> is met {{ $lighterMotor->weight_kg }} kg
                    @if($weightDiff > 0)
                        {{ $weightDiff }} kg lichter.
                    @else
                        even zwaar.
                    @endif
                    Op de rechte lijn van 500 meter, op droog asfalt, is dat genoeg voor de <b>{{ $dryWinner->label() }}</b> om als
                    eerste over de streep te komen.
                    @if($dryWinner->isNot($rainWinner))
                        Op kletsnat asfalt draait de verhouding om: daar wint de <b>{{ $rainWinner->label() }}</b>, omdat grip en
                        remvermogen dan zwaarder wegen dan pure pk's.
                    @else
                        Ook op nat asfalt blijft de <b>{{ $rainWinner->label() }}</b> de sterkste, het voordeel wordt alleen kleiner.
                    @endif
                    Twijfel je zelf tussen deze twee? Vul je eigen rijdersgewicht en wegconditie in en race ze tegen elkaar.
                </p>
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Zelf simuleren</a>
            </div>
        </div>
    </section>

    <section class="chapter" id="specificaties">
        <div class="wrap">
            <span class="eyebrow">Specificaties</span>
            <h2>Naast elkaar</h2>
            <div class="spec">
                <div class="spec__row spec__row--head">
                    <div class="spec__label">Motor</div>
                    <div class="spec__value spec__value--a">{{ $motorA->label() }}</div>
                    <div class="spec__value spec__value--b">{{ $motorB->label() }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Vermogen</div>
                    <div class="spec__value">{{ $motorA->power_hp }} pk</div>
                    <div class="spec__value">{{ $motorB->power_hp }} pk</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Koppel</div>
                    <div class="spec__value">{{ $motorA->torque_nm }} Nm</div>
                    <div class="spec__value">{{ $motorB->torque_nm }} Nm</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Gewicht</div>
                    <div class="spec__value">{{ $motorA->weight_kg }} kg</div>
                    <div class="spec__value">{{ $motorB->weight_kg }} kg</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Pk per kg</div>
                    <div class="spec__value">{{ number_format($motorA->powerToWeight(), 2, ',', '.') }}</div>
                    <div class="spec__value">{{ number_format($motorB->powerToWeight(), 2, ',', '.') }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Motortype</div>
                    <div class="spec__value">{{ $motorA->engine_type }}</div>
                    <div class="spec__value">{{ $motorB->engine_type }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Cilinderinhoud</div>
                    <div class="spec__value">{{ $motorA->displacement_cc }} cc</div>
                    <div class="spec__value">{{ $motorB->displacement_cc }} cc</div>
                </div>
                @if($motorA->top_speed_kmh && $motorB->top_speed_kmh)
                    <div class="spec__row">
                        <div class="spec__label">Topsnelheid</div>
                        <div class="spec__value">{{ $motorA->top_speed_kmh }} km/h</div>
                        <div class="spec__value">{{ $motorB->top_speed_kmh }} km/h</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight" id="simulatie">
        <div class="wrap">
            <span class="eyebrow">Simulatie</span>
            <h2>500 meter, drie wegcondities</h2>
            <p class="lede" style="margin-bottom: 2em">Dezelfde twee motoren, dezelfde afstand — alleen de wegconditie verandert. Let op wat er gebeurt met de winnaar zodra de tractie daalt.</p>

            @foreach($results as $key => $data)
                @php
                    $result = $data['result'];
                    $winner = $result['winner'] === 'A' ? $motorA : $motorB;
                    $mu = $muDisplay[$key] ?? null;
                    $fastestTime = min($result['time_a_s'], $result['time_b_s']);
                    $widthA = $result['time_a_s'] > 0 ? round(($fastestTime / $result['time_a_s']) * 100, 1) : 0;
                    $widthB = $result['time_b_s'] > 0 ? round(($fastestTime / $result['time_b_s']) * 100, 1) : 0;
                @endphp
                <h3 style="margin-bottom: 1em">{{ $data['label'] }} asfalt &mdash; {{ $winner->label() }} wint</h3>
                <div class="panel" style="margin-bottom: clamp(28px, 4vw, 44px)">
                    <div class="panel__head">
                        <span>Sprint 500m</span>
                        @if($mu)
                            <span class="mu">tractie {{ number_format($mu['t'], 2, ',', '.') }} &middot; rem {{ number_format($mu['b'], 2, ',', '.') }} &middot; bocht {{ number_format($mu['c'], 2, ',', '.') }}</span>
                        @endif
                    </div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $motorA->label() }}</span><span class="ratio">{{ number_format($motorA->powerToWeight(), 2, ',', '.') }} pk/kg</span></div>
                        <div class="bike-row__meta"><span>{{ $motorA->power_hp }} pk</span><span>{{ $motorA->weight_kg }} kg</span><span>{{ number_format($result['time_a_s'], 2, ',', '.') }}s</span></div>
                        <div class="bar"><span style="width:{{ $widthA }}%"></span></div>
                    </div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $motorB->label() }}</span><span class="ratio">{{ number_format($motorB->powerToWeight(), 2, ',', '.') }} pk/kg</span></div>
                        <div class="bike-row__meta"><span>{{ $motorB->power_hp }} pk</span><span>{{ $motorB->weight_kg }} kg</span><span>{{ number_format($result['time_b_s'], 2, ',', '.') }}s</span></div>
                        <div class="bar"><span style="width:{{ $widthB }}%"></span></div>
                    </div>
                    @if($key !== 'dry' && $winner->isNot($dryWinner))
                        <div class="panel__foot">
                            <p style="color: var(--paper-60); font-size: 0.9375rem">Bij deze wegconditie wint de {{ $winner->label() }} in plaats van de {{ $dryWinner->label() }}, die op droog asfalt voorlag. Verschil: {{ number_format($result['delta_s'], 2, ',', '.') }}s.</p>
                        </div>
                    @endif
                </div>
            @endforeach

            @include('partials.simulation-confidence', ['motorA' => $motorA, 'motorB' => $motorB])
        </div>
    </section>

    @include('partials.faq', [
        'faq' => $faq,
        'eyebrow' => 'Veelgestelde vragen',
        'heading' => $motorA->shortLabel() . ' of ' . $motorB->shortLabel() . '?',
    ])

    @if($related->isNotEmpty())
        <section class="chapter" id="verder">
            <div class="wrap">
                <span class="eyebrow">Verder kijken</span>
                <h2>Vergelijk {{ $motorA->label() }} ook met</h2>
                <div class="match-grid">
                    @foreach($related as $item)
                        <article class="match-card">
                            <p class="match-card__badge">{{ $item['motor']->categoryLabel() }}</p>
                            <h3>{{ $item['motor']->label() }}</h3>
                            <p class="spec-mini"><span><b>{{ $item['motor']->power_hp }}</b> pk</span><span><b>{{ $item['motor']->weight_kg }}</b> kg</span></p>
                            <div class="match-card__cta">
                                <a class="btn btn--ghost" href="{{ route('compare.show', $item['slug']) }}">Bekijk vergelijking &rarr;</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Meer bladeren</span>
            <div class="browse-more">
                <a class="btn btn--ghost" href="{{ route('brands.show', \Illuminate\Support\Str::slug($motorA->brand)) }}">Alle {{ $motorA->brand }}</a>
                @if($motorB->brand !== $motorA->brand)
                    <a class="btn btn--ghost" href="{{ route('brands.show', \Illuminate\Support\Str::slug($motorB->brand)) }}">Alle {{ $motorB->brand }}</a>
                @endif
                @if($motorA->category)
                    <a class="btn btn--ghost" href="{{ route('segments.show', $motorA->category) }}">Alle {{ Str::lower($motorA->categoryLabel()) }}</a>
                @endif
            </div>
        </div>
    </section>
@endsection
