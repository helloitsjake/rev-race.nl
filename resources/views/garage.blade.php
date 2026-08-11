@extends('layouts.app')

@section('title', 'Mijn garage - RevRace')

@section('content')
    <header class="chapter">
        <div class="wrap">
            <div class="page-head">
                <div>
                    <p class="eyebrow">Garage</p>
                    <h1>Mijn garage</h1>
                    <p class="lede">Sla maximaal 2 motoren op in het gratis account en laad ze snel in de simulatie.</p>
                    <p class="note-inline">Vul je <a href="{{ route('profile.edit') }}">rijdersprofiel</a> in voor nauwkeurigere simulatie-uitkomsten met deze motoren.</p>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.9rem;">
                    <span class="badge badge--limit">{{ $garage->count() }} / 2 motoren</span>
                    <form method="post" action="{{ route('garage.share') }}">
                        @csrf
                        <button class="btn btn--ghost" type="submit">Deel mijn garage</button>
                    </form>
                </div>
            </div>

            @if(auth()->user()->garage_token)
                <p class="note-inline">Publieke link: <a href="{{ route('garage.public', auth()->user()->garage_token) }}">{{ route('garage.public', auth()->user()->garage_token) }}</a></p>
            @endif

            <form class="panel" method="post" action="{{ route('garage.store') }}" style="margin-top:clamp(32px,4vw,48px)">
                @csrf
                <div class="panel__head"><span>Motor toevoegen</span></div>
                <div class="auth-row form-grid">
                    <div>
                        <label for="motor_id">Kies een motor</label>
                        <select class="field" id="motor_id" name="motor_id" required>
                            <option value="">Kies een motor</option>
                            @foreach($motors as $motor)
                                <option value="{{ $motor->id }}">{{ $motor->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="nickname">Bijnaam (optioneel)</label>
                        <input class="field" id="nickname" name="nickname" placeholder="Bijv. mijn woon-werk motor">
                    </div>
                </div>
                <button class="btn btn--primary" type="submit">Opslaan in garage</button>
            </form>

            <div class="garage-grid">
                @forelse($garage as $entry)
                    <article class="garage-card">
                        <p class="garage-card__meta">{{ $entry->motor->brand }} &middot; {{ $entry->motor->year }}</p>
                        <h3>{{ $entry->nickname ?: $entry->motor->model }}</h3>
                        <div class="garage-card__specs">
                            <div class="row"><span>Vermogen</span><span>{{ $entry->motor->power_hp }} pk</span></div>
                            <div class="row"><span>Koppel</span><span>{{ $entry->motor->torque_nm }} Nm</span></div>
                            <div class="row"><span>Gewicht</span><span>{{ $entry->motor->weight_kg }} kg</span></div>
                        </div>
                        <div class="garage-card__actions">
                            <a class="btn btn--ghost" href="{{ route('simulation.index') }}">Simuleer</a>
                            <form method="post" action="{{ route('garage.destroy', $entry) }}">
                                @csrf
                                @method('DELETE')
                                <button class="link-danger" type="submit">Verwijder</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="panel" style="grid-column:1 / -1">
                        <div class="panel__head"><span>Lege garage</span></div>
                        <h3>Je garage is leeg</h3>
                        <p style="color:var(--paper-60); margin-top:0.6em;">Voeg je eerste motor toe om hem later sneller te vergelijken.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </header>
@endsection
