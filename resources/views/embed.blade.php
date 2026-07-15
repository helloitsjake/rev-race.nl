@extends('layouts.app', ['embedded' => true])

@section('title', 'RevRace embed')

@section('content')
    <header style="margin-bottom:18px">
        <div class="brand">REV<span>RACE</span></div>
        <p class="page-sub">Ingesloten motorsimulatie</p>
    </header>

    @include('partials.simulation-panel')
@endsection
