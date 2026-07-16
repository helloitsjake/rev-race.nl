<div class="limit-box" data-limit-box>
    <div class="limit-row">
        <div>
            <div class="form-label">Simulaties - rolling 24 uur</div>
            <div><strong data-limit-used>{{ $limit['used'] }}</strong> / <span data-limit-total>{{ $limit['limit'] }}</span> gebruikt · <span data-limit-remaining>{{ $limit['remaining'] }}</span> over</div>
            <div class="small">Reset vanaf oudste simulatie: <span data-limit-reset>{{ $limit['reset_at'] ? \Carbon\Carbon::parse($limit['reset_at'])->timezone(config('app.timezone'))->format('d-m-Y H:i') : 'nog niet nodig' }}</span></div>
        </div>
        <div class="limit-track"><div class="limit-fill" data-limit-fill style="width: {{ min(100, ($limit['used'] / $limit['limit']) * 100) }}%"></div></div>
    </div>
</div>

<form class="panel" data-simulation-form>
    <div class="sim-grid">
        <div class="card card-accent-a" style="padding:16px">
            <div class="chart-head" style="margin-bottom:10px">
                <span class="form-label" style="margin-bottom:0;color:var(--orange)">Motor A</span>
                <span class="form-label" style="margin-bottom:0">Lane 01</span>
            </div>
            <div class="suggest-wrap">
                <input class="input" id="motor-a" data-motor-input="A" autocomplete="off" placeholder="Bijv. BMW S1000XR 2017">
                <input type="hidden" data-motor-id="A">
                <div class="suggestions" data-suggestions="A"></div>
            </div>
            <button class="btn ghost" type="button" data-lookup-ai="A" style="margin-top:8px;width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'A'])
            <div data-specs="A" style="margin-top:12px"></div>
        </div>
        <div class="card card-accent-b" style="padding:16px">
            <div class="chart-head" style="margin-bottom:10px">
                <span class="form-label" style="margin-bottom:0;color:var(--teal)">Motor B</span>
                <span class="form-label" style="margin-bottom:0">Lane 02</span>
            </div>
            <div class="suggest-wrap">
                <input class="input" id="motor-b" data-motor-input="B" autocomplete="off" placeholder="Bijv. Ducati Panigale V4 2022">
                <input type="hidden" data-motor-id="B">
                <div class="suggestions" data-suggestions="B"></div>
            </div>
            <button class="btn ghost" type="button" data-lookup-ai="B" style="margin-top:8px;width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'B'])
            <div data-specs="B" style="margin-top:12px"></div>
        </div>
    </div>

    <div class="section" style="display:flex;flex-wrap:wrap;align-items:flex-end;gap:22px;justify-content:space-between">
        <div style="display:flex;flex-wrap:wrap;gap:24px">
            <div class="form-row" style="margin-bottom:0">
                <span class="form-label">Simulatietype</span>
                <div class="choice-row">
                    <button class="choice active" type="button" data-choice data-group="road_type" data-value="straight">Rechte lijn</button>
                    <button class="choice" type="button" data-choice data-group="road_type" data-value="twisty">Kronkelweg</button>
                    <button class="choice" type="button" data-choice data-group="road_type" data-value="topspeed">Topsnelheid</button>
                    <button class="choice" type="button" data-choice data-group="road_type" data-value="braking">Remafstand</button>
                </div>
            </div>
            <div class="form-row" style="margin-bottom:0" data-control="condition">
                <span class="form-label">Wegconditie</span>
                <div class="choice-row">
                    <button class="choice active" type="button" data-choice data-group="road_condition" data-value="dry">Droog</button>
                    <button class="choice" type="button" data-choice data-group="road_condition" data-value="wet">Vochtig</button>
                    <button class="choice" type="button" data-choice data-group="road_condition" data-value="rain">Nat</button>
                </div>
            </div>
            <div class="form-row" style="margin-bottom:0" data-control="distance">
                <span class="form-label">Afstand</span>
                <div class="choice-row">
                    <button class="choice" type="button" data-choice data-group="distance_m" data-value="100">100m</button>
                    <button class="choice" type="button" data-choice data-group="distance_m" data-value="250">250m</button>
                    <button class="choice active" type="button" data-choice data-group="distance_m" data-value="500">500m</button>
                    <button class="choice" type="button" data-choice data-group="distance_m" data-value="1000">1000m</button>
                    <button class="choice" type="button" data-choice data-group="distance_m" data-value="2000">2km</button>
                </div>
            </div>
            <div class="form-row" style="margin-bottom:0" data-control="speed" hidden>
                <span class="form-label">Snelheid</span>
                <div class="choice-row">
                    <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="50">50 km/h</button>
                    <button class="choice active" type="button" data-choice data-group="speed_kmh" data-value="100">100 km/h</button>
                    <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="130">130 km/h</button>
                    <button class="choice" type="button" data-choice data-group="speed_kmh" data-value="160">160 km/h</button>
                </div>
            </div>
        </div>
        <button class="btn primary" type="submit" data-run @if($limit['blocked']) disabled @endif>Start simulatie</button>
    </div>

    @auth
        <div class="form-row">
            <label><input type="checkbox" data-use-profile> Rijdersprofiel meenemen</label>
            <div class="sim-grid" style="margin-top:10px">
                <div>
                    <label class="form-label">Rijder A gewicht</label>
                    <input class="input" name="rider_a_kg" type="number" value="{{ auth()->user()->weight_kg }}" min="0" max="180">
                </div>
                <div>
                    <label class="form-label">Rijder B gewicht</label>
                    <input class="input" name="rider_b_kg" type="number" min="0" max="180" placeholder="Optioneel">
                </div>
            </div>
        </div>
    @endauth

    @guest
        @if($preselectKg ?? null)
            <p class="small" style="margin-top:10px;color:var(--dim)">Rijdersgewicht van {{ $preselectKg }} kg (uit de wizard) wordt meegenomen voor Motor A.</p>
        @endif
    @endguest

    <div hidden data-message></div>

    @guest
        <p class="small" style="margin-top:10px"><a class="accent" href="{{ route('login') }}">Inloggen</a> voor garage en rijdersprofiel.</p>
    @endguest
</form>

<section class="race-track">
    <div class="lane" data-race-visual>
        <div class="lane-head"><span data-lane-name="A">Motor A</span><span data-time-a>-</span></div>
        <div class="bar-bg"><div class="bar-fill a" data-bar-a></div></div>
    </div>
    <div class="lane" data-race-visual>
        <div class="lane-head"><span data-lane-name="B">Motor B</span><span data-time-b>-</span></div>
        <div class="bar-bg"><div class="bar-fill b" data-bar-b></div></div>
    </div>
    <div class="top-grid" data-simple-visual hidden style="grid-template-columns:repeat(2,1fr);margin-bottom:16px">
        <div class="card" style="padding:16px">
            <span class="form-label" data-simple-label-a style="color:var(--orange)">Motor A</span>
            <div class="metric-value" data-simple-value-a style="font-size:30px">-</div>
        </div>
        <div class="card" style="padding:16px">
            <span class="form-label" data-simple-label-b style="color:var(--teal)">Motor B</span>
            <div class="metric-value" data-simple-value-b style="font-size:30px">-</div>
        </div>
    </div>
    <div class="band-dark result-panel" data-result style="border-radius:var(--radius);padding:20px">
        <h2 class="card-title" data-result-title style="text-transform:none"></h2>
        <p style="color:var(--dark-muted)" data-share-row>Deelbare link: <a class="accent" data-share href="#" style="color:var(--teal)"></a></p>
        <div class="hero-actions" style="margin-top:0">
            <a class="btn secondary" data-share-copy href="#" data-share-row>Deel uitslag</a>
            <a class="btn secondary" data-search-online="A" href="#" target="_blank" rel="noopener">Motor A online zoeken</a>
            <a class="btn secondary" data-search-online="B" href="#" target="_blank" rel="noopener">Motor B online zoeken</a>
        </div>
        <div class="hero-actions" style="margin-top:8px" data-share-row>
            <a class="btn secondary" data-share-social="whatsapp" href="#" target="_blank" rel="noopener">WhatsApp</a>
            <a class="btn secondary" data-share-social="x" href="#" target="_blank" rel="noopener">X</a>
            <a class="btn secondary" data-share-social="facebook" href="#" target="_blank" rel="noopener">Facebook</a>
        </div>

        <div class="chart-wrap" data-chart-wrap>
            <div class="chart-head">
                <span class="form-label" style="margin-bottom:0">Snelheid over afstand</span>
                <div class="chart-legend">
                    <span class="legend-item"><span class="legend-key" style="background:#e85d00"></span><span data-legend-a>Motor A</span></span>
                    <span class="legend-item"><span class="legend-key" style="background:#0d9488"></span><span data-legend-b>Motor B</span></span>
                </div>
            </div>
            <div class="chart-canvas" data-chart-canvas>
                <svg data-chart-svg viewBox="0 0 640 220" role="img" aria-label="Snelheidsverloop over de afstand voor beide motoren"></svg>
            </div>
            <p class="small" data-chart-readout aria-live="polite">Beweeg over de grafiek om de snelheid per punt te vergelijken.</p>
        </div>
    </div>
</section>

@php
    $preselectAData = (isset($preselect) && $preselect) ? [
        'id' => $preselect->id,
        'label' => $preselect->label(),
        'power_hp' => $preselect->power_hp,
        'weight_kg' => $preselect->weight_kg,
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
