@extends('layouts.app')

@section('title', 'Partners - RevRace')
@section('description', 'Samenwerkingen voor dealers, verzekeraars, onderhoud en events rond motorfietsen.')

@section('content')
    <header class="chapter chapter--tight">
        <div class="wrap page-head">
            <div>
                <span class="eyebrow">Partners</span>
                <h1>Onze partners</h1>
                <p class="lede">Samenwerkingen voor dealers, verzekeraars, evenementen en motorcontent.</p>
            </div>
            <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner</a>
        </div>
    </header>

    <section class="chapter chapter--tight">
        <div class="wrap">
            @if($partners->isEmpty())
                <div class="panel">
                    <p>We werken aan de eerste geverifieerde partners op deze pagina. Wil je een van de eerste zijn? <a class="accent" href="{{ route('partners.apply') }}" style="color:var(--midrange)">Meld je aan</a>.</p>
                </div>
            @else
                <nav class="cat-nav" data-filter-bar="partners" aria-label="Categorieën">
                    <button type="button" class="choice is-active" data-filter="alle">Alle</button>
                    @foreach($categories as $category)
                        <button type="button" class="choice" data-filter="{{ Str::slug($category) }}">{{ $category }}</button>
                    @endforeach
                </nav>

                <div class="partners-row" data-filter-grid="partners">
                    @foreach($partners as $partner)
                        <article class="partner-card" data-filter-category="{{ Str::slug($partner->category) }}">
                            <p class="partner-card__tag">{{ $partner->category }}</p>
                            <h3>{{ $partner->name }}</h3>
                            @if($partner->address_city)
                                <p class="note-inline" style="margin-top:0">{{ $partner->address_city }}</p>
                            @endif
                            <p>{{ $partner->description }}</p>
                            <a href="{{ route('partners.show', $partner) }}">Bekijk partner &rarr;</a>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="chapter chapter--tight">
        <div class="wrap cta-split">
            <div>
                <span class="eyebrow">Word partner</span>
                <h2>Jouw merk hier?</h2>
                <p class="lede">Neem contact op voor zichtbaarheid rond motorvergelijkingen en simulaties, op het moment dat een bezoeker middenin de keuze voor een nieuwe motor zit.</p>
            </div>
            <div class="panel">
                <span class="eyebrow" style="margin-bottom:0">Aanmelden</span>
                <h3>Vaste plek op de partnerspagina</h3>
                <a class="btn btn--primary" href="{{ route('partners.apply') }}">Word partner</a>
            </div>
        </div>
    </section>
@endsection
