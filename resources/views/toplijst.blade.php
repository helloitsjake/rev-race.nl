@extends('layouts.app')

@section('title', $config['title'] . ' - RevRace')
@section('description', $config['description'])

@push('scripts')
<script type="application/ld+json">
{!! json_encode([
    '@'.'context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Simulatie', 'item' => route('simulation.index')],
        ['@type' => 'ListItem', 'position' => 3, 'name' => $config['title']],
    ],
]) !!}
</script>
@endpush

@section('content')
    <nav class="small" aria-label="Broodkruimel">
        <a href="{{ route('home') }}">Home</a> &rarr;
        <a href="{{ route('simulation.index') }}">Simulatie</a> &rarr;
        {{ $config['title'] }}
    </nav>

    <header style="margin-top:12px">
        <span class="eyebrow">Toplijst</span>
        <h1 class="page-title">{{ $config['title'] }}</h1>
        <p class="page-sub">{{ $config['description'] }}</p>
    </header>

    <section class="panel">
        @foreach($rows as $i => $row)
            <div class="compare-row" style="grid-template-columns:40px 48px 1fr auto;align-items:center">
                <span class="spec-label">#{{ $i + 1 }}</span>
                @include('partials.motor-photo', ['motor' => $row['motor'], 'style' => 'width:48px;height:48px;min-height:0;font-size:7px'])
                <span class="spec-value" style="font-weight:700">{{ $row['motor']->label() }}</span>
                <span class="spec-value" style="color:var(--orange)">{{ ($config['format'])($row['value']) }}</span>
            </div>
        @endforeach
    </section>

    <section class="section" style="text-align:center">
        <p class="section-sub">Wil je twee van deze motoren rechtstreeks tegen elkaar laten racen?</p>
        <a class="btn primary" href="{{ route('simulation.index') }}">Start simulatie</a>
    </section>
@endsection
