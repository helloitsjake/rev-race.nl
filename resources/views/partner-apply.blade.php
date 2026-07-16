@extends('layouts.app')

@section('title', 'Partner worden van RevRace')
@section('description', 'Word partner van RevRace en bereik motorrijders die middenin hun oriëntatie op een nieuwe motor zitten.')

@section('content')
    <div class="band-dark full-bleed">
        <div class="full-bleed-inner hero" style="padding:44px 0">
            <div>
                <span class="eyebrow" style="color:var(--orange)">Voor bedrijven</span>
                <h1>Partner worden van <span>RevRace</span></h1>
                <p>RevRace bouwt aan een platform voor mensen die serieus bezig zijn met hun volgende motor. Precies het moment waarop een dealer, verzekeraar of onderhoudsbedrijf zichtbaar wil zijn.</p>
                <div class="hero-actions">
                    <a class="btn primary" href="{{ route('contact') }}">Neem contact op</a>
                    <a class="btn secondary" href="{{ route('partners.index') }}">Bekijk huidige partners</a>
                </div>
            </div>
            <div class="photo-placeholder" style="min-height:220px">Foto dealer showroom</div>
        </div>
    </div>

    <section class="section">
        <span class="eyebrow">Wat je krijgt</span>
        <h2 class="section-title">Zichtbaar op het juiste moment</h2>
        <div class="card-grid">
            <div class="card">
                <div class="card-num">01</div>
                <h3 class="card-title">Vaste plek op de partnerspagina</h3>
                <p class="section-sub">Ingedeeld op categorie, zodat bezoekers die specifiek op zoek zijn naar bijvoorbeeld een verzekering of onderhoudsspecialist jouw bedrijf makkelijk vinden.</p>
            </div>
            <div class="card">
                <div class="card-num">02</div>
                <h3 class="card-title">Eigen partnerpagina</h3>
                <p class="section-sub">Met meer informatie over je aanbod en een directe link naar je website, zodat verkeer vanaf RevRace rechtstreeks bij jou terechtkomt.</p>
            </div>
            <div class="card">
                <div class="card-num">03</div>
                <h3 class="card-title">Een doelgroep middenin de oriëntatie</h3>
                <p class="section-sub">Motorrijders die je normaal niet zo gericht bereikt, van eerste motor tot upgrade naar een volgend model.</p>
            </div>
        </div>
        <p class="section-sub" style="font-style:italic;margin-top:18px">We werken nog aan de exacte vorm en voorwaarden van het partnerschap, dit groeit mee met het platform.</p>
        <p class="section-sub">Denk je dat jouw bedrijf hier goed bij past? Neem contact op, het aanmeldformulier volgt hier binnenkort.</p>
    </section>
@endsection
