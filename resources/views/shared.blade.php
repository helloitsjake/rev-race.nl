@extends('layouts.app')

@section('title', 'Gedeeld resultaat - RevRace')

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
    </section>
@endsection
