@extends('layouts.app')

@section('title', $article->title . ' - RevRace')
@section('description', $article->meta_description ?: $article->excerpt)

@php
    // "Jake en Rory Andreas" wordt twee personen voor het schema en twee gezichten in de byline.
    $authorNames = $article->author_name === 'Jake en Rory Andreas'
        ? ['Jake Andreas', 'Rory Andreas']
        : array_filter([$article->author_name]);
    $faces = ['Jake Andreas' => ['jake-202.jpg', '52% 18%'], 'Rory Andreas' => ['rory-203.jpg', '24% 22%']];

    $body = $article->renderedBody();
    $sections = [];
    $body = preg_replace_callback('#<h2>(.*?)</h2>#s', function ($m) use (&$sections) {
        $id = 's'.(count($sections) + 1);
        $sections[] = ['id' => $id, 'title' => strip_tags($m[1])];

        return '<h2 id="'.$id.'">'.$m[1].'</h2>';
    }, $body);
    $minutes = max(1, (int) ceil(str_word_count(strip_tags($body)) / 200));
    $isNews = $article->category === \App\Models\Article::NEWS_CATEGORY;
@endphp

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
    'author' => $article->author_name
        ? array_map(fn ($name) => ['@type' => 'Person', 'name' => $name], $authorNames)
        : ['@type' => 'Organization', 'name' => 'RevRace'],
    'publisher' => ['@type' => 'Organization', 'name' => 'RevRace'],
])) !!}
</script>
@endpush

@section('content')
    <div class="wrap art-head">
        <nav class="crumb" aria-label="Broodkruimel"><a href="{{ route('kennis.index') }}">Kennisbank</a><span class="sep">/</span><span>{{ $article->category }}</span></nav>
        <p class="turn"><b>T3</b> {{ $article->category }}</p>
        <h1>{{ $article->title }}</h1>
        @if($article->excerpt)
            <p class="lede">{{ $article->excerpt }}</p>
        @endif
        <div class="byline">
            @if($article->author_name)
                <span class="authors">
                    <span class="faces">
                        @foreach($authorNames as $name)
                            @isset($faces[$name])<img src="{{ asset('images/team/'.$faces[$name][0]) }}" alt="" style="object-position:{{ $faces[$name][1] }}" width="38" height="38">@endisset
                        @endforeach
                    </span>
                    {{ $article->author_name }}
                </span>
            @endif
            @if($article->published_at)<span>{{ $article->published_at->translatedFormat('j F Y') }}</span>@endif
            <span>{{ $minutes }} min lezen</span>
        </div>
    </div>

    <div class="wrap art-layout @if(count($sections) < 2) art-layout--plain @endif">
        @if(count($sections) >= 2)
            <aside class="toc" aria-label="In dit artikel">
                <p>In dit artikel</p>
                <ol>
                    @foreach($sections as $section)
                        <li><a href="#{{ $section['id'] }}"><em>S{{ $loop->iteration }}</em>{{ Str::limit($section['title'], 42) }}</a></li>
                    @endforeach
                </ol>
            </aside>
        @endif

        <div>
            <article class="article-body @if(count($sections) >= 2) article-body--sectors @endif">
                {!! $body !!}
            </article>

            @if($article->source_url)
                <div class="article-source">
                    <p>Bron: <a href="{{ $article->source_url }}" rel="nofollow noopener" target="_blank">{{ $article->source_name ?: $article->source_url }}</a></p>
                </div>
            @endif

            @unless($isNews)
                <div class="inline-cta">
                    <div><b>Welke motor past bij jouw rijstijl?</b><span>Drie vragen, en je ziet je top 6 met uitleg waarom.</span></div>
                    <a class="btn btn--primary" href="{{ route('wizard.index') }}">Start het advies <svg viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9h12M10 4l5 5-5 5"/></svg></a>
                </div>
            @endunless

            @if(!empty($crossLinks))
                <div class="browse-more">
                    @foreach($crossLinks as $link)
                        <a class="btn btn--ghost" href="{{ $link['route'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            @endif

            @if($article->author_name)
                <div class="authorbox">
                    <span class="faces">
                        @foreach($authorNames as $name)
                            @isset($faces[$name])<img src="{{ asset('images/team/'.$faces[$name][0]) }}" alt="{{ $name }}" style="object-position:{{ $faces[$name][1] }}" width="56" height="56">@endisset
                        @endforeach
                    </span>
                    <div><b>{{ $article->author_name }}</b>@if($article->author_bio)<p>{{ $article->author_bio }}</p>@endif</div>
                </div>
            @endif
        </div>
    </div>

    @if($related->isNotEmpty())
        <section class="chapter" style="border-top:1px solid var(--stone-line)">
            <div class="wrap">
                <div class="sec-head">
                    <div><p class="turn"><b>T3</b> Lees verder</p><h2>Meer uit {{ $article->category }}</h2></div>
                    <p><a class="accent" href="{{ route('kennis.index') }}">Alle kennisartikelen</a></p>
                </div>
                <div class="kb-list">
                    @foreach($related as $item)
                        <a href="{{ route('kennis.show', $item) }}"><span class="kicker">{{ $item->category }}</span><h4><span>{{ $item->title }}</span></h4><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 10h12M11 5l5 5-5 5"/></svg></a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
