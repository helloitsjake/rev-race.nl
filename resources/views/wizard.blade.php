@extends('layouts.app')

@section('title', 'Welke motor past bij mij - RevRace')
@section('description', 'Beantwoord een paar vragen over je rijstijl en gebruik en krijg advies welke motoren uit de RevRace database bij je passen.')

@php
    $faqs = [
        [
            'question' => 'Hoe bepaalt RevRace welke motor bij mij past?',
            'answer' => 'Je rijstijl (bochten, snelheid of plezier) en het terrein waar je vooral rijdt worden vertaald naar een score per motorcategorie, zoals naked, sport, tourer, adventure, cruiser of retro. Rijstijl weegt daarbij twee keer zo zwaar als terrein. Binnen de best passende categorie rangschikken we elke motor op wat bij jouw rijstijl past: voor snelheid het meeste vermogen per kilo, voor bochten een lichte, wendbare motor met genoeg vermogen, en voor plezier het rustige midden. Altijd vergeleken met motoren uit dezelfde categorie, zodat een lichte retro niet oneerlijk wordt afgezet tegen een zware cruiser.',
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
    @php
        $arrow = '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg>';
        $voorkeurIcons = [
            'bochten' => '<path d="M4 18c0-6 5-5 7-9s1-6 7-6"/>',
            'snelheid' => '<path d="M3 11h15M13 6l5 5-5 5"/>',
            'relax' => '<circle cx="11" cy="11" r="7.5"/><path d="M8 12.5c1.5 1.7 4.5 1.7 6 0"/>',
        ];
        $voorkeurKort = ['bochten' => ['Bochten en leunhoek', 'Wendbaar, licht, sportief'], 'snelheid' => ['Snel op het rechte stuk', 'Vermogen en acceleratie'], 'relax' => ['Rijden voor het plezier', 'Comfortabel, geen haast']];
        $ervaringKort = ['beginner' => ['Net rijbewijs of A2', 'Alleen motoren tot 35 kW'], 'ervaren' => ['Vol rijbewijs', 'Alle motoren']];
        $simUrl = fn ($motor) => route('simulation.index', array_filter(['motor_a' => $motor->id, 'gewicht' => $gewicht]));
        $googleUrl = fn ($motor) => 'https://www.google.com/search?q='.urlencode($motor->label());
    @endphp

    <div class="wrap" style="padding-block:clamp(28px,4vw,56px) clamp(28px,4vw,44px)">
        <p class="turn"><b>T1</b> Advies op rijstijl</p>
        <h1 style="max-width:16ch">Vertel hoe je rijdt, wij zoeken uit wat past</h1>
        <p class="lede" style="margin-top:20px">Geen vakjargon nodig. Je antwoorden worden vertaald naar een type motor, en binnen dat type zoeken we de motoren die het beste bij jouw rijstijl en ervaring passen.</p>
    </div>

    <div class="wrap wiz" style="padding-bottom:clamp(72px,9vw,120px)">
        <form class="wiz-form" method="get" action="{{ route('wizard.index') }}#advies" data-wizard-form>
            <div class="sectorbar" aria-hidden="true"><span></span><span></span><span></span><span></span></div>

            <fieldset>
                <legend><em>S1</em> Je rij-ervaring</legend>
                <div class="cards cards--2">
                    @foreach($experienceLevels as $key => $label)
                        <label class="choice-card"><input type="radio" name="ervaring" value="{{ $key }}" @checked($selectedErvaring === $key) required><span><b>{{ $ervaringKort[$key][0] ?? $label }}</b><small>{{ $ervaringKort[$key][1] ?? '' }}</small></span></label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend><em>S2</em> Je rijstijl</legend>
                <div class="cards">
                    @foreach($voorkeuren as $key => $label)
                        <label class="choice-card"><input type="radio" name="voorkeur" value="{{ $key }}" @checked($selectedVoorkeur === $key)><svg viewBox="0 0 22 22" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $voorkeurIcons[$key] ?? '' !!}</svg><span><b>{{ $voorkeurKort[$key][0] ?? $label }}</b><small>{{ $voorkeurKort[$key][1] ?? '' }}</small></span></label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend><em>S3</em> Waar je vooral rijdt <small>meerdere mogelijk</small></legend>
                <div class="cards">
                    @foreach($terreinen as $key => $label)
                        <label class="choice-card choice-card--multi"><input type="checkbox" name="terrein[]" value="{{ $key }}" @checked(in_array($key, $selectedTerrein, true))><span><b>{{ $label }}</b></span></label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset>
                <legend><em>S4</em> Over jou <small>optioneel</small></legend>
                <div class="fields">
                    <div><label for="leeftijd">Leeftijd</label><div class="unit"><input id="leeftijd" name="leeftijd" type="number" min="16" max="99" value="{{ $leeftijd }}" placeholder="28"><i>jr</i></div></div>
                    <div><label for="lengte">Lengte</label><div class="unit"><input id="lengte" name="lengte" type="number" min="140" max="220" value="{{ $lengte }}" placeholder="180"><i>cm</i></div></div>
                    <div><label for="gewicht">Gewicht</label><div class="unit"><input id="gewicht" name="gewicht" type="number" min="30" max="180" value="{{ $gewicht }}" placeholder="75"><i>kg</i></div></div>
                </div>
                <p class="hint">Je gewicht nemen we mee als je doorklikt naar de simulator. Leeftijd en lengte hebben geen invloed op het advies.</p>
            </fieldset>

            <button class="btn btn--primary" type="submit">Geef me advies {!! $arrow !!}</button>
        </form>

        <div>
            <div class="result" id="advies" aria-live="polite" @if($anyMatches && $anyMatches->isNotEmpty()) data-lights-on-load @endif>
                <div class="grid-head">
                    <div class="lights" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                    @if($anyMatches && $anyMatches->isNotEmpty())
                        <p class="status">{{ $topMatches->count() }} van de <b>{{ $topMatches->count() + $moreMatches->count() }}</b> matches</p>
                    @endif
                </div>

                @if($anyMatches === null)
                    <h2>Jouw startopstelling</h2>
                    <p class="because">Kies links je rij-ervaring en je rijstijl of het terrein waar je rijdt. Dan zetten we hier de zes motoren neer die het beste bij je passen.</p>
                @elseif($anyMatches->isNotEmpty())
                    <h2>Jouw startopstelling</h2>
                    <p class="because">
                        @if(count($reasonParts))
                            Omdat je aangaf: <b>{!! implode('</b> en <b>', array_map('e', $reasonParts)) !!}</b>.
                        @else
                            Op basis van je antwoorden passen deze motoren het best bij je.
                        @endif
                    </p>
                    @if($merkFallbackUsed)
                        <p class="note-inline">Van {{ $selectedMerk }} hebben we geen match in deze categorie, hieronder het advies zonder merkfilter.</p>
                    @endif

                    <div class="tools">
                        @if($availableBrands->count() > 1)
                            <form method="get" action="{{ route('wizard.index') }}#advies">
                                <input type="hidden" name="ervaring" value="{{ $selectedErvaring }}">
                                @if($selectedVoorkeur)<input type="hidden" name="voorkeur" value="{{ $selectedVoorkeur }}">@endif
                                @foreach($selectedTerrein as $t)<input type="hidden" name="terrein[]" value="{{ $t }}">@endforeach
                                @if($leeftijd)<input type="hidden" name="leeftijd" value="{{ $leeftijd }}">@endif
                                @if($lengte)<input type="hidden" name="lengte" value="{{ $lengte }}">@endif
                                @if($gewicht)<input type="hidden" name="gewicht" value="{{ $gewicht }}">@endif
                                <div class="select"><select name="merk" aria-label="Versmal op merk" onchange="this.form.submit()">
                                    <option value="">Alle merken</option>
                                    @foreach($availableBrands as $brand)
                                        <option value="{{ $brand }}" @selected($selectedMerk === $brand)>{{ $brand }}</option>
                                    @endforeach
                                </select></div>
                            </form>
                        @endif
                        @foreach($topCategories as $categorie)
                            <a href="{{ route('segments.show', $categorie) }}">Alle {{ Str::lower(\App\Models\Motor::CATEGORIES[$categorie] ?? $categorie) }}-modellen</a>
                        @endforeach
                        @if($selectedErvaring === 'beginner')
                            <a href="{{ route('a2-motoren') }}">Alle A2-motoren</a>
                        @endif
                    </div>

                    <div class="startgrid startgrid--six">
                        @foreach($topMatches as $motor)
                            <div class="slot" style="--odd:{{ $loop->index % 2 }}">
                                <div class="slot__pos">P{{ $loop->iteration }}</div>
                                <div class="slot__car">
                                    <div class="slot__name">{{ $motor->label() }}@if($motor->isA2Eligible())<span class="a2">A2</span>@endif</div>
                                    <div class="slot__seg">{{ $motor->categoryLabel() }}</div>
                                    <div class="slot__specs"><span><b>{{ $motor->power_hp }}</b> pk</span><span><b>{{ $motor->weight_kg }}</b> kg</span><span><b>{{ number_format($motor->powerToWeight(), 2, ',', '') }}</b> pk/kg</span></div>
                                    <div class="slot__acts"><a href="{{ $simUrl($motor) }}">Simuleer</a><a href="{{ $googleUrl($motor) }}" target="_blank" rel="noopener">Zoek deze motor</a></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($moreMatches->isNotEmpty())
                        <details class="more-list">
                            <summary>Bekijk alle {{ $topMatches->count() + $moreMatches->count() }} matches</summary>
                            <div class="model-list model-list--compact" style="padding:0;background:none">
                                <div class="model-row model-row--head"><span>Model</span><span class="model-row__num">pk</span><span class="model-row__num">kg</span><span class="model-row__num">pk/kg</span></div>
                                @foreach($moreMatches as $motor)
                                    <div class="model-row">
                                        <span class="model-row__name"><a href="{{ $simUrl($motor) }}">{{ $motor->label() }}</a></span>
                                        <span class="model-row__num">{{ $motor->power_hp }}</span>
                                        <span class="model-row__num">{{ $motor->weight_kg }}</span>
                                        <span class="model-row__num model-row__num--strong">{{ number_format($motor->powerToWeight(), 2, ',', '') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                @else
                    <h2>Nog geen match in deze combinatie</h2>
                    <p class="because">Voor deze combinatie staat er nog geen motor in onze database die volledig past. De database groeit nog: probeer een andere rijstijl of bekijk een toplijst.</p>
                    @if($fallback && $fallback->isNotEmpty())
                        <p class="because" style="margin-top:18px">Wel A2-geschikt, in andere categorieën:</p>
                        <div class="startgrid startgrid--six">
                            @foreach($fallback->take(6) as $motor)
                                <div class="slot" style="--odd:{{ $loop->index % 2 }}">
                                    <div class="slot__pos">P{{ $loop->iteration }}</div>
                                    <div class="slot__car is-in">
                                        <div class="slot__name">{{ $motor->label() }}</div>
                                        <div class="slot__seg">{{ $motor->categoryLabel() }}</div>
                                        <div class="slot__acts"><a href="{{ $simUrl($motor) }}">Simuleer</a><a href="{{ $googleUrl($motor) }}" target="_blank" rel="noopener">Zoek deze motor</a></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div class="tools"><a href="{{ route('toplijst.show', 'beste-pk-kg-verhouding') }}">Bekijk een toplijst</a><a href="{{ route('simulation.index') }}">Naar de simulator</a></div>
                @endif
            </div>

            <div class="how">
                <p class="turn"><b>S5</b> Veelgestelde vragen</p>
                <h3>Hoe het advies werkt</h3>
                <div class="faq">
                    @foreach($faqs as $faq)
                        <details class="faq-item" @if($loop->first) open @endif>
                            <summary>{{ $faq['question'] }}</summary>
                            <p>{{ $faq['answer'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
