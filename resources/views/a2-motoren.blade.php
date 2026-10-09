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
    <header class="chapter">
        <div class="wrap">
            <span class="eyebrow">A2-rijbewijs</span>
            <h1>A2 motoren: alle geschikte modellen</h1>
            <p class="lede">
                {{ $motors->count() }} motoren in de database voldoen aan de Europese A2-eisen: maximaal 35 kW
                (circa 47 pk) vermogen en een verhouding van vermogen tot gewicht van niet meer dan 0,20 kW per kilo.
                Twijfel je welke bij jouw rijstijl past? De <a class="accent" href="{{ route('wizard.index') }}">wizard</a>
                filtert hier automatisch op.
            </p>
        </div>
    </header>

    <section class="chapter chapter--dark">
        <div class="wrap credibility">
            <div class="pass">
                <div class="pass__row"><span>Max. vermogen</span><b>35 kW (~47 pk)</b></div>
                <div class="pass__row"><span>Max. vermogen/gewicht</span><b>0,20 kW per kilo</b></div>
                <div class="pass__row"><span>Rijbewijscategorie</span><b>A2</b></div>
                <div class="pass__row"><span>Geldig voor</span><b>Elke cilinderinhoud</b></div>
            </div>
            <div>
                <p class="eyebrow">De A2-regel</p>
                <h2>Twee harde grenzen, niet de cilinderinhoud</h2>
                <p>De Europese A2-norm kijkt niet naar cc&rsquo;s, maar naar twee simpele grenzen: maximaal 35 kW vermogen, en een verhouding van vermogen tot gewicht van niet meer dan 0,20 kW per kilo. Een motor met een groot blok maar veel gewicht kan dus alsnog A2-geschikt zijn.</p>
                <p>RevRace rekent dit voor elk model in de database automatisch uit &mdash; inclusief de {{ $motors->count() }} modellen hieronder, gegroepeerd per segment.</p>
            </div>
        </div>
    </section>

    @foreach($byCategory as $categorie => $categorieMotors)
        <section class="chapter chapter--tight" id="{{ $categorie }}">
            <div class="wrap">
                <div class="seg-head">
                    <div>
                        <span class="eyebrow">Segment</span>
                        <h2>{{ \App\Models\Motor::CATEGORIES[$categorie] ?? $categorie }}</h2>
                        <p class="lede">
                            Op zoek naar een A2 {{ \App\Http\Controllers\SegmentController::BUYING_LABEL[$categorie] ?? Str::lower(\App\Models\Motor::CATEGORIES[$categorie] ?? $categorie) }}?
                            {{ \App\Http\Controllers\SegmentController::DESCRIPTIONS[$categorie] ?? '' }}
                            {{ $categorieMotors->count() }} {{ $categorieMotors->count() === 1 ? 'A2-model' : 'A2-modellen' }} in de database.
                        </p>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('segments.show', $categorie) }}">Alle {{ Str::lower(\App\Models\Motor::CATEGORIES[$categorie] ?? $categorie) }}-modellen</a>
                </div>
                <div class="model-list model-list--compact">
                    <div class="model-row model-row--head">
                        <span>Model</span><span>PK</span><span>KG</span><span>PK/KG</span>
                    </div>
                    @foreach($categorieMotors as $motor)
                        <div class="model-row">
                            <div class="model-row__name">{{ $motor->model }} <span>&mdash; {{ $motor->year }}</span></div>
                            <div class="model-row__num model-row__num--strong">{{ $motor->power_hp }}</div>
                            <div class="model-row__num">{{ $motor->weight_kg }}</div>
                            <div class="model-row__num">{{ number_format($motor->powerToWeight(), 2, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endforeach

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">Volgende stap</p>
            <h2>Wil je weten welke van deze motoren het beste bij jouw rijstijl past?</h2>
            <a class="btn btn--primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
        </div>
    </section>
@endsection
