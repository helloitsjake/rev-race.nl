@extends('layouts.app')

@section('title', 'A2 motoren: alle geschikte modellen op een rij - RevRace')
@section('description', 'Alle motoren in de RevRace-database die voldoen aan de Europese A2-eisen: maximaal 35 kW en maximaal 0,20 kW per kilo.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'A2 motoren'],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header>
        <span class="eyebrow">A2-rijbewijs</span>
        <h1 class="page-title">A2 motoren: alle geschikte modellen</h1>
        <p class="page-sub">
            {{ $motors->count() }} motoren in de database voldoen aan de Europese A2-eisen: maximaal 35 kW
            (circa 47 pk) vermogen en een verhouding van vermogen tot gewicht van niet meer dan 0,20 kW per kilo.
            Twijfel je welke bij jouw rijstijl past? De <a class="accent" href="{{ route('wizard.index') }}">wizard</a>
            filtert hier automatisch op.
        </p>
    </header>

    @foreach($byCategory as $categorie => $categorieMotors)
        <section class="section">
            <div class="chart-head" style="margin-bottom:6px">
                <div>
                    <span class="eyebrow">Segment</span>
                    <h2 class="section-title">{{ \App\Models\Motor::CATEGORIES[$categorie] ?? $categorie }}</h2>
                </div>
                <a class="btn secondary" href="{{ route('segments.show', $categorie) }}">Alle {{ Str::lower(\App\Models\Motor::CATEGORIES[$categorie] ?? $categorie) }}</a>
            </div>
            <div class="panel">
                @foreach($categorieMotors as $motor)
                    <div class="compare-row" style="grid-template-columns:1fr auto;align-items:center">
                        <span class="spec-value" style="font-weight:700">{{ $motor->label() }}</span>
                        <span class="spec-value" style="color:var(--orange)">{{ $motor->power_hp }} pk / {{ $motor->weight_kg }} kg</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

    <section class="section" style="text-align:center">
        <p class="section-sub">Wil je weten welke van deze motoren het beste bij jouw rijstijl past?</p>
        <a class="btn primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
    </section>
@endsection
