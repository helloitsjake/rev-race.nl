@extends('layouts.app')

@section('title', 'Motorsegmenten: naked, sport, tourer, adventure, cruiser en retro - RevRace')
@section('description', 'Alle motorsegmenten op een rij: welk type motor past bij welke rijstijl, met alle modellen en vergelijkingen per segment.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Segmenten'],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap">
            <span class="eyebrow">Segmenten</span>
            <h1>Welk type motor past bij welke rijstijl?</h1>
            <p class="lede">Twijfel je nog welk segment bij je past? De <a class="accent" href="{{ route('wizard.index') }}">wizard</a> vertaalt je rijstijl direct naar een advies.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="kb-grid">
                @foreach($segments as $segment)
                    <a class="kb-card" href="{{ route('segments.show', $segment['key']) }}">
                        <p class="kb-card__stage">{{ $segment['count'] }} {{ $segment['count'] === 1 ? 'model' : 'modellen' }}</p>
                        <h3>{{ $segment['label'] }}</h3>
                        @if($segment['description'])
                            <p>{{ $segment['description'] }}</p>
                        @endif
                        <span class="kb-card__link">Bekijk segment &rarr;</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">A2-rijbewijs</p>
            <h2>Rijd je met een A2-rijbewijs?</h2>
            <p class="lede">Bekijk de motoren die aan de A2-eisen voldoen, per segment op een rij.</p>
            <a class="btn btn--primary" href="{{ route('a2-motoren') }}">Bekijk A2-motoren</a>
        </div>
    </section>
@endsection
