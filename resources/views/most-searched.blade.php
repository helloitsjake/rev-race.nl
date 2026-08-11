@extends('layouts.app')

@section('title', 'Meest gezochte motoren - RevRace')
@section('description', 'De populairste motoren op RevRace van de afgelopen 30 dagen, gebaseerd op echte simulaties door bezoekers.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Populair</span>
            <h1>Meest gezochte motoren</h1>
            <p class="lede">Ranglijst op basis van simulaties door bezoekers, afgelopen 30 dagen.</p>
        </div>
    </header>

    @if($ranking->isEmpty())
        <section class="chapter chapter--tight">
            <div class="wrap">
                <div class="panel">
                    <p>Er zijn de afgelopen 30 dagen nog geen simulaties gedraaid. Kom later terug, of <a class="accent" href="{{ route('simulation.index') }}" style="color:var(--midrange)">start zelf een race</a>.</p>
                </div>
            </div>
        </section>
    @else
        <section class="chapter chapter--dark">
            <div class="wrap">
                <div class="rank-list">
                    @php $partnerIndex = 0; @endphp
                    @foreach($ranking as $i => $row)
                        <div class="rank-row">
                            <div class="rank-row__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                            <div>
                                <div class="rank-row__name">{{ $row['motor']->label() }}</div>
                                <div class="rank-row__meta">{{ $row['motor']->power_hp }} pk &middot; {{ $row['motor']->weight_kg }} kg &middot; {{ $row['motor']->engine_type }}</div>
                            </div>
                            <div>
                                <span class="rank-row__count">{{ $row['uses'] }}&times; gesimuleerd</span>
                                <div class="rank-row__ratio">{{ number_format($row['motor']->powerToWeight(), 3) }} pk/kg</div>
                            </div>
                            <a class="btn btn--ghost" href="{{ route('simulation.index', ['motor_a' => $row['motor']->id]) }}">Simuleer</a>
                        </div>

                        @if($partners->isNotEmpty() && ($i + 1) % 4 === 0)
                            @php $partner = $partners[$partnerIndex % $partners->count()]; $partnerIndex++; @endphp
                            <div class="partner-banner">
                                <span class="partner-banner__tag">Partner</span>
                                <div class="partner-banner__body">
                                    <h3>{{ $partner->name }}</h3>
                                    <p>{{ $partner->description }}</p>
                                </div>
                                <a class="btn btn--ghost" href="{{ route('partners.show', $partner) }}">Bekijk partner</a>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="chapter">
        <div class="wrap">
            <span class="eyebrow">Voor bedrijven</span>
            <h2>Zichtbaar tussen de populairste motoren</h2>
            <p class="lede" style="margin-bottom:1.6em">Als partner van RevRace kun je met een eigen banner zichtbaar zijn tussen de meest gezochte motoren van de maand, precies op het moment dat bezoekers actief aan het vergelijken zijn.</p>
            <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner</a>
        </div>
    </section>
@endsection
