@extends('layouts.app')

@section('title', 'Over ons - RevRace')
@section('description', 'Waarom RevRace bestaat: een eerlijke rekensom in plaats van fabrieksfolders, zodat je ontdekt welke motor echt bij je past.')

@section('content')
    <header style="text-align:center;max-width:760px;margin:0 auto 32px">
        <span class="eyebrow" style="justify-content:center">Over ons</span>
        <h1 class="page-title">Geen marketingpraatjes,<br><span class="accent">een eerlijke rekensom</span></h1>
        <p class="page-sub">RevRace is ontstaan uit een simpele frustratie: overal online lees je specificaties, maar nergens zie je écht wat een motor doet als je 'm naast een andere zet.</p>
    </header>

    <div class="top-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">
        <div class="photo-placeholder">Foto team met motoren</div>
        <div class="photo-placeholder">Foto circuit</div>
        <div class="photo-placeholder">Foto werkplaats</div>
    </div>

    <section class="section" style="display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:32px;align-items:start">
        <div>
            <span class="eyebrow">Het verhaal</span>
            <h2 class="section-title">Dus bouwden we een simulator die dat wél laat zien</h2>
        </div>
        <div>
            <p>Vermogen op papier is één ding, hoe die pk's zich vertalen naar acceleratie, bochtsnelheid en remgedrag op nat asfalt is een heel ander verhaal.</p>
            <p>Geen marketingpraatjes van fabrikanten, geen los rijtje getallen, maar een eerlijke rekensom op basis van vermogen, gewicht, luchtweerstand en wegconditie. Zo vergelijk je niet alleen twee motoren tegen elkaar, je ontdekt ook wat voor rijder je eigenlijk bent en welk type motor daar het beste bij past. Toermotor voor de lange weg door Duitsland, of toch een supersport voor het circuit?</p>
            <p>RevRace is nog volop in ontwikkeling. Er komen steeds meer motoren, meer simulaties en meer manieren bij om je droommotor te vinden. Achter de site staat een klein team met een grote motorliefde, en dat verhaal vertellen we je graag nog uitgebreider zodra het daar de tijd voor is.</p>
        </div>
    </section>

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

    <section class="section" style="text-align:center">
        <div class="panel" style="padding:32px;max-width:560px;margin:0 auto">
            <h2 class="section-title" style="font-size:clamp(22px,3vw,30px)">Pak twee motoren, draai een race</h2>
            <p class="section-sub">En ontdek wat het beste bij jou past.</p>
            <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>
@endsection
