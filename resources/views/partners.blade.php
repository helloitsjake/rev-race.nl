@extends('layouts.app')

@section('title', 'Partners - RevRace')

@section('content')
    <header>
        <h1 class="page-title">Partners</h1>
        <p class="page-sub">Samenwerkingen voor dealers, verzekeraars, evenementen en motorcontent.</p>
    </header>

    <div class="card-grid">
        @foreach($partners as $partner)
            <article class="card">
                <div class="eyebrow">{{ $partner->category }}</div>
                <h2 class="card-title">{{ $partner->name }}</h2>
                <p class="section-sub">{{ $partner->description }}</p>
                @if($partner->website_url)
                    <a class="btn ghost" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">Website</a>
                @endif
                @if($partner->contact_email)
                    <p class="small">{{ $partner->contact_email }}</p>
                @endif
            </article>
        @endforeach
        <article class="card">
            <div class="eyebrow">Word partner</div>
            <h2 class="card-title">Jouw merk hier?</h2>
            <p class="section-sub">Neem contact op voor zichtbaarheid rond motorvergelijkingen en simulaties.</p>
            <a class="btn primary" href="{{ route('contact') }}">Contact</a>
        </article>
    </div>
@endsection
