@extends('layouts.app')

@section('title', 'Over ons - RevRace')
@section('description', 'Waarom RevRace bestaat: een eerlijke rekensom in plaats van fabrieksfolders, zodat je ontdekt welke motor echt bij je past.')

@section('content')
    <header class="chapter">
        <div class="wrap" style="max-width:760px">
            <span class="eyebrow">Over ons</span>
            <h1>Geen marketingpraatjes, een eerlijke rekensom</h1>
            <p class="lede" style="max-width:56ch">RevRace is ontstaan uit een simpele frustratie: overal online lees je specificaties, maar nergens zie je écht wat een motor doet als je 'm naast een andere zet.</p>
        </div>
    </header>

    <section class="chapter chapter--dark">
        <div class="wrap credibility">
            <div class="pass">
                <div class="pass__row"><span>Naam</span><b>Jake Andreas</b></div>
                <div class="pass__row"><span>Op de weg</span><b>10+ jaar</b></div>
                <div class="pass__row"><span>Circuit</span><b>Actief coureur</b></div>
                <div class="pass__row"><span>Achtergrond</span><b>Data &amp; analyse</b></div>
                <div class="pass__row"><span>Rol</span><b>Bouwer van RevRace</b></div>
                <div class="pass__row"><span>Bouwt aan</span><b>RevRace, dagelijks</b></div>
            </div>
            <div>
                <span class="eyebrow">Wie er achter RevRace staat</span>
                <h2>Dit is de rekensom die ik zelf had willen hebben voordat ik mijn eerste motor kocht</h2>
                <p>Op het circuit leer je snel dat een specificatiebladzijde niets zegt over hoe een motor daadwerkelijk aanvoelt. Vermogen op papier is één ding, hoe die pk's zich vertalen naar acceleratie, bochtsnelheid en remgedrag op het asfalt is een heel ander verhaal.</p>
                <p>Geen marketingpraatjes van fabrikanten, geen los rijtje getallen, maar een eerlijke rekensom op basis van vermogen, gewicht, luchtweerstand en wegconditie. Zo vergelijk je niet alleen twee motoren tegen elkaar, je ontdekt ook wat voor rijder je eigenlijk bent en welk type motor daar het beste bij past. Toermotor voor de lange weg door Duitsland, of toch een supersport voor het circuit?</p>
                <p>RevRace is nog volop in ontwikkeling. Er komen steeds meer motoren, meer simulaties en meer manieren bij om je droommotor te vinden. Achter de site staat één bouwer met een grote motorliefde, en dat verhaal vertel ik graag nog uitgebreider zodra het platform daar de tijd voor is.</p>
                <div class="stats">
                    <div class="stat"><div class="stat__value">{{ $motors->count() }}+</div><div class="stat__label">Motoren in de database</div></div>
                    <div class="stat"><div class="stat__value">100%</div><div class="stat__label">Nederlands platform</div></div>
                    <div class="stat"><div class="stat__value">0</div><div class="stat__label">Fabrieksfolders geloofd</div></div>
                </div>
            </div>
        </div>
    </section>

    <section class="chapter" id="missie">
        <div class="wrap">
            <span class="eyebrow">Missie</span>
            <h2>Een kennisbank voor elke motorrijder</h2>
            <p class="lede">RevRace groeit door, met steeds meer motoren, meer simulaties en meer manieren om de juiste motor te vinden. Voor iedereen, niet alleen voor wie de fabrikanten het luidst aanspreken.</p>
            <div class="kb-grid" style="margin-top:clamp(32px,4vw,56px)">
                <article class="kb-card">
                    <p class="kb-card__stage">Eerlijke rekensom</p>
                    <h3>Vermogen, gewicht, luchtweerstand, wegconditie</h3>
                    <p>Geen los rijtje getallen naast elkaar, maar een berekening die laat zien wat een motor écht doet op het asfalt.</p>
                </article>
                <article class="kb-card">
                    <p class="kb-card__stage">Voor elke rijder</p>
                    <h3>Van eerste rijbewijs tot het circuit</h3>
                    <p>Of je twijfelt tussen twee A2-motoren of tussen twee supersporters: de natuurkunde erachter blijft hetzelfde.</p>
                </article>
                <article class="kb-card">
                    <p class="kb-card__stage">In ontwikkeling</p>
                    <h3>Steeds meer motoren, steeds meer simulaties</h3>
                    <p>RevRace groeit door. Nieuwe modellen, nieuwe wegcondities en nieuwe manieren om te vergelijken.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap cta-split">
            <div>
                <span class="eyebrow">Zelf proberen</span>
                <h2>Pak twee motoren, draai een race</h2>
                <p class="lede">En ontdek wat er echt gebeurt zodra de vlag valt, in plaats van te varen op een specificatiebladzijde.</p>
            </div>
            <div class="panel">
                <span class="eyebrow" style="margin-bottom:0">Simulatie</span>
                <h3>Twee motoren. Eén asfalt.</h3>
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start simulatie</a>
            </div>
        </div>
    </section>
@endsection
