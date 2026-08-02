@extends('layouts.app')

@section('title', 'Meest gezochte motoren - RevRace')
@section('description', 'De populairste motoren op RevRace van de afgelopen 30 dagen, gebaseerd op echte simulaties door bezoekers.')

@section('content')
    <header>
        <span class="eyebrow">Populair</span>
        <h1 class="page-title">Meest gezochte motoren</h1>
        <p class="page-sub">Ranglijst op basis van simulaties door bezoekers, afgelopen 30 dagen.</p>
    </header>

    @if($ranking->isEmpty())
        <div class="panel">
            <p>Er zijn de afgelopen 30 dagen nog geen simulaties gedraaid. Kom later terug, of <a class="accent" href="{{ route('simulation.index') }}">start zelf een race</a>.</p>
        </div>
    @else
        <div class="most-searched-list">
            @php $partnerIndex = 0; @endphp
            @foreach($ranking as $i => $row)
                <div class="card most-searched-row">
                    <span class="card-num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="most-searched-row-body">
                        <h2 class="card-title">{{ $row['motor']->label() }}</h2>
                        <p class="section-sub" style="margin-bottom:0">{{ $row['motor']->power_hp }} pk · {{ $row['motor']->weight_kg }} kg · {{ $row['motor']->engine_type }}</p>
                    </div>
                    <div class="most-searched-row-meta">
                        <span class="badge">{{ $row['uses'] }}&times; gesimuleerd</span>
                        <a class="btn secondary" href="{{ route('simulation.index', ['motor_a' => $row['motor']->id]) }}">Simuleer</a>
                    </div>
                </div>

                @if($partners->isNotEmpty() && ($i + 1) % 4 === 0)
                    @php $partner = $partners[$partnerIndex % $partners->count()]; $partnerIndex++; @endphp
                    <div class="card partner-banner">
                        <span class="badge partner-banner-label">Partner</span>
                        <div class="photo-placeholder photo-placeholder-sm">Logo</div>
                        <div class="partner-banner-body">
                            <h3 class="card-title">{{ $partner->name }}</h3>
                            <p class="section-sub" style="margin-bottom:0">{{ $partner->description }}</p>
                        </div>
                        <a class="btn primary" href="{{ route('partners.show', $partner) }}">Bekijk partner</a>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="band-dark full-bleed">
        <div class="full-bleed-inner" style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px">
            <div>
                <span class="eyebrow" style="color:var(--orange)">Voor bedrijven</span>
                <h2 class="section-title" style="font-size:clamp(24px,3.6vw,34px)">Zichtbaar tussen de populairste motoren</h2>
                <p style="margin:8px 0 0;max-width:620px">Als partner van RevRace kun je met een eigen banner zichtbaar zijn tussen de meest gezochte motoren van de maand, precies op het moment dat bezoekers actief aan het vergelijken zijn.</p>
            </div>
            <a class="btn primary" href="{{ route('partners.apply') }}">Word partner</a>
        </div>
    </div>
@endsection
