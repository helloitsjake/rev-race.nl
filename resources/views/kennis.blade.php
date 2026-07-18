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
    <header>
        <span class="eyebrow">Kennis</span>
        <h1 class="page-title">Alles over motorrijden, uitgelegd</h1>
        <p class="page-sub">Van je eerste motor tot verdieping voor ervaren rijders, en het laatste nieuws over nieuwe modellen.</p>
    </header>

    @if($categories->count() > 1)
        <div class="choice-row" data-filter-bar="kennis" style="margin-bottom:22px">
            <button class="filter-pill active" type="button" data-filter="alle">Alle</button>
            @foreach($categories as $category)
                <button class="filter-pill" type="button" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
            @endforeach
        </div>
    @endif

    @if($articles->isNotEmpty())
        <div class="card-grid" data-filter-grid="kennis">
            @foreach($articles as $article)
                <article class="card" data-filter-category="{{ Str::slug($article->category) }}">
                    <span class="badge">{{ $article->category }}</span>
                    <h2 class="card-title" style="margin-top:10px">{{ $article->title }}</h2>
                    @if($article->excerpt)
                        <p class="section-sub">{{ $article->excerpt }}</p>
                    @endif
                    <div class="hero-actions" style="margin-top:12px">
                        <a class="accent" style="font-size:13px;font-weight:650;color:var(--teal)" href="{{ route('kennis.show', $article) }}">Lees verder &rarr;</a>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="panel">
            <p>Binnenkort verschijnen hier de eerste artikelen.</p>
        </div>
    @endif
@endsection
