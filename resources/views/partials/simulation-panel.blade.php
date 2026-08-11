<section class="chapter chapter--tight">
<div class="wrap">

<div class="limit-box" data-limit-box>
    <div class="limit-box__row">
        <div>
            <p class="field-label" style="margin-bottom:0.5em">Simulaties - rolling 24 uur</p>
            <p style="font-family:var(--font-mono);font-variant-numeric:tabular-nums"><strong data-limit-used>{{ $limit['used'] }}</strong> / <span data-limit-total>{{ $limit['limit'] }}</span> gebruikt &middot; <span data-limit-remaining>{{ $limit['remaining'] }}</span> over</p>
            <p class="note-inline" style="margin-top:0.5em">Reset vanaf oudste simulatie: <span data-limit-reset>{{ $limit['reset_at'] ? \Carbon\Carbon::parse($limit['reset_at'])->timezone(config('app.timezone'))->format('d-m-Y H:i') : 'nog niet nodig' }}</span></p>
        </div>
        <div class="limit-box__track"><span class="limit-box__fill" data-limit-fill style="width: {{ min(100, ($limit['used'] / $limit['limit']) * 100) }}%"></span></div>
    </div>
</div>

<form data-simulation-form>
    <div class="motor-picker">
        <div class="motor-card motor-card--a">
            <p class="motor-card__label">Motor A &middot; lane 01</p>
            <div class="suggest-wrap">
                <input class="motor-card__input" id="motor-a" data-motor-input="A" autocomplete="off" placeholder="Bijv. BMW S1000XR 2017">
                <input type="hidden" data-motor-id="A">
                <div class="suggestions" data-suggestions="A" hidden></div>
            </div>
            <button class="btn btn--ghost motor-card__ai" type="button" data-lookup-ai="A" style="width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'A'])
            <div data-specs="A" style="margin-top:12px"></div>
        </div>
        <div class="motor-card motor-card--b">
            <p class="motor-card__label">Motor B &middot; lane 02</p>
            <div class="suggest-wrap">
                <input class="motor-card__input" id="motor-b" data-motor-input="B" autocomplete="off" placeholder="Bijv. Ducati Panigale V4 2022">
                <input type="hidden" data-motor-id="B">
                <div class="suggestions" data-suggestions="B" hidden></div>
            </div>
            <button class="btn btn--ghost motor-card__ai" type="button" data-lookup-ai="B" style="width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'B'])
            <div data-specs="B" style="margin-top:12px"></div>
        </div>
    </div>

    <div class="sim-controls">
        <div class="sim-control">
            <span class="field-label">Simulatietype</span>
            <div class="choice-row">
                <button class="choice is-active" type="button" data-choice data-group="road_type" data-value="straight">Rechte lijn</button>
                <button class="choice" type="button" data-choice data-group="road_type" data-value="twisty">Kronkelweg</button>
                <button class="choice" type="button" data-choice data-group="road_type" data-value="topspeed">Topsnelheid</button>
                <button class="choice" type="button" data-choice data-group="road_type" data-value="braking">Remafstand</button>
            </div>
        </div>
        <div class="sim-control" data-control="condition">
            <span class="field-label">Wegconditie</span>
            <div class="choice-row">
                <button class="choice is-active" type="button" data-choice data-group="road_condition" data-value="dry">Droog</button>
                <button class="choice" type="button" data-choice data-group="road_condition" data-value="wet">Vochtig</button>
                <button class="choice" type="button" data-choice data-group="road_condition" data-value="rain">Nat</button>
            </div>
        </div>
        <div class="sim-control" data-control="distance">
            <span class="field-label">Afstand</span>
            <div class="choice-row">
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="100">100m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="250">250m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="402">1/4 mile</button>
                <button class="choice is-active" type="button" data-choice data-group="distance_m" data-value="500">500m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="805">1/2 mile</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="1000">1000m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="2000">2km</button>
            </div>
        </div>
        <div class="sim-control" data-control="speed" hidden>
            <span class="field-label">Snelheid</span>
            <div class="choice-row">
                <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="50">50 km/h</button>
                <button class="choice is-active" type="button" data-choice data-group="speed_kmh" data-value="100">100 km/h</button>
                <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="130">130 km/h</button>
                <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="160">160 km/h</button>
            </div>
        </div>
        <button class="btn btn--primary" type="submit" data-run @if($limit['blocked']) disabled @endif>Start simulatie</button>
    </div>

    @auth
        <div class="form-row">
            <label style="display:flex;align-items:center;gap:0.6em"><input type="checkbox" data-use-profile> Rijdersprofiel meenemen</label>
        </div>
        <div class="form-grid" style="margin-top:-0.6rem">
            <div class="form-row">
                <label>Rijder A gewicht</label>
                <input name="rider_a_kg" type="number" value="{{ auth()->user()->weight_kg }}" min="0" max="180">
            </div>
            <div class="form-row">
                <label>Rijder B gewicht</label>
                <input name="rider_b_kg" type="number" min="0" max="180" placeholder="Optioneel">
            </div>
        </div>
    @endauth

    @guest
        @if($preselectKg ?? null)
            <p class="note-inline">Rijdersgewicht van {{ $preselectKg }} kg (uit de wizard) wordt meegenomen voor Motor A.</p>
        @endif
    @endguest

    <div hidden data-message></div>

    @guest
        <p class="note-inline"><a href="{{ route('login') }}">Inloggen</a> voor garage en rijdersprofiel.</p>
    @endguest
</form>

</div>
</section>

<section class="chapter chapter--dark chapter--tight">
    <div class="wrap">
        <span class="eyebrow">Resultaat</span>
        <h2>Race doorgerekend, geen giswerk</h2>
        <p class="lede" style="margin-top:0.6em;margin-bottom:2em">De server rekent elke race apart door op vermogen, gewicht, luchtweerstand en grip van beide motoren.</p>

        <div class="panel">
            <div class="panel__head"><span>Live telemetrie</span></div>
            <div class="bike-row" data-race-visual>
                <div class="bike-row__name"><span data-lane-name="A">Motor A</span><span class="ratio" data-time-a>-</span></div>
                <div class="bar"><span data-bar-a style="width:0%"></span></div>
            </div>
            <div class="bike-row" data-race-visual>
                <div class="bike-row__name"><span data-lane-name="B">Motor B</span><span class="ratio" data-time-b>-</span></div>
                <div class="bar"><span data-bar-b style="width:0%"></span></div>
            </div>

            <div class="stats" data-simple-visual hidden style="border-top:none;padding-top:0;margin-top:0">
                <div class="stat">
                    <div class="stat__value" data-simple-value-a>-</div>
                    <div class="stat__label" data-simple-label-a>Motor A</div>
                </div>
                <div class="stat" style="border-left-color:var(--midrange)">
                    <div class="stat__value" data-simple-value-b>-</div>
                    <div class="stat__label" data-simple-label-b>Motor B</div>
                </div>
            </div>

            <div class="panel__foot" data-result hidden>
                <h3 data-result-title></h3>
                <p style="margin-top:0.6em" data-share-row>Deelbare link: <a class="accent" data-share href="#" style="color:var(--midrange)"></a></p>

                <div class="share-row" data-share-row>
                    <a class="btn btn--ghost" data-share-copy href="#">Deel uitslag</a>
                    <a class="btn btn--ghost" data-search-online="A" href="#" target="_blank" rel="noopener">Motor A online zoeken</a>
                    <a class="btn btn--ghost" data-search-online="B" href="#" target="_blank" rel="noopener">Motor B online zoeken</a>
                </div>
                <div class="share-row" data-share-row>
                    <a class="btn btn--ghost" data-share-social="whatsapp" href="#" target="_blank" rel="noopener">WhatsApp</a>
                    <a class="btn btn--ghost" data-share-social="x" href="#" target="_blank" rel="noopener">X</a>
                    <a class="btn btn--ghost" data-share-social="facebook" href="#" target="_blank" rel="noopener">Facebook</a>
                </div>

                <div class="chart-wrap" data-chart-wrap hidden>
                    <div class="chart-head">
                        <span class="field-label" style="margin-bottom:0">Snelheid over afstand</span>
                        <div class="chart-legend">
                            <span class="legend-item"><span class="legend-key" style="background:#D7401F"></span><span data-legend-a>Motor A</span></span>
                            <span class="legend-item"><span class="legend-key" style="background:#2B6459"></span><span data-legend-b>Motor B</span></span>
                        </div>
                    </div>
                    <div class="chart-canvas" data-chart-canvas>
                        <svg data-chart-svg viewBox="0 0 640 220" role="img" aria-label="Snelheidsverloop over de afstand voor beide motoren"></svg>
                    </div>
                    <p class="note-inline" data-chart-readout aria-live="polite">Beweeg over de grafiek om de snelheid per punt te vergelijken.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@php
    $preselectAData = (isset($preselect) && $preselect) ? [
        'id' => $preselect->id,
        'label' => $preselect->label(),
        'brand' => $preselect->brand,
        'model' => $preselect->model,
        'power_hp' => $preselect->power_hp,
        'weight_kg' => $preselect->weight_kg,
        'photo_url' => $preselect->photo_url ? asset(ltrim($preselect->photo_url, '/')) : null,
        'photo_credit' => $preselect->photo_credit,
        'photo_source_url' => $preselect->photo_source_url,
    ] : null;
@endphp
@push('scripts')
    <script>
        window.REVRACE = {
            routes: {
                motors: @json(route('api.motors.search')),
                lookup: @json(route('api.motors.lookup')),
                manual: @json(route('api.motors.manual')),
                simulate: @json(route('api.simulation.run')),
                limit: @json(route('api.simulation.limit'))
            },
            limit: @json($limit),
            preselectA: @json($preselectAData),
            preselectRiderA: @json($preselectKg ?? null)
        };
    </script>
    <script src="{{ asset('js/simulation.js') }}?v={{ filemtime(public_path('js/simulation.js')) }}"></script>
@endpush
