@extends('layouts.app')

@section('title', 'Simulatie - RevRace')
@section('description', 'Vergelijk twee motoren met server-side racefysica en deel het resultaat.')

@section('content')
    <header>
        <h1 class="page-title">Simulatie</h1>
        <p class="page-sub">Zoek twee motoren, kies wegtype en conditie, en laat Laravel de race berekenen en opslaan.</p>
    </header>

    @include('partials.simulation-panel')
@endsection
