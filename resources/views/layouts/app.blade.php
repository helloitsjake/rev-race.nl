<!DOCTYPE html>
<html lang="nl">
<head>
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

    <meta property="og:site_name" content="RevRace">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'RevRace - Motorsimulatie')">
    <meta property="og:description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="@yield('title', 'RevRace - Motorsimulatie')">
    <meta name="twitter:description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">

    <script type="application/ld+json">
    {!! json_encode(['@'.'context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'RevRace', 'url' => 'https://www.rev-race.nl']) !!}
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:wght@600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="{{ asset('css/revrace.css') }}?v={{ filemtime(public_path('css/revrace.css')) }}">
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
    <nav class="site-nav">
        <a class="brand" href="{{ route('home') }}">REV<span>RACE</span></a>
        <div class="nav-links">
            <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">Home</a>
            <a class="nav-link @if(request()->routeIs('wizard.*')) active @endif" href="{{ route('wizard.index') }}">Welke motor past bij mij</a>
            <a class="nav-link @if(request()->routeIs('simulation.*')) active @endif" href="{{ route('simulation.index') }}">Simulatie</a>
            <a class="nav-link @if(request()->routeIs('partners.index')) active @endif" href="{{ route('partners.index') }}">Partners</a>
            <a class="nav-link @if(request()->routeIs('how-it-works')) active @endif" href="{{ route('how-it-works') }}">Hoe het werkt</a>
            <a class="nav-link @if(request()->routeIs('kennis.*')) active @endif" href="{{ route('kennis.index') }}">Kennis</a>
            <a class="nav-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}">Over ons</a>
            @auth
                <a class="nav-link @if(request()->routeIs('garage.*')) active @endif" href="{{ route('garage.index') }}">Garage</a>
                <a class="nav-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}">Mijn account</a>
            @endauth
        </div>
        <div class="nav-actions">
            @auth
                <span class="small">{{ Str::upper(Str::limit(auth()->user()->name, 12, '')) }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-button" type="submit">Uitloggen</button>
                </form>
            @else
                <a class="nav-link" href="{{ route('login') }}">Inloggen</a>
                <a class="nav-link nav-cta" href="{{ route('register') }}">Account aanmaken</a>
            @endauth
        </div>
        <button class="nav-burger" type="button" data-menu-open aria-label="Menu openen">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
        </button>
    </nav>

    <div class="mobile-menu" data-mobile-menu>
        <div class="mobile-menu-head">
            <span class="brand">REV<span>RACE</span></span>
            <button class="mobile-menu-close" type="button" data-menu-close aria-label="Menu sluiten">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
        <div class="mobile-menu-links">
            <a class="mobile-menu-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}"><span>Home</span><span class="num">01</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('wizard.*')) active @endif" href="{{ route('wizard.index') }}"><span>Welke motor past bij mij</span><span class="num">02</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('simulation.*')) active @endif" href="{{ route('simulation.index') }}"><span>Simulatie</span><span class="num">03</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('partners.index')) active @endif" href="{{ route('partners.index') }}"><span>Partners</span><span class="num">04</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('how-it-works')) active @endif" href="{{ route('how-it-works') }}"><span>Hoe het werkt</span><span class="num">05</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('kennis.*')) active @endif" href="{{ route('kennis.index') }}"><span>Kennis</span><span class="num">06</span></a>
            <a class="mobile-menu-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}"><span>Over ons</span><span class="num">07</span></a>
            @auth
                <a class="mobile-menu-link @if(request()->routeIs('garage.*')) active @endif" href="{{ route('garage.index') }}"><span>Garage</span></a>
                <a class="mobile-menu-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}"><span>Mijn account</span></a>
            @endauth
        </div>
        <div class="mobile-menu-actions">
            @auth
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn secondary" style="width:100%" type="submit">Uitloggen</button>
                </form>
            @else
                <a class="btn primary" style="width:100%" href="{{ route('register') }}">Account aanmaken</a>
                <a class="btn secondary" style="width:100%" href="{{ route('login') }}">Inloggen</a>
            @endauth
        </div>
    </div>
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
    <footer class="footer">
        <div class="footer-inner">
            <span class="brand">REV<span>RACE</span></span>
            <span>
                <a href="{{ route('partners.apply') }}">Partner worden</a> ·
                <a href="{{ route('privacy') }}">Privacy</a> ·
                <a href="{{ route('contact') }}">Contact</a>
            </span>
            <span>© {{ date('Y') }} RevRace - www.rev-race.nl</span>
        </div>
    </footer>
@endunless

<script src="{{ asset('js/site.js') }}?v={{ filemtime(public_path('js/site.js')) }}"></script>
@stack('scripts')
</body>
</html>
