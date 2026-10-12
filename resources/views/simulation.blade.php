@extends('layouts.app')

@section('title', 'Simulatie: welke motor is sneller? - RevRace')
@section('description', 'Race twee motoren tegen elkaar op droog, vochtig of nat asfalt. RevRace rekent de race door op vermogen, gewicht, luchtweerstand en grip, en laat zien waar ze te koop zijn.')

@php
    $arrow = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
    $riderKg = auth()->user()?->weight_kg ?: ($preselectKg ?? null);
    $filter = function (string $key, string $label, array $opts, string $active) {
        $buttons = collect($opts)->map(fn ($text, $value) => '<button type="button" data-value="'.e($value).'" aria-pressed="'.($value == $active ? 'true' : 'false').'">'.e($text).'</button>')->implode('');

        return '<div class="filter filter--full" role="group" aria-label="'.e($label).'" data-option="'.$key.'"><div class="filter__base">'.$buttons.'</div><div class="filter__pill" aria-hidden="true"></div></div>';
    };
@endphp

@section('content')
    {{--
        Simulatie, redesign 12-10-2026 (derde versie). Links de configurator met alle opties
        zichtbaar (veer-filters, afstandsschuif), rechts het racescherm dat meeblijft. Een nieuwe
        race is één keuze aanpassen en opnieuw starten. Signature-moment: lights out plus de race.
        Na de finish lichte keuzekaarten met aanbod. De embed gebruikt nog partials/simulation-panel.
    --}}
    <header class="wrap simx-head">
        <p class="turn"><b>T0</b> Startlijn</p>
        <div class="simx-head__row">
            <h1>Welke motor is <span>sneller?</span></h1>
            <p>Kies twee motoren, zet de race neer en RevRace rekent hem door op vermogen, gewicht, luchtweerstand en grip.</p>
        </div>
    </header>

    <div class="wrap simx" data-sim>
        <form class="simx-panel" id="sim-form" data-sim-form novalidate>
            <fieldset class="simx-group">
                <legend>Motoren</legend>
                @foreach(['A' => 'Bijv. Honda CB650R', 'B' => 'Bijv. Yamaha MT-07'] as $side => $placeholder)
                    @php($s = strtolower($side))
                    <div class="mfield mfield--{{ $s }}" data-pickfield="{{ $side }}">
                        <label class="mfield__label" for="motor-{{ $s }}">Motor {{ $side }}</label>
                        <div class="mfield__box">
                            <svg class="mfield__icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="9" cy="9" r="5.5"/><path d="M13.2 13.2 17 17"/></svg>
                            <input class="mfield__input" id="motor-{{ $s }}" data-motor-input="{{ $side }}" autocomplete="off" spellcheck="false" placeholder="{{ $placeholder }}" role="combobox" aria-expanded="false" aria-controls="sug-{{ $s }}" aria-autocomplete="list">
                            <button type="button" class="mfield__clear" data-clear="{{ $side }}" aria-label="Motor {{ $side }} wissen" hidden><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l8 8M14 6l-8 8"/></svg></button>
                        </div>
                        <p class="mfield__specs" data-specs="{{ $side }}" aria-live="polite"></p>
                        <div class="mfield__list" id="sug-{{ $s }}" role="listbox" data-suggestions="{{ $side }}" hidden></div>
                    </div>
                    @if($side === 'A')
                        <button type="button" class="simx-swap" data-swap aria-label="Motor A en B omwisselen" title="Omwisselen">
                            <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 3v14M3.5 6.5 7 3l3.5 3.5M13 17V3M16.5 13.5 13 17l-3.5-3.5"/></svg>
                        </button>
                    @endif
                @endforeach
            </fieldset>

            <fieldset class="simx-group">
                <legend>Soort race</legend>
                {!! $filter('road_type', 'Soort race', ['straight' => 'Rechte lijn', 'twisty' => 'Bochten', 'topspeed' => 'Topsnelheid', 'braking' => 'Remweg'], 'straight') !!}
                <p class="simx-hint" data-mode-hint>Wie is het eerst over de streep, vanuit stilstand.</p>
            </fieldset>

            <fieldset class="simx-group" data-when="condition">
                <legend>Wegdek</legend>
                {!! $filter('road_condition', 'Wegdek', ['dry' => 'Droog', 'wet' => 'Vochtig', 'rain' => 'Nat'], 'dry') !!}
            </fieldset>

            <fieldset class="simx-group" data-when="distance">
                <legend>Afstand <output class="simx-out" data-distance-out>500 m</output></legend>
                <div class="range">
                    <input type="range" min="0" max="6" step="1" value="3" data-distance aria-label="Afstand" aria-valuetext="500 meter">
                    <div class="range__ticks" aria-hidden="true"><span>100</span><span>250</span><span>402</span><span>500</span><span>805</span><span>1 km</span><span>2 km</span></div>
                </div>
            </fieldset>

            <fieldset class="simx-group" data-when="speed" hidden>
                <legend>Remmen vanaf</legend>
                {!! $filter('speed_kmh', 'Beginsnelheid', ['50' => '50 km/u', '100' => '100', '130' => '130', '160' => '160'], '100') !!}
            </fieldset>

            <fieldset class="simx-group simx-group--rider">
                <legend>Rijdersgewicht <span>(optioneel)</span></legend>
                <label class="kg"><input type="number" inputmode="numeric" min="0" max="180" name="rider_kg" value="{{ $riderKg }}" placeholder="75" aria-label="Rijdersgewicht in kg"><span>kg</span></label>
                <p class="simx-hint">Geldt voor beide motoren, zodat je de motoren vergelijkt.</p>
            </fieldset>

            @foreach(['A', 'B'] as $side)
                <div class="simx-manual" data-manual-wrap="{{ $side }}" hidden>
                    <p class="simx-manual__title">Motor {{ $side }} zelf invullen</p>
                    @include('partials.manual-motor-form', ['side' => $side])
                </div>
            @endforeach
        </form>

        <div class="simx-stage" data-stage>
            <section class="monitor" data-race-panel aria-labelledby="race-status">
                <div class="monitor__head">
                    <div class="lights" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                    <p class="status" id="race-status" data-status aria-live="polite">Kies twee motoren</p>
                </div>

                <div class="track simx-track" aria-hidden="true">
                    @foreach(['a', 'b'] as $side)
                        <div class="lane lane--{{ $side }} is-empty" data-lane="{{ $side }}">
                            <span class="lane__name" data-lane-name>Motor {{ strtoupper($side) }}</span>
                            <span class="lane__time" data-lane-time>&nbsp;</span>
                            <span class="lane__rail"><i class="lane__bike" data-bike>@include('partials.bike-icon', ['category' => null])</i></span>
                        </div>
                    @endforeach
                    <div class="track__marks" data-marks><span>0</span><span>100</span><span>200</span><span>300</span><span>400</span><span>500 m</span></div>
                </div>

                <div class="monitor__go">
                    <button class="btn btn--red simx-start" type="submit" form="sim-form" data-run disabled>
                        <span data-run-label>Kies twee motoren</span>{!! $arrow !!}
                    </button>
                    <div class="simx-limit" data-limit-box>
                        <span class="simx-limit__bar"><i data-limit-fill></i></span>
                        <span data-limit aria-live="polite"></span>
                    </div>
                    <p class="simx-msg" data-message role="alert" hidden></p>
                </div>

                {{-- Lege staat: met één klik een complete race. --}}
                <div class="monitor__empty" data-empty>
                    @if(count($duels))
                        <p class="monitor__label">Of start met een populair duel</p>
                        <div class="duels">
                            @foreach($duels as $i => $duel)
                                <button type="button" class="duel" data-duel="{{ $i }}">
                                    <span class="duel__a">{{ $duel['a']['short_label'] }}</span>
                                    <span class="duel__vs">vs</span>
                                    <span class="duel__b">{{ $duel['b']['short_label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="verdict" data-verdict hidden>
                    <p class="verdict__label" data-verdict-label></p>
                    <p class="verdict__name" data-verdict-name></p>
                    <p class="verdict__delta"><b data-verdict-num>0</b> <span data-verdict-unit></span></p>
                    <p class="verdict__sub" data-verdict-sub></p>
                    <div class="verdict__acts">
                        <a class="btn btn--red" href="#aanbod" data-to-offers>Bekijk aanbod{!! $arrow !!}</a>
                        <button type="button" class="btn btn--paper" data-replay>Kijk opnieuw</button>
                    </div>
                </div>
                <p class="monitor__stale" data-stale hidden>Instellingen aangepast. Start opnieuw voor een nieuwe uitslag.</p>
            </section>

            <div class="simx-history" data-history hidden>
                <p class="simx-history__title">Je races</p>
                <ol data-history-list></ol>
            </div>
        </div>
    </div>

    <section class="wrap simx-offers" id="aanbod" data-offers hidden aria-labelledby="offers-title">
        <div class="simx-offers__head">
            <h2 id="offers-title">Welke past bij jou?</h2>
            <a class="simx-compare" data-compare href="#">Volledige vergelijking{!! $arrow !!}</a>
        </div>
        <div class="pick" data-pick></div>
        <p class="pick__note">Aanbod opent in een nieuw tabblad. RevRace verkoopt zelf geen motoren.</p>

        <div class="simx-extra">
            <div class="simx-share" data-share hidden>
                <p>Deel deze uitslag</p>
                <div>
                    <button type="button" class="btn btn--ghost" data-share-copy>Kopieer link</button>
                    <a class="btn btn--ghost" data-share-social="whatsapp" href="#" target="_blank" rel="noopener">WhatsApp</a>
                    <a class="btn btn--ghost" data-share-social="x" href="#" target="_blank" rel="noopener">X</a>
                    <a class="btn btn--ghost" data-share-social="facebook" href="#" target="_blank" rel="noopener">Facebook</a>
                </div>
            </div>
            <details class="simx-chart" data-chart-wrap hidden>
                <summary>Snelheid over de afstand</summary>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-key" style="background:var(--redline)"></span><span data-legend="a">Motor A</span></span>
                    <span class="legend-item"><span class="legend-key" style="background:var(--midrange)"></span><span data-legend="b">Motor B</span></span>
                </div>
                <svg data-chart-svg viewBox="0 0 640 220" role="img" aria-label="Snelheidsverloop over de afstand voor beide motoren"></svg>
                <p class="simx-readout" data-chart-readout aria-live="polite">Beweeg over de grafiek voor de snelheid per punt.</p>
            </details>
        </div>
    </section>

    <section class="chapter chapter--tight sim-notes">
        <div class="wrap sim-notes__grid">
            <div>
                <h2>Een berekening, geen meting</h2>
                <p>RevRace rekent elke race door op vermogen, gewicht, luchtweerstand en grip, met het gewicht van de rijder erbij. Dat geeft een eerlijk beeld van hoe twee motoren zich tot elkaar verhouden, maar het blijft een simulatie: de fabrieksspecificaties zijn de basis, de luchtweerstand is per model geschat.</p>
                <p><a class="accent" href="{{ route('how-it-works') }}">Lees hoe de simulatie werkt</a></p>
            </div>
            <div>
                <h2>Weet je nog niet welke?</h2>
                <p>Vertel hoe je rijdt en welk rijbewijs je hebt. RevRace zet de motoren neer die daar het beste bij passen, en die kun je daarna hier tegen elkaar laten racen.</p>
                <p><a class="btn btn--ghost" href="{{ route('wizard.index') }}">Vind je motor{!! $arrow !!}</a></p>
            </div>
        </div>
    </section>

    {{-- Mobiel: vaste startbalk in de duimzone zodra beide motoren gekozen zijn. Verdwijnt als de
         startknop van het racescherm zelf in beeld is. --}}
    <div class="simx-dock" data-dock hidden>
        <span class="simx-dock__match" data-dock-match></span>
        <button class="btn btn--red simx-dock__btn" type="submit" form="sim-form" data-dock-run>
            <span data-dock-label>Start de race</span>{!! $arrow !!}
        </button>
    </div>

    <template data-bike-icon="sport">@include('partials.bike-icon', ['category' => 'sport'])</template>
    <template data-bike-icon="other">@include('partials.bike-icon', ['category' => null])</template>
@endsection

@push('scripts')
    <script>
        window.REVRACE = {
            routes: {
                motors: @json(route('api.motors.search')),
                lookup: @json(route('api.motors.lookup')),
                manual: @json(route('api.motors.manual')),
                simulate: @json(route('api.simulation.run')),
                register: @json(route('register')),
            },
            limit: @json($limit),
            preselect: { A: @json($preselectA), B: @json($preselectB) },
            duels: @json($duels),
        };
    </script>
    <script src="{{ asset('js/simulator.js') }}?v={{ filemtime(public_path('js/simulator.js')) }}"></script>
@endpush
