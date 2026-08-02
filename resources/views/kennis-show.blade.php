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
    <a class="small" href="{{ route('kennis.index') }}">&larr; Alle artikelen</a>

    <header style="margin-top:16px">
        <span class="badge">{{ $article->category }}</span>
        <h1 class="page-title" style="margin-top:10px">{{ $article->title }}</h1>
        @if($article->excerpt)
            <p class="page-sub">{{ $article->excerpt }}</p>
        @endif
        <p class="small" style="color:var(--dim);margin-top:8px">
            {{ $article->published_at?->translatedFormat('j F Y') }}
            @if($article->author_name)
                &middot; door {{ $article->author_name }}
            @endif
        </p>
    </header>

    <section class="panel article-body">
        {!! $article->renderedBody() !!}
    </section>

    @if($article->source_url)
        <p class="small" style="margin-top:14px;color:var(--dim)">
            Bron: <a class="accent" href="{{ $article->source_url }}" rel="nofollow noopener" target="_blank">{{ $article->source_name ?: $article->source_url }}</a>
        </p>
    @endif

    @if($article->author_name)
        <section class="panel" style="margin-top:20px;display:flex;gap:14px;align-items:flex-start">
            <div class="photo-placeholder photo-placeholder-sm">Foto</div>
            <div>
                <p style="font-weight:700">{{ $article->author_name }}</p>
                @if($article->author_bio)
                    <p class="small" style="color:var(--dim);margin-top:4px">{{ $article->author_bio }}</p>
                @endif
            </div>
        </section>
    @endif

    @if(!empty($crossLinks))
        <div class="hero-actions" style="margin-top:20px">
            @foreach($crossLinks as $link)
                <a class="btn secondary" href="{{ $link['route'] }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
    @endif

    @if($related->isNotEmpty())
        <section class="section">
            <h2 class="section-title">Meer uit {{ $article->category }}</h2>
            <div class="card-grid">
                @foreach($related as $item)
                    <article class="card">
                        <span class="badge">{{ $item->category }}</span>
                        <h3 class="card-title" style="margin-top:10px">{{ $item->title }}</h3>
                        <div class="hero-actions" style="margin-top:12px">
                            <a class="accent" style="font-size:13px;font-weight:650;color:var(--teal)" href="{{ route('kennis.show', $item) }}">Lees verder &rarr;</a>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
