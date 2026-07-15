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
    <link rel="stylesheet" href="{{ asset('css/revrace.css') }}?v={{ filemtime(public_path('css/revrace.css')) }}">
</head>
<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NFH7Z6V5"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@unless($embedded ?? false)
    <nav class="site-nav">
        <a class="brand" href="{{ route('home') }}">REV<span>RACE</span></a>
        <div class="nav-links">
            <a class="nav-link @if(request()->routeIs('simulation.*')) active @endif" href="{{ route('simulation.index') }}">Simulatie</a>
            <a class="nav-link @if(request()->routeIs('partners.index')) active @endif" href="{{ route('partners.index') }}">Partners</a>
            <a class="nav-link @if(request()->routeIs('how-it-works')) active @endif" href="{{ route('how-it-works') }}">Hoe het werkt</a>
            <a class="nav-link @if(request()->routeIs('about')) active @endif" href="{{ route('about') }}">Over ons</a>
            @auth
                <a class="nav-link @if(request()->routeIs('garage.*')) active @endif" href="{{ route('garage.index') }}">Garage</a>
                <a class="nav-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}">Mijn account</a>
            @endauth
        </div>
        <div class="nav-actions">
            @auth
                <span class="small">{{ auth()->user()->name }}</span>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="nav-button" type="submit">Uitloggen</button>
                </form>
            @else
                <a class="nav-link" href="{{ route('login') }}">Inloggen</a>
                <a class="nav-link nav-cta" href="{{ route('register') }}">Account</a>
            @endauth
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
    <footer class="footer">
        <div class="footer-inner">
            <span>© {{ date('Y') }} RevRace - www.rev-race.nl</span>
            <span>
                <a href="{{ route('partners.apply') }}">Partner worden</a> ·
                <a href="{{ route('privacy') }}">Privacy</a> ·
                <a href="{{ route('contact') }}">Contact</a> ·
                <a href="{{ route('sitemap') }}">Sitemap</a>
            </span>
        </div>
    </footer>
@endunless

@stack('scripts')
</body>
</html>
