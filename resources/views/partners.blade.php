@extends('layouts.app')

@section('title', 'Partners - RevRace')
@section('description', 'Bekijk de RevRace-partners: dealers, verzekeraars, onderhoudsbedrijven en events die zich richten op motorrijders in Nederland.')

@section('content')
    @php
        $arrowOut = '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 13L13 5M6 5h7v7"/></svg>';
        $arrow = '<svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg>';
        // Plekken die nog vrij zijn: categorieën die bij motorrijders horen en nog geen partner hebben.
        $openSlots = collect([
            ['cat' => 'Rijschool', 'text' => 'Voor wie nog moet beginnen: van eerste les tot A2-examen.', 'icon' => 'pas'],
            ['cat' => 'Dealer', 'text' => 'Voor wie na het vergelijken een proefrit wil maken.', 'icon' => 'sleutel'],
            ['cat' => 'Verzekering', 'text' => 'Voor wie net zijn keuze heeft gemaakt en wil weten wat hij kost.', 'icon' => 'euro'],
            ['cat' => 'Uitrusting', 'text' => 'Helm, pak en laarzen, voor de weg en voor het circuit.', 'icon' => 'lijst'],
            ['cat' => 'Onderhoud en tuning', 'text' => 'Voor wie meer uit zijn eigen motor wil halen.', 'icon' => 'bocht'],
        ])->reject(fn ($slot) => $categories->contains(fn ($c) => Str::contains(Str::lower($c), Str::lower(Str::before($slot['cat'], ' ')))));
        $icons = config('kennis.icons');
    @endphp

    <header class="chapter chapter--tight seg-hero">
        <div class="wrap">
            <h1>Partners van RevRace</h1>
            <div class="seg-hero__body">
                <p class="lede">Bedrijven die motorrijders verder helpen, van je eerste circuitdag tot je volgende motor. We kiezen partners die iets toevoegen voor de mensen die hier vergelijken.</p>
            </div>
        </div>
    </header>

    @foreach($partners as $partner)
        @php
            $site = $partner->outboundUrl(null, 'partneroverzicht');
            $image = config('partners.' . $partner->slug . '.spot_image') ?? $partner->hero_image;
        @endphp
        <section class="chapter chapter--dark chapter--tight pshow">
            <div class="wrap">
                <article class="pshow__card">
                    @if($image)
                        <div class="pshow__media">
                            <img src="{{ $partner->assetUrl($image) }}" alt="" width="700" height="467" loading="lazy">
                            @if($partner->logo_url)<span class="pspot__logo"><img src="{{ $partner->assetUrl($partner->logo_url) }}" alt="{{ $partner->name }}" width="120" height="48"></span>@endif
                        </div>
                    @endif
                    <div class="pshow__body">
                        <p class="pshow__tag">{{ $partner->category }}</p>
                        <h2>{{ $partner->name }}</h2>
                        <p class="pshow__text">{{ $partner->description }}</p>
                        @if(! empty($partner->facts))
                            <dl class="pshow__facts" data-inview>
                                @foreach(collect($partner->facts)->take(4) as $fact)
                                    <div><dt>{{ $fact['label'] }}</dt><dd>{{ $fact['value'] }}</dd></div>
                                @endforeach
                            </dl>
                        @endif
                        <div class="pspot__actions">
                            @if($site)<a class="btn btn--primary" href="{{ $site }}" rel="sponsored noopener" target="_blank">Naar {{ $partner->name }} {!! $arrowOut !!}</a>@endif
                            <a class="btn btn--ghost" href="{{ route('partners.show', $partner) }}">Partnerpagina</a>
                        </div>
                    </div>
                </article>

                @if(! empty($partner->offers))
                    <div class="pshow__offers">
                        @foreach($partner->offers as $offer)
                            <a class="pshow__offer" href="{{ $partner->outboundUrl($offer['url'] ?? null, 'partneroverzicht-aanbod') }}" rel="sponsored noopener" target="_blank" data-reveal style="--d: {{ $loop->index * 70 }}ms">
                                <span class="pshow__offer-tag">{{ $offer['tag'] }}</span>
                                <h3>{{ $offer['title'] }}</h3>
                                @if(! empty($offer['points']))<p>{{ implode(' · ', $offer['points']) }}</p>@endif
                                <span class="pshow__offer-go">Bekijk {!! $arrowOut !!}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    @if($partners->isEmpty())
        <section class="chapter chapter--tight">
            <div class="wrap"><div class="panel"><p>We werken aan de eerste geverifieerde partners op deze pagina.</p></div></div>
        </section>
    @endif

    @if($openSlots->isNotEmpty())
        <section class="chapter chapter--tight">
            <div class="wrap">
                <div class="plot-head plot-head--light">
                    <h2>Nog vrij</h2>
                    <p>Voor deze categorieën zoeken we nog een partner. Herken je je bedrijf erin? Dan praten we graag.</p>
                </div>
                <ul class="slots">
                    @foreach($openSlots->values() as $slot)
                        <li data-reveal style="--d: {{ $loop->index * 60 }}ms">
                            <a class="slot-card" href="{{ route('partners.apply') }}">
                                <svg class="slot-card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$slot['icon']] ?? '' !!}</svg>
                                <h3>{{ $slot['cat'] }}</h3>
                                <p>{{ $slot['text'] }}</p>
                                <span class="slot-card__go">Claim deze plek</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap pitch">
            <figure class="pitch__photo">
                <img src="{{ asset('images/over-ons/rory-portret.jpg') }}" alt="Rory Andreas in de pitstraat van TT Circuit Assen" width="640" height="804" loading="lazy">
                <figcaption><b>Rory Andreas</b> Mede-oprichter, aanspreekpunt voor partners</figcaption>
            </figure>
            <div class="pitch__body">
                <h2>Jouw bedrijf hier? Rory denkt met je mee.</h2>
                <p class="lede">RevRace bereikt motorrijders op het moment dat ze een keuze maken: welke motor, welk segment, wat past bij hoe ze rijden.</p>
                <ul class="pitch__list">
                    <li><b>Een eigen partnerpagina</b> met je aanbod, locaties en een directe link naar je site.</li>
                    <li><b>Een plek tussen de ranglijsten</b>, zoals bij de meest gezochte motoren.</li>
                    <li><b>Meetbare links</b>: elke klik heeft UTM-tags, zodat je in je eigen analytics ziet wat RevRace oplevert.</li>
                </ul>
                <p class="pitch__stats">{{ $motorCount }} motoren in de database @if($monthSimulations >= 100) &middot; {{ number_format($monthSimulations, 0, ',', '.') }} races in de afgelopen 30 dagen @endif</p>
                <div class="pspot__actions">
                    <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner {!! $arrow !!}</a>
                </div>
            </div>
        </div>
    </section>
@endsection
