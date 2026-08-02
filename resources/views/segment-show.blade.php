@extends('layouts.app')

@section('title', $label . ': alle modellen en vergelijkingen - RevRace')
@section('description', $description ?? ($label . ' motoren vergelijken op vermogen, gewicht en simulatieresultaten.'))

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Segmenten', 'item' => route('segments.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $label],
    ],
]) !!}
</script>
@endpush

@section('content')
    <nav class="small" aria-label="Broodkruimel">
        <a href="{{ route('home') }}">Home</a> &rarr;
        <a href="{{ route('segments.index') }}">Segmenten</a> &rarr;
        {{ $label }}
    </nav>

    <header style="margin-top:12px">
        <span class="eyebrow">Segment</span>
        <h1 class="page-title">{{ $label }}</h1>
        <p class="page-sub">{{ $description }}</p>
    </header>

    <section class="panel">
        <h2 class="card-title" style="margin-bottom:12px">Alle {{ Str::lower($label) }}-modellen, gesorteerd op vermogen/gewicht</h2>
        @foreach($motors as $motor)
            <div class="compare-row" style="grid-template-columns:1fr auto auto;align-items:center">
                <span class="spec-value" style="font-weight:700">{{ $motor->label() }}</span>
                <span class="spec-value" style="color:var(--dim)">{{ $motor->power_hp }} pk / {{ $motor->weight_kg }} kg</span>
                @if($motor->isA2Eligible())
                    <span class="badge">A2</span>
                @else
                    <span></span>
                @endif
            </div>
        @endforeach
    </section>

    @if($comparisons->isNotEmpty())
        <section class="section">
            <div class="chart-head" style="margin-bottom:6px">
                <div>
                    <span class="eyebrow">Vergelijkingen</span>
                    <h2 class="section-title">Populaire {{ Str::lower($label) }}-vergelijkingen</h2>
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
        <p class="section-sub">Nog niet zeker of {{ Str::lower($label) }} bij je past?</p>
        <a class="btn primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
    </section>
@endsection
