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
        $feature = $articles->first(fn ($a) => $a->category !== \App\Models\Article::NEWS_CATEGORY) ?? $articles->first();
    @endphp
    <div class="wrap" style="padding-block:clamp(28px,4vw,56px) clamp(28px,4vw,44px)">
        <p class="turn"><b>T3</b> Kennisbank</p>
        <h1 style="max-width:15ch">Alles over motorrijden, uitgelegd</h1>
        <p class="lede" style="margin-top:20px">Van je eerste motor tot verdieping voor ervaren rijders, en het laatste nieuws over nieuwe modellen. Geschreven door Jake en Rory Andreas.</p>
    </div>

    <section class="chapter" style="padding-top:0">
        <div class="wrap">
            @if($articles->isNotEmpty())
                <div class="kb" style="margin-bottom:clamp(48px,6vw,80px)">
                    <a class="feature" href="{{ route('kennis.show', $feature) }}" data-reveal>
                        <svg class="feature__line" viewBox="0 0 400 260" fill="none" aria-hidden="true"><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#141518" stroke-width="2"/><path d="M10 230 C 120 230, 150 40, 250 50 S 360 200, 395 120" stroke="#E4431E" stroke-width="2" stroke-dasharray="4 10"/><circle cx="250" cy="50" r="7" fill="#E4431E"/></svg>
                        <span class="kicker">{{ $feature->category }}</span>
                        <div><h3>{{ $feature->title }}</h3>@if($feature->excerpt)<p>{{ $feature->excerpt }}</p>@endif</div>
                    </a>
                    <div>
                        @if($categories->count() > 1)
                            <div class="choice-row" data-filter-bar="kennis" style="margin-bottom:20px">
                                <button class="choice is-active" type="button" data-filter="alle">Alle</button>
                                @foreach($categories as $category)
                                    <button class="choice" type="button" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
                                @endforeach
                            </div>
                        @endif
                        <div class="kb-list" data-filter-grid="kennis">
                            @foreach($articles as $article)
                                @continue($article->is($feature))
                                <a href="{{ route('kennis.show', $article) }}" data-filter-category="{{ Str::slug($article->category) }}"><span class="kicker">{{ $article->category }}</span><h4><span>{{ $article->title }}</span></h4>{!! $arrow !!}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <p class="lede">Binnenkort verschijnen hier de eerste artikelen.</p>
            @endif
        </div>
    </section>

    <div class="finish on-dark">
        <span class="flag" aria-hidden="true"></span>
        <div class="wrap">
            <p class="turn"><b>FIN</b> Zelf doorrekenen</p>
            <h2>Lezen is één stap. Doorrekenen is de volgende.</h2>
            <p>Kies twee motoren en laat de simulator de rest doen, op jouw wegconditie.</p>
            <div class="finish__row"><a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie {!! $arrow !!}</a><a class="btn btn--line" href="{{ route('wizard.index') }}">Of vind je motor</a></div>
        </div>
    </div>
@endsection
