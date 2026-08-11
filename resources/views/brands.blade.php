@extends('layouts.app')

@section('title', 'Alle motormerken - RevRace')
@section('description', 'Overzicht van alle motormerken in de RevRace-database, met alle modellen en vergelijkingen per merk.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Merken'],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap">
            <span class="eyebrow">Merken</span>
            <h1>Alle motormerken</h1>
            <p class="lede">{{ $brands->count() }} merken, {{ $brands->sum('count') }} motoren in totaal &mdash; kies een merk voor alle modellen en vergelijkingen.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="kb-grid">
                @foreach($brands as $brand)
                    <a class="kb-card" href="{{ route('brands.show', $brand['slug']) }}">
                        <p class="kb-card__stage">{{ $brand['count'] }} {{ $brand['count'] === 1 ? 'model' : 'modellen' }}</p>
                        <h3>{{ $brand['brand'] }}</h3>
                        <p>Bekijk alle {{ $brand['brand'] }}-modellen, specificaties en vergelijkingen.</p>
                        <span class="kb-card__link">Bekijk merk &rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">Twijfel je?</p>
            <h2>Race twee modellen tegen elkaar op droog, vochtig en nat asfalt.</h2>
            <p class="lede">Dezelfde twee motoren, soms een andere winnaar &mdash; grip verandert de uitslag volledig.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie</a>
        </div>
    </section>
@endsection
