@extends('layouts.app')

@section('title', 'Mijn garage - RevRace')

@section('content')
    <div class="chart-head" style="align-items:flex-start">
        <header>
            <span class="eyebrow">Garage</span>
            <h1 class="page-title">Mijn garage</h1>
            <p class="page-sub">Sla maximaal 2 motoren op in het gratis account en laad ze snel in de simulatie.</p>
        </header>
        <form method="post" action="{{ route('garage.share') }}">
            @csrf
            <button class="btn secondary" type="submit">Deel mijn garage</button>
        </form>
    </div>

    @if(auth()->user()->garage_token)
        <p class="small">Publieke link: <a class="accent" href="{{ route('garage.public', auth()->user()->garage_token) }}">{{ route('garage.public', auth()->user()->garage_token) }}</a></p>
    @endif

    <form class="panel" method="post" action="{{ route('garage.store') }}">
        @csrf
        <div class="sim-grid">
            <div class="form-row">
                <label class="form-label" for="motor_id">Motor toevoegen</label>
                <select class="select" id="motor_id" name="motor_id" required>
                    <option value="">Kies een motor</option>
                    @foreach($motors as $motor)
                        <option value="{{ $motor->id }}">{{ $motor->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-row">
                <label class="form-label" for="nickname">Bijnaam optioneel</label>
                <input class="input" id="nickname" name="nickname" placeholder="Bijv. mijn woon-werk motor">
            </div>
        </div>
        <button class="btn primary" type="submit">Opslaan in garage</button>
    </form>

    <section class="section">
        <div class="garage-list">
            @forelse($garage as $entry)
                <article class="card">
                    @include('partials.motor-photo', ['motor' => $entry->motor, 'style' => 'margin-bottom:10px'])
                    <div class="eyebrow">{{ $entry->motor->brand }} · {{ $entry->motor->year }}</div>
                    <h2 class="card-title">{{ $entry->nickname ?: $entry->motor->model }}</h2>
                    <div class="spec-row"><span class="spec-label">Vermogen</span><span class="spec-value">{{ $entry->motor->power_hp }} pk</span></div>
                    <div class="spec-row"><span class="spec-label">Koppel</span><span class="spec-value">{{ $entry->motor->torque_nm }} Nm</span></div>
                    <div class="spec-row"><span class="spec-label">Gewicht</span><span class="spec-value">{{ $entry->motor->weight_kg }} kg</span></div>
                    <div class="garage-actions" style="margin-top:14px">
                        <a class="btn secondary" href="{{ route('simulation.index') }}">Simuleer</a>
                        <form method="post" action="{{ route('garage.destroy', $entry) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn danger" type="submit">Verwijder</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="panel">
                    <h2 class="card-title">Je garage is leeg</h2>
                    <p class="page-sub">Voeg je eerste motor toe om hem later sneller te vergelijken.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
