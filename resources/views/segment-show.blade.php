@extends('layouts.app')

@section('title', 'Beste ' . ($buyingLabel ?? Str::lower($label)) . ': alle modellen op een rij - RevRace')
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
    <header class="chapter">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('segments.index') }}">Segmenten</a><span class="sep">&rarr;</span>
                <span>{{ $label }}</span>
            </nav>
            <span class="eyebrow">Segment</span>
            <h1>{{ $label }}</h1>
            <p class="lede">{{ $description }} {{ $motors->count() }} {{ $motors->count() === 1 ? 'model' : 'modellen' }} in de database, hieronder gesorteerd op vermogen/gewicht.</p>
            @if($buyingIntro)
                <p class="lede">{!! str_replace(
                    ':wizard',
                    '<a class="accent" href="' . route('wizard.index') . '">wizard</a>',
                    $buyingIntro
                ) !!}</p>
            @endif
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="model-list model-list--compact">
                <div class="model-list__title">Alle {{ Str::lower($label) }}-modellen, gesorteerd op pk/kg</div>
                <div class="model-row model-row--head">
                    <span>Model</span><span>PK</span><span>KG</span><span>A2</span>
                </div>
                @foreach($motors as $motor)
                    <div class="model-row">
                        <div class="model-row__name">{{ $motor->model }} <span>&mdash; {{ $motor->year }}</span></div>
                        <div class="model-row__num model-row__num--strong">{{ $motor->power_hp }}</div>
                        <div class="model-row__num">{{ $motor->weight_kg }}</div>
                        <div class="model-row__tag">{{ $motor->isA2Eligible() ? 'A2' : '' }}</div>
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
                        <h2>Populaire {{ Str::lower($label) }}-vergelijkingen</h2>
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
            <p class="eyebrow">Nog niet zeker?</p>
            <h2>Nog niet zeker of {{ Str::lower($label) }} bij je past?</h2>
            <a class="btn btn--primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
        </div>
    </section>
@endsection
