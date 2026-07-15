@extends('layouts.app')

@section('title', 'RevRace - Vergelijk motoren op snelheid, koppel en grip')
@section('description', 'Gratis motorsimulatie voor sprint en kronkelweg. Vergelijk twee motoren op droog, vochtig en nat asfalt.')

@section('content')
    <section class="hero">
        <div>
            <span class="eyebrow">Server-side motorsimulatie</span>
            <h1>Vergelijk elke motor op <span>grip, koppel en gewicht</span>.</h1>
            <p>RevRace berekent races op de server met vermogen, koppel, gewicht, luchtweerstand, wegconditie en rijdersgewicht. Resultaten zijn deelbaar en tellen mee voor je rolling 24-uurslimiet.</p>
            <div class="hero-actions">
                <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
                @guest
                    <a class="btn secondary" href="{{ route('register') }}">Maak account</a>
                @else
                    <a class="btn secondary" href="{{ route('garage.index') }}">Mijn garage</a>
                @endguest
            </div>
            <div class="metric-grid">
                <div class="metric">
                    <div class="metric-value">{{ $motors->count() }}</div>
                    <div class="metric-label">Motoren in cache</div>
                </div>
                <div class="metric">
                    <div class="metric-value">10</div>
                    <div class="metric-label">Simulaties per 24 uur</div>
                </div>
                <div class="metric">
                    <div class="metric-value">3</div>
                    <div class="metric-label">Wegcondities</div>
                </div>
                <div class="metric">
                    <div class="metric-value">2</div>
                    <div class="metric-label">Wegtypen</div>
                </div>
            </div>
        </div>
        <aside class="hero-panel">
            <span class="eyebrow">Top pk/kg</span>
            @foreach($topPowerWeight as $motor)
                <div class="spec-row">
                    <span>{{ $motor->brand }} {{ $motor->model }}</span>
                    <span class="spec-value">{{ number_format($motor->power_hp / $motor->weight_kg, 2) }}</span>
                </div>
            @endforeach
            <div class="hero-actions">
                <a class="btn ghost" href="{{ route('simulation.index') }}">Zelf vergelijken</a>
            </div>
        </aside>
    </section>

    <section class="section">
        <span class="eyebrow">Productie-MVP</span>
        <h2 class="section-title">Wat staat nu echt liveklaar?</h2>
        <p class="section-sub">Geen demo-login meer: de kernfuncties lopen via Laravel, sessies, database en server-side validatie.</p>
        <div class="card-grid">
            <div class="card">
                <h3 class="card-title">Echte accounts</h3>
                <p class="section-sub">Registreren, inloggen, uitloggen en profiel opslaan via Laravel sessions en hashing.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Serverlimiet</h3>
                <p class="section-sub">Gasten en gratis accounts krijgen 10 simulaties per rolling 24 uur, gemeten in de database.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Deelbare links</h3>
                <p class="section-sub">Elke simulatie wordt opgeslagen en krijgt een echte `/s/{code}` URL.</p>
            </div>
        </div>
    </section>
@endsection
