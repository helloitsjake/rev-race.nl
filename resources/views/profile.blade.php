@extends('layouts.app')

@section('title', 'Rijdersprofiel - RevRace')

@section('content')
    <header>
        <h1 class="page-title">Rijdersprofiel</h1>
        <p class="page-sub">Deze waarden kunnen optioneel worden meegenomen in simulaties.</p>
    </header>

    <form class="panel" method="post" action="{{ route('profile.update') }}">
        @csrf
        <div class="sim-grid">
            <div>
                <div class="form-row">
                    <label class="form-label" for="name">Naam</label>
                    <input class="input" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="form-row">
                    <label class="form-label" for="weight_kg">Rijdersgewicht incl. uitrusting</label>
                    <input class="input" id="weight_kg" name="weight_kg" type="number" min="35" max="180" value="{{ old('weight_kg', $user->weight_kg) }}">
                </div>
                <div class="form-row">
                    <label class="form-label" for="height_cm">Lengte</label>
                    <input class="input" id="height_cm" name="height_cm" type="number" min="120" max="230" value="{{ old('height_cm', $user->height_cm) }}">
                </div>
                <div class="form-row">
                    <label class="form-label" for="age">Leeftijd</label>
                    <input class="input" id="age" name="age" type="number" min="16" max="90" value="{{ old('age', $user->age) }}">
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
                <button class="btn primary" type="submit">Profiel opslaan</button>
            </div>
        </div>
    </form>
@endsection
