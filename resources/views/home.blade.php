@extends('layouts.app')

@section('title', 'RevRace - Welke motor past bij jou? Vergelijk en simuleer')
@section('description', 'Ontdek welke motor bij jouw rijstijl past. Vergelijk motoren op vermogen, gewicht en wegconditie met een gratis rijsimulatie.')

@section('content')
    <div class="band-dark full-bleed">
        <section class="hero">
            <div>
                <span class="eyebrow">Server-side motorsimulatie</span>
                <h1>Welke motor past nou echt <span>bij jou</span>?</h1>
                <p>Wil je toeren door de Duitse bergen of het liefst knallen op het circuit? Op RevRace vergelijk je motoren niet met natte vingers, maar met een echte rijsimulatie op basis van vermogen, gewicht en jouw manier van rijden. Zo weet je niet alleen wie er wint, maar ook welke motor daadwerkelijk bij je past.</p>
                <div class="hero-actions">
                    <a class="btn primary" href="{{ route('wizard.index') }}">Welke motor past bij mij?</a>
                    <a class="btn secondary" href="{{ route('simulation.index') }}">Start simulatie</a>
                    @guest
                        <a class="btn ghost" href="{{ route('register') }}">Maak account</a>
                    @else
                        <a class="btn ghost" href="{{ route('garage.index') }}">Mijn garage</a>
                    @endguest
                </div>
            </div>
            <aside class="hero-panel">
                <div class="spec-row"><span class="spec-label">Top pk/kg</span><span class="spec-label">Live uit cache</span></div>
                @foreach($topPowerWeight as $motor)
                    <div class="spec-row">
                        <span>{{ $motor->brand }} {{ $motor->model }}</span>
                        <span class="spec-value" style="color:var(--orange)">{{ number_format($motor->power_hp / $motor->weight_kg, 2) }}</span>
                    </div>
                @endforeach
                <div class="hero-actions">
                    <a class="btn ghost" style="width:100%" href="{{ route('simulation.index') }}">Zelf vergelijken</a>
                </div>
            </aside>
        </section>
    </div>

    <div class="stat-band">
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

    <section class="section">
        <span class="eyebrow">Zo werkt het</span>
        <h2 class="section-title">Van gokwerk naar keihard bewijs</h2>
        <p class="section-sub">Geen fabrieksfolder en geen meningen van je maat: RevRace rekent per race uit welke motor wint, op basis van vermogen, gewicht en de wegconditie van dat moment.</p>
        <div class="card-grid">
            <div class="card card-accent-a">
                <div class="card-num">01</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20a8 8 0 1 1 8 -8"/><path d="M12 12l4 -4"/><circle cx="12" cy="12" r="1"/></svg>
                <h3 class="card-title">Rekenwerk, geen giswerk</h3>
                <p class="section-sub">Vermogen, koppel, gewicht en luchtweerstand: elke race wordt uitgerekend met dezelfde cijfers die op het asfalt het verschil maken.</p>
            </div>
            <div class="card card-accent-b">
                <div class="card-num">02</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16a4 4 0 0 1 0 -8 5 5 0 0 1 9.6 -1.5A4.5 4.5 0 0 1 17 16H7z"/><path d="M9 19l-1 2M13 19l-1 2M17 19l-1 2"/></svg>
                <h3 class="card-title">Drie soorten asfalt</h3>
                <p class="section-sub">Droog, vochtig of kletsnat: de wegconditie verandert de uitslag volledig. Zo weet je ook welke motor wint als het weer tegenzit.</p>
            </div>
            <div class="card">
                <div class="card-num">03</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="12" r="2.2"/><circle cx="18" cy="6" r="2.2"/><circle cx="18" cy="18" r="2.2"/><path d="M8 11l8 -4M8 13l8 4"/></svg>
                <h3 class="card-title">Deel je gelijk</h3>
                <p class="section-sub">Race gedraaid? Stuur de uitslag door naar je maat, de dealer, of dat ene forum waar de discussie al jaren loopt.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="chart-head" style="margin-bottom:6px">
            <div>
                <span class="eyebrow">Populair</span>
                <h2 class="section-title">Meest gezochte motoren</h2>
            </div>
            <a class="btn secondary" href="{{ route('simulation.index') }}">Alle motoren</a>
        </div>
        <p class="section-sub">De modellen die andere bezoekers het vaakst tegen elkaar laten racen.</p>
        <div class="top-grid">
            @foreach($mostSearched as $motor)
                <div class="card">
                    @include('partials.motor-photo', ['motor' => $motor, 'style' => 'margin-bottom:12px'])
                    <h3 class="card-title">{{ $motor->label() }}</h3>
                    <p class="section-sub" style="margin-bottom:0">{{ $motor->power_hp }} pk · {{ $motor->weight_kg }} kg · {{ $motor->engine_type }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="band-dark full-bleed">
        <div class="full-bleed-inner" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px">
            <div>
                <span class="eyebrow" style="color:var(--orange)">Voor bedrijven</span>
                <h2 class="section-title" style="font-size:clamp(24px,3.6vw,34px)">Wil jij partner worden van RevRace?</h2>
                <p style="margin:8px 0 0;max-width:620px">We werken toe naar een groeiend platform vol gebruikers die net hun droommotor aan het uitzoeken zijn, het perfecte moment om zichtbaar te zijn. Als dealer, verzekeraar, onderhoudsbedrijf of accessoiremerk kun je aanhaken als partner.</p>
            </div>
            <a class="btn primary" href="{{ route('partners.apply') }}">Word partner</a>
        </div>
    </div>
@endsection
