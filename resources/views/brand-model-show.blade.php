@extends('layouts.app')

@php
    /*
     * Search Console (3 maanden t/m 31 augustus 2026) laat zien dat de zoekvraag naar deze
     * pagina's letterlijk "{model} gewicht" en "{model} topsnelheid" is, niet "{model}
     * specificaties". Die pagina's stonden op positie 9 tot 11 met nul klikken: de vraag werd
     * wel vertoond, maar de title beantwoordde 'm niet zichtbaar. Vandaar de specificaties in
     * de title in plaats van het woord "specificaties".
     *
     * Topsnelheid staat alleen in de title als die er ook echt is: het veld is leeg bij twee
     * derde van de modellen, en een title die iets belooft wat de pagina niet toont kost
     * uiteindelijk meer clicks dan het oplevert.
     */
    $specSummary = $motor->top_speed_kmh ? 'vermogen, gewicht en topsnelheid' : 'vermogen, gewicht en specificaties';

    /*
     * Eén bron voor zowel het zichtbare FAQ-blok als het FAQPage-schema, zodat de twee niet uit
     * elkaar kunnen lopen. Alleen vragen waarvoor de data er is: een FAQ met "onbekend" als
     * antwoord helpt de bezoeker niet en is voor Google een kwaliteitssignaal de verkeerde kant op.
     */
    $faq = collect([
        [
            'q' => 'Hoeveel pk heeft de ' . $motor->label() . '?',
            'a' => $motor->label() . ' levert ' . $motor->power_hp . ' pk en ' . $motor->torque_nm . ' Nm koppel, bij een gewicht van ' . $motor->weight_kg . ' kg. Dat komt neer op ' . number_format($motor->powerToWeight(), 2, ',', '.') . ' pk per kilo.',
        ],
        [
            'q' => 'Hoe zwaar is de ' . $motor->label() . '?',
            'a' => 'De ' . $motor->label() . ' weegt ' . $motor->weight_kg . ' kg. In combinatie met ' . $motor->power_hp . ' pk geeft dat een vermogen/gewicht-verhouding van ' . number_format($motor->powerToWeight(), 2, ',', '.') . ' pk/kg.',
        ],
        $motor->top_speed_kmh ? [
            'q' => 'Wat is de topsnelheid van de ' . $motor->label() . '?',
            'a' => 'De opgegeven topsnelheid van de ' . $motor->label() . ' is ' . $motor->top_speed_kmh . ' km/u.',
        ] : null,
        $motor->zero_to_hundred_s ? [
            'q' => 'Hoe snel gaat de ' . $motor->label() . ' van 0 naar 100?',
            'a' => 'De ' . $motor->label() . ' doet ongeveer ' . number_format($motor->zero_to_hundred_s, 1, ',', '.') . ' seconden over de sprint van 0 naar 100 km/u.',
        ] : null,
        [
            'q' => 'Is de ' . $motor->label() . ' geschikt voor een A2-rijbewijs?',
            'a' => $motor->isA2Eligible()
                ? 'Ja. Met ' . $motor->power_hp . ' pk en ' . $motor->weight_kg . ' kg blijft de ' . $motor->label() . ' binnen de A2-grenzen van maximaal 35 kW en maximaal 0,20 kW per kilo, zonder dat er een opvoerkit aan te pas komt.'
                : 'Nee. Met ' . $motor->power_hp . ' pk en ' . $motor->weight_kg . ' kg valt de ' . $motor->label() . ' buiten de A2-grenzen van maximaal 35 kW en maximaal 0,20 kW per kilo.',
        ],
    ])->filter()->values();
@endphp

@section('title', $motor->label() . ': ' . $specSummary . ' - RevRace')
@section('description', $motor->label() . ' levert ' . $motor->power_hp . ' pk bij ' . $motor->weight_kg . ' kg (' . number_format($motor->powerToWeight(), 2, ',', '.') . ' pk/kg)' . ($motor->top_speed_kmh ? ', topsnelheid ' . $motor->top_speed_kmh . ' km/u' : '') . '. Alle specificaties en directe vergelijkingen.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Merken', 'item' => route('brands.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $motor->brand, 'item' => route('brands.show', $brandSlug)],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $motor->model . ' ' . $motor->year],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    // 'Vehicle'-subtype i.p.v. 'Product': dit is een specificatiepagina zonder prijs/aanbieding,
    // en Product zonder offers/review/aggregateRating triggert Google's rich-results-validatie-
    // fout (347 stuks in Ahrefs). Motorcycle vraagt niet om die commerciële velden.
    '@'.'context' => 'https://schema.org',
    '@type' => 'Motorcycle',
    'name' => $motor->label(),
    'brand' => ['@type' => 'Brand', 'name' => $motor->brand],
    'category' => $motor->categoryLabel(),
    'additionalProperty' => [
        ['@type' => 'PropertyValue', 'name' => 'Vermogen', 'value' => $motor->power_hp, 'unitText' => 'pk'],
        ['@type' => 'PropertyValue', 'name' => 'Koppel', 'value' => $motor->torque_nm, 'unitText' => 'Nm'],
        ['@type' => 'PropertyValue', 'name' => 'Gewicht', 'value' => $motor->weight_kg, 'unitText' => 'kg'],
        ['@type' => 'PropertyValue', 'name' => 'Cilinderinhoud', 'value' => $motor->displacement_cc, 'unitText' => 'cc'],
        // Alleen meegeven als de waarde er is: een PropertyValue met een lege value is voor
        // Google een ongeldige property, geen neutrale.
        ...($motor->top_speed_kmh ? [['@type' => 'PropertyValue', 'name' => 'Topsnelheid', 'value' => $motor->top_speed_kmh, 'unitText' => 'km/u']] : []),
        ...($motor->zero_to_hundred_s ? [['@type' => 'PropertyValue', 'name' => 'Acceleratie 0-100 km/u', 'value' => $motor->zero_to_hundred_s, 'unitText' => 's']] : []),
        ['@type' => 'PropertyValue', 'name' => 'Vermogen/gewicht', 'value' => number_format($motor->powerToWeight(), 2), 'unitText' => 'pk/kg'],
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
@endpush

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.index') }}">Merken</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.show', $brandSlug) }}">{{ $motor->brand }}</a><span class="sep">&rarr;</span>
                <span>{{ $motor->model }} {{ $motor->year }}</span>
            </nav>
            <span class="eyebrow">{{ $motor->categoryLabel() }}</span>
            <h1>{{ $motor->label() }}</h1>
            <p class="lede">
                {{ $motor->label() }} levert {{ $motor->power_hp }} pk bij {{ $motor->weight_kg }} kg, een vermogen/gewicht-verhouding van {{ number_format($motor->powerToWeight(), 2, ',', '.') }} pk/kg.
                @if($motor->isA2Eligible())
                    Op basis van deze specificaties A2-geschikt (zonder opvoerkit).
                @endif
            </p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="spec-head">
                <span class="eyebrow">Specificaties</span>
            </div>
            <div class="spec spec--single">
                <div class="spec__row spec__row--head">
                    <div class="spec__label">Motor</div>
                    <div class="spec__value spec__value--a">{{ $motor->label() }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Vermogen</div>
                    <div class="spec__value">{{ $motor->power_hp }} pk</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Koppel</div>
                    <div class="spec__value">{{ $motor->torque_nm }} Nm</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Gewicht</div>
                    <div class="spec__value">{{ $motor->weight_kg }} kg</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Pk per kg</div>
                    <div class="spec__value">{{ number_format($motor->powerToWeight(), 2, ',', '.') }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Motortype</div>
                    <div class="spec__value">{{ $motor->engine_type }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Cilinderinhoud</div>
                    <div class="spec__value">{{ $motor->displacement_cc }} cc</div>
                </div>
                @if($motor->top_speed_kmh)
                    <div class="spec__row">
                        <div class="spec__label">Topsnelheid</div>
                        <div class="spec__value">{{ $motor->top_speed_kmh }} km/h</div>
                    </div>
                @endif
                <div class="spec__row">
                    <div class="spec__label">Categorie</div>
                    <div class="spec__value">
                        @if($motor->category)
                            <a class="accent" href="{{ route('segments.show', $motor->category) }}">{{ $motor->categoryLabel() }}</a>
                        @else
                            {{ $motor->categoryLabel() }}
                        @endif
                    </div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">A2-geschikt</div>
                    <div class="spec__value">{{ $motor->isA2Eligible() ? 'Ja' : 'Nee' }}</div>
                </div>
            </div>

            <details class="report-disclosure">
                <summary>Klopt deze informatie niet?</summary>
                <form method="post" action="{{ route('motors.report', $motor) }}" class="form-card" style="margin-top:1rem">
                    @csrf
                    <div class="form-honeypot" aria-hidden="true">
                        <label for="report-website">Laat dit veld leeg</label>
                        <input type="text" id="report-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-row" style="margin-bottom:0.4rem">
                        <label for="report-message">Wat klopt er niet?</label>
                        <textarea id="report-message" name="message" rows="3" required placeholder="Bijv. het vermogen of gewicht klopt niet met de fabrieksopgave"></textarea>
                    </div>
                    <div class="form-row">
                        <label for="report-email">E-mailadres (optioneel, voor een reactie)</label>
                        <input id="report-email" name="reporter_email" type="email">
                    </div>
                    <div class="form-foot">
                        <button class="btn btn--ghost" type="submit">Melding versturen</button>
                    </div>
                </form>
            </details>
        </div>
    </section>

    @if($comparisons->isNotEmpty())
        @php
            // Bewust ongelimiteerd doorgegeven vanuit BrandController::modelComparisons() zodat
            // elke vergelijkingspagina in deze categorie minstens één inkomende link krijgt. De
            // eerste 8 direct tonen, de rest achter een <details> om de pagina niet te laten
            // ontsporen bij grote categorieën (tot ~74 motoren, dus tot ~73 kaarten).
            $visibleComparisons = $comparisons->take(8);
            $restComparisons = $comparisons->slice(8);
        @endphp
        <section class="chapter">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <span class="eyebrow">Vergelijkingen</span>
                        <h2>{{ $motor->label() }} tegen andere {{ Str::lower($motor->categoryLabel()) }}s</h2>
                    </div>
                </div>
                <div class="kb-grid">
                    @foreach($visibleComparisons as $row)
                        <a class="kb-card" href="{{ route('compare.show', $row['slug']) }}">
                            <h3>{{ $motor->label() }}</h3>
                            <p>vs {{ $row['motor']->label() }}</p>
                            <span class="kb-card__link">Bekijk vergelijking &rarr;</span>
                        </a>
                    @endforeach
                </div>
                @if($restComparisons->isNotEmpty())
                    <details class="report-disclosure">
                        <summary>Toon alle {{ $comparisons->count() }} vergelijkingen</summary>
                        <div class="kb-grid" style="margin-top: 1.2rem">
                            @foreach($restComparisons as $row)
                                <a class="kb-card" href="{{ route('compare.show', $row['slug']) }}">
                                    <h3>{{ $motor->label() }}</h3>
                                    <p>vs {{ $row['motor']->label() }}</p>
                                    <span class="kb-card__link">Bekijk vergelijking &rarr;</span>
                                </a>
                            @endforeach
                        </div>
                    </details>
                @endif
            </div>
        </section>
    @endif

    @include('partials.faq', [
        'faq' => $faq,
        'eyebrow' => 'Veelgestelde vragen',
        'heading' => 'Over de ' . $motor->label(),
    ])

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">Twijfel je?</p>
            <h2>Race de {{ $motor->model }} tegen een andere motor.</h2>
            <p class="lede">Op droog, vochtig en nat asfalt &mdash; zelfde twee motoren, soms een andere winnaar.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>
@endsection
