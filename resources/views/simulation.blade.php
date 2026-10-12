@extends('layouts.app')

@section('title', 'Simulatie: welke motor is sneller? - RevRace')
@section('description', 'Race twee motoren tegen elkaar op droog, vochtig of nat asfalt. RevRace rekent de race door op vermogen, gewicht, luchtweerstand en grip, en laat zien waar ze te koop zijn.')

@php
    $arrow = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
    $riderKg = auth()->user()?->weight_kg ?: ($preselectKg ?? null);
@endphp

@section('content')
    {{--
        Simulatie 2.0 (12-10-2026). De keuze is één zin, net als de rijstijlkiezer op de home, en
        de baan staat er direct onder: na "Start de race" gaan de lichten aan en uit en rijden de
        motoren de race op de echte simulatiedata. Direct na de finish de keuzekaarten met aanbod.
        Signature-moment: lights out plus de race. De embed gebruikt nog partials/simulation-panel.
    --}}
    <div class="sim" data-sim>
        <div class="wrap sim__head">
            <p class="turn"><b>T0</b> Startlijn</p>
            <h1>Welke motor is sneller? <span>Laat ze racen.</span></h1>

            <form class="sim__form" data-sim-form novalidate>
                <div class="sentence sim__line">
                    <span>Race de</span>
                    @foreach(['A' => 'Bijv. Honda CB650R 2023', 'B' => 'Bijv. Yamaha MT-07 2024'] as $side => $placeholder)
                        <div class="pickfield pickfield--{{ strtolower($side) }}" data-pickfield="{{ $side }}">
                            <label class="sr-only" for="motor-{{ strtolower($side) }}">Motor {{ $side }}</label>
                            <input class="pickfield__input" id="motor-{{ strtolower($side) }}" data-motor-input="{{ $side }}" autocomplete="off" spellcheck="false" placeholder="{{ $placeholder }}" role="combobox" aria-expanded="false" aria-controls="sug-{{ strtolower($side) }}" aria-autocomplete="list">
                            <div class="pickfield__list" id="sug-{{ strtolower($side) }}" role="listbox" data-suggestions="{{ $side }}" hidden></div>
                        </div>
                        @if($side === 'A')<span>tegen de</span>@endif
                    @endforeach
                </div>

                <div class="sentence sim__line">
                    <span>op</span>
                    <div class="filter" role="group" aria-label="Soort race" data-option="road_type">
                        <div class="filter__base">
                            <button type="button" data-value="straight" aria-pressed="true">het rechte stuk</button>
                            <button type="button" data-value="twisty" aria-pressed="false">een kronkelweg</button>
                            <button type="button" data-value="topspeed" aria-pressed="false">topsnelheid</button>
                            <button type="button" data-value="braking" aria-pressed="false">de rem</button>
                        </div>
                        <div class="filter__pill" aria-hidden="true"></div>
                    </div>
                    <span data-when="condition">met</span>
                    <div class="filter" role="group" aria-label="Wegdek" data-option="road_condition" data-when="condition">
                        <div class="filter__base">
                            <button type="button" data-value="dry" aria-pressed="true">droog</button>
                            <button type="button" data-value="wet" aria-pressed="false">vochtig</button>
                            <button type="button" data-value="rain" aria-pressed="false">nat</button>
                        </div>
                        <div class="filter__pill" aria-hidden="true"></div>
                    </div>
                    <span data-when="condition">asfalt</span>
                    <label class="sim__sel" data-when="distance">
                        <span>over</span>
                        <select data-option="distance_m" aria-label="Afstand">
                            <option value="100">100 m</option>
                            <option value="250">250 m</option>
                            <option value="402">402 m (1/4 mijl)</option>
                            <option value="500" selected>500 m</option>
                            <option value="805">805 m (1/2 mijl)</option>
                            <option value="1000">1 km</option>
                            <option value="2000">2 km</option>
                        </select>
                    </label>
                    <label class="sim__sel" data-when="speed" hidden>
                        <span>vanaf</span>
                        <select data-option="speed_kmh" aria-label="Beginsnelheid">
                            <option value="50">50 km/u</option>
                            <option value="100" selected>100 km/u</option>
                            <option value="130">130 km/u</option>
                            <option value="160">160 km/u</option>
                        </select>
                    </label>
                </div>

                <div class="sim__go">
                    <button class="btn btn--red sim__start" type="submit" data-run disabled>
                        <span data-run-label>Start de race</span>{!! $arrow !!}
                    </button>
                    <div class="sim__riders">
                        <label>Rijder A <input type="number" inputmode="numeric" min="0" max="180" name="rider_a_kg" value="{{ $riderKg }}" placeholder="kg"> kg</label>
                        <label>Rijder B <input type="number" inputmode="numeric" min="0" max="180" name="rider_b_kg" value="{{ $riderKg }}" placeholder="kg"> kg</label>
                    </div>
                    <p class="sim__limit" data-limit aria-live="polite"></p>
                </div>
                <p class="sim__msg" data-message role="alert" hidden></p>

                @foreach(['A', 'B'] as $side)
                    <div class="sim__manual" data-manual-wrap="{{ $side }}" hidden>
                        <p class="sim__manual-title">Motor {{ $side }} zelf invullen</p>
                        @include('partials.manual-motor-form', ['side' => $side])
                    </div>
                @endforeach
            </form>
        </div>

        <section class="grid-panel sim__race" aria-labelledby="race-status" data-race-panel>
            <div class="wrap">
                <div class="grid-head">
                    <div class="lights" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                    <p class="status" id="race-status" data-status aria-live="polite">Kies twee motoren</p>
                </div>

                <div class="track sim__track" aria-hidden="true">
                    @foreach(['a', 'b'] as $side)
                        <div class="lane lane--{{ $side }} is-empty" data-lane="{{ $side }}">
                            <span class="lane__name" data-lane-name>Motor {{ strtoupper($side) }}</span>
                            <span class="lane__rail"><i class="lane__bike" data-bike>@include('partials.bike-icon', ['category' => null])</i></span>
                            <span class="lane__time" data-lane-time>&nbsp;</span>
                        </div>
                    @endforeach
                    <div class="lane lane--marks"><span></span><span class="track__marks" data-marks><span>0</span><span>100</span><span>200</span><span>300</span><span>400</span><span>500 m</span></span><span></span></div>
                </div>

                <div class="sim__result" data-result hidden>
                    <div class="sim__verdict">
                        <h2 data-result-title></h2>
                        <p data-result-sub></p>
                        <button type="button" class="race__replay" data-replay>Kijk de race terug</button>
                    </div>
                    <div class="sim__pick" data-pick></div>
                    <div class="sim__after">
                        <div class="sim__share" data-share hidden>
                            <span>Deel de uitslag</span>
                            <button type="button" data-share-copy>Kopieer link</button>
                            <a data-share-social="whatsapp" href="#" target="_blank" rel="noopener">WhatsApp</a>
                            <a data-share-social="x" href="#" target="_blank" rel="noopener">X</a>
                            <a data-share-social="facebook" href="#" target="_blank" rel="noopener">Facebook</a>
                        </div>
                        <a class="sim__how" href="{{ route('how-it-works') }}">Zo rekent RevRace</a>
                    </div>
                    <div class="sim__chart" data-chart-wrap hidden>
                        <div class="chart-head">
                            <span class="field-label" style="margin-bottom:0">Snelheid over de afstand</span>
                            <div class="chart-legend">
                                <span class="legend-item"><span class="legend-key" style="background:var(--redline)"></span><span data-legend="a">Motor A</span></span>
                                <span class="legend-item"><span class="legend-key" style="background:var(--midrange)"></span><span data-legend="b">Motor B</span></span>
                            </div>
                        </div>
                        <svg data-chart-svg viewBox="0 0 640 200" role="img" aria-label="Snelheidsverloop over de afstand voor beide motoren"></svg>
                        <p class="sim__readout" data-chart-readout aria-live="polite">Beweeg over de grafiek voor de snelheid per punt.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

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
        };
    </script>
    <script src="{{ asset('js/simulator.js') }}?v={{ filemtime(public_path('js/simulator.js')) }}"></script>
@endpush
