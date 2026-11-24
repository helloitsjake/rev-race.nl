<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    {{--
        Meting (zie MEETPLAN.md): Consent Mode v2. GTM laadt altijd, maar alles staat standaard
        op 'denied'; de GA4-tags in GTM vuren pas met analytics_storage = granted. De keuze staat
        in localStorage 'revrace_consent' (site.js). Eigen events gaan via dataLayer met een
        'rr'-object (rrTrack in site.js, of 'track' in de sessie voor events na een redirect).
        ?intern=1 markeert dit apparaat als intern verkeer (?intern=0 zet het terug).
    --}}
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    (function () {
        var c = null, intern = false, m = location.search.match(/[?&]intern=([01])/);
        try { c = JSON.parse(localStorage.getItem('revrace_consent')); } catch (e) {}
        try { if (m) localStorage.setItem('revrace_intern', m[1]); intern = localStorage.getItem('revrace_intern') === '1'; } catch (e) {}
        gtag('consent', 'default', {
            analytics_storage: c && c.analytics ? 'granted' : 'denied',
            ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied',
            functionality_storage: 'granted', security_storage: 'granted'
        });
        var d = { page_type: @json(Route::currentRouteName() ?? 'overig') };
        if (intern) d.traffic_type = 'internal';
        dataLayer.push(d);
    })();
    @if (is_array(session('track')))
    dataLayer.push({ rr: null });
    dataLayer.push(@json(['event' => session('track')['event'] ?? 'rr_event', 'rr' => session('track')]));
    @endif
    </script>
    @unless($embedded ?? false)
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-NFH7Z6V5');</script>
    <script async src="https://analytics.ahrefs.com/analytics.js" data-key="x3pTCkZRmLD0nmLUPg2tpg"></script>
    @endunless
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RevRace - Motorsimulatie')</title>
    <meta name="description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('images/brand/icon-128.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('images/brand/icon-256.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#F2F1EC">

    <meta property="og:site_name" content="RevRace">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'RevRace - Motorsimulatie')">
    <meta property="og:description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('ogImage', asset('og-image.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'RevRace - Motorsimulatie')">
    <meta name="twitter:description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <meta name="twitter:image" content="@yield('ogImage', asset('og-image.png'))">

    <script type="application/ld+json">
    {!! json_encode(['@'.'context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'RevRace', 'url' => 'https://www.rev-race.nl']) !!}
    </script>
    <link rel="preload" href="{{ asset('fonts/inter-tight-800.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/revrace.css') }}?v={{ filemtime(public_path('css/revrace.css')) }}">
    <script>document.documentElement.classList.add('js')</script>
    @stack('head')
</head>
<body class="@yield('bodyClass')">
<div id="consent-banner" class="consent-banner" hidden role="region" aria-label="Cookievoorkeuren">
    <div class="consent-banner__inner wrap">
        <div class="consent-banner__text">
            <p class="consent-banner__title">Cookies op RevRace</p>
            <p>Mag je bezoek meetellen in Google Analytics? Dat helpt ons RevRace beter te maken. Zonder toestemming tellen we alleen anoniem bezoek, zonder cookies. Functionele cookies (inloggen, beveiliging) staan altijd aan. Meer op de <a href="{{ route('privacy') }}">privacypagina</a>.</p>
        </div>
        <div class="consent-banner__actions">
            <button type="button" class="btn btn--ghost" data-consent-action="reject">Alleen noodzakelijk</button>
            <button type="button" class="btn btn--primary" data-consent-action="accept">Alles toestaan</button>
        </div>
    </div>
</div>
@unless($embedded ?? false)
    <header class="header">
        <nav class="nav wrap" aria-label="Hoofdmenu">
            <a class="nav__logo" href="{{ route('home') }}">@include('partials.brand-icon')Rev<span>Race</span></a>
            <input type="checkbox" id="nav-toggle" class="nav__toggle-input" aria-label="Menu openen">
            <label for="nav-toggle" class="nav__burger" aria-hidden="true"><span></span><span></span><span></span></label>
            <div class="nav__panel">
                <div class="nav__links">
                    <a href="{{ route('wizard.index') }}" class="@if(request()->routeIs('wizard.*')) is-active @endif">Motor kiezen</a>
                    <a href="{{ route('simulation.index') }}" class="@if(request()->routeIs('simulation.*', 'compare.*')) is-active @endif">Vergelijken</a>
                    <a href="{{ route('kennis.index') }}" class="@if(request()->routeIs('kennis.*')) is-active @endif">Kennisbank</a>
                    <details class="nav__dropdown @if(request()->routeIs('most-searched.*', 'brands.*', 'segments.*', 'a2-motoren', 'toplijst.*', 'partners.*')) is-active @endif">
                        <summary>Ontdekken</summary>
                        <div class="nav__dropdown-menu">
                            <a href="{{ route('most-searched.index') }}">Meest gezocht</a>
                            <a href="{{ route('brands.index') }}">Merken</a>
                            <a href="{{ route('segments.index') }}">Segmenten</a>
                            <a href="{{ route('a2-motoren') }}">A2-motoren</a>
                            <a href="{{ route('partners.index') }}">Partners</a>
                        </div>
                    </details>
                    <a href="{{ route('about') }}" class="@if(request()->routeIs('about')) is-active @endif">Over RevRace</a>
                    @auth
                        <a href="{{ route('garage.index') }}" class="@if(request()->routeIs('garage.*')) is-active @endif">Garage</a>
                        <a href="{{ route('profile.edit') }}" class="@if(request()->routeIs('profile.*')) is-active @endif">Mijn account</a>
                    @endauth
                </div>
                <div class="nav__actions">
                    @auth
                        <span class="nav__user">{{ Str::limit(auth()->user()->name, 16) }}</span>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn--ghost" type="submit">Uitloggen</button>
                        </form>
                    @else
                        <a class="nav__login" href="{{ route('login') }}">Inloggen</a>
                        <a class="btn btn--primary" href="{{ route('register') }}">Start je garage</a>
                    @endauth
                </div>
            </div>
        </nav>
        <span class="header__progress" aria-hidden="true"></span>
    </header>
@endunless

<main class="page @yield('pageClass')">
    @if (session('status'))
        <div class="notice">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @yield('content')
</main>

@unless($embedded ?? false)
    <footer class="site-footer">
        <div class="wrap footer">
            <a class="nav__logo" href="{{ route('home') }}">@include('partials.brand-icon', ['dark' => true])Rev<span>Race</span></a>
            <div class="footer__links">
                {{-- Enige sitebrede plek die naar deze 3 pagina's linkt: stonden zonder deze
                     footer-links nergens bereikbaar vanuit navigatie en waren daardoor orphan
                     (Ahrefs "Orphan page", 18 aug), ondanks vermelding in de sitemap. --}}
                <a href="{{ route('toplijst.show', 'beste-pk-kg-verhouding') }}">Beste pk/kg-verhouding</a>
                <a href="{{ route('toplijst.show', 'hoogste-topsnelheid') }}">Hoogste topsnelheid</a>
                <a href="{{ route('toplijst.show', 'snelste-0-100-sprint') }}">Snelste 0-100 sprint</a>
                <a href="{{ route('how-it-works') }}">Hoe het werkt</a>
                <a href="{{ route('partners.apply') }}">Partner worden</a>
                <a href="{{ route('yearly-report.show') }}">Staat van de Nederlandse motorrijder</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <button type="button" class="footer__link-btn" data-consent-open>Cookie-instellingen</button>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
            <small>© {{ date('Y') }} RevRace · Jake en Rory Andreas</small>
        </div>
    </footer>
@endunless

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}"></script>
@stack('scripts')
</body>
</html>
