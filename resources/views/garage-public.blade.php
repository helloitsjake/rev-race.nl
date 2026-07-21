@extends('layouts.app')

@section('title', "Garage van {$owner->name} - RevRace")
@section('description', "Bekijk de motorgarage van {$owner->name} op RevRace.")

@section('content')
    <header>
        <span class="eyebrow">Garage</span>
        <h1 class="page-title">Garage van {{ $owner->name }}</h1>
        <p class="page-sub">Gedeeld via RevRace, de motorsimulator die helpt bij het vinden van je volgende motor.</p>
    </header>

    <section class="section">
        <div class="garage-list">
            @forelse($garage as $entry)
                <article class="card">
                    <div class="eyebrow">{{ $entry->motor->brand }} &middot; {{ $entry->motor->year }}</div>
                    <h2 class="card-title">{{ $entry->nickname ?: $entry->motor->model }}</h2>
                    <div class="spec-row"><span class="spec-label">Vermogen</span><span class="spec-value">{{ $entry->motor->power_hp }} pk</span></div>
                    <div class="spec-row"><span class="spec-label">Koppel</span><span class="spec-value">{{ $entry->motor->torque_nm }} Nm</span></div>
                    <div class="spec-row"><span class="spec-label">Gewicht</span><span class="spec-value">{{ $entry->motor->weight_kg }} kg</span></div>
                </article>
            @empty
                <div class="panel">
                    <h2 class="card-title">Deze garage is nog leeg</h2>
                </div>
            @endforelse
        </div>
    </section>

    <section class="section" style="text-align:center">
        <p class="section-sub">Wil je zelf ontdekken welke motor bij jou past?</p>
        <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
    </section>
@endsection
