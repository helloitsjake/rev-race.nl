@extends('layouts.app')

@section('title', 'Partners - RevRace')
@section('description', 'Samenwerkingen voor dealers, verzekeraars, onderhoud en events rond motorfietsen.')

@section('content')
    <div class="chart-head" style="align-items:flex-start">
        <div>
            <span class="eyebrow">Partners</span>
            <h1 class="page-title">Onze partners</h1>
            <p class="page-sub">Samenwerkingen voor dealers, verzekeraars, evenementen en motorcontent.</p>
        </div>
        <a class="btn primary" href="{{ route('partners.apply') }}">Word partner</a>
    </div>

    <div class="choice-row" data-partner-filters style="margin-bottom:22px">
        <button class="filter-pill active" type="button" data-filter="alle">Alle</button>
        @foreach($categories as $category)
            <button class="filter-pill" type="button" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
        @endforeach
    </div>

    <div class="card-grid" data-partner-grid>
        @foreach($partners as $partner)
            <article class="card" data-partner-category="{{ Str::slug($partner->category) }}">
                <div class="chart-head" style="margin-bottom:12px">
                    <div class="photo-placeholder photo-placeholder-sm">Logo</div>
                    <span class="badge">{{ $partner->category }}</span>
                </div>
                <h2 class="card-title">{{ $partner->name }}</h2>
                <p class="section-sub">{{ $partner->description }}</p>
                <div class="hero-actions" style="margin-top:6px;align-items:center">
                    @if($partner->website_url)
                        <a class="btn secondary" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">Website</a>
                    @endif
                    <a class="accent" style="font-size:13px;font-weight:650;color:var(--teal)" href="{{ route('partners.show', $partner) }}">Bekijk partner &rarr;</a>
                </div>
            </article>
        @endforeach
        <article class="card" style="border-style:dashed;background:var(--bg-2)">
            <span class="badge" style="border-color:var(--orange);color:var(--orange)">Word partner</span>
            <h2 class="card-title" style="margin-top:10px">Jouw merk hier?</h2>
            <p class="section-sub">Neem contact op voor zichtbaarheid rond motorvergelijkingen en simulaties.</p>
            <a class="btn primary" href="{{ route('contact') }}">Contact</a>
        </article>
    </div>
@endsection
