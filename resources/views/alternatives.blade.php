@extends('layouts.app')

@php
    /*
     * Alternatieven voor één model (24-11-2026), /merken/{merk}/{model}/alternatieven. Keuze en
     * waarom: App\Support\Alternatives. Opbouw en klassen zoals segment-show (matchups, spec-plot),
     * dus geen nieuwe CSS. Het signature-moment blijft de race achter elke matchup.
     */
    $name = $motor->shortLabel();
    $fmt = fn ($n) => number_format($n, 2, ',', '.');
    $first = $alternatives->first();
    $stronger = $alternatives->filter(fn ($r) => $r['hp'] > 0)->sortByDesc('hp')->first();
    $lighter = $alternatives->filter(fn ($r) => $r['kg'] < 0)->sortBy('kg')->first();
    $altMotors = $alternatives->pluck('motor');
    $maxRatio = $altMotors->push($motor)->max(fn ($m) => $m->powerToWeight());
    $list = $alternatives->map(fn ($r) => $r['motor']->shortLabel())->values();
    $listText = $list->count() > 1 ? $list->slice(0, -1)->implode(', ') . ' en ' . $list->last() : $list->first();

    $faq = collect([
        [
            'q' => 'Wat is een goed alternatief voor de ' . $name . '?',
            'a' => 'Op vermogen, gewicht en cilinderinhoud komt de ' . $first['motor']->label() . ' het dichtst bij de ' . $motor->label() . ' (' . \App\Support\Alternatives::delta($first) . '). Andere alternatieven in hetzelfde segment: ' . $list->slice(1)->implode(', ') . '.',
        ],
        $stronger ? [
            'q' => 'Welk alternatief voor de ' . $name . ' heeft meer vermogen?',
            'a' => 'De ' . $stronger['motor']->label() . ' levert ' . $stronger['motor']->power_hp . ' pk, ' . $stronger['hp'] . ' pk meer dan de ' . $motor->label() . ', bij ' . $stronger['motor']->weight_kg . ' kg.',
        ] : null,
        $lighter ? [
            'q' => 'Welk alternatief voor de ' . $name . ' is lichter?',
            'a' => 'De ' . $lighter['motor']->label() . ' weegt ' . $lighter['motor']->weight_kg . ' kg, ' . abs($lighter['kg']) . ' kg minder dan de ' . $motor->label() . ' (' . $motor->weight_kg . ' kg).',
        ] : null,
        $motor->isA2Eligible() ? [
            'q' => 'Zijn deze alternatieven ook A2-geschikt?',
            'a' => 'Ja. De ' . $motor->label() . ' is A2-geschikt, dus hier staan alleen alternatieven die ook zonder drosselkit binnen de A2-grenzen vallen.',
        ] : null,
    ])->filter()->values();
@endphp

@section('title', 'Alternatieven voor de ' . $name . ': ' . $alternatives->count() . ' vergelijkbare motoren - RevRace')
@section('description', 'Zoek je een alternatief voor de ' . $name . ' (' . $motor->power_hp . ' pk, ' . $motor->weight_kg . ' kg)? Vergelijk de ' . $listText . ' op vermogen en gewicht, en race ze tegen elkaar.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Merken', 'item' => route('brands.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $motor->brand, 'item' => route('brands.show', $brandSlug)],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $motor->model . ' ' . $motor->year, 'item' => route('brands.model', [$brandSlug, $motor->slug()])],
        ['@type' => 'ListItem', 'position' => 5, 'name' => 'Alternatieven'],
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
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.show', $brandSlug) }}">{{ $motor->brand }}</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.model', [$brandSlug, $motor->slug()]) }}">{{ $motor->model }} {{ $motor->year }}</a><span class="sep">&rarr;</span>
                <span>Alternatieven</span>
            </nav>
            <h1>Alternatieven voor de {{ $name }}</h1>
            <div class="seg-hero__body">
                <div>
                    <p class="lede">
                        De {{ $motor->label() }} levert {{ $motor->power_hp }} pk bij {{ $motor->weight_kg }} kg. Deze {{ $alternatives->count() }} {{ Str::lower($motor->categoryLabel()) }}-modellen komen daar op vermogen, gewicht en cilinderinhoud het dichtst bij.
                    </p>
                    <p class="seg-hero__intro">
                        Per model één bouwjaar en maximaal twee per merk, zodat je echt iets te kiezen hebt.
                        @if($motor->isA2Eligible())
                            Omdat de {{ $name }} A2-geschikt is, staan hier alleen A2-alternatieven.
                        @endif
                        Klik op een duel om ze op droog, vochtig en nat asfalt tegen elkaar te racen.
                    </p>
                </div>
                <dl class="seg-facts">
                    <div><dt>Vermogen</dt><dd>{{ $motor->power_hp }} <small>pk</small></dd></div>
                    <div><dt>Gewicht</dt><dd>{{ $motor->weight_kg }} <small>kg</small></dd></div>
                    <div><dt>Pk per kg</dt><dd>{{ $fmt($motor->powerToWeight()) }}</dd></div>
                    <div><dt>Segment</dt><dd>{{ $motor->categoryLabel() }}</dd></div>
                </dl>
            </div>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="kb-head">
                <div>
                    <h2>{{ $name }} tegen {{ $alternatives->count() }} alternatieven</h2>
                    <p class="lede">Gesorteerd op hoe dicht ze bij de {{ $name }} liggen. Het verschil staat onder elke naam.</p>
                </div>
            </div>
            <div class="matchups">
                @foreach($alternatives as $i => $row)
                    <a class="matchup" href="{{ route('compare.show', $row['slug']) }}" data-reveal style="--d: {{ $i * 60 }}ms">
                        <span class="matchup__side matchup__side--a">
                            <span class="matchup__name">{{ $motor->label() }}</span>
                            <span class="matchup__val">{{ $fmt($motor->powerToWeight()) }} pk/kg</span>
                            <span class="meter" aria-hidden="true"><i style="--w: {{ round($motor->powerToWeight() / $maxRatio, 3) }}; --d: {{ 200 + $i * 60 }}ms"></i></span>
                        </span>
                        <span class="matchup__side matchup__side--b">
                            <span class="matchup__name">{{ $row['motor']->label() }}</span>
                            <span class="matchup__val">{{ \App\Support\Alternatives::delta($row) }}</span>
                            <span class="meter" aria-hidden="true"><i style="--w: {{ round($row['motor']->powerToWeight() / $maxRatio, 3) }}; --d: {{ 200 + $i * 60 }}ms"></i></span>
                        </span>
                        <span class="matchup__go">Race ze tegen elkaar</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap">
            <div class="plot-head">
                <h2>De {{ $name }} en zijn alternatieven op één veld</h2>
                <p>Hoe hoger en verder naar links een stip staat, hoe meer vermogen per kilo. De schuine lijnen tonen de pk/kg-verhouding.</p>
            </div>
            @include('partials.spec-plot', ['motors' => $alternatives->pluck('motor')->push($motor), 'iso' => true, 'best' => $motor])
            <div class="plot-legend">
                <span><i class="is-best"></i>{{ $name }}</span>
                <span><i class="is-dot"></i>Alternatieven</span>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <h2 class="speclist__title">Specificaties naast elkaar</h2>
            @include('partials.spec-list', ['motors' => $alternatives->pluck('motor')->push($motor), 'limit' => 7])
        </div>
    </section>

    @include('partials.faq', ['faq' => $faq, 'heading' => 'Alternatieven voor de ' . $name])

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Staat jouw alternatief er niet tussen?</h2>
            <p class="lede">Zet de {{ $name }} in de simulator en kies zelf de tegenstander, uit alle motoren in de database.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index', ['motor_a' => $motor->id]) }}">Race de {{ $name }}</a>
        </div>
    </section>
@endsection
