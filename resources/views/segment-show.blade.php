@extends('layouts.app')

@section('title', 'Beste ' . ($buyingLabel ?? Str::lower($label)) . ': alle modellen op een rij - RevRace')
@section('description', $description ?? ($label . ' motoren vergelijken op vermogen, gewicht en simulatieresultaten.'))

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Segmenten', 'item' => route('segments.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $label],
    ],
]) !!}
</script>
@endpush

@push('scripts')
<script src="{{ asset('js/overzicht.js') }}?v={{ filemtime(public_path('js/overzicht.js')) }}" defer></script>
@endpush

@section('content')
    @php
        $best = $motors->first();
        $a2Count = $motors->filter(fn ($m) => $m->isA2Eligible())->count();
        $lightest = $motors->sortBy('weight_kg')->first();
        $strongest = $motors->sortByDesc('power_hp')->first();
        $fmt = fn ($n) => number_format($n, 2, ',', '.');
    @endphp
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('segments.index') }}">Segmenten</a><span class="sep">&rarr;</span>
                <span>{{ $label }}</span>
            </nav>
            <h1>Beste {{ $buyingLabel }}</h1>
            <div class="seg-hero__body">
                <div>
                    <p class="lede">{{ $description }}</p>
                    @if($buyingIntro)
                        <p class="seg-hero__intro">{!! str_replace(
                            ':wizard',
                            '<a class="accent" href="' . route('wizard.index') . '">wizard</a>',
                            $buyingIntro
                        ) !!}</p>
                    @endif
                </div>
                <dl class="seg-facts">
                    <div><dt>Modellen</dt><dd>{{ $motors->count() }}</dd></div>
                    <div><dt>Waarvan A2</dt><dd>{{ $a2Count }}</dd></div>
                    <div><dt>Sterkste</dt><dd>{{ $strongest->power_hp }} <small>pk</small></dd><dd class="seg-facts__who">{{ $strongest->brand }} {{ $strongest->model }}</dd></div>
                    <div><dt>Lichtste</dt><dd>{{ $lightest->weight_kg }} <small>kg</small></dd><dd class="seg-facts__who">{{ $lightest->brand }} {{ $lightest->model }}</dd></div>
                </dl>
            </div>
        </div>
    </header>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap">
            <div class="plot-head">
                <h2>Elke {{ $buyingLabel }} op één veld</h2>
                <p>Hoe hoger en verder naar links een stip staat, hoe meer vermogen per kilo. De schuine lijnen tonen de pk/kg-verhouding. Snelste op papier: de {{ $best->brand }} {{ $best->model }} met {{ $fmt($best->powerToWeight()) }} pk/kg.</p>
            </div>
            @include('partials.spec-plot', ['motors' => $motors, 'iso' => true, 'best' => $best])
            <div class="plot-legend">
                <span><i class="is-dot"></i>{{ $label }}</span>
                @if($a2Count)<span><i class="is-a2"></i>A2-geschikt</span>@endif
                <span><i class="is-best"></i>Beste pk/kg</span>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <h2 class="speclist__title">Alle {{ $motors->count() }} {{ Str::lower($label) }}-modellen</h2>
            @include('partials.spec-list', ['motors' => $motors, 'limit' => 12, 'a2Toggle' => $a2Count > 0])
        </div>
    </section>

    @if($comparisons->isNotEmpty())
        @php
            $maxRatio = $motors->max(fn ($m) => $m->powerToWeight());
            $featuredComparisons = $comparisons->take(6);
            $restComparisons = $comparisons->slice(6)->values();
        @endphp
        <section class="chapter chapter--tight">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <h2>Populaire {{ Str::lower($label) }}-vergelijkingen</h2>
                        <p class="lede">Telkens twee buren in de pk/kg-ranglijst: de modellen die in de praktijk tegen elkaar afgewogen worden.</p>
                    </div>
                </div>
                <div class="matchups">
                    @foreach($featuredComparisons as $i => $row)
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
                @if($restComparisons->isNotEmpty())
                    <details class="report-disclosure">
                        <summary>Toon alle {{ $comparisons->count() }} {{ Str::lower($label) }}-vergelijkingen</summary>
                        <ul class="matchup-list">
                            @foreach($restComparisons as $row)
                                <li><a href="{{ route('compare.show', $row['slug']) }}">{{ $row['motorA']->label() }} <span>vs</span> {{ $row['motorB']->label() }}</a></li>
                            @endforeach
                        </ul>
                    </details>
                @endif
            </div>
        </section>
    @endif

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Nog niet zeker of {{ Str::lower($label) }} bij je past?</h2>
            <p class="lede">De wizard kijkt naar hoe je rijdt en zet de drie motoren neer die daar het beste bij passen.</p>
            <a class="btn btn--primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
        </div>
    </section>
@endsection
