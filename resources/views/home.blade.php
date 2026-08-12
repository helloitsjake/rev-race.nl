@extends('layouts.app')

@section('title', 'RevRace - Welke motor past bij jou? Vergelijk en simuleer')
@section('description', 'Ontdek welke motor bij jouw rijstijl past. Vergelijk motoren op vermogen, gewicht en wegconditie met een gratis rijsimulatie.')

@push('head')
    <meta name="ahrefs-site-verification" content="8f2470a126e81d20eb49805c8cf579484fd2b61a109fa70e1395870349d60250">
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap hero">
            <div>
                <p class="eyebrow">Geen marketingpraatjes</p>
                <h1>Welke motor wint de race?</h1>
                <p class="lede">Vergelijk twee motoren op vermogen, gewicht en wegconditie. Onze simulator rekent de race door — in seconden, zonder fabrieksfolder.</p>
                <div class="hero__ctas">
                    <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start gratis simulatie</a>
                    <a class="btn btn--ghost" href="{{ route('how-it-works') }}">Hoe het werkt</a>
                </div>
            </div>
            <div class="panel">
                @if($heroRace)
                    <div class="panel__head"><span>Simulatie #hero</span><span class="live">live berekend</span></div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $heroRace['motor_a']->label() }}</span></div>
                        <div class="bike-row__meta"><span>{{ number_format($heroRace['time_a_s'], 2) }}s</span><span>500m · droog asfalt</span></div>
                        <div class="bar"><span style="width:{{ $heroRace['width_a'] }}%"></span></div>
                    </div>
                    <div class="bike-row">
                        <div class="bike-row__name"><span>{{ $heroRace['motor_b']->label() }}</span></div>
                        <div class="bike-row__meta"><span>{{ number_format($heroRace['time_b_s'], 2) }}s</span><span>500m · droog asfalt</span></div>
                        <div class="bar"><span style="width:{{ $heroRace['width_b'] }}%"></span></div>
                    </div>
                @else
                    <div class="panel__head"><span>Simulatie</span></div>
                    <p style="color:var(--paper-60)">Kies twee motoren en bekijk de race.</p>
                @endif
                <div class="panel__foot"><a class="btn btn--ghost" href="{{ route('simulation.index') }}">Zelf vergelijken</a></div>
            </div>
        </div>
    </header>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap stats" style="border-top:none;margin-top:0;padding-top:0">
            <div class="stat">
                <div class="stat__value">{{ $motors->count() }}+</div>
                <div class="stat__label">Motoren in de database</div>
            </div>
            <div class="stat">
                <div class="stat__value">100%</div>
                <div class="stat__label">Nederlands platform</div>
            </div>
            <div class="stat">
                <div class="stat__value">0</div>
                <div class="stat__label">Fabrieksfolders geloofd</div>
            </div>
        </div>
    </section>

    <section class="chapter">
        <div class="wrap">
            <p class="eyebrow">Zo werkt het</p>
            <h2>Drie stappen naar een eerlijk antwoord</h2>
            <div class="match-grid" style="margin-top:clamp(28px,4vw,44px)">
                <div class="match-card">
                    <p class="match-card__badge">01</p>
                    <h3>Kies twee motoren</h3>
                    <p style="color:var(--ink-60)">Zoek in onze database of laat AI 'm opzoeken.</p>
                </div>
                <div class="match-card">
                    <p class="match-card__badge">02</p>
                    <h3>Stel de conditie in</h3>
                    <p style="color:var(--ink-60)">Rechte lijn of bochten, droog of nat asfalt.</p>
                </div>
                <div class="match-card">
                    <p class="match-card__badge">03</p>
                    <h3>Bekijk de race</h3>
                    <p style="color:var(--ink-60)">En vind meteen waar je die motor kan kopen.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark">
        <div class="wrap">
            <div class="kb-head">
                <div>
                    <p class="eyebrow">Populair</p>
                    <h2>Deze week populair</h2>
                </div>
                <a class="btn btn--ghost" href="{{ route('most-searched.index') }}">Bekijk alle →</a>
            </div>
            <p class="lede" style="margin-bottom:2em">De modellen die andere bezoekers het vaakst tegen elkaar laten racen.</p>
            <div class="rank-list">
                @foreach($weeklyPopular as $row)
                    <div class="rank-row">
                        <div class="rank-row__num">#{{ $loop->iteration }}</div>
                        <div>
                            <div class="rank-row__name">{{ $row['motor']->label() }}</div>
                            <div class="rank-row__meta">{{ $row['motor']->power_hp }} pk · {{ $row['motor']->weight_kg }} kg · {{ $row['motor']->engine_type }}</div>
                        </div>
                        <div><span class="rank-row__count">{{ $row['uses'] }}× gesimuleerd</span><span class="rank-row__ratio">{{ number_format($row['motor']->powerToWeight(), 2) }} pk/kg</span></div>
                        <a class="btn btn--ghost" href="{{ route('simulation.index', ['motor_a' => $row['motor']->id]) }}">Simuleer →</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="chapter">
        <div class="wrap">
            <p class="eyebrow">Overzicht</p>
            <h2>Ontdek de database op jouw manier</h2>
            <p class="lede" style="margin-bottom:2em">Liever bladeren dan simuleren? Blader per merk, per rijstijl-segment, of bekijk direct alle A2-geschikte motoren.</p>
            <div class="kb-grid">
                <a class="kb-card" href="{{ route('brands.index') }}">
                    <h3>Alle merken</h3>
                    <p>Elk merk in de database met alle modellen en vergelijkingen.</p>
                </a>
                <a class="kb-card" href="{{ route('segments.index') }}">
                    <h3>Segmenten</h3>
                    <p>Naked, sport, tourer, adventure, cruiser en retro naast elkaar.</p>
                </a>
                <a class="kb-card" href="{{ route('a2-motoren') }}">
                    <h3>A2-motoren</h3>
                    <p>Alle modellen die voldoen aan de Europese A2-eisen.</p>
                </a>
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:2rem">
            <div>
                <p class="eyebrow">Voor bedrijven</p>
                <h2 style="font-size:clamp(1.75rem,3vw,2.5rem)">Sta tussen de motoren die mensen écht vergelijken</h2>
                <p class="lede" style="margin-top:0.6em;max-width:52ch">Word zichtbaar als dealer, verzekeraar of circuit precies op het moment dat bezoekers aan het vergelijken zijn.</p>
            </div>
            <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner →</a>
        </div>
    </section>
@endsection
