@extends('layouts.app')

@section('title', 'Inloggen - RevRace')

@section('content')
    <div class="auth-shell">
        <form class="auth-card" method="post" action="{{ route('login.store') }}">
            @csrf
            <h1 class="card-title">Inloggen</h1>
            <div class="form-row">
                <label class="form-label" for="email">E-mailadres</label>
                <input class="input" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-row">
                <label class="form-label" for="password">Wachtwoord</label>
                <input class="input" id="password" name="password" type="password" required>
            </div>
            <div class="form-row">
                <label><input type="checkbox" name="remember" value="1"> Ingelogd blijven</label>
            </div>
            <button class="btn primary" type="submit" style="width:100%">Inloggen</button>
            <p class="page-sub">Nog geen account? <a class="accent" href="{{ route('register') }}">Registreer gratis</a></p>
        </form>
    </div>
@endsection
