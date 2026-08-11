@extends('layouts.app')

@section('title', 'Registreren - RevRace')

@section('content')
    <header class="chapter">
        <div class="wrap auth">
            <div>
                <p class="eyebrow">Account</p>
                <h1>Maak een gratis account en vind sneller je volgende motor.</h1>
                <p class="lede">Gratis account met een garage voor 2 motoren, een rijdersprofiel voor nauwkeurigere simulaties en 10 simulaties per rollend etmaal.</p>
            </div>
            <form class="panel" method="post" action="{{ route('register.store') }}">
                @csrf
                <div class="panel__head"><span>Account aanmaken</span></div>
                <div class="auth-row">
                    <label for="name">Naam</label>
                    <input class="field" id="name" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="auth-row">
                    <label for="email">E-mailadres</label>
                    <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required>
                </div>
                <div class="auth-row">
                    <label for="password">Wachtwoord</label>
                    <input class="field" id="password" name="password" type="password" required>
                </div>
                <div class="auth-row">
                    <label for="password_confirmation">Herhaal wachtwoord</label>
                    <input class="field" id="password_confirmation" name="password_confirmation" type="password" required>
                </div>
                <div class="auth-row form-grid">
                    <div>
                        <label for="weight_kg">Gewicht incl. uitrusting (kg)</label>
                        <input class="field" id="weight_kg" name="weight_kg" type="number" min="35" max="180" value="{{ old('weight_kg') }}">
                    </div>
                    <div>
                        <label for="height_cm">Lengte (cm)</label>
                        <input class="field" id="height_cm" name="height_cm" type="number" min="120" max="230" value="{{ old('height_cm') }}">
                    </div>
                </div>
                <div class="auth-row">
                    <label for="riding_style">Rijstijl</label>
                    <select class="field" id="riding_style" name="riding_style">
                        @foreach (['recreatief' => 'Recreatief', 'sportief' => 'Sportief', 'track' => 'Track'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('riding_style') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn--primary" type="submit" style="width:100%">Account aanmaken</button>
                <p class="auth-foot">Heb je al een account? <a href="{{ route('login') }}">Inloggen</a></p>
            </form>
        </div>
    </header>
@endsection
