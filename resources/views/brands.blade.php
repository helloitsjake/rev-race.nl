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
    <header>
        <span class="eyebrow">Merken</span>
        <h1 class="page-title">Alle motormerken</h1>
        <p class="page-sub">{{ $brands->count() }} merken, {{ $brands->sum('count') }} motoren in totaal. Kies een merk voor alle modellen en vergelijkingen.</p>
    </header>

    <div class="card-grid">
        @foreach($brands as $brand)
            <a class="card" href="{{ route('brands.show', $brand['slug']) }}" style="display:block">
                <h2 class="card-title">{{ $brand['brand'] }}</h2>
                <p class="section-sub" style="margin-bottom:0">{{ $brand['count'] }} {{ $brand['count'] === 1 ? 'model' : 'modellen' }}</p>
            </a>
        @endforeach
    </div>
@endsection
