@extends('layouts.app')

@section('title', 'Hoe RevRace jouw motorvergelijking berekent')
@section('description', 'Ontdek hoe RevRace motoren simuleert op basis van vermogen, gewicht, luchtweerstand en wegconditie, en waarom die berekening zo nauwkeurig is.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Is deze simulatie echt nauwkeurig?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Ja, we gebruiken dezelfde natuurkundige principes die ook in de motorsport en voertuigontwikkeling gebruikt worden: vermogen tegen gewicht, luchtweerstand en de tractielimiet per wegconditie. We houden onze motorendatabase voortdurend up to date zodat de simulatie relevant blijft naarmate nieuwe modellen uitkomen.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Waarom laten jullie de formule niet zien?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Net als bij elk goed recept zit het verschil in de details. De rekenmethode is het resultaat van veel testen en fijnslijpen, en dat is precies waarom RevRace anders aanvoelt dan een simpele tabel met specificaties naast elkaar.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Kan ik mijn eigen rijdersgewicht meenemen?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Zeker, met een gratis account vul je je rijdersprofiel in en reken je dat automatisch mee in elke race.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Kan ik ook topsnelheid en remafstand vergelijken?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Ja. Naast de rechte lijn en de kronkelweg kun je op de simulatiepagina ook kiezen voor topsnelheid (op basis van de opgegeven fabrieksspecificatie) en remafstand vanaf een zelf gekozen snelheid en wegconditie.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Is RevRace ook geschikt als dit mijn eerste motor wordt?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Juist dan is RevRace handig. Twijfel je tussen een instapper en iets stoerders, vergelijk ze naast elkaar op gewicht, vermogen en hoe ze zich gedragen bij regen of in de bocht, in plaats van te varen op wat de verkoper zegt.',
            ],
        ],
    ],
]) !!}
</script>
@endpush

@section('content')
    <header class="chapter">
        <div class="wrap" style="max-width:760px">
            <span class="eyebrow">Hoe het werkt</span>
            <h1>Hoe RevRace jouw motorvergelijking berekent</h1>
            <p class="lede" style="max-width:56ch">Twee motoren invoeren en binnen enkele seconden een uitslag zien, dat voelt bijna te makkelijk. Toch zit er een serieuze rekenkern achter elke race.</p>
        </div>
    </header>

    <section class="chapter chapter--dark">
        <div class="wrap">
            <div class="article-body">
                <span class="eyebrow">De rekenkern</span>
                <p>Elke simulatie start met de technische specificaties van een motor: vermogen, koppel, gewicht, cilinderinhoud en het type motorblok. Die cijfers halen we op uit een uitgebreide database en waar nodig via kunstmatige intelligentie, die vervolgens realistische aannames doet over zaken als luchtweerstand op basis van het type carrosserie.</p>
                <p>Vervolgens rekent onze fysica engine, seconde voor seconde, uit hoe een motor zich gedraagt op het gekozen traject: een rechte sprint of een kronkelweg vol bochten.</p>
                <p>We delen bewust niet de precieze rekenformules achter de schermen, net zoals een goede kok zijn recept niet op straat gooit. Wat we wel delen is het resultaat: een uitslag waar je op kunt vertrouwen, of je nu twijfelt tussen twee specifieke modellen of gewoon wilt weten wat voor type motor bij jouw rijstijl past.</p>
            </div>
        </div>
    </section>

    <section class="chapter" id="variabelen">
        <div class="wrap">
            <span class="eyebrow">De methode</span>
            <h2 class="spec-head">Van specificatie naar startopstelling</h2>
            <p class="lede">Elke race wordt uitgerekend met dezelfde vier variabelen die op het asfalt het verschil maken.</p>
            <div class="spec">
                <div class="spec__row">
                    <div class="spec__label">Vermogen</div>
                    <div class="spec__value">pk · koppel (Nm)</div>
                    <div class="spec__note">Pk op papier zegt weinig zonder de verhouding tot gewicht. Koppel bepaalt hoe die pk's zich vertalen naar acceleratie vanaf lage toeren.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Gewicht</div>
                    <div class="spec__value">Rijklaar gewicht (kg)</div>
                    <div class="spec__note">Samen met vermogen bepaalt gewicht de pk/kg-ratio, de eerste indicatie van hoe een motor zich gedraagt in een sprint.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Luchtweerstand</div>
                    <div class="spec__value">Cw · A-waarde</div>
                    <div class="spec__note">Waarom een naakte motor bij topsnelheid inlevert op een verkleed model met dezelfde pk/kg-ratio.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Wegconditie</div>
                    <div class="spec__value">Droog · vochtig · nat</div>
                    <div class="spec__note">Dezelfde twee motoren, een andere winnaar. Grip verandert de uitslag volledig, zie hieronder.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight" id="wegcondities">
        <div class="wrap">
            <span class="eyebrow">Wegconditie in detail</span>
            <h2 class="spec-head">Dezelfde motoren, een andere winnaar</h2>
            <p class="lede">Elke wegconditie heeft een eigen tractiewaarde (µ) voor optrekken, remmen en bochten.</p>
            <div class="spec">
                <div class="spec__row">
                    <div class="spec__label">Droog asfalt</div>
                    <div class="spec__value">µ tractie 1.00 · rem 1.00 · bocht 1.00</div>
                    <div class="spec__note">Maximale grip. Het pure vermogen van een motor mag spreken.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Vochtig asfalt</div>
                    <div class="spec__value">µ tractie 0.70 · rem 0.72 · bocht 0.65</div>
                    <div class="spec__note">Tractie neemt af, remwegen worden langer. Motoren met gelijkmatig koppel beginnen het verschil te maken.</div>
                </div>
                <div class="spec__row">
                    <div class="spec__label">Nat asfalt</div>
                    <div class="spec__value">µ tractie 0.45 · rem 0.50 · bocht 0.40</div>
                    <div class="spec__note">Hier wint vaak niet de motor met het meeste vermogen, maar de motor met de beste geometrie en gewichtsverdeling.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="chapter chapter--dark" id="faq">
        <div class="wrap">
            <span class="eyebrow">Veelgestelde vragen</span>
            <h2>Nog vragen over de berekening</h2>
            <div class="faq">
                <details class="faq-item">
                    <summary><span>Is deze simulatie echt nauwkeurig?</span></summary>
                    <p>Ja, we gebruiken dezelfde natuurkundige principes die ook in de motorsport en voertuigontwikkeling gebruikt worden: vermogen tegen gewicht, luchtweerstand en de tractielimiet per wegconditie. We houden onze motorendatabase voortdurend up to date zodat de simulatie relevant blijft naarmate nieuwe modellen uitkomen.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Waarom laten jullie de formule niet zien?</span></summary>
                    <p>Net als bij elk goed recept zit het verschil in de details. De rekenmethode is het resultaat van veel testen en fijnslijpen, en dat is precies waarom RevRace anders aanvoelt dan een simpele tabel met specificaties naast elkaar.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Kan ik mijn eigen rijdersgewicht meenemen?</span></summary>
                    <p>Zeker, met een gratis account vul je je rijdersprofiel in en reken je dat automatisch mee in elke race.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Kan ik ook topsnelheid en remafstand vergelijken?</span></summary>
                    <p>Ja. Naast de rechte lijn en de kronkelweg kun je op de simulatiepagina ook kiezen voor topsnelheid (op basis van de opgegeven fabrieksspecificatie) en remafstand vanaf een zelf gekozen snelheid en wegconditie.</p>
                </details>
                <details class="faq-item">
                    <summary><span>Is RevRace ook geschikt als dit mijn eerste motor wordt?</span></summary>
                    <p>Juist dan is RevRace handig. Twijfel je tussen een instapper en iets stoerders, vergelijk ze naast elkaar op gewicht, vermogen en hoe ze zich gedragen bij regen of in de bocht, in plaats van te varen op wat de verkoper zegt.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap cta-split">
            <div>
                <span class="eyebrow">Zelf proberen</span>
                <h2>Kies twee motoren, kies een wegconditie, en race.</h2>
            </div>
            <div class="panel">
                <span class="eyebrow" style="margin-bottom:0">Simulatie</span>
                <h3>Twee motoren. Eén asfalt.</h3>
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start simulatie</a>
            </div>
        </div>
    </section>
@endsection
