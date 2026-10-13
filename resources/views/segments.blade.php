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
    @php
        $W = 400; $H = 220; $P = 14;
        $kgMin = floor($motors->min('weight_kg') / 25) * 25;
        $kgMax = ceil($motors->max('weight_kg') / 25) * 25;
        $hpMax = ceil($motors->max('power_hp') / 25) * 25;
        $sx = fn ($kg) => round($P + ($kg - $kgMin) / max(1, $kgMax - $kgMin) * ($W - 2 * $P), 1);
        $sy = fn ($hp) => round($H - $P - $hp / max(1, $hpMax) * ($H - 2 * $P), 1);
        $ghosts = $motors->map(fn ($m) => [$sx($m->weight_kg), $sy($m->power_hp)])->unique()->values();
    @endphp
    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <h1>Welk type motor past bij welke rijstijl?</h1>
            <div class="seg-hero__body">
                <p class="lede">Zes segmenten, {{ $motors->count() }} motoren, allemaal op dezelfde schaal: vermogen omhoog, gewicht naar rechts. Zo zie je in één oogopslag waar een segment zit. Twijfel je nog? De <a class="accent" href="{{ route('wizard.index') }}">wizard</a> vertaalt je rijstijl direct naar een advies.</p>
            </div>
        </div>
    </header>

    <section class="chapter chapter--tight segs-wrap">
        <div class="wrap">
            <ul class="segs">
                @foreach($segments as $i => $segment)
                    <li class="seg-tile" data-inview style="--d: {{ $i * 80 }}ms">
                        <a href="{{ route('segments.show', $segment['key']) }}">
                            <div class="seg-tile__head">
                                <h2>{{ $segment['label'] }}</h2>
                                <span class="seg-tile__count"><b>{{ $segment['count'] }}</b> modellen</span>
                            </div>
                            <svg class="mplot" viewBox="0 0 {{ $W }} {{ $H }}" aria-hidden="true" focusable="false">
                                <line class="mplot__axis" x1="{{ $P }}" y1="{{ $H - $P }}" x2="{{ $W - $P }}" y2="{{ $H - $P }}"/>
                                <line class="mplot__axis" x1="{{ $P }}" y1="{{ $P }}" x2="{{ $P }}" y2="{{ $H - $P }}"/>
                                <g class="mplot__ghosts">
                                    @foreach($ghosts as $g)<circle cx="{{ $g[0] }}" cy="{{ $g[1] }}" r="2.6"/>@endforeach
                                </g>
                                <g class="mplot__dots">
                                    @foreach($segment['motors']->sortBy('weight_kg')->values() as $j => $m)
                                        <circle class="{{ $m->isA2Eligible() ? 'is-a2' : '' }}" cx="{{ $sx($m->weight_kg) }}" cy="{{ $sy($m->power_hp) }}" r="4.6" style="--i: {{ $j }}"/>
                                    @endforeach
                                </g>
                            </svg>
                            <dl class="seg-tile__facts">
                                <div><dt>Vermogen</dt><dd>{{ $segment['hp_min'] }} tot {{ $segment['hp_max'] }} pk</dd></div>
                                <div><dt>Gemiddeld</dt><dd>{{ $segment['kg_avg'] }} kg</dd></div>
                                <div><dt>A2</dt><dd>{{ $segment['a2'] ?: 'geen' }}</dd></div>
                            </dl>
                            @if($segment['description'])<p class="seg-tile__text">{{ $segment['description'] }}</p>@endif
                            <span class="seg-tile__go">Beste {{ $segment['buying_label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <p class="segs__legend"><span><i></i>Dit segment</span><span><i class="is-a2"></i>A2-geschikt</span><span><i class="is-ghost"></i>Alle andere motoren</span></p>
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <h2>Rijd je met een A2-rijbewijs?</h2>
            <p class="lede">Bekijk welke motoren binnen de twee A2-grenzen vallen, per segment op een rij.</p>
            <a class="btn btn--primary" href="{{ route('a2-motoren') }}">Bekijk A2-motoren</a>
        </div>
    </section>
@endsection
