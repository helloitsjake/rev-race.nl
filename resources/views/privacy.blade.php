@extends('layouts.app')

@section('title', 'Privacy - RevRace')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap" style="max-width:640px">
            <span class="eyebrow">Privacy</span>
            <h1 style="font-size:clamp(2.25rem,4vw,3.5rem)">Privacy</h1>
            <p class="lede">Korte productieverklaring voor de MVP. Laat deze juridisch nalopen voor brede lancering.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap legal">
            <h2>Welke gegevens gebruikt RevRace?</h2>
            <p>Voor accounts slaan we naam, e-mailadres, gehasht wachtwoord en optionele profielwaarden op. Voor rate limiting loggen we simulaties per account of IP-adres gedurende de rolling 24-uursperiode.</p>

            <h2>Cookies</h2>
            <p>RevRace gebruikt functionele sessiecookies voor inloggen en CSRF-beveiliging. Er zijn in deze MVP geen advertentie- of trackingcookies opgenomen.</p>

            <h2>Externe diensten</h2>
            <p>De site kan motorgegevens ophalen via Anthropic wanneer een API-key is ingesteld. Betalingen zijn in deze MVP nog niet zichtbaar geactiveerd.</p>
        </div>
    </section>
@endsection
