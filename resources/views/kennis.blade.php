@extends('layouts.app')

@section('title', 'Kennis - RevRace')
@section('description', 'Alles wat je moet weten over motorrijden: van je eerste motor als beginner tot verdieping voor ervaren rijders, en het laatste nieuws over nieuwe modellen.')

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Kennis'],
    ],
]) !!}
</script>
@endpush

@section('content')
    @php
        $arrow = '<svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
        // Twee soorten inhoud, twee vormen: eigen gidsen als kaarten, crawlernieuws als compacte lijst.
        [$news, $guides] = $articles->partition(fn ($a) => $a->category === \App\Models\Article::NEWS_CATEGORY);
        $feature = $guides->first();
        $newsVisible = 10;
    @endphp
    <div class="wrap" style="padding-block:clamp(28px,4vw,56px) clamp(24px,3vw,36px)">
        <p class="turn"><b>T3</b> Kennisbank</p>
        <h1 style="max-width:15ch">Alles over motorrijden, uitgelegd</h1>
        <p class="lede" style="margin-top:20px">Van je eerste motor tot verdieping voor ervaren rijders, en het laatste nieuws over nieuwe modellen. Geschreven door Jake en Rory Andreas.</p>
        @if($guides->isNotEmpty() && $news->isNotEmpty())
            <nav class="kb-jump" aria-label="Onderdelen">
                <a href="#gidsen"><b>{{ $guides->count() }}</b> gidsen</a>
                <a href="#nieuws"><b>{{ $news->count() }}</b> nieuwe modellen</a>
            </nav>
        @endif
    </div>

    @if($articles->isEmpty())
        <section class="chapter" style="padding-top:0"><div class="wrap"><p class="lede">Binnenkort verschijnen hier de eerste artikelen.</p></div></section>
    @endif

    @if($guides->isNotEmpty())
        <section class="chapter kb-section" id="gidsen" style="padding-top:0">
            <div class="wrap">
                <div class="kb-section__head">
                    <h2>Gidsen</h2>
                    <p>Zelf geschreven en onderbouwd met officiële bronnen, zoals het CBR en de Rijksoverheid.</p>
                </div>
                <div class="kb-guides">
                    <a class="feature kb-guides__feature" href="{{ route('kennis.show', $feature) }}" data-reveal>
                        <svg class="feature__line" viewBox="0 0 400 260" fill="none" aria-hidden="true"><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#141518" stroke-width="2"/><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#E4431E" stroke-width="2" stroke-dasharray="4 10"/><circle cx="250" cy="50" r="7" fill="#E4431E"/></svg>
                        <span class="kicker">{{ $feature->category }}</span>
                        <div>
                            <h3>{{ $feature->title }}</h3>
                            @if($feature->excerpt)<p>{{ $feature->excerpt }}</p>@endif
                            <span class="kb-card__link">Lees de gids</span>
                        </div>
                    </a>
                    @foreach($guides->skip(1) as $guide)
                        <a class="kb-card" href="{{ route('kennis.show', $guide) }}">
                            <span class="kb-card__stage">{{ $guide->category }}</span>
                            <h3>{{ $guide->title }}</h3>
                            @if($guide->excerpt)<p>{{ Str::limit($guide->excerpt, 120) }}</p>@endif
                            <span class="kb-card__link">Lees de gids</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($news->isNotEmpty())
        <section class="chapter kb-section" id="nieuws" style="padding-top:{{ $guides->isNotEmpty() ? 'clamp(24px,4vw,48px)' : '0' }}">
            <div class="wrap">
                <div class="kb-section__head">
                    <h2>Nieuwe modellen</h2>
                    <p>Bijgehouden uit internationale motornieuwsbronnen en in het Nederlands herschreven. De bron staat onder elk bericht.</p>
                </div>
                <ol class="news-list{{ $news->count() > $newsVisible ? ' is-collapsed' : '' }}" data-news>
                    @foreach($news->values() as $i => $item)
                        <li @if($i >= $newsVisible) data-more @endif>
                            <a href="{{ route('kennis.show', $item) }}">
                                <time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->translatedFormat('j M') }}</time>
                                <span class="news-list__title"><span>{{ $item->title }}</span></span>
                                {!! $arrow !!}
                            </a>
                        </li>
                    @endforeach
                </ol>
                @if($news->count() > $newsVisible)
                    <button class="btn btn--ghost news-more" type="button" data-news-more>Toon alle {{ $news->count() }} berichten</button>
                @endif
            </div>
        </section>
    @endif

    <div class="finish on-dark">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Zelf doorrekenen</p>
            <h2>Lezen is één stap. Doorrekenen is de volgende.</h2>
            <p>Kies twee motoren en laat de simulator de rest doen, op jouw wegconditie.</p>
            <div class="finish__row"><a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie {!! $arrow !!}</a><a class="btn btn--line" href="{{ route('wizard.index') }}">Of vind je motor</a></div>
        </div>
    </div>

    <script>
        document.querySelector('[data-news-more]')?.addEventListener('click', (e) => {
            document.querySelector('[data-news]').classList.remove('is-collapsed');
            e.currentTarget.remove();
        });
    </script>
@endsection
