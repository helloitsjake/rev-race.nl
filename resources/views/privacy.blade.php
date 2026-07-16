@extends('layouts.app')

@section('title', 'Privacy - RevRace')

@section('content')
    <header>
        <span class="eyebrow">Privacy</span>
        <h1 class="page-title">Privacy</h1>
        <p class="page-sub">Korte productieverklaring voor de MVP. Laat deze juridisch nalopen voor brede lancering.</p>
    </header>

    <section class="panel">
        <h2 class="card-title">Welke gegevens gebruikt RevRace?</h2>
        <p>Voor accounts slaan we naam, e-mailadres, gehasht wachtwoord en optionele profielwaarden op. Voor rate limiting loggen we simulaties per account of IP-adres gedurende de rolling 24-uursperiode.</p>
        <h2 class="card-title">Cookies</h2>
        <p>RevRace gebruikt functionele sessiecookies voor inloggen en CSRF-beveiliging. Er zijn in deze MVP geen advertentie- of trackingcookies opgenomen.</p>
        <h2 class="card-title">Externe diensten</h2>
        <p>De site kan motorgegevens ophalen via Anthropic wanneer een API-key is ingesteld. Betalingen zijn in deze MVP nog niet zichtbaar geactiveerd.</p>
    </section>
@endsection
