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
        </div>

        {{-- Eén cockpit: de motoren kies je op hun eigen rijstrook, de instellingen staan naast de startknop. --}}
        <form class="grid-panel sim__race" data-sim-form data-race-panel novalidate aria-labelledby="race-status">
            <div class="wrap">
                <div class="grid-head">
                    <div class="lights" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                    <p class="status" id="race-status" data-status aria-live="polite">Kies twee motoren</p>
                </div>

                <div class="track sim__track">
                    @foreach(['a' => 'Bijv. Honda CB650R 2023', 'b' => 'Bijv. Yamaha MT-07 2024'] as $side => $placeholder)
                        @php($S = strtoupper($side))
                        <div class="lane lane--{{ $side }} is-empty" data-lane="{{ $side }}">
                            <div class="pickfield pickfield--{{ $side }}" data-pickfield="{{ $S }}">
                                <label class="sr-only" for="motor-{{ $side }}">Motor {{ $S }}</label>
                                <input class="pickfield__input" id="motor-{{ $side }}" data-motor-input="{{ $S }}" autocomplete="off" spellcheck="false" placeholder="Kies motor {{ $S }}" role="combobox" aria-expanded="false" aria-controls="sug-{{ $side }}" aria-autocomplete="list">
                                <div class="pickfield__list" id="sug-{{ $side }}" role="listbox" data-suggestions="{{ $S }}" hidden></div>
                            </div>
                            <span class="lane__rail" aria-hidden="true"><i class="lane__bike" data-bike>@include('partials.bike-icon', ['category' => null])</i></span>
                            <span class="lane__time" data-lane-time aria-hidden="true">&nbsp;</span>
                        </div>
                    @endforeach
                    <div class="lane lane--marks" aria-hidden="true"><span></span><span class="track__marks" data-marks><span>0</span><span>100</span><span>200</span><span>300</span><span>400</span><span>500 m</span></span><span></span></div>
                </div>

                <div class="sim__go">
                    <div class="sim__opts">
                        <label class="chip"><span class="sr-only">Soort race</span>
                            <select data-option="road_type">
                                <option value="straight" selected>Rechte lijn</option>
                                <option value="twisty">Kronkelweg</option>
                                <option value="topspeed">Topsnelheid</option>
                                <option value="braking">Remweg</option>
                            </select>
                        </label>
                        <label class="chip" data-when="condition"><span class="sr-only">Wegdek</span>
                            <select data-option="road_condition">
                                <option value="dry" selected>Droog</option>
                                <option value="wet">Vochtig</option>
                                <option value="rain">Nat</option>
                            </select>
                        </label>
                        <label class="chip" data-when="distance"><span class="sr-only">Afstand</span>
                            <select data-option="distance_m">
                                <option value="100">100 m</option>
                                <option value="250">250 m</option>
                                <option value="402">402 m</option>
                                <option value="500" selected>500 m</option>
                                <option value="805">805 m</option>
                                <option value="1000">1 km</option>
                                <option value="2000">2 km</option>
                            </select>
                        </label>
                        <label class="chip" data-when="speed" hidden><span class="sr-only">Beginsnelheid</span>
                            <select data-option="speed_kmh">
                                <option value="50">Vanaf 50 km/u</option>
                                <option value="100" selected>Vanaf 100 km/u</option>
                                <option value="130">Vanaf 130 km/u</option>
                                <option value="160">Vanaf 160 km/u</option>
                            </select>
                        </label>
                        <label class="chip chip--kg">Rijder <input type="number" inputmode="numeric" min="0" max="180" name="rider_kg" value="{{ $riderKg }}" placeholder="0" aria-label="Gewicht rijder in kg"> kg</label>
                    </div>
                    <p class="sim__limit" data-limit aria-live="polite"></p>
                    <button class="btn btn--red sim__start" type="submit" data-run disabled>
                        <span data-run-label>Start de race</span>{!! $arrow !!}
                    </button>
                </div>
                <p class="sim__msg" data-message role="alert" hidden></p>

                @foreach(['A', 'B'] as $side)
                    <div class="sim__manual" data-manual-wrap="{{ $side }}" hidden>
                        <p class="sim__manual-title">Motor {{ $side }} zelf invullen</p>
                        @include('partials.manual-motor-form', ['side' => $side])
                    </div>
                @endforeach

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
        </form>
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
