@extends('layouts.app')

@section('title', $partner->name . ' - RevRace partners')
@section('description', $partner->description)

@php
    $address = $partner->fullAddress();
    $mapsUrl = $partner->mapsUrl();
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
    '@type' => 'LocalBusiness',
    'name' => $partner->name,
    'description' => $partner->about_text ?: $partner->description,
    'url' => $partner->website_url,
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
    <header class="chapter chapter--tight">
        <div class="wrap">
            <a class="back-link" href="{{ route('partners.index') }}">&larr; Alle partners</a>
            <div class="partner-head">
                <div>
                    <span class="badge">{{ $partner->category }}</span>
                    <h1 style="margin-top:10px;font-size:clamp(2.25rem,4.4vw,4rem)">{{ $partner->name }}</h1>
                    <p class="lede" style="margin-top:0.6em">{{ $partner->description }}</p>
                </div>
            </div>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap partner-layout">
            <div>
                <div class="block">
                    <span class="eyebrow">Bedrijfsprofiel</span>
                    <h2 style="font-size:clamp(1.5rem,2.2vw,2rem)">Over {{ $partner->name }}</h2>
                    <p>{{ $partner->about_text ?: $partner->description }}</p>
                    @if($partner->founded_year)
                        <p class="note-inline">Actief sinds {{ $partner->founded_year }}</p>
                    @endif
                </div>

                @if($partner->why_choose_text)
                    <div class="block">
                        <span class="eyebrow">Waarom kiezen voor {{ $partner->name }}</span>
                        <h2 style="font-size:clamp(1.5rem,2.2vw,2rem)">Wat {{ $partner->name }} onderscheidt</h2>
                        <p>{{ $partner->why_choose_text }}</p>
                    </div>
                @endif

                @if(!empty($partner->usps))
                    <div class="block">
                        <span class="eyebrow">In het kort</span>
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
                        <div class="pass__row" style="align-items:flex-start"><span>Adres</span><b style="text-align:right">{{ $partner->address_street }}<br>{{ $partner->address_postcode }} {{ $partner->address_city }}</b></div>
                    @endif
                    @if($partner->opening_hours)
                        <div class="pass__row"><span>Openingstijden</span><b style="text-align:right">{{ $partner->opening_hours }}</b></div>
                    @endif
                    @if($partner->contact_phone)
                        <div class="pass__row"><span>Telefoon</span><b><a href="tel:{{ preg_replace('/\s+/', '', $partner->contact_phone) }}">{{ $partner->contact_phone }}</a></b></div>
                    @endif
                    @if($partner->contact_email)
                        <div class="pass__row"><span>E-mail</span><b><a href="mailto:{{ $partner->contact_email }}">{{ $partner->contact_email }}</a></b></div>
                    @endif

                    <div style="margin-top:20px">
                        @if($mapsUrl)
                            <a class="btn btn--primary" href="{{ $mapsUrl }}" rel="nofollow noopener" target="_blank">Routebeschrijving</a>
                        @endif
                        @if($partner->website_url)
                            <a class="btn btn--ghost" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">Naar website</a>
                        @endif
                        <a class="btn btn--ghost" href="{{ route('partners.index') }}">Alle partners</a>
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
