@extends('layouts.app')

@php
    $winnerMotor = $result->winner === 'A' ? $result->motorA : $result->motorB;
    $shareText = "{$winnerMotor->label()} wint met " . number_format(abs($result->time_a_s - $result->time_b_s), 2) . "s verschil op RevRace!";
    $shareUrl = url()->current();
@endphp

@section('title', "{$result->motorA->label()} vs {$result->motorB->label()} - Gedeeld resultaat - RevRace")
@section('description', $shareText)

@section('content')
    <header>
        <h1 class="page-title">Gedeeld resultaat</h1>
        <p class="page-sub">{{ $result->motorA->label() }} vs {{ $result->motorB->label() }}</p>
    </header>

    <section class="panel">
        <div class="sim-grid">
            <div>
                <span class="eyebrow">Motor A</span>
                <h2 class="card-title">{{ $result->motorA->label() }}</h2>
                <p class="page-sub">{{ number_format($result->time_a_s, 3) }}s</p>
            </div>
            <div>
                <span class="eyebrow">Motor B</span>
                <h2 class="card-title">{{ $result->motorB->label() }}</h2>
                <p class="page-sub">{{ number_format($result->time_b_s, 3) }}s</p>
            </div>
        </div>
        <div class="notice">
            Winnaar: {{ $result->winner === 'A' ? $result->motorA->label() : $result->motorB->label() }}
            · verschil {{ number_format(abs($result->time_a_s - $result->time_b_s), 3) }}s
        </div>
        <div class="spec-row"><span class="spec-label">Wegtype</span><span class="spec-value">{{ $result->road_type === 'straight' ? 'Rechte lijn' : 'Kronkelweg' }}</span></div>
        <div class="spec-row"><span class="spec-label">Conditie</span><span class="spec-value">{{ $result->road_condition }}</span></div>
        <div class="spec-row"><span class="spec-label">Afstand</span><span class="spec-value">{{ $result->distance_m }}m</span></div>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('simulation.index') }}">Nieuwe simulatie</a>
        </div>

        <div class="hero-actions" style="margin-top:10px">
            <a class="btn secondary" target="_blank" rel="noopener" href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}">WhatsApp</a>
            <a class="btn secondary" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text={{ urlencode($shareText) }}&url={{ urlencode($shareUrl) }}">X</a>
            <a class="btn secondary" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}">Facebook</a>
        </div>
    </section>
@endsection
