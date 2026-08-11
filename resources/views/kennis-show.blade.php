@extends('layouts.app')

@section('title', $article->title . ' - RevRace')
@section('description', $article->meta_description ?: $article->excerpt)

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Kennis', 'item' => route('kennis.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $article->title],
    ],
]) !!}
</script>
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BlogPosting',
    'headline' => $article->title,
    'description' => $article->meta_description ?: $article->excerpt,
    'image' => $article->cover_image_url,
    'datePublished' => $article->published_at?->toIso8601String(),
    'dateModified' => $article->updated_at?->toIso8601String(),
    'author' => $article->author_name ? ['@type' => 'Person', 'name' => $article->author_name] : ['@type' => 'Organization', 'name' => 'RevRace'],
    'publisher' => ['@type' => 'Organization', 'name' => 'RevRace'],
])) !!}
</script>
@endpush

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap">
            <nav class="crumb" aria-label="Broodkruimel"><a href="{{ route('kennis.index') }}">&larr; Alle artikelen</a></nav>
            <span class="eyebrow">{{ $article->category }}</span>
            <h1>{{ $article->title }}</h1>
            @if($article->excerpt)
                <p class="lede">{{ $article->excerpt }}</p>
            @endif
            <p class="article-meta">
                @if($article->published_at){{ $article->published_at->translatedFormat('j F Y') }}@endif
                @if($article->author_name)
                    &middot; door {{ $article->author_name }}
                @endif
            </p>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            <div class="article-body">
                {!! $article->renderedBody() !!}
            </div>

            @if($article->source_url)
                <div class="article-source">
                    <p>Bron: <a href="{{ $article->source_url }}" rel="nofollow noopener" target="_blank">{{ $article->source_name ?: $article->source_url }}</a></p>
                </div>
            @endif

            @if(!empty($crossLinks))
                <div class="browse-more" style="margin-top: clamp(28px, 4vw, 44px)">
                    @foreach($crossLinks as $link)
                        <a class="btn btn--ghost" href="{{ $link['route'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if($article->author_name)
        <section class="chapter chapter--dark chapter--tight">
            <div class="wrap" style="max-width: 640px">
                <span class="eyebrow">Over de auteur</span>
                <h2>{{ $article->author_name }}</h2>
                @if($article->author_bio)
                    <p class="lede" style="max-width: none">{{ $article->author_bio }}</p>
                @endif
            </div>
        </section>
    @endif

    @if($related->isNotEmpty())
        <section class="chapter chapter--tight">
            <div class="wrap">
                <div class="kb-head">
                    <div>
                        <span class="eyebrow">Verder lezen</span>
                        <h2>Meer uit {{ $article->category }}</h2>
                    </div>
                    <a class="btn btn--ghost" href="{{ route('kennis.index') }}">Alle kennisartikelen</a>
                </div>
                <div class="kb-grid">
                    @foreach($related as $item)
                        <article class="kb-card">
                            <div class="kb-card__media">{{ $item->category }}</div>
                            <p class="kb-card__stage">{{ $item->category }}</p>
                            <h3>{{ $item->title }}</h3>
                            <a class="kb-card__link" href="{{ route('kennis.show', $item) }}">Lees het artikel &rarr;</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
