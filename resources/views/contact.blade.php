@extends('layouts.app')

@section('title', 'Contact - RevRace')

@section('content')
    <header>
        <h1 class="page-title">Contact</h1>
        <p class="page-sub">Voor partners, bugs en inhoudelijke correcties.</p>
    </header>

    <section class="panel">
        <div class="spec-row"><span class="spec-label">E-mail</span><span class="spec-value"><a class="accent" href="mailto:jake@helloitsme.online">jake@helloitsme.online</a></span></div>
        <div class="spec-row"><span class="spec-label">Partners</span><span class="spec-value"><a class="accent" href="mailto:partners@rev-race.nl">partners@rev-race.nl</a></span></div>
    </section>

    <form class="panel" method="post" action="{{ route('contact.store') }}" style="margin-top:20px">
        @csrf
        <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label for="website">Laat dit veld leeg</label>
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>
        <div class="form-row">
            <label class="form-label" for="name">Naam</label>
            <input class="input" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div class="form-row">
            <label class="form-label" for="email">E-mailadres</label>
            <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
        </div>
        <div class="form-row">
            <label class="form-label" for="message">Bericht</label>
            <textarea class="input" id="message" name="message" rows="5" required>{{ old('message') }}</textarea>
        </div>
        <button class="btn primary" type="submit">Versturen</button>
    </form>
@endsection
