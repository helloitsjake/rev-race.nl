@extends('layouts.app')

@php
    /*
     * A2 per segment (24-11-2026), /a2-motoren/{categorie}. Opbouw en klassen zoals
     * segment-show en a2-motoren, dus geen nieuwe CSS. FAQ en FAQPage-schema uit één bron.
     */
    $a2Label = 'A2 ' . $buyingLabel;
    $strongest = $motors->sortByDesc('power_hp')->first();
    $lightest = $motors->sortBy('weight_kg')->first();
    $best = $motors->first();
    $ccMin = $motors->min('displacement_cc');
    $ccMax = $motors->max('displacement_cc');
    $fmt = fn ($n) => number_format($n, 2, ',', '.');
    $outside = $segment->count() - $motors->count();

    $faq = collect([
        [
            'q' => 'Wat is de sterkste ' . $a2Label . '?',
            'a' => 'De ' . $buyingLabel . ' met het meeste vermogen die zonder drosselkit binnen de A2-grenzen valt, is in onze database de ' . $strongest->label() . ': ' . $strongest->power_hp . ' pk bij ' . $strongest->weight_kg . ' kg.',
        ],
        [
            'q' => 'Wat is de lichtste ' . $a2Label . '?',
            'a' => 'De lichtste is de ' . $lightest->label() . ' met ' . $lightest->weight_kg . ' kg en ' . $lightest->power_hp . ' pk. Een lichte motor is vooral bij lage snelheid en bij het keren prettig als je net begint.',
        ],
        [
            'q' => 'Hoeveel cc heeft een ' . $a2Label . '?',
            'a' => 'De ' . $motors->count() . ' A2-modellen in dit segment hebben tussen ' . $ccMin . ' en ' . $ccMax . ' cc. Voor A2 telt de cilinderinhoud niet: de grens is maximaal 35 kW (ongeveer 47 pk) en maximaal 0,20 kW per kilo.',
        ],
        [
            'q' => 'Mag ik met A2 een zwaardere ' . $buyingLabel . ' rijden?',
            'a' => 'Ja, als er een gedrosselde versie van 35 kW bestaat en het originele vermogen niet hoger is dan 70 kW. Veel populaire modellen zijn zo te koop. Deze lijst toont alleen motoren die ook zonder drosselkit al binnen de A2-grenzen vallen.',
        ],
    ]);
@endphp

@section('title', $a2Label . ': alle ' . $motors->count() . ' geschikte modellen - RevRace')
@section('description', 'Alle ' . $motors->count() . ' ' . $buyingLabel . '-modellen die je met een A2-rijbewijs mag rijden, vergeleken op vermogen, gewicht en pk per kilo. Sterkste: ' . $strongest->shortLabel() . ' (' . $strongest->power_hp . ' pk).')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'A2 motoren', 'item' => route('a2-motoren')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $a2Label],
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
<script src="{{ asset('js/overzicht.js') }}?v={{ filemtime(public_path('js/overzicht.js')) }}" defer></script>
@endpush

@section('content')
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('a2-motoren') }}">A2 motoren</a><span class="sep">&rarr;</span>
                <span>{{ $label }}</span>
            </nav>
            <h1>{{ $a2Label }}: alle {{ $motors->count() }} geschikte modellen</h1>
            <div class="seg-hero__body">
                <div>
                    <p class="lede">{{ $intro }}</p>
                    <p class="seg-hero__intro">
                        Hieronder alle {{ $motors->count() }} {{ Str::lower($label) }}-modellen die zonder drosselkit binnen de A2-grenzen vallen.
                        Twijfel je tussen segmenten? De <a class="accent" href="{{ route('wizard.index', ['ervaring' => 'beginner']) }}">wizard</a> kiest op basis van je rijstijl.
                    </p>
                </div>
                <dl class="seg-facts">
                    <div><dt>Modellen</dt><dd>{{ $motors->count() }}</dd></div>
                    <div><dt>Cilinderinhoud</dt><dd>{{ $ccMin }}–{{ $ccMax }} <small>cc</small></dd></div>
                    <div><dt>Sterkste</dt><dd>{{ $strongest->power_hp }} <small>pk</small></dd><dd class="seg-facts__who">{{ $strongest->brand }} {{ $strongest->model }}</dd></div>
                    <div><dt>Lichtste</dt><dd>{{ $lightest->weight_kg }} <small>kg</small></dd><dd class="seg-facts__who">{{ $lightest->brand }} {{ $lightest->model }}</dd></div>
                </dl>
            </div>
        </div>
    </header>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap">
            <div class="plot-head">
                <h2>Elke {{ $a2Label }} binnen de grenzen</h2>
                <p>Het groene vlak is wat je met A2 mag rijden. De grijze stippen zijn {{ Str::lower($label) }}-modellen die te sterk of te licht zijn voor A2{{ $outside ? ' (' . $outside . ' in totaal, de zwaarste vallen buiten beeld)' : '' }}. Meeste vermogen per kilo: de {{ $best->brand }} {{ $best->model }} met {{ $fmt($best->powerToWeight()) }} pk/kg.</p>
            </div>
            @include('partials.spec-plot', [
                'motors' => $motors,
                'context' => $segment->reject(fn ($m) => $m->isA2Eligible())->values(),
                'zone' => 'a2',
                'best' => $best,
                'xMin' => 100, 'xMax' => 300, 'yMax' => 80,
            ])
            <div class="plot-legend">
                <span><i class="is-a2"></i>A2-geschikt</span>
                <span><i class="is-ghost"></i>Andere {{ Str::lower($label) }}-modellen</span>
                <span><i class="is-best"></i>Beste pk/kg</span>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <h2 class="speclist__title">Alle {{ $motors->count() }} A2 {{ $buyingLabel }}-modellen</h2>
            @include('partials.spec-list', ['motors' => $motors, 'limit' => 12])
        </div>
    </section>

    @if($comparisons->isNotEmpty())
        @php
            $maxRatio = $motors->max(fn ($m) => $m->powerToWeight());
            $featured = $comparisons->take(6);
            $rest = $comparisons->slice(6)->values();
        @endphp
        <section class="chapter chapter--tight">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <h2>{{ $a2Label }} tegen elkaar</h2>
                        <p class="lede">Telkens twee buren in de pk/kg-ranglijst: de A2-modellen die in de praktijk tegen elkaar afgewogen worden.</p>
                    </div>
                </div>
                <div class="matchups">
                    @foreach($featured as $i => $row)
                        <a class="matchup" href="{{ route('compare.show', $row['slug']) }}" data-reveal style="--d: {{ $i * 60 }}ms">
                            @foreach(['motorA' => 'a', 'motorB' => 'b'] as $key => $side)
                                <span class="matchup__side matchup__side--{{ $side }}">
                                    <span class="matchup__name">{{ $row[$key]->label() }}</span>
                                    <span class="matchup__val">{{ $fmt($row[$key]->powerToWeight()) }} pk/kg</span>
                                    <span class="meter" aria-hidden="true"><i style="--w: {{ round($row[$key]->powerToWeight() / $maxRatio, 3) }}; --d: {{ 200 + $i * 60 }}ms"></i></span>
                                </span>
                            @endforeach
                            <span class="matchup__go">Bekijk de race</span>
                        </a>
                    @endforeach
                </div>
                @if($rest->isNotEmpty())
                    <details class="report-disclosure">
                        <summary>Toon alle {{ $comparisons->count() }} vergelijkingen</summary>
                        <ul class="matchup-list">
                            @foreach($rest as $row)
                                <li><a href="{{ route('compare.show', $row['slug']) }}">{{ $row['motorA']->label() }} <span>vs</span> {{ $row['motorB']->label() }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        </section>
    @endif

    @include('partials.faq', ['faq' => $faq, 'heading' => 'Over de ' . $a2Label])

    @if($otherSegments->isNotEmpty())
        <section class="chapter chapter--tight seg-cards-wrap">
            <div class="wrap">
                <h2>Andere A2-segmenten</h2>
                <div class="seg-cards">
                    @foreach($otherSegments as $key => $count)
                        <a class="seg-card" href="{{ route('a2-motoren.segment', $key) }}" data-reveal style="--d: {{ $loop->index * 60 }}ms">
                            <span class="seg-card__count">{{ $count }}</span>
                            <h3>A2 {{ \App\Http\Controllers\SegmentController::BUYING_LABEL[$key] }}</h3>
                            <p>{{ \App\Http\Controllers\SegmentController::DESCRIPTIONS[$key] ?? '' }}</p>
                            <span class="seg-card__go">Bekijk de {{ $count }} modellen</span>
                        </a>
                    @endforeach
                </div>
                <p class="seg-hero__intro" style="margin-top:1.4rem">
                    Ook zwaardere {{ Str::lower($label) }}-modellen bekijken? Zie <a class="accent" href="{{ route('segments.show', $categorie) }}">alle {{ $segment->count() }} {{ Str::lower($label) }}-modellen</a> of de <a class="accent" href="{{ route('a2-motoren') }}">volledige A2-lijst</a>.
                </p>
            </div>
        </section>
    @endif

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Welke {{ $a2Label }} past bij jou?</h2>
            <p class="lede">De wizard zet de A2-motoren neer die het beste passen bij hoe jij rijdt.</p>
            <a class="btn btn--primary" href="{{ route('wizard.index', ['ervaring' => 'beginner']) }}">Doe de wizard</a>
        </div>
    </section>
@endsection
