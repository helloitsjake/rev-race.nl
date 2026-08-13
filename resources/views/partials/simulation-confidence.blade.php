@php
    $needsReview = collect([$motorA, $motorB])->contains(
        fn ($motor) => in_array($motor->verification_status, ['unverified', 'flagged'], true)
    );
@endphp
<details class="report-disclosure">
    <summary>Over deze uitslag</summary>
    <div class="report-disclosure__body">
        <p>
            Dit is een datagedreven indicatie op basis van een fysicasimulatie, geen gemeten testresultaat. Vermogen, gewicht en cilinderinhoud komen uit de fabrieksspecificaties. Luchtweerstand (drag coefficient en frontaal oppervlak) is bij elk model een schatting &mdash; fabrikanten publiceren dat zelf niet &mdash; wat het resultaat een indicatie maakt, geen exacte meting.
        </p>
        @if($needsReview)
            <p>
                De specificaties van {{ collect([$motorA, $motorB])->first(fn ($motor) => in_array($motor->verification_status, ['unverified', 'flagged'], true))->label() }} zijn nog niet handmatig gecontroleerd tegen een officiële bron.
            </p>
        @endif
        <p>
            <a class="accent" href="{{ route('how-it-works') }}">Lees hoe de simulatie precies werkt</a>.
        </p>
    </div>
</details>
