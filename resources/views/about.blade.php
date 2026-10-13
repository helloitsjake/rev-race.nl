@extends('layouts.app')

@section('title', 'Over RevRace - Jake en Rory Andreas')
@section('description', 'RevRace begon in juni 2026 met een discussie tussen de broers Jake en Rory Andreas: wie is er sneller in de bergen, de rijder of de motor? Hun verhaal, hun rondetijden en waar ze naartoe willen.')

@section('content')
    @php
        $arrow = '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg>';
        $icons = config('kennis.icons');
        $fmt = fn ($n) => number_format($n, 2, ',', '.');
    @endphp

    {{-- T0: binnenkomst --}}
    <header class="chapter chapter--tight about-hero">
        <div class="wrap about-hero__grid">
            <div class="about-hero__text">
                <p class="turn"><b>T0</b> Juni 2026</p>
                <h1>Wie is er sneller in de bergen?</h1>
                <p class="lede">RevRace begon met een discussie tussen twee broers. Rory haalt Jake op elk recht stuk in. Maar in de bochten rijdt Jake regelmatig nog voorop. Ligt dat aan de jaren ervaring, of is de motor daar echt sneller?</p>
                <p class="about-hero__answer">We besloten het uit te rekenen. Dat werd RevRace.</p>
            </div>
            <figure class="about-hero__photo">
                <img src="{{ asset('images/over-ons/pitstraat.jpg') }}" alt="Rory en Jake Andreas in racepak in de pitstraat van TT Circuit Assen" width="1100" height="1375">
                <figcaption>Rory (links) en Jake in de pitstraat van TT Circuit Assen, september 2026.</figcaption>
            </figure>
        </div>
    </header>

    {{-- T1: de vraag --}}
    @if($jakeMotor && $roryMotor)
        <section class="chapter chapter--dark">
            <div class="wrap">
                <div class="sec-head" data-reveal>
                    <div><p class="turn"><b>T1</b> De vraag</p><h2>Twee motoren die niet te vergelijken zijn</h2></div>
                    <p>Dat wisten we allang. Dus vergeleken we ze toch: met vermogen, gewicht, luchtweerstand en wegdek, in plaats van met wie er het hardst roept aan de koffietafel.</p>
                </div>
                <div class="bro-duel" data-inview>
                    @foreach([['who' => 'Jake', 'm' => $jakeMotor, 'no' => '202', 'side' => 'a'], ['who' => 'Rory', 'm' => $roryMotor, 'no' => '203', 'side' => 'b']] as $r)
                        <div class="bro-duel__side bro-duel__side--{{ $r['side'] }}">
                            <span class="bro-duel__no" aria-hidden="true">{{ $r['no'] }}</span>
                            <p class="bro-duel__who">{{ $r['who'] }}</p>
                            <h3>{{ $r['m']->brand }} {{ $r['m']->model }} <small>{{ $r['m']->year }}</small></h3>
                            <dl>
                                <div><dt>Vermogen</dt><dd>{{ $r['m']->power_hp }} <small>pk</small></dd></div>
                                <div><dt>Gewicht</dt><dd>{{ $r['m']->weight_kg }} <small>kg</small></dd></div>
                                <div><dt>Per kilo</dt><dd>{{ $fmt($r['m']->powerToWeight()) }} <small>pk/kg</small></dd></div>
                            </dl>
                        </div>
                    @endforeach
                    <span class="bro-duel__vs" aria-hidden="true">vs</span>
                </div>
                <div class="bro-duel__cta" data-reveal>
                    <p>Op papier is het geen wedstrijd. Op de weg wel, en dat is precies wat RevRace laat zien.</p>
                    <a class="btn btn--primary" href="{{ route('compare.show', $duelSlug) }}">Bekijk onze eigen race {!! $arrow !!}</a>
                </div>
            </div>
        </section>
    @endif

    {{-- T2: het team --}}
    <section class="chapter">
        <div class="wrap">
            <div class="sec-head" data-reveal>
                <div><p class="turn"><b>T2</b> Het team</p><h2>Een coureur, een coach en een monteur</h2></div>
                <p>Rory kwam er laatst mee: hij is de jonge coureur, Jake staat aan de pitmuur en haalt met videobeelden snelheid uit elke ronde, en onze vader is de monteur. Het klopt aardiger dan je zou denken.</p>
            </div>
            <div class="crew">
                <article class="crew-card" data-reveal>
                    <div class="crew-card__img"><img src="{{ asset('images/over-ons/rory-actie.jpg') }}" alt="Rory Andreas op zijn KTM 1290 Super Duke met startnummer 203, diep ingeleund op TT Circuit Assen" width="1600" height="1067" loading="lazy"></div>
                    <div class="crew-card__body">
                        <p class="crew-card__role">De coureur</p>
                        <h3>Rory Andreas</h3>
                        <dl class="crew-card__facts">
                            <div><dt>Motor</dt><dd>KTM 1290 Super Duke R (2021)</dd></div>
                            <div><dt>Startnummer</dt><dd>203</dd></div>
                            <div><dt>Op de weg</dt><dd>3 jaar</dd></div>
                            <div><dt>Op het circuit</dt><dd>1 jaar</dd></div>
                            <div><dt>Bij RevRace</dt><dd>Aanspreekpunt voor partners</dd></div>
                        </dl>
                        <p class="crew-card__fav"><b>Favoriete stuk:</b> het rechte stuk na de GT-chicane in Assen. Daar laat de Super Duke merken waarom hij {{ $roryMotor?->power_hp ?? 177 }} pk heeft.</p>
                        <a class="crew-card__link" href="https://www.instagram.com/ktm_prime/" rel="noopener" target="_blank">Volg Rory op Instagram: @ktm_prime</a>
                    </div>
                </article>
                <article class="crew-card" data-reveal style="--d: 100ms">
                    <div class="crew-card__img"><img src="{{ asset('images/over-ons/jake-actie.jpg') }}" alt="Jake Andreas op zijn Honda CB1300 met startnummer 202, ingeleund in een bocht op TT Circuit Assen" width="1600" height="1067" loading="lazy"></div>
                    <div class="crew-card__body">
                        <p class="crew-card__role">De coach aan de pitmuur</p>
                        <h3>Jake Andreas</h3>
                        <dl class="crew-card__facts">
                            <div><dt>Motor</dt><dd>Honda CB1300 (2006)</dd></div>
                            <div><dt>Startnummer</dt><dd>202</dd></div>
                            <div><dt>Op de weg</dt><dd>10+ jaar</dd></div>
                            <div><dt>Circuits</dt><dd>Zolder, Val de Vienne, Mettet, TT Assen, Nürburgring</dd></div>
                            <div><dt>Bij RevRace</dt><dd>Techniek en data</dd></div>
                        </dl>
                        <p class="crew-card__fav"><b>Favoriete bocht:</b> de Strubben in Assen. In die haarspeld pak je met laat remmen op een zware CB1300 nog iets terug.</p>
                    </div>
                </article>
                <article class="crew-card crew-card--plain" data-reveal style="--d: 200ms">
                    <div class="crew-card__body">
                        <svg class="crew-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['sleutel'] !!}</svg>
                        <p class="crew-card__role">De monteur</p>
                        <h3>Onze vader</h3>
                        <p>Het derde lid van het team, in de opstelling van Rory. Elke coureur heeft iemand in de box nodig.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    {{-- T3: rondetijden --}}
    <section class="chapter on-dark">
        <div class="wrap">
            <div class="sec-head" data-reveal>
                <div><p class="turn"><b>T3</b> Rondetijden</p><h2>Geen coureurs. Wel liefhebbers.</h2></div>
                <p>We zijn niet de snelsten op het circuit en dat hoeft ook niet. We rijden omdat we het geweldig vinden, en we leren er elke trackday weer iets bij.</p>
            </div>
            <div class="timing timing--laps">
                <div class="t-row t-head"><span>POS</span><span>RIJDER</span><span class="t-hide">MOTOR</span><span class="t-num">TIJD</span><span class="t-num t-hide">VERSCHIL</span></div>
                <div class="t-row" style="--d: 0ms">
                    <span class="t-pos">1</span>
                    <span class="t-name">Jake Andreas<small>#202</small></span>
                    <span class="t-hide">Honda CB1300 · {{ $jakeMotor?->power_hp ?? 100 }} pk</span>
                    <span class="t-num t-best">2:02.110</span>
                    <span class="t-num t-hide">&nbsp;</span>
                </div>
                <div class="t-row" style="--d: 120ms">
                    <span class="t-pos">2</span>
                    <span class="t-name">Rory Andreas<small>#203, na 2 trackdays</small></span>
                    <span class="t-hide">KTM 1290 Super Duke R · {{ $roryMotor?->power_hp ?? 177 }} pk</span>
                    <span class="t-num">2:10.900</span>
                    <span class="t-num t-hide">+8.790</span>
                </div>
            </div>
            <div class="timing-legend"><span>Snelste ronde, TT Circuit Assen</span><span><i></i>Snelste tijd</span></div>
            <p class="laps-note" data-reveal>De motor met {{ ($roryMotor?->power_hp ?? 177) - ($jakeMotor?->power_hp ?? 100) }} pk minder staat bovenaan. Een rondetijd zegt net zoveel over de rijder als over de motor. RevRace vergelijkt de motoren, de rest is oefenen.</p>
        </div>
    </section>

    {{-- samen sneller --}}
    <section class="chapter chapter--tight">
        <div class="wrap team">
            <div class="team__text" data-reveal>
                <h2>Samen sneller, niet tegen elkaar</h2>
                <p>Een echte rivaliteit is er niet. Als we het over snelheid hebben, gaat het over hoe we allebei meer uit onze eigen rijstijl en onze eigen motor halen.</p>
                <p>Daar draait het voor ons om: samen plezier maken op de motor, en er steeds iets van leren.</p>
            </div>
            <div class="team__photos">
                <figure data-reveal><img src="{{ asset('images/over-ons/high-five.jpg') }}" alt="Jake en Rory geven elkaar een high five in de box" width="1000" height="1333" loading="lazy"></figure>
                <figure data-reveal style="--d: 90ms"><img src="{{ asset('images/over-ons/samen-box.jpg') }}" alt="Rory en Jake met de duim omhoog in de box, met de Honda CB1300 erachter" width="1000" height="1333" loading="lazy"></figure>
                <figure data-reveal style="--d: 180ms"><img src="{{ asset('images/over-ons/motoren-box.jpg') }}" alt="De KTM met nummer 203 en de Honda met nummer 202 naast elkaar in de box" width="1400" height="1050" loading="lazy"></figure>
            </div>
        </div>
    </section>

    {{-- T4: waar we naartoe willen --}}
    <section class="chapter chapter--dark">
        <div class="wrap">
            <div class="sec-head" data-reveal>
                <div><p class="turn"><b>T4</b> Waar we naartoe willen</p><h2>Een plek voor elke motorrijder</h2></div>
                <p>Beginner of al jaren onderweg: RevRace moet je verder helpen. Nu met {{ $motors->count() }} motoren in de database, en er komen er steeds meer bij.</p>
            </div>
            <ul class="goals">
                @foreach([
                    ['icon' => 'pas', 'title' => 'Je eerste motor', 'text' => 'Net je rijbewijs, of nog bezig? We helpen je kiezen zonder verkooppraat.'],
                    ['icon' => 'trap', 'title' => 'Je volgende motor', 'text' => 'Doorstromen naar meer vermogen, of juist iets dat beter past bij hoe je nu rijdt.'],
                    ['icon' => 'bocht', 'title' => 'De discussie beslechten', 'text' => 'Wie heeft de snelste motor in de vriendengroep? Zet ze tegen elkaar en kijk.'],
                    ['icon' => 'sleutel', 'title' => 'De community leren kennen', 'text' => 'Partners zoals Trackdays4all, zodat je meer uit de motorwereld haalt.'],
                ] as $goal)
                    <li data-reveal style="--d: {{ $loop->index * 70 }}ms">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$goal['icon']] !!}</svg>
                        <h3>{{ $goal['title'] }}</h3>
                        <p>{{ $goal['text'] }}</p>
                    </li>
                @endforeach
            </ul>
            <p class="goals__end" data-reveal>En over een jaar? Dan hebben we er hopelijk nog net zoveel plezier in als op de dag dat we begonnen.</p>
        </div>
    </section>

    <div class="finish on-dark" style="border-top:1px solid var(--asphalt-line)">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Zelf proberen</p>
            <h2>Pak twee motoren en draai een race</h2>
            <p>Jouw motor tegen die van je maat. Zie wat er gebeurt zodra de lichten uitgaan.</p>
            <div class="finish__row">
                <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie {!! $arrow !!}</a>
                <a class="btn btn--line" href="{{ route('partners.apply') }}">Partner worden</a>
            </div>
        </div>
    </div>
@endsection
