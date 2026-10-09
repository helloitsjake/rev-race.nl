@extends('layouts.app')

@section('title', 'Alle ' . $brand . ' modellen en vergelijkingen - RevRace')
{{--
    Model-aantal in de tekst i.p.v. een vaste zin: bij korte merknamen (BMW, KTM, SWM) kwam de
    vaste tekst onder Ahrefs' "meta description too short"-grens, bij langere merknamen net
    erover. Met het aantal erin is de lengte niet meer afhankelijk van de merknaamlengte.
--}}
@section('description', 'Alle ' . $motors->count() . ' ' . $brand . '-modellen in de RevRace-database, met specificaties en directe vergelijkingen tegen andere motoren.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Merken', 'item' => route('brands.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $brand],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.index') }}">Merken</a><span class="sep">&rarr;</span>
                <span>{{ $brand }}</span>
            </nav>
            <span class="eyebrow">Merk</span>
            <h1>{{ $brand }}</h1>
            <p class="lede">{{ $motors->count() }} {{ $motors->count() === 1 ? 'model' : 'modellen' }} van {{ $brand }} in de RevRace-database.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="model-list">
                <div class="model-list__title">Alle {{ $brand }}-modellen</div>
                <div class="model-row model-row--head">
                    <span>Model</span><span>Categorie</span><span>PK</span><span>KG</span><span>PK/KG</span>
                </div>
                @foreach($motors as $motor)
                    <div class="model-row">
                        <div class="model-row__name"><a href="{{ route('brands.model', [$slug, $motor->slug()]) }}">{{ $motor->model }}</a> <span>&mdash; {{ $motor->year }}</span></div>
                        <div class="model-row__cat">{{ $motor->categoryLabel() }}</div>
                        <div class="model-row__num model-row__num--strong">{{ $motor->power_hp }}</div>
                        <div class="model-row__num">{{ $motor->weight_kg }}</div>
                        <div class="model-row__num">{{ number_format($motor->powerToWeight(), 2, ',', '.') }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($comparisons->isNotEmpty())
        <section class="chapter">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <span class="eyebrow">Vergelijkingen</span>
                        <h2>{{ $brand }} tegen de concurrentie</h2>
                    </div>
                </div>
                <div class="kb-grid">
                    @foreach($comparisons as $row)
                        <a class="kb-card" href="{{ route('compare.show', $row['slug']) }}">
                            <h3>{{ $row['motorA']->label() }}</h3>
                            <p>vs {{ $row['motorB']->label() }}</p>
                            <span class="kb-card__link">Bekijk vergelijking &rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">Twijfel je?</p>
            <h2>Twijfel je tussen twee modellen? Race ze tegen elkaar.</h2>
            <p class="lede">Op droog, vochtig en nat asfalt &mdash; dezelfde twee motoren, soms een andere winnaar.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>
@endsection
