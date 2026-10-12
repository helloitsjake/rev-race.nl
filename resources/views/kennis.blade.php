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
        $icon = fn ($name, $size = 24) => '<svg viewBox="0 0 24 24" width="'.$size.'" height="'.$size.'" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.(config('kennis.icons.'.$name) ?? config('kennis.icons.artikel')).'</svg>';
        $stages = config('kennis.stages');
        // Eigen artikelen (briefings) en crawlernieuws krijgen elk hun eigen vorm.
        [$news, $guides] = $articles->partition(fn ($a) => $a->category === \App\Models\Article::NEWS_CATEGORY);
        $briefings = $guides->map(function ($a) use ($stages) {
            $meta = config('kennis.articles.'.$a->slug, []);
            $stage = $meta['stage'] ?? config('kennis.fallback_stage.'.$a->category, 'eerste-motor');

            return (object) [
                'article' => $a,
                'stage' => isset($stages[$stage]) ? $stage : array_key_first($stages),
                'icon' => $meta['icon'] ?? null,
                'figure' => $meta['figure'] ?? null,
                'figure_label' => $meta['figure_label'] ?? null,
                'minutes' => max(1, (int) ceil(str_word_count(strip_tags($a->body)) / 220)),
            ];
        })->sortBy(fn ($b) => array_search($b->stage, array_keys($stages)))->values();
        $perStage = $briefings->countBy('stage');
        $newsVisible = 8;
    @endphp

    <div class="wrap kb-hero">
        <p class="turn"><b>T3</b> Kennisbank</p>
        <h1>Van rijbewijs tot circuit, uitgelegd</h1>
        <p class="lede">Kort en helder, onderbouwd met officiële bronnen. Geschreven door Jake en Rory Andreas, zelf rijders op de weg en op het circuit.</p>
    </div>

    @if($briefings->isNotEmpty())
        <section class="chapter kb-section" id="briefing" style="padding-top:0">
            <div class="wrap">
                <div class="stagebar" data-stagebar>
                    <p class="stagebar__q" id="stage-q">Waar sta jij?</p>
                    <div class="stagebar__track" role="group" aria-labelledby="stage-q" style="--n: {{ count($stages) }}">
                        <span class="stagebar__rail" aria-hidden="true"><i data-stage-fill></i></span>
                        @foreach($stages as $key => $stage)
                            <button type="button" class="stage" data-stage="{{ $key }}" aria-pressed="false" style="--i: {{ $loop->index }}">
                                <span class="stage__dot" aria-hidden="true"></span>
                                <span class="stage__icon">{!! $icon($stage['icon']) !!}</span>
                                <span class="stage__num">0{{ $loop->iteration }}</span>
                                <span class="stage__label">{{ $stage['label'] }}</span>
                                <span class="stage__sub">{{ $stage['sub'] }}</span>
                                <span class="stage__count">{{ $perStage[$key] ?? 0 }} {{ ($perStage[$key] ?? 0) === 1 ? 'briefing' : 'briefings' }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="kb-section__head">
                    <h2>Briefing</h2>
                    <p data-stage-note>Alles wat je wilt weten voor je de weg op gaat. Kies hierboven je fase, of lees alles.</p>
                    <button type="button" class="kb-reset" data-stage-reset hidden>Toon alles</button>
                </div>

                <div class="briefings" data-briefings>
                    @foreach($briefings as $b)
                        <a class="briefing" href="{{ route('kennis.show', $b->article) }}" data-stage-item="{{ $b->stage }}">
                            <span class="briefing__top">
                                <span class="briefing__icon">{!! $icon($b->icon ?? $stages[$b->stage]['icon'], 22) !!}</span>
                                <span class="briefing__stage">{{ $stages[$b->stage]['label'] }}</span>
                                <span class="briefing__time">{{ $b->minutes }} min</span>
                            </span>
                            @if($b->figure)
                                <span class="briefing__fig"><b>{{ $b->figure }}</b><span>{{ $b->figure_label }}</span></span>
                            @endif
                            <h3 class="briefing__title">{{ $b->article->title }}</h3>
                            <span class="briefing__go">Lees de briefing {!! $arrow !!}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if($news->isNotEmpty())
        <section class="chapter chapter--dark kb-news" id="nieuws">
            <div class="wrap">
                <div class="kb-section__head kb-section__head--dark">
                    <h2><span class="live-dot" aria-hidden="true"></span>Net binnen</h2>
                    <p>Nieuwe modellen, bijgehouden uit internationale motornieuwsbronnen en in het Nederlands herschreven. De bron staat onder elk bericht.</p>
                </div>
                <ol class="newsboard{{ $news->count() > $newsVisible ? ' is-collapsed' : '' }}" data-news>
                    @foreach($news->values() as $i => $item)
                        <li @if($i >= $newsVisible) data-more @endif>
                            <a href="{{ route('kennis.show', $item) }}">
                                <time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->translatedFormat('j M') }}</time>
                                <span class="newsboard__title">{{ $item->title }}</span>
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

    @if($articles->isEmpty())
        <section class="chapter" style="padding-top:0"><div class="wrap"><p class="lede">Binnenkort verschijnen hier de eerste artikelen.</p></div></section>
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
        (() => {
            const bar = document.querySelector('[data-stagebar]');
            if (bar) {
                const btns = [...bar.querySelectorAll('[data-stage]')];
                const items = [...document.querySelectorAll('[data-stage-item]')];
                const fill = bar.querySelector('[data-stage-fill]');
                const reset = document.querySelector('[data-stage-reset]');
                const note = document.querySelector('[data-stage-note]');
                const noteAll = note.textContent;
                const set = (key) => {
                    const idx = btns.findIndex((b) => b.dataset.stage === key);
                    btns.forEach((b, i) => {
                        b.setAttribute('aria-pressed', String(b.dataset.stage === key));
                        b.classList.toggle('is-passed', idx >= 0 && i < idx);
                    });
                    fill.style.setProperty('--p', idx < 0 ? 0 : idx / (btns.length - 1));
                    items.forEach((el) => { el.hidden = Boolean(key) && el.dataset.stageItem !== key; });
                    reset.hidden = !key;
                    note.textContent = key ? `${btns[idx].querySelector('.stage__label').textContent}. ${btns[idx].querySelector('.stage__sub').textContent}.` : noteAll;
                };
                btns.forEach((b) => b.addEventListener('click', () => set(b.getAttribute('aria-pressed') === 'true' ? null : b.dataset.stage)));
                reset.addEventListener('click', () => set(null));
            }
            document.querySelector('[data-news-more]')?.addEventListener('click', (e) => {
                document.querySelector('[data-news]').classList.remove('is-collapsed');
                e.currentTarget.remove();
            });
        })();
    </script>
@endsection
