@extends('layouts.app')

@section('title', 'Welke motor past bij mij - RevRace')
@section('description', 'Beantwoord twee vragen over je rijstijl en ervaring en zie welke motoren uit de RevRace database bij je passen.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Welke motor past bij mij'],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header>
        <span class="eyebrow">Welke motor past bij mij</span>
        <h1 class="page-title">Twee vragen, en je weet waar je moet zoeken</h1>
        <p class="page-sub">Geen specs vergelijken tussen modellen die je toevallig al kende. Kies je rijstijl en ervaring, en zie welke motoren uit onze database erbij passen.</p>
    </header>

    <form class="panel" method="get" action="{{ route('wizard.index') }}">
        <div class="form-row">
            <span class="form-label">Wat voor rijstijl past het best bij jou?</span>
            <div class="choice-row">
                @foreach($categories as $key => $label)
                    <label class="choice">
                        <input type="radio" name="rijstijl" value="{{ $key }}" @checked($selectedRijstijl === $key)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="form-row">
            <span class="form-label">Wat is je rij-ervaring?</span>
            <div class="choice-row">
                @foreach($experienceLevels as $key => $label)
                    <label class="choice">
                        <input type="radio" name="ervaring" value="{{ $key }}" @checked($selectedErvaring === $key)>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <button class="btn primary" type="submit">Toon match</button>
    </form>

    @if($selectedRijstijl && $selectedErvaring)
        <section class="section">
            @if($matches->isNotEmpty())
                <h2 class="section-title">{{ $matches->count() === 1 ? 'Deze motor past bij je' : 'Deze motoren passen bij je' }}</h2>
                <div class="card-grid">
                    @foreach($matches as $motor)
                        <article class="card">
                            <div class="chart-head" style="margin-bottom:10px">
                                <span class="badge">{{ $motor->categoryLabel() }}</span>
                                @if($motor->isA2Eligible())
                                    <span class="badge" style="border-color:var(--teal);color:var(--teal)">A2 geschikt</span>
                                @endif
                            </div>
                            <h3 class="card-title">{{ $motor->label() }}</h3>
                            <div class="spec-row">
                                <span class="spec-label">Vermogen</span>
                                <span class="spec-value">{{ $motor->power_hp }} pk</span>
                            </div>
                            <div class="spec-row">
                                <span class="spec-label">Gewicht</span>
                                <span class="spec-value">{{ $motor->weight_kg }} kg</span>
                            </div>
                            <div class="hero-actions" style="margin-top:14px">
                                <a class="btn primary" href="{{ route('simulation.index', ['motor_a' => $motor->id]) }}">Simuleer met deze motor</a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="panel">
                    <h2 class="card-title">Nog geen match in deze combinatie</h2>
                    <p style="margin-top:8px">Voor {{ Str::lower($categories[$selectedRijstijl]) }} in combinatie met {{ Str::lower($experienceLevels[$selectedErvaring]) }} staat op dit moment geen motor in onze database. De database groeit nog, probeer een andere rijstijl of bekijk de volledige toplijst.</p>

                    @if($fallback && $fallback->isNotEmpty())
                        <p class="small" style="margin-top:14px;color:var(--dim)">Wel A2 geschikt, in een andere categorie:</p>
                        <div class="card-grid" style="margin-top:12px">
                            @foreach($fallback as $motor)
                                <article class="card">
                                    <span class="badge">{{ $motor->categoryLabel() }}</span>
                                    <h3 class="card-title" style="margin-top:8px">{{ $motor->label() }}</h3>
                                    <div class="hero-actions" style="margin-top:12px">
                                        <a class="btn secondary" href="{{ route('simulation.index', ['motor_a' => $motor->id]) }}">Simuleer met deze motor</a>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <div class="hero-actions" style="margin-top:18px">
                        <a class="btn secondary" href="{{ route('toplijst.show', 'beste-pk-kg-verhouding') }}">Bekijk een toplijst</a>
                        <a class="btn ghost" href="{{ route('simulation.index') }}">Naar de simulator</a>
                    </div>
                </div>
            @endif
        </section>
    @endif
@endsection
