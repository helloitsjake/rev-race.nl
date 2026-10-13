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

@push('scripts')
<script src="{{ asset('js/overzicht.js') }}?v={{ filemtime(public_path('js/overzicht.js')) }}" defer></script>
@endpush

@section('content')
    @php
        $max = $brands->max('count');
        $cats = \App\Models\Motor::CATEGORIES;
        $byCount = $brands->sortByDesc('count')->values();
    @endphp
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <h1>Alle motormerken</h1>
            <p class="lede">{{ $brands->count() }} merken, {{ $brands->sum('count') }} motoren. Kies een segment en zie welke merken daar het sterkst in zijn, of open een merk voor alle modellen en vergelijkingen.</p>
        </div>
    </header>

    <section class="chapter chapter--tight brands-wrap">
        <div class="wrap" data-brandgrid>
            <div class="brands__controls">
                <div class="filter-scroll">
                    <div class="filter" role="group" aria-label="Segment" data-key="seg">
                        <div class="filter__base">
                            <button type="button" data-value="all" aria-pressed="true">Alle segmenten</button>
                            @foreach($segments as $segment)
                                <button type="button" data-value="{{ $segment['key'] }}" aria-pressed="false">{{ $segment['label'] }}</button>
                            @endforeach
                        </div>
                        <div class="filter__pill" aria-hidden="true"></div>
                    </div>
                </div>
                <div class="filter filter--small" role="group" aria-label="Volgorde" data-key="sort">
                    <div class="filter__base">
                        <button type="button" data-value="count" aria-pressed="true">Meeste modellen</button>
                        <button type="button" data-value="az" aria-pressed="false">A tot Z</button>
                    </div>
                    <div class="filter__pill" aria-hidden="true"></div>
                </div>
            </div>
            <p class="brands__status" aria-live="polite" data-status>{{ $brands->count() }} merken</p>

            <ol class="brands" data-inview>
                @foreach($byCount as $i => $brand)
                    @php
                        $top = collect($brand['segments'])->take(3)->map(fn ($n, $c) => $n . ' ' . Str::lower($cats[$c] ?? $c))->implode(' · ');
                    @endphp
                    <li class="brand-tile" data-brand="{{ $brand['brand'] }}" data-total="{{ $brand['count'] }}" data-segs='@json($brand['segments'])' style="--i: {{ min($i, 14) }}">
                        <a href="{{ route('brands.show', $brand['slug']) }}">
                            <span class="brand-tile__pos">{{ $i + 1 }}</span>
                            <h2 class="brand-tile__name">{{ $brand['brand'] }}</h2>
                            <span class="brand-tile__num"><b>{{ $brand['count'] }}</b> <small>{{ $brand['count'] === 1 ? 'model' : 'modellen' }}</small></span>
                            <span class="meter" aria-hidden="true"><i style="--w: {{ round($brand['count'] / $max, 3) }}"></i></span>
                            <span class="brand-tile__mix">{{ $top }}</span>
                            <span class="brand-tile__go" aria-hidden="true"><svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h12M10 4l5 5-5 5"/></svg></span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Twijfel je tussen twee merken? Laat ze racen.</h2>
            <p class="lede">Op droog, vochtig en nat asfalt. Dezelfde twee motoren, soms een andere winnaar.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie</a>
        </div>
    </section>
@endsection
