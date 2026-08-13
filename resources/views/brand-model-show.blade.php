@extends('layouts.app')

@section('title', $motor->label() . ': specificaties en vergelijkingen - RevRace')
@section('description', $motor->label() . ' levert ' . $motor->power_hp . ' pk bij ' . $motor->weight_kg . ' kg (' . number_format($motor->powerToWeight(), 2) . ' pk/kg). Specificaties, categorie en directe vergelijkingen op RevRace.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Merken', 'item' => route('brands.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $motor->brand, 'item' => route('brands.show', $brandSlug)],
        ['@type' => 'ListItem', 'position' => 4, 'name' => $motor->model . ' ' . $motor->year],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $motor->label(),
    'brand' => ['@type' => 'Brand', 'name' => $motor->brand],
    'category' => $motor->categoryLabel(),
    'additionalProperty' => [
        ['@type' => 'PropertyValue', 'name' => 'Vermogen', 'value' => $motor->power_hp, 'unitText' => 'pk'],
        ['@type' => 'PropertyValue', 'name' => 'Koppel', 'value' => $motor->torque_nm, 'unitText' => 'Nm'],
        ['@type' => 'PropertyValue', 'name' => 'Gewicht', 'value' => $motor->weight_kg, 'unitText' => 'kg'],
        ['@type' => 'PropertyValue', 'name' => 'Cilinderinhoud', 'value' => $motor->displacement_cc, 'unitText' => 'cc'],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel">
                <a href="{{ route('home') }}">Home</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.index') }}">Merken</a><span class="sep">&rarr;</span>
                <a href="{{ route('brands.show', $brandSlug) }}">{{ $motor->brand }}</a><span class="sep">&rarr;</span>
                <span>{{ $motor->model }} {{ $motor->year }}</span>
            </nav>
            <span class="eyebrow">{{ $motor->categoryLabel() }}</span>
            <h1>{{ $motor->label() }}</h1>
            <p class="lede">
                {{ $motor->label() }} levert {{ $motor->power_hp }} pk bij {{ $motor->weight_kg }} kg, een vermogen/gewicht-verhouding van {{ number_format($motor->powerToWeight(), 2) }} pk/kg.
                @if($motor->isA2Eligible())
                    Op basis van deze specificaties A2-geschikt (zonder opvoerkit).
                @endif
            </p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="spec-head">
                <span class="eyebrow">Specificaties</span>
            </div>
            <div class="spec spec--single">
                <div class="spec__row spec__row--head">
                    <div class="spec__label">Motor</div>
                    <div class="spec__value spec__value--a">{{ $motor->label() }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Vermogen</div>
                    <div class="spec__value">{{ $motor->power_hp }} pk</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Koppel</div>
                    <div class="spec__value">{{ $motor->torque_nm }} Nm</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Gewicht</div>
                    <div class="spec__value">{{ $motor->weight_kg }} kg</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Pk per kg</div>
                    <div class="spec__value">{{ number_format($motor->powerToWeight(), 2) }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Motortype</div>
                    <div class="spec__value">{{ $motor->engine_type }}</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Cilinderinhoud</div>
                    <div class="spec__value">{{ $motor->displacement_cc }} cc</div>
                </div>
                @if($motor->top_speed_kmh)
                    <div class="spec__row">
                        <div class="spec__label">Topsnelheid</div>
                        <div class="spec__value">{{ $motor->top_speed_kmh }} km/h</div>
                    </div>
                @endif
                <div class="spec__row">
                    <div class="spec__label">Categorie</div>
                    <div class="spec__value">
                        @if($motor->category)
                            <a class="accent" href="{{ route('segments.show', $motor->category) }}">{{ $motor->categoryLabel() }}</a>
                        @else
                            {{ $motor->categoryLabel() }}
                        @endif
                    </div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">A2-geschikt</div>
                    <div class="spec__value">{{ $motor->isA2Eligible() ? 'Ja' : 'Nee' }}</div>
                </div>
            </div>

            <details class="report-disclosure">
                <summary>Klopt deze informatie niet?</summary>
                <form method="post" action="{{ route('motors.report', $motor) }}" class="form-card" style="margin-top:1rem">
                    @csrf
                    <div class="form-honeypot" aria-hidden="true">
                        <label for="report-website">Laat dit veld leeg</label>
                        <input type="text" id="report-website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="form-row" style="margin-bottom:0.4rem">
                        <label for="report-message">Wat klopt er niet?</label>
                        <textarea id="report-message" name="message" rows="3" required placeholder="Bijv. het vermogen of gewicht klopt niet met de fabrieksopgave"></textarea>
                    </div>
                    <div class="form-row">
                        <label for="report-email">E-mailadres (optioneel, voor een reactie)</label>
                        <input id="report-email" name="reporter_email" type="email">
                    </div>
                    <div class="form-foot">
                        <button class="btn btn--ghost" type="submit">Melding versturen</button>
                    </div>
                </form>
            </details>
        </div>
    </section>

    @if($comparisons->isNotEmpty())
        <section class="chapter">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <span class="eyebrow">Vergelijkingen</span>
                        <h2>{{ $motor->label() }} tegen andere {{ Str::lower($motor->categoryLabel()) }}s</h2>
                    </div>
                </div>
                <div class="kb-grid">
                    @foreach($comparisons as $row)
                        <a class="kb-card" href="{{ route('compare.show', $row['slug']) }}">
                            <h3>{{ $motor->label() }}</h3>
                            <p>vs {{ $row['motor']->label() }}</p>
                            <span class="kb-card__link">Bekijk vergelijking &rarr;</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap cta-line">
            <p class="eyebrow">Twijfel je?</p>
            <h2>Race de {{ $motor->model }} tegen een andere motor.</h2>
            <p class="lede">Op droog, vochtig en nat asfalt &mdash; zelfde twee motoren, soms een andere winnaar.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start simulatie</a>
        </div>
    </section>
@endsection
