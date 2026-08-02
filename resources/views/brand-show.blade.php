@extends('layouts.app')

@section('title', 'Alle ' . $brand . ' modellen en vergelijkingen - RevRace')
@section('description', 'Alle ' . $brand . ' motoren in de RevRace-database, met specificaties en vergelijkingen tegen andere modellen.')

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
    <nav class="small" aria-label="Broodkruimel">
        <a href="{{ route('home') }}">Home</a> &rarr;
        <a href="{{ route('brands.index') }}">Merken</a> &rarr;
        {{ $brand }}
    </nav>

    <header style="margin-top:12px">
        <span class="eyebrow">Merk</span>
        <h1 class="page-title">{{ $brand }}</h1>
        <p class="page-sub">{{ $motors->count() }} {{ $motors->count() === 1 ? 'model' : 'modellen' }} van {{ $brand }} in de RevRace-database.</p>
    </header>

    <section class="panel">
        <h2 class="card-title" style="margin-bottom:12px">Alle modellen</h2>
        @foreach($motors as $motor)
            <div class="compare-row" style="grid-template-columns:1fr auto auto;align-items:center">
                <span class="spec-value" style="font-weight:700">{{ $motor->label() }}</span>
                <span class="spec-value" style="color:var(--dim)">{{ $motor->categoryLabel() }}</span>
                <span class="spec-value" style="color:var(--orange)">{{ $motor->power_hp }} pk</span>
            </div>
        @endforeach
    </section>

    @if($comparisons->isNotEmpty())
        <section class="section">
            <div class="chart-head" style="margin-bottom:6px">
                <div>
                    <span class="eyebrow">Vergelijkingen</span>
                    <h2 class="section-title">{{ $brand }} tegen de concurrentie</h2>
                </div>
            </div>
            <div class="card-grid">
                @foreach($comparisons as $row)
                    <a class="card" href="{{ route('compare.show', $row['slug']) }}" style="display:block">
                        <h3 class="card-title" style="font-size:16px">{{ $row['motorA']->label() }}</h3>
                        <p class="section-sub" style="margin-bottom:0">vs {{ $row['motorB']->label() }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section" style="text-align:center">
        <p class="section-sub">Twijfel je tussen twee modellen? Race ze tegen elkaar op droog, vochtig en nat asfalt.</p>
        <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
    </section>
@endsection
