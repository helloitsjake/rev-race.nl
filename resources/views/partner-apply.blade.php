@extends('layouts.app')

@section('title', 'Partner worden van RevRace')
@section('description', 'Word partner van RevRace en bereik motorrijders die middenin hun oriëntatie op een nieuwe motor zitten.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap" style="max-width:760px">
            <span class="eyebrow">Voor bedrijven</span>
            <h1 style="font-size:clamp(2.25rem,4.4vw,4rem)">Partner worden van RevRace</h1>
            <p class="lede" style="max-width:56ch">RevRace bouwt aan een platform voor mensen die serieus bezig zijn met hun volgende motor. Precies het moment waarop een dealer, verzekeraar of onderhoudsbedrijf zichtbaar wil zijn.</p>
            <div class="hero__ctas">
                <a class="btn btn--primary" href="#aanmelden">Meld je aan</a>
                <a class="btn btn--ghost" href="{{ route('partners.index') }}">Bekijk huidige partners</a>
            </div>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Wat je krijgt</span>
            <h2 style="font-size:clamp(1.75rem,2.8vw,2.5rem)">Zichtbaar op het juiste moment</h2>
            <div class="kb-grid" style="margin-top:clamp(28px,3vw,44px)">
                <article class="kb-card">
                    <p class="kb-card__stage">01</p>
                    <h3>Vaste plek op de partnerspagina</h3>
                    <p>Ingedeeld op categorie, zodat bezoekers die specifiek op zoek zijn naar bijvoorbeeld een verzekering of onderhoudsspecialist jouw bedrijf makkelijk vinden.</p>
                </article>
                <article class="kb-card">
                    <p class="kb-card__stage">02</p>
                    <h3>Eigen partnerpagina</h3>
                    <p>Met meer informatie over je aanbod en een directe link naar je website, zodat verkeer vanaf RevRace rechtstreeks bij jou terechtkomt.</p>
                </article>
                <article class="kb-card">
                    <p class="kb-card__stage">03</p>
                    <h3>Een doelgroep middenin de oriëntatie</h3>
                    <p>Motorrijders die je normaal niet zo gericht bereikt, van eerste motor tot upgrade naar een volgend model.</p>
                </article>
            </div>
            <p class="form-note" style="margin-top:1.6rem;font-style:italic">We werken nog aan de exacte vorm en voorwaarden van het partnerschap, dit groeit mee met het platform.</p>
        </div>
    </section>

    <section class="chapter chapter--tight" id="aanmelden">
        <div class="wrap" style="max-width:840px">
            <span class="eyebrow">Aanmelden</span>
            <h2 style="font-size:clamp(1.75rem,2.8vw,2.5rem)">Denk je dat jouw bedrijf hier goed bij past?</h2>
            <p class="lede" style="margin-bottom:1.8rem">Vul het formulier in, we nemen snel contact met je op.</p>

            <form class="form-card" method="post" action="{{ route('partners.apply.store') }}">
                @csrf
                <div class="form-honeypot" aria-hidden="true">
                    <label for="website">Laat dit veld leeg</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="form-grid">
                    <div>
                        <div class="form-row">
                            <label for="company_name">Bedrijfsnaam</label>
                            <input id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                        </div>
                        <div class="form-row">
                            <label for="contact_name">Contactpersoon</label>
                            <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required>
                        </div>
                        <div class="form-row">
                            <label for="email">E-mailadres</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                        </div>
                        <div class="form-row">
                            <label for="phone">Telefoon (optioneel)</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}">
                        </div>
                    </div>
                    <div>
                        <div class="form-row">
                            <label for="website_url">Website (optioneel)</label>
                            <input id="website_url" name="website_url" type="url" placeholder="https://" value="{{ old('website_url') }}">
                        </div>
                        <div class="form-row">
                            <label for="category">Categorie</label>
                            <select id="category" name="category">
                                <option value="">Kies een categorie</option>
                                <option value="Dealer" @selected(old('category') === 'Dealer')>Dealer</option>
                                <option value="Verzekering" @selected(old('category') === 'Verzekering')>Verzekering</option>
                                <option value="Onderhoud" @selected(old('category') === 'Onderhoud')>Onderhoud</option>
                                <option value="Evenementen" @selected(old('category') === 'Evenementen')>Evenementen</option>
                                <option value="Anders" @selected(old('category') === 'Anders')>Anders</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="message">Bericht (optioneel)</label>
                            <textarea id="message" name="message" rows="4">{{ old('message') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="form-foot">
                    <button class="btn btn--primary" type="submit">Versturen</button>
                    <span class="form-note">We reageren doorgaans binnen een paar werkdagen.</span>
                </div>
            </form>
        </div>
    </section>
@endsection
