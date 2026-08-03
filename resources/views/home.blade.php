@extends('layouts.app')

@section('title', 'RevRace - Welke motor past bij jou? Vergelijk en simuleer')
@section('description', 'Ontdek welke motor bij jouw rijstijl past. Vergelijk motoren op vermogen, gewicht en wegconditie met een gratis rijsimulatie.')

@section('content')
    <div class="band-dark full-bleed">
        <section class="hero">
            <div>
                <span class="eyebrow">Geen marketingpraatjes</span>
                <h1>Welke motor wint de <span>race</span>?</h1>
                <p>Vergelijk twee motoren op vermogen, gewicht en wegconditie. Onze simulator rekent de race door — in seconden, zonder fabrieksfolder.</p>
                <div class="hero-actions">
                    <a class="btn primary" href="{{ route('simulation.index') }}">Start gratis simulatie</a>
                    <a class="btn secondary" href="{{ route('how-it-works') }}">Hoe het werkt</a>
                </div>
            </div>
            <aside class="hero-panel">
                <div class="chart-head" style="margin-bottom:14px">
                    <span class="form-label" style="margin-bottom:0;color:var(--orange)">Motor A</span>
                    <span class="badge">VS</span>
                    <span class="form-label" style="margin-bottom:0;color:var(--teal)">Motor B</span>
                </div>
                @if($heroRace)
                    <div class="lane">
                        <div class="lane-head"><span>{{ $heroRace['motor_a']->label() }}</span><span>{{ number_format($heroRace['time_a_s'], 2) }}s</span></div>
                        <div class="bar-bg"><div class="bar-fill a" style="width:{{ $heroRace['width_a'] }}%"></div></div>
                    </div>
                    <div class="lane" style="margin-bottom:0">
                        <div class="lane-head"><span>{{ $heroRace['motor_b']->label() }}</span><span>{{ number_format($heroRace['time_b_s'], 2) }}s</span></div>
                        <div class="bar-bg"><div class="bar-fill b" style="width:{{ $heroRace['width_b'] }}%"></div></div>
                    </div>
                    <p class="small" style="margin-top:10px">500m op droog asfalt · echt door de simulator berekend</p>
                @endif
                <div class="hero-actions" style="margin-top:14px">
                    <a class="btn ghost" style="width:100%" href="{{ route('simulation.index') }}">Zelf vergelijken</a>
                </div>
            </aside>
        </section>
    </div>

    <div class="band-dark full-bleed">
        <div class="full-bleed-inner stat-band" style="border:0;padding:28px 0">
            <div class="metric" style="border-left:2px solid var(--orange)">
                <div class="metric-value">{{ $motors->count() }}+</div>
                <div class="metric-label">Motoren in de database</div>
            </div>
            <div class="metric" style="border-left:2px solid var(--teal)">
                <div class="metric-value">100%</div>
                <div class="metric-label">Nederlands platform</div>
            </div>
            <div class="metric" style="border-left:2px solid var(--line-2)">
                <div class="metric-value">0</div>
                <div class="metric-label">Fabrieksfolders geloofd</div>
            </div>
        </div>
    </div>

    <section class="section">
        <span class="eyebrow">Zo werkt het</span>
        <h2 class="section-title">Drie stappen naar een eerlijk antwoord</h2>
        <div class="card-grid">
            <div class="card card-accent-a">
                <div class="card-num">01</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="10" height="14" rx="1.5"/><rect x="11" y="3" width="10" height="14" rx="1.5"/></svg>
                <h3 class="card-title">Kies twee motoren</h3>
                <p class="section-sub">Zoek in onze database of laat AI 'm opzoeken.</p>
            </div>
            <div class="card card-accent-b">
                <div class="card-num">02</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16a4 4 0 0 1 0 -8 5 5 0 0 1 9.6 -1.5A4.5 4.5 0 0 1 17 16H7z"/><path d="M9 19l-1 2M13 19l-1 2M17 19l-1 2"/></svg>
                <h3 class="card-title">Stel de conditie in</h3>
                <p class="section-sub">Rechte lijn of bochten, droog of nat asfalt.</p>
            </div>
            <div class="card">
                <div class="card-num">03</div>
                <svg class="card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v18"/><path d="M5 4h9l-2 3 2 3H5"/></svg>
                <h3 class="card-title">Bekijk de race</h3>
                <p class="section-sub">En vind meteen waar je die motor kan kopen.</p>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="chart-head" style="margin-bottom:6px">
            <div>
                <span class="eyebrow">Populair</span>
                <h2 class="section-title">Deze week populair</h2>
            </div>
            <a class="btn secondary" href="{{ route('most-searched.index') }}">Bekijk alle →</a>
        </div>
        <p class="section-sub">De modellen die andere bezoekers het vaakst tegen elkaar laten racen.</p>
        <div class="top-grid top-grid-3">
            @foreach($weeklyPopular as $row)
                <div class="card">
                    <div class="card-num">#{{ $loop->iteration }}</div>
                    <h3 class="card-title">{{ $row['motor']->label() }}</h3>
                    <p class="section-sub">{{ $row['motor']->power_hp }} pk · {{ $row['motor']->weight_kg }} kg · {{ $row['motor']->engine_type }}</p>
                    <a class="btn secondary" style="width:100%" href="{{ route('simulation.index', ['motor_a' => $row['motor']->id]) }}">Simuleer →</a>
                </div>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="chart-head" style="margin-bottom:6px">
            <div>
                <span class="eyebrow">Overzicht</span>
                <h2 class="section-title">Ontdek de database op jouw manier</h2>
            </div>
        </div>
        <p class="section-sub">Liever bladeren dan simuleren? Blader per merk, per rijstijl-segment, of bekijk direct alle A2 geschikte motoren.</p>
        <div class="card-grid">
            <a class="card" href="{{ route('brands.index') }}" style="display:block">
                <h3 class="card-title">Alle merken</h3>
                <p class="section-sub" style="margin-bottom:0">Elk merk in de database met alle modellen en vergelijkingen.</p>
            </a>
            <a class="card" href="{{ route('segments.index') }}" style="display:block">
                <h3 class="card-title">Segmenten</h3>
                <p class="section-sub" style="margin-bottom:0">Naked, sport, tourer, adventure, cruiser en retro naast elkaar.</p>
            </a>
            <a class="card" href="{{ route('a2-motoren') }}" style="display:block">
                <h3 class="card-title">A2 motoren</h3>
                <p class="section-sub" style="margin-bottom:0">Alle modellen die voldoen aan de Europese A2-eisen.</p>
            </a>
        </div>
    </section>

    <div class="band-teal full-bleed">
        <div class="full-bleed-inner" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px">
            <div>
                <span class="eyebrow">Voor bedrijven</span>
                <h2 class="section-title" style="font-size:clamp(24px,3.6vw,34px)">Sta tussen de motoren die mensen écht vergelijken</h2>
                <p style="margin:8px 0 0;max-width:620px">Word zichtbaar als dealer, verzekeraar of circuit precies op het moment dat bezoekers aan het vergelijken zijn.</p>
            </div>
            <a class="btn primary" href="{{ route('partners.apply') }}">Word partner →</a>
        </div>
    </div>
@endsection
