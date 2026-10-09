@extends('layouts.app')

@section('title', 'Privacy - RevRace')
@section('description', 'Hoe RevRace omgaat met je gegevens: welke data er wordt opgeslagen bij een account, een simulatie of het garageprofiel, en waarom.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap" style="max-width:640px">
            <span class="eyebrow">Privacy</span>
            <h1 style="font-size:clamp(2.25rem,4vw,3.5rem)">Privacy</h1>
            <p class="lede">Wat RevRace bijhoudt, welke cookies er zijn, en hoe je jouw keuze kunt aanpassen.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap legal">
            <h2>Welke gegevens gebruikt RevRace?</h2>
            <p>Voor accounts slaan we naam, e-mailadres, gehasht wachtwoord en optionele profielwaarden op. Voor rate limiting loggen we simulaties per account of IP-adres gedurende de rolling 24-uursperiode.</p>

            <h2>Cookies</h2>
            <p>RevRace gebruikt twee soorten cookies:</p>
            <ul style="margin:0.6em 0 0.6em 1.2em; color:var(--ink-60)">
                <li><strong>Functioneel, altijd aan.</strong> Sessiecookies voor inloggen en CSRF-beveiliging. Zonder deze cookies werkt inloggen niet.</li>
                <li><strong>Analytisch, alleen na toestemming.</strong> Google Tag Manager (dat op onze site alleen Google Analytics inzet, geen advertentie-tags) en Ahrefs Web Analytics, om te zien hoeveel bezoekers de site gebruiken en welke pagina's het meest bezocht worden. Er wordt niet geadverteerd op basis van deze gegevens.</li>
            </ul>
            <p style="margin-top:0.6em">Je kiest bij je eerste bezoek of je de analytische cookies toestaat. Die keuze kun je op elk moment wijzigen via <button type="button" class="accent" style="background:none;border:none;padding:0;font:inherit;cursor:pointer;text-decoration:underline" data-consent-open>cookie-instellingen</button>, ook onderaan elke pagina te vinden.</p>

            <h2>Externe diensten</h2>
            <p>Staat een motor niet in onze database, dan kan de site de specificaties opzoeken via OpenAI. Daarbij sturen we alleen de zoekopdracht mee (bijvoorbeeld "bmw s1000rr 2022"), geen naam, e-mailadres of andere persoonsgegevens. Ook nieuwsartikelen over nieuwe modellen worden via OpenAI naar het Nederlands herschreven. Betalingen zijn in deze MVP nog niet zichtbaar geactiveerd.</p>
        </div>
    </section>
@endsection
