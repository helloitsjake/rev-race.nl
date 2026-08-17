@extends('layouts.app')

@section('title', 'Contact - RevRace')
@section('description', 'Vraag, opmerking of een motor die niet klopt in de database? Stuur een bericht naar RevRace, je krijgt persoonlijk antwoord van de bouwer.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap" style="max-width:640px">
            <span class="eyebrow">Contact</span>
            <h1 style="font-size:clamp(2.25rem,4vw,3.5rem)">Contact</h1>
            <p class="lede">Voor partners, bugs en inhoudelijke correcties.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap" style="max-width:640px">
            <div class="form-card" style="display:flex;justify-content:space-between;align-items:center;gap:1rem;margin-bottom:clamp(24px,3vw,36px)">
                <span class="note-inline" style="margin-top:0">Partners</span>
                <a class="btn btn--ghost" href="mailto:partners@rev-race.nl" style="padding:0.6em 1.1em;font-size:0.875rem">partners@rev-race.nl</a>
            </div>

            <form class="form-card" method="post" action="{{ route('contact.store') }}">
                @csrf
                <div class="form-honeypot" aria-hidden="true">
                    <label for="website">Laat dit veld leeg</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <div class="form-row">
                    <label for="name">Naam</label>
                    <input id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="form-row">
                    <label for="email">E-mailadres</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="form-row" style="margin-bottom:0.4rem">
                    <label for="message">Bericht</label>
                    <textarea id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
                </div>
                <div class="form-foot">
                    <button class="btn btn--primary" type="submit">Versturen</button>
                </div>
            </form>
        </div>
    </section>
@endsection
