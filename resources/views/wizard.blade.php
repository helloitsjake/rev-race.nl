@extends('layouts.app')

@section('title', 'Welke motor past bij mij - RevRace')
@section('description', 'Beantwoord een paar vragen over je rijstijl en gebruik en krijg advies welke motoren uit de RevRace database bij je passen.')

@php
    $faqs = [
        [
            'question' => 'Hoe bepaalt RevRace welke motor bij mij past?',
            'answer' => 'Je rijstijl (bochten, snelheid of relax) en het terrein waar je vooral rijdt worden vertaald naar een score per motorcategorie, zoals naked, sport, tourer, adventure, cruiser of retro. Rijstijl weegt daarbij zwaarder dan terrein: terrein is alleen de omgeving, rijstijl zegt direct iets over het karakter van de motor dat bij je past. Binnen de best passende categorie rangschikken we vervolgens elke motor op basis van de verhouding tussen vermogen en gewicht, altijd vergeleken met andere motoren in dezelfde categorie, zodat een lichte 125cc-retro niet oneerlijk wordt afgezet tegen een zware cruiser.',
        ],
        [
            'question' => 'Wat betekent het label "A2 geschikt"?',
            'answer' => 'Een motor krijgt dit label als het vermogen niet boven de 35 kW (circa 47 pk) uitkomt en de verhouding tussen vermogen en gewicht niet hoger is dan 0,20 kW per kilo, de twee eisen van het Europese A2-rijbewijs. Kies je bij rij-ervaring voor "Net rijbewijs / A2", dan filteren we automatisch op deze motoren. Let op: dit is gebaseerd op de fabrieksspecificaties in onze database, sommige modellen hebben daarnaast een losse gedrosseerde A2-uitvoering die hier niet apart in is opgenomen.',
        ],
        [
            'question' => 'Moet ik alle vragen invullen?',
            'answer' => 'Nee. Leeftijd, lengte en gewicht zijn optioneel en worden alleen gebruikt om de simulatie preciezer te maken als je doorklikt naar "Simuleer met deze motor". Voor een advies heb je alleen je rij-ervaring nodig, gecombineerd met minimaal je rijstijl of het terrein waar je rijdt.',
        ],
        [
            'question' => 'Ik krijg meerdere motoren te zien, kan ik dat verder versmallen?',
            'answer' => 'Ja. Zodra er een match is, verschijnt er een merkfilter boven de resultaten waarmee je binnen de geadviseerde categorie op merk kunt filteren. Staat je voorkeursmerk er niet tussen voor deze combinatie, dan laten we het volledige advies zonder merkfilter zien in plaats van een lege pagina.',
        ],
        [
            'question' => 'Is dit advies net zo goed als een proefrit?',
            'answer' => 'Nee, en dat is ook niet het doel. Dit advies is een datagedreven eerste selectie op basis van specificaties, geen vervanging voor zelf op de motor zitten of een proefrit bij een dealer. Gebruik het om een shortlist te maken, en race je twijfelgevallen daarna tegen elkaar in de simulator om te zien hoe ze zich verhouden op droog, vochtig en nat asfalt.',
        ],
    ];
@endphp

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Welke motor past bij mij'],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(fn (array $faq) => [
        '@type' => 'Question',
        'name' => $faq['question'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['answer']],
    ], $faqs),
]) !!}
</script>
@endpush

@section('content')
    <header>
        <span class="eyebrow">Welke motor past bij mij</span>
        <h1 class="page-title">Vertel hoe je rijdt, wij zoeken uit wat past</h1>
        <p class="page-sub">Geen vakjargon om zelf te kiezen. Vertel iets over jezelf en hoe je het liefst rijdt, en we vertalen dat naar motoren die daar echt bij passen.</p>
    </header>

    <section class="section">
        <div class="panel">
            <p>
                Dit is geen quiz met een vaste uitkomst uit een lijstje, maar een advies dat direct uit onze motordatabase
                wordt berekend. We vertalen je rijstijl en het terrein waar je vooral rijdt naar een motorcategorie, en
                rangschikken daarbinnen elke motor op de verhouding tussen vermogen en gewicht, zodat het advies past bij
                zowel je manier van rijden als je ervaring.
            </p>
            <p style="margin-top:10px">
                Geef je aan net je rijbewijs te hebben of op een A2-rijbewijs te rijden, dan tonen we alleen motoren die
                aan de Europese A2-eisen voldoen: maximaal 35 kW vermogen en een verhouding van vermogen tot gewicht van
                niet meer dan 0,20 kW per kilo. Twijfel je tussen twee modellen uit het advies? Race ze daarna tegen
                elkaar in de <a class="accent" href="{{ route('simulation.index') }}">simulator</a> om te zien hoe ze
                zich verhouden op droog, vochtig en nat asfalt.
            </p>
        </div>
    </section>

    <form class="panel" method="get" action="{{ route('wizard.index') }}">
        <div class="form-row">
            <span class="form-label">Over jou (optioneel)</span>
            <div class="sim-grid">
                <div>
                    <label class="form-label" for="leeftijd">Leeftijd</label>
                    <input class="input" id="leeftijd" name="leeftijd" type="number" min="16" max="99" value="{{ $leeftijd }}" placeholder="Bijv. 28">
                </div>
                <div>
                    <label class="form-label" for="lengte">Lengte (cm)</label>
                    <input class="input" id="lengte" name="lengte" type="number" min="140" max="220" value="{{ $lengte }}" placeholder="Bijv. 180">
                </div>
                <div>
                    <label class="form-label" for="gewicht">Gewicht (kg)</label>
                    <input class="input" id="gewicht" name="gewicht" type="number" min="30" max="180" value="{{ $gewicht }}" placeholder="Bijv. 75">
                </div>
            </div>
        </div>

        <div class="form-row">
            <span class="form-label">Wat is je rij-ervaring?</span>
            <div class="choice-row">
                @foreach($experienceLevels as $key => $label)
                    <label class="choice">
                        <input type="radio" name="ervaring" value="{{ $key }}" @checked($selectedErvaring === $key)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-row">
            <span class="form-label">Wat past het best bij jouw rijstijl?</span>
            <div class="choice-row">
                @foreach($voorkeuren as $key => $label)
                    <label class="choice">
                        <input type="radio" name="voorkeur" value="{{ $key }}" @checked($selectedVoorkeur === $key)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-row">
            <span class="form-label">Waar rijd je vooral? (meerdere mogelijk)</span>
            <div class="choice-row">
                @foreach($terreinen as $key => $label)
                    <label class="choice">
                        <input type="checkbox" name="terrein[]" value="{{ $key }}" @checked(in_array($key, $selectedTerrein, true))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <button class="btn primary" type="submit">Geef me advies</button>
    </form>

    @if($anyMatches !== null)
        <section class="section">
            @if($anyMatches->isNotEmpty())
                @php
                    $profielZin = ($lengte || $gewicht) ? ', en rekening houdend met je lengte en gewicht' : '';
                    $totalCount = $topMatches->count() + $moreMatches->count();
                @endphp
                <div class="panel" style="margin-bottom:20px">
                    <h2 class="card-title">Ons advies voor jou</h2>
                    <p style="margin-top:8px">
                        @if(count($reasonParts))
                            Omdat je aangaf: {{ implode(' en ', $reasonParts) }}{{ $profielZin }}, passen deze motoren het best bij je.
                        @else
                            Op basis van je antwoorden passen deze motoren het best bij je.
                        @endif
                    </p>

                    @if($merkFallbackUsed)
                        <p class="small" style="margin-top:10px;color:var(--dim)">Van {{ $selectedMerk }} hebben we geen match in deze categorie, hieronder ons advies zonder merkfilter.</p>
                    @endif

                    @if($availableBrands->count() > 1)
                        <form method="get" action="{{ route('wizard.index') }}" style="margin-top:14px;max-width:280px">
                            <input type="hidden" name="ervaring" value="{{ $selectedErvaring }}">
                            @if($selectedVoorkeur)<input type="hidden" name="voorkeur" value="{{ $selectedVoorkeur }}">@endif
                            @foreach($selectedTerrein as $t)<input type="hidden" name="terrein[]" value="{{ $t }}">@endforeach
                            @if($leeftijd)<input type="hidden" name="leeftijd" value="{{ $leeftijd }}">@endif
                            @if($lengte)<input type="hidden" name="lengte" value="{{ $lengte }}">@endif
                            @if($gewicht)<input type="hidden" name="gewicht" value="{{ $gewicht }}">@endif

                            <label class="form-label" for="merk">Versmal op merk (optioneel)</label>
                            <select class="select" id="merk" name="merk" onchange="this.form.submit()">
                                <option value="">Alle merken</option>
                                @foreach($availableBrands as $brand)
                                    <option value="{{ $brand }}" @selected($selectedMerk === $brand)>{{ $brand }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
                <div class="card-grid">
                    @foreach($topMatches as $motor)
                        <article class="card">
                            <div class="chart-head" style="margin-bottom:10px">
                                <span class="badge">{{ $motor->categoryLabel() }}</span>
                                @if($motor->isA2Eligible())
                                    <span class="badge" style="border-color:var(--teal);color:var(--teal)">A2 geschikt</span>
                                @endif
                            </div>
                            <h3 class="card-title">{{ $motor->label() }}</h3>
                            <div class="spec-row">
                                <span class="spec-label">Vermogen</span>
                                <span class="spec-value">{{ $motor->power_hp }} pk</span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Gewicht</span>
                                <span class="spec-value">{{ $motor->weight_kg }} kg</span>
                            </div>
                            <div class="hero-actions" style="margin-top:14px">
                                <a class="btn primary" href="{{ route('simulation.index', array_filter(['motor_a' => $motor->id, 'gewicht' => $gewicht])) }}">Simuleer met deze motor</a>
                                <a class="btn secondary" href="https://www.google.com/search?q={{ urlencode($motor->label()) }}" target="_blank" rel="noopener">Zoek deze motor</a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($moreMatches->isNotEmpty())
                    <details style="margin-top:22px">
                        <summary style="cursor:pointer;font-weight:600">Bekijk alle {{ $totalCount }} modellen in deze categorie</summary>
                        <div class="card-grid" style="margin-top:16px">
                            @foreach($moreMatches as $motor)
                                <article class="card">
                                    <div class="chart-head" style="margin-bottom:10px">
                                        <span class="badge">{{ $motor->categoryLabel() }}</span>
                                        @if($motor->isA2Eligible())
                                            <span class="badge" style="border-color:var(--teal);color:var(--teal)">A2 geschikt</span>
                                        @endif
                                    </div>
                                            <h3 class="card-title">{{ $motor->label() }}</h3>
                                    <div class="spec-row">
                                        <span class="spec-label">Vermogen</span>
                                        <span class="spec-value">{{ $motor->power_hp }} pk</span>
                                    </div>
                                    <div class="spec-row">
                                        <span class="spec-label">Gewicht</span>
                                        <span class="spec-value">{{ $motor->weight_kg }} kg</span>
                                    </div>
                                    <div class="hero-actions" style="margin-top:14px">
                                        <a class="btn primary" href="{{ route('simulation.index', array_filter(['motor_a' => $motor->id, 'gewicht' => $gewicht])) }}">Simuleer met deze motor</a>
                                        <a class="btn secondary" href="https://www.google.com/search?q={{ urlencode($motor->label()) }}" target="_blank" rel="noopener">Zoek deze motor</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </details>
                @endif
            @else
                <div class="panel">
                    <h2 class="card-title">Nog geen match in deze combinatie</h2>
                    <p style="margin-top:8px">Voor deze combinatie van antwoorden staat op dit moment geen motor in onze database die volledig past. De database groeit nog, probeer een andere rijstijl of bekijk de volledige toplijst.</p>

                    @if($fallback && $fallback->isNotEmpty())
                        <p class="small" style="margin-top:14px;color:var(--dim)">Wel A2 geschikt, in andere categorieën:</p>
                        <div class="card-grid" style="margin-top:12px">
                            @foreach($fallback as $motor)
                                <article class="card">
                                    <span class="badge">{{ $motor->categoryLabel() }}</span>
                                    <h3 class="card-title" style="margin-top:8px">{{ $motor->label() }}</h3>
                                    <div class="hero-actions" style="margin-top:12px">
                                        <a class="btn secondary" href="{{ route('simulation.index', ['motor_a' => $motor->id]) }}">Simuleer met deze motor</a>
                                        <a class="btn secondary" href="https://www.google.com/search?q={{ urlencode($motor->label()) }}" target="_blank" rel="noopener">Zoek deze motor</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <div class="hero-actions" style="margin-top:18px">
                        <a class="btn secondary" href="{{ route('toplijst.show', 'beste-pk-kg-verhouding') }}">Bekijk een toplijst</a>
                        <a class="btn ghost" href="{{ route('simulation.index') }}">Naar de simulator</a>
                    </div>
                </div>
            @endif
        </section>
    @endif

    <section class="section">
        <span class="eyebrow">Veelgestelde vragen</span>
        <h2 class="section-title">Hoe het advies werkt</h2>
        <div class="panel">
            @foreach($faqs as $faq)
                <details style="{{ $loop->first ? '' : 'margin-top:14px;' }}border-top:{{ $loop->first ? 'none' : '1px solid var(--line)' }};padding-top:{{ $loop->first ? '0' : '14px' }}">
                    <summary style="cursor:pointer;font-weight:600">{{ $faq['question'] }}</summary>
                    <p style="margin-top:10px">{{ $faq['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>
@endsection
