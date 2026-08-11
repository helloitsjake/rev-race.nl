@extends('layouts.app')

@section('title', "Garage van {$owner->name} - RevRace")
@section('description', "Bekijk de motorgarage van {$owner->name} op RevRace.")

@section('content')
    <header class="chapter">
        <div class="wrap">
            <p class="eyebrow">Gedeelde garage</p>
            <h1>De garage van {{ $owner->name }}</h1>
            <p class="lede">Gedeeld via RevRace, de motorsimulator die helpt bij het vinden van je volgende motor.</p>

            <div class="garage-grid">
                @forelse($garage as $entry)
                    <article class="garage-card">
                        <p class="garage-card__meta">{{ $entry->motor->brand }} &middot; {{ $entry->motor->year }}</p>
                        <h3>{{ $entry->nickname ?: $entry->motor->model }}</h3>
                        <div class="garage-card__specs">
                            <div class="row"><span>Vermogen</span><span>{{ $entry->motor->power_hp }} pk</span></div>
                            <div class="row"><span>Koppel</span><span>{{ $entry->motor->torque_nm }} Nm</span></div>
                            <div class="row"><span>Gewicht</span><span>{{ $entry->motor->weight_kg }} kg</span></div>
                        </div>
                    </article>
                @empty
                    <div class="panel" style="grid-column:1 / -1">
                        <div class="panel__head"><span>Lege garage</span></div>
                        <h3>Deze garage is nog leeg</h3>
                    </div>
                @endforelse
            </div>
        </div>
    </header>

    <section class="chapter chapter--dark" style="text-align:center">
        <div class="wrap">
            <p class="eyebrow">Zelf ontdekken</p>
            <h2>Wil je zelf ontdekken welke motor bij jou past?</h2>
            <p class="lede" style="margin-inline:auto">Maak een gratis account voor je eigen garage, rijdersprofiel en simulaties &mdash; of start direct met een eerste vergelijking.</p>
            <div class="hero__ctas" style="justify-content:center; margin-top:2rem;">
                <a class="btn btn--primary" href="{{ route('register') }}">Account aanmaken</a>
                <a class="btn btn--ghost" href="{{ route('simulation.index') }}">Start een simulatie</a>
            </div>
        </div>
    </section>
@endsection
