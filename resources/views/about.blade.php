@extends('layouts.app')

@section('title', 'Over ons - RevRace')
@section('description', 'Waarom RevRace bestaat: een eerlijke rekensom in plaats van fabrieksfolders, zodat je ontdekt welke motor echt bij je past.')

@section('content')
    <header>
        <h1 class="page-title">Over RevRace</h1>
    </header>

    <section class="panel">
        <p>RevRace is ontstaan uit een simpele frustratie: overal online lees je specificaties, maar nergens zie je écht wat een motor doet als je 'm naast een andere zet. Vermogen op papier is één ding, hoe die pk's zich vertalen naar acceleratie, bochtsnelheid en remgedrag op nat asfalt is een heel ander verhaal.</p>
        <p>Dus bouwden we een simulator die dat wel laat zien. Geen marketingpraatjes van fabrikanten, geen los rijtje getallen, maar een eerlijke rekensom op basis van vermogen, gewicht, luchtweerstand en wegconditie. Zo vergelijk je niet alleen twee motoren tegen elkaar, je ontdekt ook wat voor rijder je eigenlijk bent en welk type motor daar het beste bij past. Toermotor voor de lange weg door Duitsland, of toch een supersport voor het circuit?</p>
        <p>RevRace is nog volop in ontwikkeling. Er komen steeds meer motoren, meer simulaties en meer manieren bij om je droommotor te vinden. Achter de site staat een klein team met een grote motorliefde, en dat verhaal vertellen we je graag nog uitgebreider zodra het daar de tijd voor is.</p>
        <p>Voor nu: pak twee motoren, draai een race, en ontdek wat het beste bij jou past.</p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>
@endsection
