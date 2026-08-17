@extends('layouts.app')

@section('title', 'Simulatie - RevRace')
@section('description', 'Vergelijk twee motoren met een server-side racesimulatie op droog, vochtig en nat asfalt, en deel het resultaat met anderen.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Simulatie</span>
            <h1>Motor A vs. Motor B</h1>
            <p class="lede">Zoek twee motoren, kies wegtype en conditie, en laat de server de race berekenen.</p>
        </div>
    </header>

    @include('partials.simulation-panel')
@endsection
