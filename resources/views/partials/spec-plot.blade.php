{{--
    Spreidingsgrafiek vermogen tegen gewicht (13-10-2026). Server-side SVG, zonder bibliotheek.
    - $motors:  de motoren die meedoen (stippen met link naar de modelpagina)
    - $context: optioneel, overige motoren als grijze stippen (A2-pagina)
    - $zone:    'a2' tekent het A2-vlak met beide grenzen
    - $iso:     schuine pk/kg-lijnen (segmentpagina)
    - $best:    optioneel, motor die een label krijgt
    - $xMin, $xMax, $yMax: optioneel vast venster
    De stippen dragen data-id, zodat de lijst eronder ze kan oplichten (js/overzicht.js).
--}}
@php
    $context = $context ?? collect();
    $zone = $zone ?? null;
    $iso = $iso ?? false;
    $best = $best ?? null;
    $W = 1000; $H = 600; $L = 70; $R = 28; $T = 28; $B = 70;
    $pool = $context->concat($motors);
    $xMin = $xMin ?? max(0, floor(($pool->min('weight_kg') - 10) / 25) * 25);
    $xMax = $xMax ?? ceil(($pool->max('weight_kg') + 10) / 25) * 25;
    $yMax = $yMax ?? ceil(($pool->max('power_hp') + 8) / 25) * 25;
    $sx = fn ($kg) => round($L + ($kg - $xMin) / ($xMax - $xMin) * ($W - $L - $R), 1);
    $sy = fn ($hp) => round($H - $B - $hp / $yMax * ($H - $T - $B), 1);
    $inView = fn ($m) => $m->weight_kg >= $xMin && $m->weight_kg <= $xMax && $m->power_hp <= $yMax;
    $xStep = ($xMax - $xMin) <= 150 ? 25 : 50;
    $yStep = $yMax <= 100 ? 20 : ($yMax <= 200 ? 25 : 50);
    $fmt = fn ($n, $d = 2) => number_format($n, $d, ',', '.');
    // Stippen van links naar rechts laten binnenkomen: volgorde op gewicht.
    $dots = $motors->filter($inView)->sortBy('weight_kg')->values();
    $ghosts = $context->filter($inView)->reject(fn ($m) => $motors->contains('id', $m->id));
    $a2Hp = 35 / 0.7457;           // 46,9 pk
    $a2Ratio = 0.20 / 0.7457;      // pk per kg bij 0,20 kW/kg
@endphp
<figure class="plot" data-plot data-inview>
    <svg viewBox="0 0 {{ $W }} {{ $H }}" role="img" aria-label="Vermogen tegen gewicht van {{ $dots->count() }} motoren">
        {{-- raster --}}
        <g class="plot__grid">
            @for($y = 0; $y <= $yMax; $y += $yStep)
                <line x1="{{ $L }}" x2="{{ $W - $R }}" y1="{{ $sy($y) }}" y2="{{ $sy($y) }}"/>
                <text x="{{ $L - 14 }}" y="{{ $sy($y) + 5 }}" text-anchor="end">{{ $y }}</text>
            @endfor
            @for($x = $xMin; $x <= $xMax; $x += $xStep)
                <text x="{{ $sx($x) }}" y="{{ $H - $B + 32 }}" text-anchor="middle">{{ $x }}</text>
            @endfor
            <text class="plot__axis" x="{{ $L }}" y="{{ $T - 6 }}">pk</text>
            <text class="plot__axis" x="{{ $W - $R }}" y="{{ $H - 10 }}" text-anchor="end">gewicht in kg</text>
        </g>

        @if($zone === 'a2')
            @php
                $kink = $a2Hp / $a2Ratio; // kg waar beide grenzen elkaar raken
                $startHp = min($a2Hp, $a2Ratio * $xMin);
                $zonePts = collect([[$xMin, 0], [$xMin, $startHp], [$kink, $a2Hp], [$xMax, $a2Hp], [$xMax, 0]])
                    ->map(fn ($p) => $sx($p[0]) . ',' . $sy($p[1]))->implode(' ');
            @endphp
            <g class="plot__zone">
                <polygon points="{{ $zonePts }}"/>
                <line class="plot__limit" x1="{{ $sx($xMin) }}" y1="{{ $sy($startHp) }}" x2="{{ $sx($kink) }}" y2="{{ $sy($a2Hp) }}"/>
                <line class="plot__limit" x1="{{ $sx($kink) }}" y1="{{ $sy($a2Hp) }}" x2="{{ $sx($xMax) }}" y2="{{ $sy($a2Hp) }}"/>
                <text class="plot__limit-label" x="{{ $sx($xMax) - 8 }}" y="{{ $sy($a2Hp) - 12 }}" text-anchor="end">max. 35 kW (47 pk)</text>
                <text class="plot__limit-label" x="{{ $sx($xMin) + 10 }}" y="{{ $sy($startHp) - 14 }}" transform="rotate({{ round(rad2deg(atan2($sy($startHp) - $sy($a2Hp), $sx($kink) - $sx($xMin))) * -1, 1) }} {{ $sx($xMin) + 10 }} {{ $sy($startHp) - 14 }})">max. 0,20 kW per kg</text>
                <text class="plot__zone-label" x="{{ $sx($xMax) - 8 }}" y="{{ $sy(6) }}" text-anchor="end">A2-zone</text>
            </g>
        @endif

        @if($iso)
            <g class="plot__iso">
                @foreach([0.25, 0.5, 0.75, 1.0, 1.25] as $r)
                    @php
                        $kg0 = max($xMin, 1); $hp0 = $r * $kg0;
                        $kg1 = min($xMax, $yMax / $r); $hp1 = $r * $kg1;
                    @endphp
                    @if($hp0 < $yMax && $kg1 > $kg0)
                        <line x1="{{ $sx($kg0) }}" y1="{{ $sy($hp0) }}" x2="{{ $sx($kg1) }}" y2="{{ $sy($hp1) }}"/>
                        <text x="{{ $sx($kg1) - 6 }}" y="{{ $sy($hp1) + ($hp1 >= $yMax - 1 ? 22 : -10) }}" text-anchor="end">{{ $fmt($r) }} pk/kg</text>
                    @endif
                @endforeach
            </g>
        @endif

        @foreach($ghosts as $m)
            <circle class="plot__ghost" cx="{{ $sx($m->weight_kg) }}" cy="{{ $sy($m->power_hp) }}" r="5"/>
        @endforeach

        <g class="plot__dots">
            @foreach($dots as $i => $m)
                <a href="{{ route('brands.model', [\Illuminate\Support\Str::slug($m->brand), $m->slug()]) }}" tabindex="-1"
                   data-id="{{ $m->id }}" data-tip="{{ $m->label() }}" data-meta="{{ $m->power_hp }} pk &middot; {{ $m->weight_kg }} kg &middot; {{ $fmt($m->powerToWeight()) }} pk/kg">
                    <circle class="plot__dot{{ $m->isA2Eligible() ? ' is-a2' : '' }}{{ $best && $best->is($m) ? ' is-best' : '' }}"
                            cx="{{ $sx($m->weight_kg) }}" cy="{{ $sy($m->power_hp) }}" r="8" style="--i: {{ $i }}"/>
                </a>
            @endforeach
        </g>

        @if($best && $inView($best))
            @php $bx = $sx($best->weight_kg); $by = $sy($best->power_hp); $right = $bx > $L + 320; @endphp
            <g class="plot__note">
                <circle cx="{{ $bx }}" cy="{{ $by }}" r="15"/>
                <text x="{{ $right ? $bx - 24 : $bx + 24 }}" y="{{ $by + 6 }}" text-anchor="{{ $right ? 'end' : 'start' }}">{{ $best->brand }} {{ $best->model }}, {{ $fmt($best->powerToWeight()) }} pk/kg</text>
            </g>
        @endif
    </svg>
    <div class="plot__tip" aria-hidden="true"><b></b><span></span></div>
</figure>
