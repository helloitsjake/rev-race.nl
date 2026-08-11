@extends('layouts.app')

@section('title', $config['title'] . ' - RevRace')
@section('description', $config['description'])

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Simulatie', 'item' => route('simulation.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title']],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a> <span class="sep">&rarr;</span>
                <a href="{{ route('simulation.index') }}">Simulatie</a> <span class="sep">&rarr;</span>
                {{ $config['title'] }}
            </nav>
            <span class="eyebrow">Toplijst</span>
            <h1>{{ $config['title'] }}</h1>
            <p class="lede">{{ $config['description'] }}</p>
        </div>
    </header>

    <section class="chapter--dark">
        <div class="wrap" style="padding-block: clamp(48px, 6vw, 88px)">
            <div class="rank-list">
                @foreach($rows as $i => $row)
                    <div class="top-row @if($i === 0) top-row--first @endif">
                        <span class="top-row__pos">#{{ $i + 1 }}</span>
                        <div class="top-row__name">{{ $row['motor']->brand }} {{ $row['motor']->model }} <span>{{ $row['motor']->year }}</span></div>
                        <span class="top-row__metric">{{ ($config['format'])($row['value']) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight" id="doorrekenen">
        <div class="wrap">
            <span class="eyebrow">Zelf doorrekenen</span>
            <h2>Wil je twee van deze motoren tegen elkaar laten racen?</h2>
            <p class="lede" style="margin-top: 0.6em; margin-bottom: 1.8em">Eén cijfer geeft nooit het volledige beeld. Vermogen, gewicht en wegconditie samen geven het echte antwoord.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie</a>
        </div>
    </section>
@endsection
