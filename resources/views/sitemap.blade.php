@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><priority>1.0</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('wizard.index') }}</loc><priority>0.9</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('simulation.index') }}</loc><priority>0.9</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('partners.index') }}</loc><priority>0.5</priority><changefreq>monthly</changefreq></url>
    <url><loc>{{ route('partners.apply') }}</loc><priority>0.4</priority><changefreq>monthly</changefreq></url>
    <url><loc>{{ route('how-it-works') }}</loc><priority>0.7</priority><changefreq>monthly</changefreq></url>
    <url><loc>{{ route('kennis.index') }}</loc><priority>0.7</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('about') }}</loc><priority>0.4</priority><changefreq>monthly</changefreq></url>
    <url><loc>{{ route('privacy') }}</loc><priority>0.2</priority><changefreq>yearly</changefreq></url>
    <url><loc>{{ route('contact') }}</loc><priority>0.2</priority><changefreq>yearly</changefreq></url>
    @foreach($partners as $partner)
        <url><loc>{{ route('partners.show', $partner) }}</loc><priority>0.4</priority><changefreq>monthly</changefreq></url>
    @endforeach
    @foreach($articles as $article)
        <url><loc>{{ route('kennis.show', $article) }}</loc><priority>0.6</priority><changefreq>monthly</changefreq></url>
    @endforeach
    @foreach($toplijsten as $slug)
        <url><loc>{{ route('toplijst.show', $slug) }}</loc><priority>0.5</priority><changefreq>weekly</changefreq></url>
    @endforeach
    @foreach($pairs as $pair)
        <url><loc>{{ route('compare.show', $pair) }}</loc><priority>0.6</priority><changefreq>monthly</changefreq></url>
    @endforeach
</urlset>
