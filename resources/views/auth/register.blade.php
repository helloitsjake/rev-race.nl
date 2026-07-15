@extends('layouts.app')

@section('title', 'Registreren - RevRace')

@section('content')
    <div class="auth-shell">
        <form class="auth-card" method="post" action="{{ route('register.store') }}">
            @csrf
            <h1 class="card-title">Account aanmaken</h1>
            <p class="page-sub">Gratis account met garage, profiel en 10 simulaties per rolling 24 uur.</p>
            <div class="form-row">
                <label class="form-label" for="name">Naam</label>
                <input class="input" id="name" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="email">E-mailadres</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="password">Wachtwoord</label>
                <input class="input" id="password" name="password" type="password" required>
            </div>
            <div class="form-row">
                <label class="form-label" for="password_confirmation">Herhaal wachtwoord</label>
                <input class="input" id="password_confirmation" name="password_confirmation" type="password" required>
            </div>
            <div class="sim-grid">
                <div class="form-row">
                    <label class="form-label" for="weight_kg">Gewicht incl. uitrusting</label>
                    <input class="input" id="weight_kg" name="weight_kg" type="number" min="35" max="180" value="{{ old('weight_kg') }}">
                </div>
                <div class="form-row">
                    <label class="form-label" for="height_cm">Lengte</label>
                    <input class="input" id="height_cm" name="height_cm" type="number" min="120" max="230" value="{{ old('height_cm') }}">
                </div>
            </div>
            <div class="form-row">
                <label class="form-label" for="riding_style">Rijstijl</label>
                <select class="select" id="riding_style" name="riding_style">
                    <option value="recreatief">Recreatief</option>
                    <option value="sportief">Sportief</option>
                    <option value="track">Track</option>
                </select>
            </div>
            <button class="btn primary" type="submit" style="width:100%">Account aanmaken</button>
            <p class="page-sub">Heb je al een account? <a class="accent" href="{{ route('login') }}">Inloggen</a></p>
        </form>
    </div>
@endsection
