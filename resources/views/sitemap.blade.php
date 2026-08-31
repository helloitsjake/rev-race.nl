@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
{{--
    Geen <priority> en <changefreq>: Google negeert beide al jaren. Wel <lastmod>, het enige veld
    dat wél meeweegt bij de vraag of een pagina opnieuw gecrawld moet worden. Vaste pagina's
    krijgen geen lastmod, omdat er voor die pagina's geen betrouwbare wijzigingsdatum bestaat.
--}}
@php
    $lastmod = fn ($date) => $date ? '<lastmod>' . \Illuminate\Support\Carbon::parse($date)->toAtomString() . '</lastmod>' : '';
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('wizard.index') }}</loc></url>
    <url><loc>{{ route('simulation.index') }}</loc></url>
    <url><loc>{{ route('most-searched.index') }}</loc></url>
    <url><loc>{{ route('partners.index') }}</loc></url>
    <url><loc>{{ route('partners.apply') }}</loc></url>
    <url><loc>{{ route('how-it-works') }}</loc></url>
    <url><loc>{{ route('kennis.index') }}</loc></url>
    <url><loc>{{ route('about') }}</loc></url>
    <url><loc>{{ route('privacy') }}</loc></url>
    <url><loc>{{ route('contact') }}</loc></url>
    <url><loc>{{ route('brands.index') }}</loc>{!! $lastmod($motorsLastmod) !!}</url>
    <url><loc>{{ route('segments.index') }}</loc>{!! $lastmod($motorsLastmod) !!}</url>
    <url><loc>{{ route('a2-motoren') }}</loc>{!! $lastmod($motorsLastmod) !!}</url>
    <url><loc>{{ route('yearly-report.show') }}</loc></url>
    @foreach($brandSlugs as $brand)
        <url><loc>{{ route('brands.show', $brand['slug']) }}</loc>{!! $lastmod($brand['lastmod']) !!}</url>
    @endforeach
    @foreach($modelSlugs as $model)
        <url><loc>{{ route('brands.model', [$model['brand'], $model['model']]) }}</loc>{!! $lastmod($model['lastmod']) !!}</url>
    @endforeach
    @foreach($segments as $segment)
        <url><loc>{{ route('segments.show', $segment['key']) }}</loc>{!! $lastmod($segment['lastmod']) !!}</url>
    @endforeach
    @foreach($partners as $partner)
        <url><loc>{{ route('partners.show', $partner) }}</loc>{!! $lastmod($partner->updated_at) !!}</url>
    @endforeach
    @foreach($articles as $article)
        <url><loc>{{ route('kennis.show', $article) }}</loc>{!! $lastmod($article->updated_at) !!}</url>
    @endforeach
    @foreach($toplijsten as $slug)
        <url><loc>{{ route('toplijst.show', $slug) }}</loc>{!! $lastmod($motorsLastmod) !!}</url>
    @endforeach
    @foreach($pairs as $pair)
        <url><loc>{{ route('compare.show', $pair['slug']) }}</loc>{!! $lastmod($pair['lastmod']) !!}</url>
    @endforeach
</urlset>
