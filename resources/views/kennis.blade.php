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
    <header class="chapter chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Kennis</span>
            <h1>Alles over motorrijden, uitgelegd</h1>
            <p class="lede">Van je eerste motor tot verdieping voor ervaren rijders, en het laatste nieuws over nieuwe modellen.</p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            @if($categories->count() > 1)
                <div class="choice-row" data-filter-bar="kennis" style="margin-bottom: clamp(28px, 4vw, 44px)">
                    <button class="choice is-active" type="button" data-filter="alle">Alle</button>
                    @foreach($categories as $category)
                        <button class="choice" type="button" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
                    @endforeach
                </div>
            @endif

            @if($articles->isNotEmpty())
                <div class="kb-grid" data-filter-grid="kennis">
                    @foreach($articles as $article)
                        <article class="kb-card @if($loop->first) kb-card--wide @endif" data-filter-category="{{ Str::slug($article->category) }}">
                            <div class="kb-card__media">
                                @if($article->cover_image_url)
                                    <img src="{{ $article->cover_image_url }}" alt="" loading="lazy">
                                @else
                                    {{ $article->category }}
                                @endif
                            </div>
                            <p class="kb-card__stage">{{ $article->category }}</p>
                            <h3>{{ $article->title }}</h3>
                            @if($article->excerpt)
                                <p>{{ $article->excerpt }}</p>
                            @endif
                            @if($article->author_name)
                                <p class="kb-card__byline">Door <b>{{ $article->author_name }}</b></p>
                            @endif
                            <a class="kb-card__link" href="{{ route('kennis.show', $article) }}">Lees het artikel &rarr;</a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="panel">
                    <p>Binnenkort verschijnen hier de eerste artikelen.</p>
                </div>
            @endif
        </div>
    </section>

    <section class="chapter chapter--dark chapter--tight">
        <div class="wrap">
            <span class="eyebrow">Zelf doorrekenen</span>
            <h2>Lezen is één stap. Doorrekenen is de volgende.</h2>
            <p class="lede" style="margin-top: 0.6em; margin-bottom: 1.8em">Kies twee motoren en laat de simulator de rest doen, op jouw wegconditie.</p>
            <a class="btn btn--primary" href="{{ route('simulation.index') }}">Start een simulatie</a>
        </div>
    </section>
@endsection
