@extends('layouts.app')

@php
    $winnerMotor = $result->winner === 'A' ? $result->motorA : $result->motorB;
    $shareText = "{$winnerMotor->label()} wint met " . number_format(abs($result->time_a_s - $result->time_b_s), 2, ',', '.') . "s verschil op RevRace!";
    $shareUrl = url()->current();
    $delta = number_format(abs($result->time_a_s - $result->time_b_s), 2, ',', '.');
    $slowest = max($result->time_a_s, $result->time_b_s);
    $distanceLabel = match ((int) $result->distance_m) { 402 => '1/4 mile', 805 => '1/2 mile', default => $result->distance_m . 'm' };
@endphp

@section('title', "{$result->motorA->label()} vs {$result->motorB->label()} - Gedeeld resultaat - RevRace")
@section('description', $shareText)

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Gedeeld resultaat</span>
            <h1>{{ $result->motorA->label() }} vs {{ $result->motorB->label() }}</h1>
            <p class="lede">Een race doorgerekend en gedeeld door een bezoeker. Iedereen met deze link ziet exact dezelfde uitslag.</p>
        </div>
    </header>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap" style="max-width:760px">
            <div class="panel">
                <div class="panel__head"><span>{{ $distanceLabel }} &middot; {{ $result->road_condition }}</span><span class="live">race voltooid</span></div>
                <div class="bike-row">
                    <div class="bike-row__name"><span>{{ $result->motorA->label() }}</span><span class="ratio">{{ number_format($result->motorA->powerToWeight(), 2, ',', '.') }} pk/kg</span></div>
                    <div class="bike-row__meta"><span>{{ $result->motorA->power_hp }} pk</span><span>{{ $result->motorA->weight_kg }} kg</span><span>{{ number_format($result->time_a_s, 2, ',', '.') }}s</span></div>
                    <div class="bar"><span style="width:{{ ($result->time_a_s / $slowest) * 100 }}%"></span></div>
                </div>
                <div class="bike-row">
                    <div class="bike-row__name"><span>{{ $result->motorB->label() }}</span><span class="ratio">{{ number_format($result->motorB->powerToWeight(), 2, ',', '.') }} pk/kg</span></div>
                    <div class="bike-row__meta"><span>{{ $result->motorB->power_hp }} pk</span><span>{{ $result->motorB->weight_kg }} kg</span><span>{{ number_format($result->time_b_s, 2, ',', '.') }}s</span></div>
                    <div class="bar"><span style="width:{{ ($result->time_b_s / $slowest) * 100 }}%"></span></div>
                </div>
                <div class="panel__foot">
                    <p>Winnaar: {{ $result->winner === 'A' ? $result->motorA->label() : $result->motorB->label() }} &middot; verschil {{ $delta }}s</p>
                    <div class="share-row">
                        <a class="btn btn--ghost" href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank" rel="noopener">WhatsApp</a>
                        <a class="btn btn--ghost" href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">X</a>
                        <a class="btn btn--ghost" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener">Facebook</a>
                    </div>
                </div>
            </div>

            @include('partials.simulation-confidence', ['motorA' => $result->motorA, 'motorB' => $result->motorB])

            <div class="spec" style="margin-top:clamp(28px,4vw,44px)">
                <div class="spec__row">
                    <div class="spec__label">Wegtype</div>
                    <div class="spec__value">{{ $result->road_type === 'straight' ? 'Rechte lijn' : 'Kronkelweg' }}</div>
                    <div class="spec__note">Het gekozen wegtype voor deze race.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Conditie</div>
                    <div class="spec__value">{{ ucfirst($result->road_condition) }}</div>
                    <div class="spec__note">Asfaltconditie tijdens deze race, van invloed op grip en remweg.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Afstand</div>
                    <div class="spec__value">{{ $distanceLabel }}</div>
                    <div class="spec__note">De afgelegde afstand in deze race.</div>
                </div>
            </div>

            <div class="hero__ctas" style="margin-top:clamp(28px,4vw,44px)">
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Nieuwe simulatie</a>
            </div>
        </div>
    </section>
@endsection
