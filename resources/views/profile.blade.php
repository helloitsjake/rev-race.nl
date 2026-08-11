@extends('layouts.app')

@section('title', 'Mijn account - RevRace')

@section('content')
    <header class="chapter">
        <div class="wrap">
            <div class="page-head">
                <div>
                    <p class="eyebrow">Mijn account</p>
                    <h1>Mijn rijdersprofiel</h1>
                    <p class="lede">Deze gegevens worden gebruikt om simulaties nauwkeuriger te maken voor jouw postuur en ervaring.</p>
                </div>
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.7rem;">
                    <span class="badge">{{ $limit['used'] }} / {{ $limit['limit'] }} simulaties vandaag</span>
                    <a class="note-inline" href="{{ route('garage.index') }}">Naar mijn garage &rarr;</a>
                </div>
            </div>

            <div class="pass" style="max-width:480px; margin-bottom:clamp(32px,4vw,48px);">
                <div class="pass__row"><span>Naam</span><b>{{ $user->name }}</b></div>
                <div class="pass__row"><span>E-mail</span><b>{{ $user->email }}</b></div>
                <div class="pass__row"><span>Lid sinds</span><b>{{ $user->created_at->translatedFormat('F Y') }}</b></div>
                <div class="pass__row"><span>Account</span><b>{{ $user->isPremium() ? 'Premium' : 'Gratis' }}</b></div>
                <div class="pass__row"><span>Lengte</span><b>{{ $user->height_cm ? $user->height_cm . ' cm' : '-' }}</b></div>
                <div class="pass__row"><span>Gewicht</span><b>{{ $user->weight_kg ? $user->weight_kg . ' kg' : '-' }}</b></div>
                <div class="pass__row"><span>Leeftijd</span><b>{{ $user->birthdate ? $user->birthdate->age . ' jaar' : '-' }}</b></div>
                <div class="pass__row"><span>Rijstijl</span><b>{{ $user->riding_style ? ucfirst($user->riding_style) : '-' }}</b></div>
                <div class="pass__row"><span>Rijervaring</span><b>{{ $user->riding_experience_years !== null ? $user->riding_experience_years . ' jaar' : '-' }}</b></div>
                <div class="pass__row"><span>Rijbewijs</span><b>{{ $user->license_category ? 'Categorie ' . $user->license_category : '-' }}</b></div>
            </div>

            <form class="panel" id="profile-form" method="post" action="{{ route('profile.update') }}">
                @csrf
                <div class="panel__head"><span>Profiel bewerken</span></div>
                <div class="auth-row form-grid">
                    <div>
                        <label for="name">Naam</label>
                        <input class="field" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div>
                        <label for="license_category">Rijbewijscategorie</label>
                        <select class="field" id="license_category" name="license_category">
                            @foreach(['A', 'A2', 'A1'] as $category)
                                <option value="{{ $category }}" @selected(old('license_category', $user->license_category) === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="auth-row form-grid">
                    <div>
                        <label for="height_cm">Lengte (cm)</label>
                        <input class="field" id="height_cm" name="height_cm" type="number" min="120" max="230" value="{{ old('height_cm', $user->height_cm) }}">
                    </div>
                    <div>
                        <label for="weight_kg">Gewicht incl. uitrusting (kg)</label>
                        <input class="field" id="weight_kg" name="weight_kg" type="number" min="35" max="180" value="{{ old('weight_kg', $user->weight_kg) }}">
                    </div>
                </div>
                <div class="auth-row form-grid">
                    <div>
                        <label for="birthdate">Geboortedatum</label>
                        <input class="field" id="birthdate" name="birthdate" type="date" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}">
                    </div>
                    <div>
                        <label for="riding_experience_years">Jaren rijervaring</label>
                        <input class="field" id="riding_experience_years" name="riding_experience_years" type="number" min="0" max="70" value="{{ old('riding_experience_years', $user->riding_experience_years) }}">
                    </div>
                </div>
                <div class="auth-row">
                    <label for="riding_style">Rijstijl</label>
                    <select class="field" id="riding_style" name="riding_style">
                        @foreach(['recreatief' => 'Recreatief', 'sportief' => 'Sportief', 'track' => 'Track'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('riding_style', $user->riding_style) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn--primary" type="submit" style="width:100%">Profiel opslaan</button>
            </form>
        </div>
    </header>
@endsection
