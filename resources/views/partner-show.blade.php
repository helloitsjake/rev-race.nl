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
    <a class="small" href="{{ route('partners.index') }}">&larr; Alle partners</a>

    <div class="chart-head" style="align-items:flex-start;margin-top:16px">
        <div>
            <span class="badge">{{ $partner->category }}</span>
            <h1 class="page-title" style="margin-top:10px">{{ $partner->name }}</h1>
            <p class="page-sub">{{ $partner->description }}</p>
        </div>
        <div class="photo-placeholder photo-placeholder-sm">Logo</div>
    </div>

    <div class="partner-layout">
        <div>
            <section class="panel">
                <h2 class="card-title">Over {{ $partner->name }}</h2>
                <p style="margin-top:10px">{{ $partner->about_text ?: $partner->description }}</p>
                @if($partner->founded_year)
                    <p class="small" style="margin-top:10px;color:var(--dim)">Actief sinds {{ $partner->founded_year }}</p>
                @endif
            </section>

            @if($partner->why_choose_text)
                <section class="panel" style="margin-top:20px">
                    <h2 class="card-title">Waarom kiezen voor {{ $partner->name }}</h2>
                    <p style="margin-top:10px">{{ $partner->why_choose_text }}</p>
                </section>
            @endif

            @if(!empty($partner->usps))
                <section class="panel" style="margin-top:20px">
                    <h2 class="card-title">In het kort</h2>
                    <ul class="partner-usp-list" style="margin-top:12px">
                        @foreach($partner->usps as $usp)
                            <li>
                                <svg class="card-icon" style="width:20px;height:20px;margin-bottom:0;flex-shrink:0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l4 4 10 -10"/></svg>
                                <span>{{ $usp }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside>
            <section class="panel">
                <h2 class="card-title" style="margin-bottom:4px">Contact</h2>

                @if($address)
                    <div class="spec-row" style="align-items:flex-start">
                        <span class="spec-label">Adres</span>
                        <span class="spec-value" style="text-align:right">{{ $partner->address_street }}<br>{{ $partner->address_postcode }} {{ $partner->address_city }}</span>
                    </div>
                @endif
                @if($partner->opening_hours)
                    <div class="spec-row">
                        <span class="spec-label">Openingstijden</span>
                        <span class="spec-value" style="text-align:right">{{ $partner->opening_hours }}</span>
                    </div>
                @endif
                @if($partner->contact_phone)
                    <div class="spec-row">
                        <span class="spec-label">Telefoon</span>
                        <span class="spec-value"><a class="accent" href="tel:{{ preg_replace('/\s+/', '', $partner->contact_phone) }}">{{ $partner->contact_phone }}</a></span>
                    </div>
                @endif
                @if($partner->contact_email)
                    <div class="spec-row">
                        <span class="spec-label">E-mail</span>
                        <span class="spec-value"><a class="accent" href="mailto:{{ $partner->contact_email }}">{{ $partner->contact_email }}</a></span>
                    </div>
                @endif

                <div class="hero-actions" style="margin-top:18px">
                    @if($mapsUrl)
                        <a class="btn primary" href="{{ $mapsUrl }}" rel="nofollow noopener" target="_blank">Routebeschrijving</a>
                    @endif
                    @if($partner->website_url)
                        <a class="btn secondary" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">Naar website</a>
                    @endif
                    <a class="btn ghost" href="{{ route('partners.index') }}">Alle partners</a>
                </div>
            </section>
        </aside>
    </div>
@endsection
