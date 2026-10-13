@extends('layouts.app')

@section('title', 'Meest gezochte motoren - RevRace')
@section('description', 'De populairste motoren op RevRace van de afgelopen 30 dagen, gebaseerd op echte simulaties door bezoekers.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <h1>Meest gezochte motoren</h1>
            <p class="lede">De motoren die bezoekers de afgelopen 30 dagen het vaakst in een race zetten. Elke simulatie telt voor beide motoren.</p>
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
        @php
            $maxUses = $ranking->max('uses');
            $fmt = fn ($n) => number_format($n, 2, ',', '.');
            $modelUrl = fn ($m) => route('brands.model', [\Illuminate\Support\Str::slug($m->brand), $m->slug()]);
            $podium = $ranking->take(3);
            $rest = $ranking->slice(3)->values();
        @endphp
        <section class="chapter chapter--dark chapter--tight ms">
            <div class="wrap">
                <ol class="podium" data-inview aria-label="Top 3">
                    @foreach($podium as $i => $row)
                        @php $m = $row['motor']; @endphp
                        <li class="podium__spot podium__spot--p{{ $i + 1 }}">
                            <div class="podium__card">
                                <span class="podium__bike">@include('partials.bike-icon', ['category' => $m->category])</span>
                                <h2 class="podium__name"><a href="{{ $modelUrl($m) }}">{{ $m->brand }} {{ $m->model }}</a> <small>{{ $m->year }}</small></h2>
                                <p class="podium__meta">{{ $m->power_hp }} pk &middot; {{ $m->weight_kg }} kg &middot; {{ $fmt($m->powerToWeight()) }} pk/kg</p>
                                <p class="podium__count"><b data-count="{{ $row['uses'] }}">{{ $row['uses'] }}</b>&times; gesimuleerd</p>
                                <a class="btn btn--primary" href="{{ route('simulation.index', ['motor_a' => $m->id]) }}">Race deze motor <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg></a>
                            </div>
                            <div class="podium__step" aria-hidden="true"><span>P{{ $i + 1 }}</span></div>
                        </li>
                    @endforeach
                </ol>

                @if($partners->isNotEmpty())
                    @include('partials.partner-spot', ['partner' => $partners->first(), 'source' => 'meest-gezocht'])
                @endif

                @if($rest->isNotEmpty())
                    <div class="rank-list rank-list--meter" data-inview>
                        @foreach($rest as $j => $row)
                            @php $m = $row['motor']; $pos = $j + 4; @endphp
                            <div class="rank-row" style="--d: {{ $j * 50 }}ms">
                                <div class="rank-row__num">{{ str_pad($pos, 2, '0', STR_PAD_LEFT) }}</div>
                                <div>
                                    <div class="rank-row__name"><a href="{{ $modelUrl($m) }}">{{ $m->label() }}</a></div>
                                    <div class="rank-row__meta">{{ $m->power_hp }} pk &middot; {{ $m->weight_kg }} kg &middot; {{ $fmt($m->powerToWeight()) }} pk/kg</div>
                                </div>
                                <div class="rank-row__uses">
                                    <span class="meter" aria-hidden="true"><i style="--w: {{ round($row['uses'] / $maxUses, 3) }}"></i></span>
                                    <span class="rank-row__count">{{ $row['uses'] }}&times; gesimuleerd</span>
                                </div>
                                <a class="btn btn--primary rank-row__go" href="{{ route('simulation.index', ['motor_a' => $m->id]) }}" aria-label="Race met de {{ $m->label() }}">Race <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg></a>
                            </div>

                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    <section class="chapter">
        <div class="wrap">
            <h2>Zichtbaar tussen de populairste motoren</h2>
            <p class="lede" style="margin-bottom:1.6em">Als partner van RevRace kun je met een eigen banner zichtbaar zijn tussen de meest gezochte motoren van de maand, precies op het moment dat bezoekers actief aan het vergelijken zijn.</p>
            <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner</a>
        </div>
    </section>
@endsection
