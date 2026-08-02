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
    <header>
        <span class="eyebrow">Segmenten</span>
        <h1 class="page-title">Welk type motor past bij welke rijstijl?</h1>
        <p class="page-sub">Twijfel je nog welk segment bij je past? De <a class="accent" href="{{ route('wizard.index') }}">wizard</a> vertaalt je rijstijl direct naar een advies.</p>
    </header>

    <div class="card-grid">
        @foreach($segments as $segment)
            <a class="card" href="{{ route('segments.show', $segment['key']) }}" style="display:block">
                <h2 class="card-title">{{ $segment['label'] }}</h2>
                <p class="section-sub">{{ $segment['description'] }}</p>
                <p class="small" style="color:var(--dim);margin-top:6px">{{ $segment['count'] }} {{ $segment['count'] === 1 ? 'model' : 'modellen' }}</p>
            </a>
        @endforeach
    </div>
@endsection
