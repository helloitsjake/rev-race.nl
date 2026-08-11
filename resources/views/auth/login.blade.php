@extends('layouts.app')

@section('title', 'Inloggen - RevRace')

@section('content')
    <header class="chapter">
        <div class="wrap auth">
            <div>
                <p class="eyebrow">Account</p>
                <h1>Log in en pak je garage en rijdersprofiel er weer bij.</h1>
                <p class="lede">Met een account bewaar je tot 2 motoren in je garage, sla je je rijdersprofiel op voor nauwkeurigere simulaties, en deel je resultaten met &eacute;&eacute;n druk op de knop.</p>
            </div>
            <form class="panel" method="post" action="{{ route('login.store') }}">
                @csrf
                <div class="panel__head"><span>Inloggen</span></div>
                <div class="auth-row">
                    <label for="email">E-mailadres</label>
                    <input class="field" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="auth-row">
                    <label for="password">Wachtwoord</label>
                    <input class="field" id="password" name="password" type="password" required>
                </div>
                <label class="auth-check"><input type="checkbox" name="remember" value="1"> Ingelogd blijven</label>
                <button class="btn btn--primary" type="submit" style="width:100%">Inloggen</button>
                <p class="auth-foot">Nog geen account? <a href="{{ route('register') }}">Registreer gratis</a></p>
                <div class="auth-note">Even proberen zonder eigen account? Demo-login: <b>demo@revrace.nl</b> &middot; wachtwoord <b>revrace</b></div>
            </form>
        </div>
    </header>
@endsection
