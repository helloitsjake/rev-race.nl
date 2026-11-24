@extends('layouts.app')

@php
    /*
     * 24-11-2026: FAQ met antwoorden uit de eigen database. "hoeveel cc met a2 rijbewijs" en
     * "a2 motor kiezen" hadden vertoningen maar stonden op positie 57 tot 89: de pagina gaf het
     * antwoord niet letterlijk.
     */
    $ccSorted = $motors->pluck('displacement_cc')->filter()->sort()->values();
    $a2Faq = collect([
        [
            'q' => 'Hoeveel cc mag je rijden met een A2-rijbewijs?',
            'a' => 'Voor A2 geldt geen grens in cc. De regels gaan over vermogen: maximaal 35 kW (ongeveer 47 pk) en maximaal 0,20 kW per kilo. In de praktijk hebben de ' . $motors->count() . ' A2-motoren in onze database tussen ' . $ccSorted->first() . ' en ' . $ccSorted->last() . ' cc, de helft zit onder ' . (int) $ccSorted->median() . ' cc.',
        ],
        [
            'q' => 'Mag ik met A2 een motor rijden die gedrosseld is?',
            'a' => 'Ja, zolang het originele vermogen niet hoger is dan 70 kW en de gedrosselde versie binnen 35 kW en 0,20 kW per kilo valt. De lijst op deze pagina toont alleen motoren die zonder drosselkit al binnen de grenzen vallen.',
        ],
        [
            'q' => 'Hoe kies je een A2-motor?',
            'a' => 'Begin bij het segment dat bij je rijstijl past (naked, sport, adventure, cruiser of retro), kijk daarna naar gewicht en zithoogte, en pas dan naar vermogen. Binnen A2 liggen de vermogens dicht bij elkaar; het gewicht verschilt meer en bepaalt hoe makkelijk de motor bij lage snelheid is.',
        ],
    ]);
@endphp

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

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => $a2Faq->map(fn (array $item) => [
        '@type' => 'Question',
        'name' => $item['q'],
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
    ])->all(),
]) !!}
</script>
<script src="{{ asset('js/overzicht.js') }}?v={{ filemtime(public_path('js/overzicht.js')) }}" defer></script>
@endpush

@section('content')
    @include('partials.page-photo', ['photo' => 'a2', 'widths' => [800, 1200], 'caption' => 'Jake bij zijn eerste rijlessen'])
    @php
        $segLabels = $byCategory->keys()->mapWithKeys(fn ($c) => [$c => \App\Models\Motor::CATEGORIES[$c] ?? $c])->all();
        $outside = $all->filter(fn ($m) => $m->power_hp > 80 || $m->weight_kg > 300)->count();
    @endphp
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <span>A2 motoren</span>
            </nav>
            <h1>A2 motoren: alle geschikte modellen</h1>
            <div class="seg-hero__body">
                <p class="lede">
                    {{ $motors->count() }} motoren in de database voldoen aan de Europese A2-eisen: maximaal 35 kW
                    (circa 47 pk) en niet meer dan 0,20 kW per kilo. Twijfel je welke bij jouw rijstijl past? De
                    <a class="accent" href="{{ route('wizard.index') }}">wizard</a> filtert hier automatisch op.
                </p>
                <dl class="seg-facts">
                    <div><dt>Max. vermogen</dt><dd>35 <small>kW</small></dd></div>
                    <div><dt>Max. per kilo</dt><dd>0,20 <small>kW/kg</small></dd></div>
                    <div><dt>Cilinderinhoud</dt><dd>vrij</dd></div>
                </dl>
            </div>
        </div>
    </header>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap">
            <div class="plot-head">
                <h2>Twee grenzen, niet de cilinderinhoud</h2>
                <p>Het groene vlak is wat je met A2 mag rijden. Een motor met een groot blok maar veel gewicht kan er dus gewoon in vallen. De grijze stippen zijn de andere motoren in de database; {{ $outside }} zwaardere of sterkere modellen vallen buiten beeld.</p>
            </div>
            @include('partials.spec-plot', ['motors' => $motors, 'context' => $all, 'zone' => 'a2', 'xMin' => 100, 'xMax' => 300, 'yMax' => 80])
            <div class="plot-legend">
                <span><i class="is-a2"></i>A2-geschikt</span>
                <span><i class="is-ghost"></i>Te sterk of te licht voor A2</span>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <h2 class="speclist__title">Alle {{ $motors->count() }} A2-motoren</h2>
            @include('partials.spec-list', ['motors' => $motors, 'limit' => 15, 'segments' => $segLabels])
        </div>
    </section>

    <section class="chapter chapter--tight seg-cards-wrap">
        <div class="wrap">
            <h2>A2 per segment</h2>
            <div class="seg-cards">
                @foreach($byCategory as $categorie => $categorieMotors)
                    @php $segLabel = \App\Models\Motor::CATEGORIES[$categorie] ?? $categorie; @endphp
                    <a class="seg-card" id="{{ $categorie }}" href="{{ $a2Segments->has($categorie) ? route('a2-motoren.segment', $categorie) : route('segments.show', $categorie) }}" data-reveal style="--d: {{ $loop->index * 60 }}ms">
                        <span class="seg-card__count">{{ $categorieMotors->count() }}</span>
                        <h3>A2 {{ \App\Http\Controllers\SegmentController::BUYING_LABEL[$categorie] ?? Str::lower($segLabel) }}</h3>
                        <p>{{ \App\Http\Controllers\SegmentController::DESCRIPTIONS[$categorie] ?? '' }}</p>
                        <span class="seg-card__go">{{ $a2Segments->has($categorie) ? 'Alle A2 ' . (\App\Http\Controllers\SegmentController::BUYING_LABEL[$categorie] ?? Str::lower($segLabel)) . '-modellen' : 'Alle ' . Str::lower($segLabel) . '-modellen' }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    @include('partials.faq', ['faq' => $a2Faq, 'heading' => 'Over A2 motoren'])

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Welke van deze motoren past bij jouw rijstijl?</h2>
            <p class="lede">De wizard zet de drie A2-motoren neer die het beste passen bij hoe jij rijdt.</p>
            <a class="btn btn--primary" href="{{ route('wizard.index') }}">Doe de wizard</a>
        </div>
    </section>
@endsection
