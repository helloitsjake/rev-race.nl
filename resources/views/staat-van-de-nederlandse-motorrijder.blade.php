@extends('layouts.app')

@section('title', 'Staat van de Nederlandse motorrijder ' . $year . ' - RevRace')
@section('description', 'Jaarlijks overzicht van hoe Nederlandse motorrijders vergelijken en kiezen, op basis van echte simulatiedata van RevRace: meest vergeleken modellen, populairste merken en wegcondities.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Staat van de Nederlandse motorrijder ' . $year],
    ],
]) !!}
</script>
@if($hasEnoughData)
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Dataset',
    'name' => 'Staat van de Nederlandse motorrijder ' . $year,
    'description' => 'Jaarlijks databestand over motorvergelijkingen in Nederland, gebaseerd op ' . $totalSimulations . ' echte simulaties op RevRace: meest vergeleken modellen, populairste merken, wegcondities en A2-aandeel.',
    'creator' => ['@type' => 'Organization', 'name' => 'RevRace', 'url' => 'https://www.rev-race.nl'],
    'temporalCoverage' => (string) $year,
    'url' => route('yearly-report.show'),
    'license' => 'https://www.rev-race.nl/privacy',
]) !!}
</script>
@endif
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap">
            <span class="eyebrow">Databestand &middot; {{ $year }}</span>
            <h1>Staat van de Nederlandse motorrijder</h1>
            @if($hasEnoughData)
                <p class="lede">In {{ $year }} zijn er op RevRace {{ number_format($totalSimulations, 0, ',', '.') }} motorvergelijkingen gedraaid. Dit zijn de patronen daarin, rechtstreeks uit de eigen simulatiedata &mdash; geen enquête, geen schatting.</p>
            @else
                <p class="lede">Dit rapport wordt gevuld met echte simulatiedata van RevRace-bezoekers. Er zijn nog niet genoeg simulaties gedraaid om een betrouwbaar beeld te geven, kom later terug.</p>
            @endif
        </div>
    </header>

    @if($hasEnoughData)
        <section class="chapter chapter--dark chapter--tight">
            <div class="wrap stats" style="border-top:none;margin-top:0;padding-top:0">
                <div class="stat">
                    <div class="stat__value">{{ number_format($totalSimulations, 0, ',', '.') }}</div>
                    <div class="stat__label">Gedraaide simulaties</div>
                </div>
                @if($topModels->isNotEmpty())
                    <div class="stat">
                        <div class="stat__value">{{ $topModels->first()['motor']->brand }}</div>
                        <div class="stat__label">Meest vergeleken merk (model)</div>
                    </div>
                @endif
                @if($a2Share)
                    <div class="stat">
                        <div class="stat__value">{{ $a2Share['percentage'] }}%</div>
                        <div class="stat__label">Van vergeleken motoren is A2-geschikt</div>
                    </div>
                @endif
            </div>
        </section>

        @if($topModels->isNotEmpty())
            <section class="chapter">
                <div class="wrap">
                    <span class="eyebrow">Ranglijst</span>
                    <h2>Meest vergeleken modellen van {{ $year }}</h2>
                    <div class="rank-list">
                        @foreach($topModels as $i => $row)
                            <div class="rank-row">
                                <div class="rank-row__num">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</div>
                                <div>
                                    <div class="rank-row__name"><a href="{{ route('brands.model', [\Illuminate\Support\Str::slug($row['motor']->brand), $row['motor']->slug()]) }}">{{ $row['motor']->label() }}</a></div>
                                    <div class="rank-row__meta">{{ $row['motor']->categoryLabel() }} &middot; {{ $row['motor']->power_hp }} pk</div>
                                </div>
                                <div>
                                    <span class="rank-row__count">{{ $row['uses'] }}&times; in een vergelijking</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($topBrands->isNotEmpty())
            <section class="chapter chapter--tight">
                <div class="wrap">
                    <span class="eyebrow">Merken</span>
                    <h2>Populairste merken in vergelijkingen</h2>
                    <div class="bar-list">
                        @php $maxBrandUses = $topBrands->max('uses'); @endphp
                        @foreach($topBrands as $row)
                            <div class="bar-row">
                                <a class="bar-row__label" href="{{ route('brands.show', \Illuminate\Support\Str::slug($row['brand'])) }}">{{ $row['brand'] }}</a>
                                <div class="bar"><span style="width:{{ $maxBrandUses > 0 ? round(($row['uses'] / $maxBrandUses) * 100) : 0 }}%"></span></div>
                                <span class="bar-row__value">{{ $row['uses'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="chapter chapter--tight">
            <div class="wrap form-grid">
                @if($conditionSplit->isNotEmpty())
                    <div>
                        <span class="eyebrow">Wegcondities</span>
                        <h2>Op welk asfalt wordt vergeleken</h2>
                        <div class="bar-list">
                            @foreach($conditionSplit as $row)
                                <div class="bar-row">
                                    <span class="bar-row__label">{{ $row['label'] }}</span>
                                    <div class="bar"><span style="width:{{ $row['percentage'] }}%"></span></div>
                                    <span class="bar-row__value">{{ $row['percentage'] }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($ridingStyleSplit->isNotEmpty())
                    <div>
                        <span class="eyebrow">Rijstijl</span>
                        <h2>Rijstijl van vergelijkende rijders</h2>
                        <div class="bar-list">
                            @php $maxStyle = $ridingStyleSplit->max('total'); @endphp
                            @foreach($ridingStyleSplit as $row)
                                <div class="bar-row">
                                    <span class="bar-row__label">{{ $row['style'] }}</span>
                                    <div class="bar"><span style="width:{{ $maxStyle > 0 ? round(($row['total'] / $maxStyle) * 100) : 0 }}%"></span></div>
                                    <span class="bar-row__value">{{ $row['total'] }}</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="spec__note" style="margin-top:1em">Alleen simulaties van ingelogde gebruikers met een ingevulde rijstijl.</p>
                    </div>
                @endif
            </div>
        </section>

        @if($ageGroupBrands->isNotEmpty())
            <section class="chapter">
                <div class="wrap">
                    <span class="eyebrow">Leeftijd</span>
                    <h2>Populairste merk per leeftijdsgroep</h2>
                    <p class="lede">Alleen op basis van ingelogde gebruikers met een bekende geboortedatum, en alleen groepen met genoeg simulaties voor een betrouwbaar beeld.</p>
                    <div class="kb-grid">
                        @foreach($ageGroupBrands as $row)
                            <div class="kb-card">
                                <h3>{{ $row['label'] }} jaar</h3>
                                <p>{{ $row['top_brand'] }} vergelijkt het vaakst ({{ $row['count'] }} van {{ $row['total'] }} keer)</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="chapter chapter--dark chapter--tight">
            <div class="wrap cta-line">
                <p class="eyebrow">Bron</p>
                <h2>Dit rapport citeren of gebruiken?</h2>
                <p class="lede">Alle cijfers komen rechtstreeks uit de RevRace-simulatie-engine, niet uit een enquête. Verwijs bij gebruik naar RevRace ({{ route('yearly-report.show') }}) als bron.</p>
                <a class="btn btn--primary" href="{{ route('contact') }}">Vraag over deze data</a>
            </div>
        </section>
    @endif
@endsection
