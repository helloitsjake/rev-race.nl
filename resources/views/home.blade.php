@extends('layouts.app')

@section('title', 'RevRace - Welke motor past het beste bij jouw rijstijl?')
@section('description', 'Ontdek welke motor bij jouw rijstijl past. Advies op basis van vermogen, gewicht en rijervaring, plus een gratis rijsimulatie om motoren te vergelijken.')

@push('head')
    <meta name="ahrefs-site-verification" content="8f2470a126e81d20eb49805c8cf579484fd2b61a109fa70e1395870349d60250">
@endpush

@php
    $arrow = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
    $start = $styleAdvice['beginner']['bochten'] ?? [];
@endphp

@section('content')
    {{-- T0: rijstijlkiezer met startopstelling (signature-moment: lights out) --}}
    <div class="hero2" data-style-picker>
        <script type="application/json">@json($styleAdvice)</script>
        <div class="wrap">
            <p class="turn"><b>T0</b> Startopstelling</p>
            <h1>Welke motor past het beste bij <span>jouw rijstijl?</span></h1>
            <div class="hero2__row">
                <div class="sentence">
                    <span>Ik rij het liefst</span>
                    <div class="filter" role="group" aria-label="Rijstijl" data-key="voorkeur">
                        <div class="filter__base">
                            <button type="button" data-value="bochten" aria-pressed="true">door bochten</button>
                            <button type="button" data-value="snelheid" aria-pressed="false">op het rechte stuk</button>
                            <button type="button" data-value="relax" aria-pressed="false">voor het plezier</button>
                        </div>
                        <div class="filter__pill" aria-hidden="true"></div>
                    </div>
                    <span>met</span>
                    <div class="filter" role="group" aria-label="Rijbewijs" data-key="ervaring">
                        <div class="filter__base">
                            <button type="button" data-value="beginner" aria-pressed="true">een A2-rijbewijs</button>
                            <button type="button" data-value="ervaren" aria-pressed="false">een vol rijbewijs</button>
                        </div>
                        <div class="filter__pill" aria-hidden="true"></div>
                    </div>
                </div>
                <p class="hero2__aside">Kies hoe je rijdt. RevRace zet de drie motoren neer die daar het beste bij passen, op basis van vermogen, gewicht en wat dat op de weg betekent.</p>
            </div>
        </div>

        <div class="grid-panel" aria-live="polite">
            <div class="wrap">
                <div class="grid-head">
                    <div class="lights" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                    <p class="status">Startopstelling uit <b>{{ $motors->count() }}</b> motoren</p>
                </div>
                <div class="startgrid" data-startgrid>
                    @foreach($start as $i => $m)
                        <div class="slot" style="--row:{{ $i }}">
                            <div class="slot__pos">P{{ $i + 1 }}</div>
                            <div class="slot__car">
                                <div class="slot__name"><a href="{{ $m['url'] }}">{{ $m['label'] }}</a>@if($m['a2'])<span class="a2">A2</span>@endif</div>
                                <div class="slot__specs"><span><b>{{ $m['hp'] }}</b> pk</span><span><b>{{ $m['kg'] }}</b> kg</span><span><b>{{ number_format($m['hp'] / max($m['kg'], 1), 2, ',', '') }}</b> pk/kg</span></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="grid-foot">
                    <div class="sectors">
                        <span><em>S1</em><b data-count="{{ $motors->count() }}">{{ $motors->count() }}</b> motoren</span>
                        <span><em>S2</em><b data-count="{{ $a2Count }}">{{ $a2Count }}</b> A2-modellen</span>
                        <span><em>S3</em><b data-count="{{ $segmentCount }}">{{ $segmentCount }}</b> segmenten</span>
                    </div>
                    <a class="btn btn--primary" data-advice-link href="{{ route('wizard.index', ['ervaring' => 'beginner', 'voorkeur' => 'bochten']) }}#advies">Volledig advies op maat {!! $arrow !!}</a>
                </div>
            </div>
        </div>
    </div>

    {{-- T1: routes --}}
    <section class="chapter">
        <div class="wrap">
            <div class="sec-head" data-reveal>
                <div><p class="turn"><b>T1</b> Jouw route</p><h2>Vijf manieren om bij de juiste motor uit te komen</h2></div>
                <p>Weet je al welke twee je twijfelt, laat ze dan tegen elkaar racen. Twijfel je nog over alles, begin dan bij je rijstijl.</p>
            </div>
            <div class="routes">
                <a class="route" href="{{ route('wizard.index') }}" data-reveal><span class="route__no">01</span><h3>Advies op rijstijl</h3><p>Drie vragen over je ervaring, je rijstijl en waar je rijdt.</p><span class="route__go">{!! $arrow !!}</span></a>
                <a class="route" href="{{ route('simulation.index') }}" data-reveal style="--d:60ms"><span class="route__no">02</span><h3>Twee motoren laten racen</h3>
                    <p>@if($heroRace){{ $heroRace['motor_a']->label() }} tegen {{ $heroRace['motor_b']->label() }}: {{ number_format($heroRace['time_a_s'], 2, ',', '') }} s tegen {{ number_format($heroRace['time_b_s'], 2, ',', '') }} s over 500 meter.@else 500 meter, droog of nat, recht of bochtig. De simulator rekent het door.@endif</p><span class="route__go">{!! $arrow !!}</span></a>
                <a class="route" href="{{ route('a2-motoren') }}" data-reveal style="--d:120ms"><span class="route__no">03</span><h3>A2-motoren</h3><p>Alle {{ $a2Count }} modellen onder 35 kW en 0,2 kW per kilo, per categorie.</p><span class="route__go">{!! $arrow !!}</span></a>
                <a class="route" href="{{ route('segments.index') }}" data-reveal style="--d:180ms"><span class="route__no">04</span><h3>Segmenten</h3><p>Naked, sport, tourer, adventure, cruiser en retro naast elkaar.</p><span class="route__go">{!! $arrow !!}</span></a>
                <a class="route" href="{{ route('brands.index') }}" data-reveal style="--d:240ms"><span class="route__no">05</span><h3>Merken</h3><p>Elk merk met alle modellen en de vergelijkingen die het vaakst gemaakt worden.</p><span class="route__go">{!! $arrow !!}</span></a>
            </div>
        </div>
    </section>

    {{-- T2: live timing (alleen als er genoeg data is, zie MIN_WEEKLY_POPULAR_USES) --}}
    @if($weeklyPopular->isNotEmpty())
        @php($bestRatio = $weeklyPopular->max(fn ($row) => $row['motor']->powerToWeight()))
        <section class="chapter on-dark">
            <div class="wrap">
                <div class="sec-head" data-reveal>
                    <div><p class="turn"><b>T2</b> Live timing</p><h2>Waar anderen deze week over twijfelen</h2></div>
                    <p>De motoren die het vaakst in de simulator staan, met de cijfers die ertoe doen. Paars is de beste vermogen-gewichtverhouding in de lijst.</p>
                </div>
                <div class="timing">
                    <div class="t-row t-head"><span>POS</span><span>MOTOR</span><span class="t-num t-hide">PK</span><span class="t-num t-hide">KG</span><span class="t-num">PK/KG</span><span class="t-num t-hide">SIMULATIES</span></div>
                    @foreach($weeklyPopular as $row)
                        @php($ratio = $row['motor']->powerToWeight())
                        <a class="t-row" href="{{ route('simulation.index', ['motor_a' => $row['motor']->id]) }}" style="--d:{{ $loop->index * 120 }}ms">
                            <span class="t-pos">{{ $loop->iteration }}</span>
                            <span class="t-name">{{ $row['motor']->label() }}<small>{{ $row['motor']->categoryLabel() }}</small></span>
                            <span class="t-num t-hide">{{ $row['motor']->power_hp }}</span>
                            <span class="t-num t-hide">{{ $row['motor']->weight_kg }}</span>
                            <span class="t-num @if($ratio === $bestRatio) t-best @endif">{{ number_format($ratio, 2, ',', '') }}</span>
                            <span class="t-num t-hide">{{ $row['uses'] }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="timing-legend"><span class="live">Laatste 7 dagen</span><span><i></i>Beste pk/kg</span><a href="{{ route('most-searched.index') }}">Volledige ranglijst</a></div>
            </div>
        </section>
    @endif

    {{-- T3: kennisbank --}}
    @if($articles->isNotEmpty())
        @php($feature = $articles->first())
        <section class="chapter">
            <div class="wrap">
                <div class="sec-head" data-reveal>
                    <div><p class="turn"><b>T3</b> Kennisbank</p><h2>Wat je wilt weten voordat je tekent</h2></div>
                    <p>Geschreven voor wie net begint en voor wie al jaren rijdt. Met bronnen, zonder verkooppraat.</p>
                </div>
                <div class="kb">
                    <a class="feature" href="{{ route('kennis.show', $feature) }}" data-reveal>
                        <svg class="feature__line" viewBox="0 0 400 260" fill="none" aria-hidden="true"><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#141518" stroke-width="2"/><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#E4431E" stroke-width="2" stroke-dasharray="4 10"/><circle cx="250" cy="50" r="7" fill="#E4431E"/></svg>
                        <span class="kicker">{{ $feature->category }}</span>
                        <div><h3>{{ $feature->title }}</h3>@if($feature->excerpt)<p>{{ $feature->excerpt }}</p>@endif</div>
                    </a>
                    <div class="kb-list">
                        @foreach($articles->slice(1) as $article)
                            <a href="{{ route('kennis.show', $article) }}" data-reveal style="--d:{{ $loop->index * 60 }}ms"><span class="kicker">{{ $article->category }}</span><h4><span>{{ $article->title }}</span></h4>{!! $arrow !!}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- T4: Jake en Rory --}}
    @include('partials.riders', ['turn' => 'T4'])

    {{-- Finish (signature-moment 2) --}}
    <div class="finish on-dark">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Laatste ronde</p>
            <h2>Drie vragen, en je weet welke motor bij je past</h2>
            <p>Gratis en zonder account. Je ziet meteen je top 6, met uitleg waarom.</p>
            <div class="finish__row">
                <a class="btn btn--primary" href="{{ route('wizard.index') }}">Start het advies {!! $arrow !!}</a>
                <a class="btn btn--line" href="{{ route('partners.apply') }}">Word partner</a>
            </div>
        </div>
    </div>
@endsection
