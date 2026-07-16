@extends('layouts.app')

@section('title', 'Mijn account - RevRace')

@section('content')
    <div class="account-shell">
        <aside class="account-sidebar">
            <div class="sidebar-label">Account</div>
            <a class="sidebar-link active" href="{{ route('profile.edit') }}">Mijn profiel</a>
            <a class="sidebar-link" href="{{ route('garage.index') }}">Mijn garage</a>
            <a class="sidebar-link" href="{{ route('simulation.index') }}">Simulaties</a>

            <div class="sidebar-label">Instellingen</div>
            <a class="sidebar-link" href="{{ route('privacy') }}">Privacy</a>

            <div class="sidebar-widget">
                <div class="form-label">Simulaties vandaag</div>
                <div class="metric-value" style="color:var(--teal)">{{ $limit['used'] }} / {{ $limit['limit'] }}</div>
                <div class="limit-track" style="margin-top:8px">
                    <div class="limit-fill" style="width:{{ min(100, ($limit['used'] / $limit['limit']) * 100) }}%"></div>
                </div>
            </div>
        </aside>

        <div class="account-content">
            <div class="chart-head" style="align-items:flex-start">
                <div>
                    <h1 class="page-title">Mijn account</h1>
                    <p class="page-sub">Deze waarden kunnen optioneel worden meegenomen in simulaties.</p>
                </div>
                <button class="btn primary" type="submit" form="profile-form">Profiel opslaan</button>
            </div>

            <div class="profile-summary">
                <span class="avatar">{{ Str::of($user->name)->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('') }}</span>
                <div>
                    <div style="font-weight:700;font-size:16px">{{ Str::upper($user->name) }}</div>
                    <div class="small">{{ $user->email }} &middot; lid sinds {{ $user->created_at->translatedFormat('F Y') }} &middot; {{ $user->isPremium() ? 'premium account' : 'gratis account' }}</div>
                </div>
            </div>

            <form class="panel" id="profile-form" method="post" action="{{ route('profile.update') }}">
                @csrf
                <div class="eyebrow">Rijdersprofiel</div>
                <div class="sim-grid">
                    <div>
                        <div class="form-row">
                            <label class="form-label" for="name">Naam</label>
                            <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="weight_kg">Rijdersgewicht incl. uitrusting (kg)</label>
                            <input class="input" id="weight_kg" name="weight_kg" type="number" min="35" max="180" value="{{ old('weight_kg', $user->weight_kg) }}">
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="height_cm">Lengte (cm)</label>
                            <input class="input" id="height_cm" name="height_cm" type="number" min="120" max="230" value="{{ old('height_cm', $user->height_cm) }}">
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="birthdate">Geboortedatum</label>
                            <input class="input" id="birthdate" name="birthdate" type="date" value="{{ old('birthdate', $user->birthdate?->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div>
                        <div class="form-row">
                            <label class="form-label" for="riding_style">Rijstijl</label>
                            <select class="select" id="riding_style" name="riding_style">
                                @foreach(['recreatief' => 'Recreatief', 'sportief' => 'Sportief', 'track' => 'Track'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('riding_style', $user->riding_style) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="riding_experience_years">Jaren rijervaring</label>
                            <input class="input" id="riding_experience_years" name="riding_experience_years" type="number" min="0" max="70" value="{{ old('riding_experience_years', $user->riding_experience_years) }}">
                        </div>
                        <div class="form-row">
                            <label class="form-label" for="license_category">Rijbewijscategorie</label>
                            <select class="select" id="license_category" name="license_category">
                                @foreach(['A', 'A2', 'A1'] as $category)
                                    <option value="{{ $category }}" @selected(old('license_category', $user->license_category) === $category)>{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button class="btn primary" type="submit" style="width:100%">Profiel opslaan</button>
            </form>
        </div>
    </div>
@endsection
