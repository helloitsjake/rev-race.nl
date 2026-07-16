@extends('layouts.app')

@section('title', 'Simulatie - RevRace')
@section('description', 'Vergelijk twee motoren met server-side racefysica en deel het resultaat.')

@section('content')
    <header>
        <span class="eyebrow">Simulatie</span>
        <h1 class="page-title">Motor A vs. Motor B</h1>
        <p class="page-sub">Zoek twee motoren, kies wegtype en conditie, en laat de server de race berekenen.</p>
    </header>

    @include('partials.simulation-panel')
@endsection
