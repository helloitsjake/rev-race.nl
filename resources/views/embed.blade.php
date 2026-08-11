@extends('layouts.app', ['embedded' => true])

@section('title', 'RevRace embed')

@section('content')
    <header class="chapter chapter--tight" style="padding-bottom:0">
        <div class="wrap">
            <a class="nav__logo" href="{{ route('home') }}">@include('partials.brand-icon')Rev<span>Race</span></a>
            <p class="lede" style="margin-top:0.4em">Ingesloten motorsimulatie</p>
        </div>
    </header>

    @include('partials.simulation-panel')
@endsection
