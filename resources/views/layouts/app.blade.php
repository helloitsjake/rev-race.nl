<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'RevRace - Motorsimulatie')</title>
    <meta name="description" content="@yield('description', 'Vergelijk motoren met een server-side fysica-simulatie op droog, vochtig en nat asfalt.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="stylesheet" href="{{ asset('css/revrace.css') }}">
</head>
<body>
@unless($embedded ?? false)
    <nav class="site-nav">
        <a class="brand" href="{{ route('home') }}">REV<span>RACE</span></a>
        <div class="nav-links">
            <a class="nav-link @if(request()->routeIs('simulation.*')) active @endif" href="{{ route('simulation.index') }}">Simulatie</a>
            <a class="nav-link @if(request()->routeIs('partners.*')) active @endif" href="{{ route('partners.index') }}">Partners</a>
            @auth
                <a class="nav-link @if(request()->routeIs('garage.*')) active @endif" href="{{ route('garage.index') }}">Garage</a>
                <a class="nav-link @if(request()->routeIs('profile.*')) active @endif" href="{{ route('profile.edit') }}">Profiel</a>
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
