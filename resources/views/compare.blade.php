@extends('layouts.app')

@php
    $dryResult = $results['dry']['result'];
    $dryWinner = $dryResult['winner'] === 'A' ? $motorA : $motorB;
    $rainWinner = $results['rain']['result']['winner'] === 'A' ? $motorA : $motorB;
    $powerDiff = abs($motorA->power_hp - $motorB->power_hp);
    $weightDiff = abs($motorA->weight_kg - $motorB->weight_kg);
    $strongerMotor = $motorA->power_hp >= $motorB->power_hp ? $motorA : $motorB;
    $lighterMotor = $motorA->weight_kg <= $motorB->weight_kg ? $motorA : $motorB;
@endphp

@section('title', "{$motorA->label()} vs {$motorB->label()} - wie is sneller? - RevRace")
@section('description', "Vergelijk de {$motorA->label()} met de {$motorB->label()}: vermogen, gewicht en simulatieresultaten op droog, vochtig en nat asfalt.")

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
    <nav class="small" aria-label="Broodkruimel">
        <a href="{{ route('home') }}">Home</a> &rarr;
        <a href="{{ route('simulation.index') }}">Simulatie</a> &rarr;
        {{ $motorA->label() }} vs {{ $motorB->label() }}
    </nav>

    <header style="margin-top:12px">
        <span class="eyebrow">Vergelijking</span>
        <h1 class="page-title">{{ $motorA->label() }} vs {{ $motorB->label() }}</h1>
        <p class="page-sub">Op droog asfalt wint de {{ $dryWinner->label() }}. Bekijk hieronder hoe dat verandert per wegconditie.</p>
    </header>

    <div class="top-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:16px">
        <div>
            @include('partials.motor-photo', ['motor' => $motorA])
        </div>
        <div>
            @include('partials.motor-photo', ['motor' => $motorB])
        </div>
    </div>

    <section class="panel">
        <p>
            Wie is er nou sneller, de {{ $motorA->label() }} of de {{ $motorB->label() }}? Op papier heeft de
            {{ $strongerMotor->label() }} met {{ $strongerMotor->power_hp }} pk het meeste vermogen
            @if($powerDiff > 0)
                ({{ $powerDiff }} pk meer dan de {{ $strongerMotor->is($motorA) ? $motorB->label() : $motorA->label() }}),
            @else
                (evenveel als de andere),
            @endif
            maar de {{ $lighterMotor->label() }} is met {{ $lighterMotor->weight_kg }} kg
            @if($weightDiff > 0)
                {{ $weightDiff }} kg lichter.
            @else
                even zwaar.
            @endif
            Op de rechte lijn van 500 meter, op droog asfalt, is dat genoeg voor de {{ $dryWinner->label() }} om als
            eerste over de streep te komen.
            @if($dryWinner->isNot($rainWinner))
                Op kletsnat asfalt draait de verhouding om: daar wint de {{ $rainWinner->label() }}, omdat grip en
                remvermogen dan zwaarder wegen dan pure pk's.
            @else
                Ook op nat asfalt blijft de {{ $rainWinner->label() }} de sterkste, het voordeel wordt alleen kleiner.
            @endif
            Twijfel je zelf tussen deze twee? Vul je eigen rijdersgewicht en wegconditie in en race ze tegen elkaar.
        </p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('simulation.index') }}">Zelf simuleren</a>
        </div>
    </section>

    <section class="section">
        <span class="eyebrow">Specificaties</span>
        <h2 class="section-title">Naast elkaar</h2>
        <div class="panel">
            <div class="compare-row"><span class="spec-label">Motor</span><span class="spec-value" style="color:var(--orange)">{{ $motorA->label() }}</span><span class="spec-value" style="color:var(--teal)">{{ $motorB->label() }}</span></div>
            <div class="compare-row"><span class="spec-label">Vermogen</span><span class="spec-value">{{ $motorA->power_hp }} pk</span><span class="spec-value">{{ $motorB->power_hp }} pk</span></div>
            <div class="compare-row"><span class="spec-label">Koppel</span><span class="spec-value">{{ $motorA->torque_nm }} Nm</span><span class="spec-value">{{ $motorB->torque_nm }} Nm</span></div>
            <div class="compare-row"><span class="spec-label">Gewicht</span><span class="spec-value">{{ $motorA->weight_kg }} kg</span><span class="spec-value">{{ $motorB->weight_kg }} kg</span></div>
            <div class="compare-row"><span class="spec-label">Pk per kg</span><span class="spec-value">{{ number_format($motorA->powerToWeight(), 2) }}</span><span class="spec-value">{{ number_format($motorB->powerToWeight(), 2) }}</span></div>
            <div class="compare-row"><span class="spec-label">Motortype</span><span class="spec-value">{{ $motorA->engine_type }}</span><span class="spec-value">{{ $motorB->engine_type }}</span></div>
            <div class="compare-row"><span class="spec-label">Cilinderinhoud</span><span class="spec-value">{{ $motorA->displacement_cc }} cc</span><span class="spec-value">{{ $motorB->displacement_cc }} cc</span></div>
            @if($motorA->top_speed_kmh && $motorB->top_speed_kmh)
                <div class="compare-row"><span class="spec-label">Topsnelheid</span><span class="spec-value">{{ $motorA->top_speed_kmh }} km/h</span><span class="spec-value">{{ $motorB->top_speed_kmh }} km/h</span></div>
            @endif
        </div>
    </section>

    <section class="section">
        <span class="eyebrow">Simulatie</span>
        <h2 class="section-title">500 meter, drie wegcondities</h2>
        <div class="card-grid">
            @foreach($results as $key => $data)
                @php $winner = $data['result']['winner'] === 'A' ? $motorA : $motorB; @endphp
                <div class="card">
                    <div class="card-num">{{ $data['label'] }}</div>
                    <h3 class="card-title">{{ $winner->label() }} wint</h3>
                    <div class="spec-row"><span class="spec-label">{{ $motorA->brand }}</span><span class="spec-value">{{ number_format($data['result']['time_a_s'], 3) }}s</span></div>
                    <div class="spec-row"><span class="spec-label">{{ $motorB->brand }}</span><span class="spec-value">{{ number_format($data['result']['time_b_s'], 3) }}s</span></div>
                    <p class="section-sub" style="margin-bottom:0">Verschil: {{ number_format($data['result']['delta_s'], 3) }}s</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
