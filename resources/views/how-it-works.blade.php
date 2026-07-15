@extends('layouts.app')

@section('title', 'Hoe RevRace jouw motorvergelijking berekent')
@section('description', 'Ontdek hoe RevRace motoren simuleert op basis van vermogen, gewicht, luchtweerstand en wegconditie, en waarom die berekening zo nauwkeurig is.')

@section('content')
    <header>
        <h1 class="page-title">Hoe RevRace jouw motorvergelijking berekent</h1>
    </header>

    <section class="panel">
        <p>Twee motoren invoeren en binnen enkele seconden een uitslag zien, dat voelt bijna te makkelijk. Toch zit er een serieuze rekenkern achter elke race op RevRace.</p>
        <p>Elke simulatie start met de technische specificaties van een motor: vermogen, koppel, gewicht, cilinderinhoud en het type motorblok. Die cijfers halen we op uit een uitgebreide database en waar nodig via kunstmatige intelligentie, die vervolgens realistische aannames doet over zaken als luchtweerstand op basis van het type carrosserie. Vervolgens rekent onze fysica engine, seconde voor seconde, uit hoe een motor zich gedraagt op het gekozen traject: een rechte sprint of een kronkelweg vol bochten.</p>
        <p>Daarbij spelen ook de omstandigheden een grote rol. Droog asfalt geeft maximale grip en laat het pure vermogen van een motor spreken. Op vochtig of nat asfalt verandert het spel volledig: tractie neemt af, remwegen worden langer en de motor met het gelijkmatigste koppel wint dan vaak van de motor met de meeste pk's op papier. Dat detail alleen al maakt het verschil tussen een simulatie die indruk probeert te maken en een simulatie die klopt.</p>
        <p>We delen bewust niet de precieze rekenformules achter de schermen, net zoals een goede kok zijn recept niet op straat gooit. Wat we wel delen is het resultaat: een uitslag waar je op kunt vertrouwen, of je nu twijfelt tussen twee specifieke modellen of gewoon wilt weten wat voor type motor bij jouw rijstijl past. Toeren, sportief rijden of het liefst op het circuit staan, de natuurkunde erachter verandert niet, en RevRace rekent het voor je uit.</p>
        <p>Zelf proberen werkt het snelst. Kies twee motoren, kies een wegconditie, en race.</p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>

    <section class="section">
        <h2 class="section-title">Veelgestelde vragen</h2>
        <div class="card-grid">
            <div class="card">
                <h3 class="card-title">Is deze simulatie echt nauwkeurig?</h3>
                <p class="section-sub">Ja, we gebruiken dezelfde natuurkundige principes die ook in de motorsport en voertuigontwikkeling gebruikt worden: vermogen tegen gewicht, luchtweerstand en de tractielimiet per wegconditie. We houden onze motorendatabase voortdurend up to date zodat de simulatie relevant blijft naarmate nieuwe modellen uitkomen.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Waarom laten jullie de formule niet zien?</h3>
                <p class="section-sub">Net als bij elk goed recept zit het verschil in de details. De rekenmethode is het resultaat van veel testen en fijnslijpen, en dat is precies waarom RevRace anders aanvoelt dan een simpele tabel met specificaties naast elkaar.</p>
            </div>
            <div class="card">
                <h3 class="card-title">Kan ik mijn eigen rijdersgewicht meenemen?</h3>
                <p class="section-sub">Zeker, met een gratis account vul je je rijdersprofiel in en reken je dat automatisch mee in elke race.</p>
            </div>
        </div>
    </section>
@endsection
