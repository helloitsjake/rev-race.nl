<!DOCTYPE html>
<html lang="nl">
<head>
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="x3pTCkZRmLD0nmLUPg2tpg" async></script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-NFH7Z6V5');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RevRace - Motorsimulatie')</title>
    <meta name="description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="128x128" href="{{ asset('images/brand/icon-128.png') }}">
    <link rel="icon" type="image/png" sizes="256x256" href="{{ asset('images/brand/icon-256.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#D7401F">

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
    <link rel="stylesheet" href="{{ asset('css/revrace.css') }}?v={{ filemtime(public_path('css/revrace.css')) }}">
    @stack('head')
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NFH7Z6V5"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@unless($embedded ?? false)
    <div class="topbar">
        <span>Server-side motorsimulatie</span>
        <span>{{ \App\Models\SimulationLog::LIMIT }} gratis simulaties per 24 uur</span>
    </div>
    <nav class="nav wrap">
        <a class="nav__logo" href="{{ route('home') }}">@include('partials.brand-icon')Rev<span>Race</span></a>
        <input type="checkbox" id="nav-toggle" class="nav__toggle-input">
        <label for="nav-toggle" class="nav__burger" aria-label="Menu"><span></span><span></span><span></span></label>
        <div class="nav__panel">
            <div class="nav__links">
                <a href="{{ route('home') }}" class="@if(request()->routeIs('home')) is-active @endif">Home</a>
                <details class="nav__dropdown @if(request()->routeIs('wizard.*', 'simulation.*', 'most-searched.*', 'kennis.*')) is-active @endif">
                    <summary>Ontdekken</summary>
                    <div class="nav__dropdown-menu">
                        <a href="{{ route('wizard.index') }}">Welke motor past bij mij</a>
                        <a href="{{ route('simulation.index') }}">Simulatie</a>
                        <a href="{{ route('most-searched.index') }}">Meest gezocht</a>
                        <a href="{{ route('kennis.index') }}">Kennis</a>
                    </div>
                </details>
                <a href="{{ route('partners.index') }}" class="@if(request()->routeIs('partners.index')) is-active @endif">Partners</a>
                <a href="{{ route('how-it-works') }}" class="@if(request()->routeIs('how-it-works')) is-active @endif">Hoe het werkt</a>
                <a href="{{ route('about') }}" class="@if(request()->routeIs('about')) is-active @endif">Over ons</a>
                @auth
                    <a href="{{ route('garage.index') }}" class="@if(request()->routeIs('garage.*')) is-active @endif">Garage</a>
                    <a href="{{ route('profile.edit') }}" class="@if(request()->routeIs('profile.*')) is-active @endif">Mijn account</a>
                @endauth
            </div>
            <div class="nav__actions">
                @auth
                    <span class="nav__user">{{ Str::upper(Str::limit(auth()->user()->name, 12, '')) }}</span>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn--ghost" type="submit">Uitloggen</button>
                    </form>
                @else
                    <a class="nav__login" href="{{ route('login') }}">Inloggen</a>
                    <a class="btn btn--primary" href="{{ route('register') }}">Account aanmaken</a>
                @endauth
            </div>
        </div>
    </nav>
@endunless

<main class="page">
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
    <footer class="chapter--dark">
        <div class="wrap footer">
            <a class="nav__logo" href="{{ route('home') }}">@include('partials.brand-icon', ['dark' => true])Rev<span style="color:var(--redline)">Race</span></a>
            <div class="footer__links">
                <a href="{{ route('partners.apply') }}">Partner worden</a>
                <a href="{{ route('yearly-report.show') }}">Staat van de Nederlandse motorrijder</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="{{ route('contact') }}">Contact</a>
            </div>
            <small>© {{ date('Y') }} RevRace - www.rev-race.nl</small>
        </div>
    </footer>
@endunless

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}"></script>
@stack('scripts')
</body>
</html>
