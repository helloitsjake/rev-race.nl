@extends('layouts.app')

@section('title', 'Over RevRace - Jake en Rory Andreas')
@section('description', 'RevRace is van de broers Jake en Rory Andreas. Ze rijden trackdays op een Honda CB1300 en een KTM 1290 Super Duke, en bouwden het motoradvies dat ze zelf hadden willen hebben.')

@section('content')
    @include('partials.riders', ['turn' => 'T0', 'first' => true])

    <section class="chapter on-dark" id="missie">
        <div class="wrap">
            <div class="sec-head" data-reveal>
                <div><p class="turn"><b>T1</b> Waar we naartoe willen</p><h2>Een kennisbank voor elke motorrijder</h2></div>
                <p>RevRace groeit door, met steeds meer motoren, meer simulaties en meer manieren om de juiste motor te vinden. Voor iedereen, niet alleen voor wie de fabrikanten het luidst aanspreken.</p>
            </div>
            <div class="kb-grid">
                <article class="kb-card" data-reveal>
                    <p class="kb-card__stage">Eerlijke rekensom</p>
                    <h3>Vermogen, gewicht, luchtweerstand, wegconditie</h3>
                    <p>Geen los rijtje getallen naast elkaar, maar een berekening die laat zien wat een motor doet op het asfalt.</p>
                </article>
                <article class="kb-card" data-reveal style="--d:70ms">
                    <p class="kb-card__stage">Voor elke rijder</p>
                    <h3>Van eerste rijbewijs tot het circuit</h3>
                    <p>Of je twijfelt tussen twee A2-motoren of tussen twee supersporters: de natuurkunde erachter blijft hetzelfde.</p>
                </article>
                <article class="kb-card" data-reveal style="--d:140ms">
                    <p class="kb-card__stage">In ontwikkeling</p>
                    <h3>Steeds meer motoren en simulaties</h3>
                    <p>Nu {{ $motors->count() }} motoren in de database, en er komen nieuwe modellen, wegcondities en manieren om te vergelijken bij.</p>
                </article>
            </div>
        </div>
    </section>

    <div class="finish on-dark" style="border-top:1px solid var(--asphalt-line)">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Zelf proberen</p>
            <h2>Pak twee motoren en draai een race</h2>
            <p>En zie wat er gebeurt zodra de lichten uitgaan, in plaats van te varen op een specificatieblad.</p>
            <div class="finish__row">
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg></a>
                <a class="btn btn--line" href="{{ route('wizard.index') }}">Of vind je motor</a>
            </div>
        </div>
    </div>
@endsection
