@extends('layouts.app')

@section('title', $partner->name . ' - RevRace partners')
@section('description', $partner->description)

@section('content')
    <a class="small" href="{{ route('partners.index') }}">&larr; Alle partners</a>

    <div class="chart-head" style="align-items:flex-start;margin-top:16px">
        <div>
            <span class="badge">{{ $partner->category }}</span>
            <h1 class="page-title" style="margin-top:10px">{{ $partner->name }}</h1>
        </div>
        <div class="photo-placeholder photo-placeholder-sm">Logo</div>
    </div>

    <section class="panel" style="margin-top:20px">
        <p>{{ $partner->description }}</p>

        <div class="spec-row">
            <span class="spec-label">Categorie</span>
            <span class="spec-value">{{ $partner->category }}</span>
        </div>
        @if($partner->website_url)
            <div class="spec-row">
                <span class="spec-label">Website</span>
                <span class="spec-value"><a class="accent" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">{{ $partner->website_url }}</a></span>
            </div>
        @endif
        @if($partner->contact_email)
            <div class="spec-row">
                <span class="spec-label">E-mail</span>
                <span class="spec-value"><a class="accent" href="mailto:{{ $partner->contact_email }}">{{ $partner->contact_email }}</a></span>
            </div>
        @endif
        @if($partner->contact_phone)
            <div class="spec-row">
                <span class="spec-label">Telefoon</span>
                <span class="spec-value">{{ $partner->contact_phone }}</span>
            </div>
        @endif

        <div class="hero-actions">
            @if($partner->website_url)
                <a class="btn primary" href="{{ $partner->website_url }}" rel="nofollow noopener" target="_blank">Naar website</a>
            @endif
            <a class="btn secondary" href="{{ route('partners.index') }}">Alle partners</a>
        </div>
    </section>
@endsection
