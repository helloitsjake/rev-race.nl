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
                    <a class="btn primary" href="#aanmelden">Meld je aan</a>
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
    </section>

    <section class="section" id="aanmelden">
        <span class="eyebrow">Aanmelden</span>
        <h2 class="section-title">Denk je dat jouw bedrijf hier goed bij past?</h2>
        <p class="section-sub">Vul het formulier in, we nemen snel contact met je op.</p>

        <form class="panel" method="post" action="{{ route('partners.apply.store') }}">
            @csrf
            <div style="position:absolute;left:-9999px" aria-hidden="true">
                <label for="website">Laat dit veld leeg</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="sim-grid">
                <div>
                    <div class="form-row">
                        <label class="form-label" for="company_name">Bedrijfsnaam</label>
                        <input class="input" id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="contact_name">Contactpersoon</label>
                        <input class="input" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required>
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="email">E-mailadres</label>
                        <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="phone">Telefoon (optioneel)</label>
                        <input class="input" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                </div>
                <div>
                    <div class="form-row">
                        <label class="form-label" for="website_url">Website (optioneel)</label>
                        <input class="input" id="website_url" name="website_url" type="url" placeholder="https://" value="{{ old('website_url') }}">
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="category">Categorie</label>
                        <select class="select" id="category" name="category">
                            <option value="">Kies een categorie</option>
                            <option value="Dealer" @selected(old('category') === 'Dealer')>Dealer</option>
                            <option value="Verzekering" @selected(old('category') === 'Verzekering')>Verzekering</option>
                            <option value="Onderhoud" @selected(old('category') === 'Onderhoud')>Onderhoud</option>
                            <option value="Evenementen" @selected(old('category') === 'Evenementen')>Evenementen</option>
                            <option value="Anders" @selected(old('category') === 'Anders')>Anders</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="form-label" for="message">Bericht (optioneel)</label>
                        <textarea class="input" id="message" name="message" rows="4">{{ old('message') }}</textarea>
                    </div>
                </div>
            </div>
            <button class="btn primary" type="submit">Versturen</button>
        </form>
    </section>
@endsection
