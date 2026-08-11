@extends('layouts.app')

@section('title', 'Simulatie - RevRace')
@section('description', 'Vergelijk twee motoren met server-side racefysica en deel het resultaat.')

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
