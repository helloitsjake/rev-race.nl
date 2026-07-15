@extends('layouts.app')

@section('title', 'Partner worden van RevRace')
@section('description', 'Word partner van RevRace en bereik motorrijders die middenin hun oriëntatie op een nieuwe motor zitten.')

@section('content')
    <header>
        <h1 class="page-title">Partner worden van RevRace</h1>
    </header>

    <section class="panel">
        <p>RevRace bouwt aan een platform voor mensen die serieus bezig zijn met hun volgende motor. Bezoekers vergelijken specificaties, testen rijgedrag in de simulator, en zoeken uit welk type motor bij hun rijstijl past, precies het moment waarop een dealer, verzekeraar of onderhoudsbedrijf zichtbaar wil zijn.</p>
        <p>Als partner van RevRace krijg je een vaste plek op onze partnerspagina, ingedeeld op categorie, zodat bezoekers die specifiek op zoek zijn naar bijvoorbeeld een verzekering of onderhoudsspecialist jouw bedrijf makkelijk vinden. Elke partner krijgt bovendien een eigen pagina met meer informatie over je aanbod en een directe link naar je website, zodat verkeer vanaf RevRace rechtstreeks bij jou terechtkomt.</p>
        <p>We werken nog aan de exacte vorm en voorwaarden van het partnerschap, dit groeit mee met het platform. Wat vaststaat: RevRace richt zich op een doelgroep die je normaal niet zo gericht bereikt, motorrijders die middenin hun oriëntatie zitten, van eerste motor tot upgrade naar een volgend model.</p>
        <p>Denk je dat jouw bedrijf hier goed bij past? Neem contact op via onderstaande gegevens, het aanmeldformulier volgt hier binnenkort.</p>
        <div class="hero-actions">
            <a class="btn primary" href="{{ route('contact') }}">Neem contact op</a>
            <a class="btn secondary" href="{{ route('partners.index') }}">Bekijk huidige partners</a>
        </div>
    </section>
@endsection
