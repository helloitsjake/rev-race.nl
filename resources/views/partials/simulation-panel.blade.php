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
        <div>
            <label class="form-label" for="motor-a">Motor A</label>
            <div class="suggest-wrap">
                <input class="input" id="motor-a" data-motor-input="A" autocomplete="off" placeholder="Bijv. BMW S1000XR 2017">
                <input type="hidden" data-motor-id="A">
                <div class="suggestions" data-suggestions="A"></div>
            </div>
            <button class="btn ghost" type="button" data-lookup-ai="A" style="margin-top:8px;width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'A'])
            <div class="card" data-specs="A" style="margin-top:12px"></div>
        </div>
        <div>
            <label class="form-label" for="motor-b">Motor B</label>
            <div class="suggest-wrap">
                <input class="input" id="motor-b" data-motor-input="B" autocomplete="off" placeholder="Bijv. Ducati Panigale V4 2022">
                <input type="hidden" data-motor-id="B">
                <div class="suggestions" data-suggestions="B"></div>
            </div>
            <button class="btn ghost" type="button" data-lookup-ai="B" style="margin-top:8px;width:100%">Staat er niet bij? Zoek 'm op met AI</button>
            @include('partials.manual-motor-form', ['side' => 'B'])
            <div class="card" data-specs="B" style="margin-top:12px"></div>
        </div>
    </div>

    <div class="section">
        <div class="form-row">
            <span class="form-label">Wegtype</span>
            <div class="choice-row">
                <button class="choice active" type="button" data-choice data-group="road_type" data-value="straight">Rechte lijn</button>
                <button class="choice" type="button" data-choice data-group="road_type" data-value="twisty">Kronkelweg</button>
            </div>
        </div>
        <div class="form-row">
            <span class="form-label">Wegconditie</span>
            <div class="choice-row">
                <button class="choice active" type="button" data-choice data-group="road_condition" data-value="dry">Droog</button>
                <button class="choice" type="button" data-choice data-group="road_condition" data-value="wet">Vochtig</button>
                <button class="choice" type="button" data-choice data-group="road_condition" data-value="rain">Nat</button>
            </div>
        </div>
        <div class="form-row">
            <span class="form-label">Afstand</span>
            <div class="choice-row">
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="100">100m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="250">250m</button>
                <button class="choice active" type="button" data-choice data-group="distance_m" data-value="500">500m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="1000">1000m</button>
                <button class="choice" type="button" data-choice data-group="distance_m" data-value="2000">2km</button>
            </div>
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
    </div>

    <div hidden data-message></div>

    <div class="hero-actions">
        <button class="btn primary" type="submit" data-run @if($limit['blocked']) disabled @endif>Start simulatie</button>
        @guest
            <a class="btn secondary" href="{{ route('login') }}">Inloggen voor garage/profiel</a>
        @endguest
    </div>
</form>

<section class="panel race-track">
    <div class="lane">
        <div class="lane-head"><span data-lane-name="A">Motor A</span><span data-time-a>-</span></div>
        <div class="bar-bg"><div class="bar-fill a" data-bar-a></div></div>
    </div>
    <div class="lane">
        <div class="lane-head"><span data-lane-name="B">Motor B</span><span data-time-b>-</span></div>
        <div class="bar-bg"><div class="bar-fill b" data-bar-b></div></div>
    </div>
    <div class="panel result-panel" data-result>
        <h2 class="card-title" data-result-title></h2>
        <p class="section-sub">Deelbare link: <a class="accent" data-share href="#"></a></p>
    </div>
</section>

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
            limit: @json($limit)
        };
    </script>
    <script src="{{ asset('js/simulation.js') }}?v={{ filemtime(public_path('js/simulation.js')) }}"></script>
@endpush
