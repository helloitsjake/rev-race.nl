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
@endphp

@section('title', "{$motorA->label()} vs {$motorB->label()} - wie is sneller? - RevRace")
@section('description', "Vergelijk de {$motorA->label()} met de {$motorB->label()}: vermogen, gewicht en simulatieresultaten op droog, vochtig en nat asfalt.")
@section('ogImage', asset('og-image-vergelijk.png'))

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
                    <div class="spec__value">{{ number_format($motorA->powerToWeight(), 2) }}</div>
                    <div class="spec__value">{{ number_format($motorB->powerToWeight(), 2) }}</div>
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
                            <span class="mu">tractie {{ number_format($mu['t'], 2) }} &middot; rem {{ number_format($mu['b'], 2) }} &middot; bocht {{ number_format($mu['c'], 2) }}</span>
                        @endif
                    </div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $motorA->label() }}</span><span class="ratio">{{ number_format($motorA->powerToWeight(), 2) }} pk/kg</span></div>
                        <div class="bike-row__meta"><span>{{ $motorA->power_hp }} pk</span><span>{{ $motorA->weight_kg }} kg</span><span>{{ number_format($result['time_a_s'], 3) }}s</span></div>
                        <div class="bar"><span style="width:{{ $widthA }}%"></span></div>
                    </div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $motorB->label() }}</span><span class="ratio">{{ number_format($motorB->powerToWeight(), 2) }} pk/kg</span></div>
                        <div class="bike-row__meta"><span>{{ $motorB->power_hp }} pk</span><span>{{ $motorB->weight_kg }} kg</span><span>{{ number_format($result['time_b_s'], 3) }}s</span></div>
                        <div class="bar"><span style="width:{{ $widthB }}%"></span></div>
                    </div>
                    @if($key !== 'dry' && $winner->isNot($dryWinner))
                        <div class="panel__foot">
                            <p style="color: var(--paper-60); font-size: 0.9375rem">Bij deze wegconditie wint de {{ $winner->label() }} in plaats van de {{ $dryWinner->label() }}, die op droog asfalt voorlag. Verschil: {{ number_format($result['delta_s'], 3) }}s.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

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
