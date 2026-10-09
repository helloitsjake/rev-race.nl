@extends('layouts.app')

@section('title', $partner->name . ' - RevRace partners')
@section('description', $partner->description)

@php
    $address = $partner->fullAddress();
    $mapsUrl = $partner->mapsUrl();
    $logo = $partner->assetUrl($partner->logo_url);
    $hero = $partner->assetUrl($partner->hero_image);
    $site = $partner->outboundUrl();
    $siteLabel = $partner->website_url ? preg_replace('#^https?://(www\.)?|/$#', '', $partner->website_url) : null;
    $tel = $partner->contact_phone ? preg_replace('/[^\d+]/', '', str_replace('(0)', '', $partner->contact_phone)) : null;
    $arrow = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
    $turn = 0;
@endphp

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Partners', 'item' => route('partners.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $partner->name],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => $address ? 'LocalBusiness' : 'Organization',
    'name' => $partner->name,
    'description' => $partner->about_text ?: $partner->description,
    'url' => $partner->website_url,
    'logo' => $logo,
    'image' => $hero,
    'telephone' => $partner->contact_phone,
    'email' => $partner->contact_email,
    'foundingDate' => $partner->founded_year ? (string) $partner->founded_year : null,
    'address' => $address ? [
        '@type' => 'PostalAddress',
        'streetAddress' => $partner->address_street,
        'postalCode' => $partner->address_postcode,
        'addressLocality' => $partner->address_city,
        'addressCountry' => 'NL',
    ] : null,
])) !!}
</script>
@endpush

@section('content')
    {{-- T0: wie het is --}}
    <header class="chapter chapter--tight partner-hero">
        <div class="wrap">
            <a class="back-link" href="{{ route('partners.index') }}">&larr; Alle partners</a>
            <div class="partner-hero__grid">
                <div>
                    <p class="turn"><b>T{{ $turn++ }}</b> Partner · {{ $partner->category }}</p>
                    @if($logo)
                        <img class="partner-hero__logo" src="{{ $logo }}" alt="Logo {{ $partner->name }}" width="160" height="68">
                    @endif
                    <h1>{{ $partner->name }}</h1>
                    <p class="lede">{{ $partner->description }}</p>
                    <div class="partner-hero__actions">
                        @if($site)
                            <a class="btn btn--primary" href="{{ $site }}" rel="sponsored noopener" target="_blank">Naar {{ $siteLabel }} {!! $arrow !!}</a>
                        @endif
                        @if($tel)
                            <a class="btn btn--ghost" href="tel:{{ $tel }}">Bel {{ $partner->contact_phone }}</a>
                        @endif
                    </div>
                </div>
                @if($hero)
                    <figure class="partner-hero__photo" data-reveal>
                        <img src="{{ $hero }}" alt="{{ $partner->name }} op het circuit" width="1600" height="1067" fetchpriority="high">
                        @if(!empty($partner->venues))
                            <figcaption><span>{{ count($partner->venues) }} circuits</span>{{ collect($partner->venues)->pluck('name')->join(' · ') }}</figcaption>
                        @endif
                    </figure>
                @endif
            </div>

            @if(!empty($partner->facts))
                <div class="stats" data-reveal>
                    @foreach($partner->facts as $fact)
                        <div class="stat"><div class="stat__value">{{ $fact['value'] }}</div><div class="stat__label">{{ $fact['label'] }}</div></div>
                    @endforeach
                </div>
            @endif
        </div>
    </header>

    {{-- T1: locaties als timingbord --}}
    @if(!empty($partner->venues))
        <section class="chapter chapter--dark">
            <div class="wrap">
                <div class="sec-head" data-reveal>
                    <div><p class="turn"><b>T{{ $turn++ }}</b> Kalender</p><h2>Waar je rijdt</h2></div>
                    <p>Elk circuit met de cijfers die ertoe doen: hoe lang, hoeveel bochten en welke geluidslimiet je uitlaat moet halen.</p>
                </div>
                <div class="timing timing--venues">
                    <div class="t-row t-head"><span>POS</span><span>CIRCUIT</span><span class="t-num">LENGTE</span><span class="t-num t-hide">BOCHTEN</span><span class="t-num t-hide">GELUID</span></div>
                    @foreach($partner->venues as $venue)
                        <a class="t-row" href="{{ $partner->outboundUrl($venue['url'] ?? null, 'circuit') }}" rel="sponsored noopener" target="_blank" style="--d:{{ $loop->index * 120 }}ms">
                            <span class="t-pos">{{ $loop->iteration }}</span>
                            <span class="t-name">{{ $venue['name'] }}<small>{{ $venue['place'] }}@if(!empty($venue['note'])) · {{ $venue['note'] }}@endif</small></span>
                            <span class="t-num">{{ $venue['length'] }}</span>
                            <span class="t-num t-hide">{{ $venue['corners'] }}</span>
                            <span class="t-num t-hide">{{ $venue['sound'] }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="timing-legend"><span>Gegevens van {{ $siteLabel ?? $partner->name }}</span>@if($site)<a href="{{ $partner->outboundUrl(null, 'kalender') }}" rel="sponsored noopener" target="_blank">Data en prijzen bekijken</a>@endif</div>
            </div>
        </section>
    @endif

    {{-- T2: aanbod --}}
    @if(!empty($partner->offers))
        <section class="chapter">
            <div class="wrap">
                <div class="sec-head" data-reveal>
                    <div><p class="turn"><b>T{{ $turn++ }}</b> Aanbod</p><h2>Wat je kunt boeken</h2></div>
                    <p>Actuele data en prijzen staan op de site van {{ $partner->name }}, de kaarten linken er direct naartoe.</p>
                </div>
                <div class="kb-grid offer-grid">
                    @foreach($partner->offers as $offer)
                        <a class="kb-card" href="{{ $partner->outboundUrl($offer['url'] ?? null, 'aanbod') }}" rel="sponsored noopener" target="_blank" data-reveal style="--d:{{ $loop->index * 70 }}ms">
                            <p class="kb-card__stage">{{ $offer['tag'] }}</p>
                            <h3>{{ $offer['title'] }}</h3>
                            <p>{{ $offer['text'] }}</p>
                            @if(!empty($offer['points']))
                                <ul class="usp-list usp-list--compact">
                                    @foreach($offer['points'] as $point)<li>{{ $point }}</li>@endforeach
                                </ul>
                            @endif
                            <span class="kb-card__link">Bekijk het aanbod</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- T3: verhaal en contact --}}
    <section class="chapter @if(!empty($partner->offers)) chapter--flush @else chapter--tight @endif">
        <div class="wrap partner-layout">
            <div>
                <div class="block" data-reveal>
                    <p class="turn"><b>T{{ $turn++ }}</b> Bedrijfsprofiel</p>
                    <h2 class="partner-h2">Over {{ $partner->name }}</h2>
                    <p>{{ $partner->about_text ?: $partner->description }}</p>
                </div>

                @if($partner->why_choose_text)
                    <div class="block" data-reveal>
                        <h2 class="partner-h2">Waarom {{ $partner->name }}</h2>
                        <p>{{ $partner->why_choose_text }}</p>
                    </div>
                @endif

                @if(!empty($partner->usps))
                    <div class="block" data-reveal>
                        <h2 class="partner-h2">In het kort</h2>
                        <ul class="usp-list">
                            @foreach($partner->usps as $usp)
                                <li>{{ $usp }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <aside class="partner-aside">
                <div class="panel">
                    <span class="eyebrow" style="margin-bottom:0.6em">Contact</span>

                    @if($address)
                        <div class="pass__row" style="align-items:flex-start"><span>Adres</span><b>{{ $partner->address_street }}<br>{{ $partner->address_postcode }} {{ $partner->address_city }}</b></div>
                    @endif
                    @if($partner->founded_year)
                        <div class="pass__row"><span>Actief sinds</span><b>{{ $partner->founded_year }}</b></div>
                    @endif
                    @if($partner->opening_hours)
                        <div class="pass__row"><span>Bereikbaar</span><b>{{ $partner->opening_hours }}</b></div>
                    @endif
                    @if($partner->contact_phone)
                        <div class="pass__row"><span>Telefoon</span><b><a href="tel:{{ $tel }}">{{ $partner->contact_phone }}</a></b></div>
                    @endif
                    @if($partner->contact_email)
                        <div class="pass__row"><span>E-mail</span><b><a href="mailto:{{ $partner->contact_email }}">{{ $partner->contact_email }}</a></b></div>
                    @endif

                    <div style="margin-top:20px">
                        @if($site)
                            <a class="btn btn--primary" href="{{ $partner->outboundUrl(null, 'contactblok') }}" rel="sponsored noopener" target="_blank">Naar {{ $siteLabel }}</a>
                        @endif
                        @if($mapsUrl)
                            <a class="btn btn--ghost" href="{{ $mapsUrl }}" rel="nofollow noopener" target="_blank">Routebeschrijving</a>
                        @endif
                        <a class="btn btn--ghost" href="{{ route('partners.index') }}">Alle partners</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    {{-- Finish: terug naar waar RevRace voor is --}}
    <div class="finish on-dark">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Voor je je inschrijft</p>
            <h2>Rij je op de juiste motor naar het circuit?</h2>
            <p>Drie vragen over je ervaring en rijstijl, en je ziet welke motoren daarbij passen. Of laat je eigen motor racen tegen die waar je op aast.</p>
            <div class="finish__row">
                <a class="btn btn--primary" href="{{ route('wizard.index') }}">Start het advies {!! $arrow !!}</a>
                <a class="btn btn--line" href="{{ route('simulation.index') }}">Start een simulatie</a>
            </div>
        </div>
    </div>
@endsection
