@php echo '<?xml version="1.0" encoding="UTF-8"?>'; @endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc><priority>1.0</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('simulation.index') }}</loc><priority>0.9</priority><changefreq>weekly</changefreq></url>
    <url><loc>{{ route('partners.index') }}</loc><priority>0.5</priority><changefreq>monthly</changefreq></url>
    <url><loc>{{ route('privacy') }}</loc><priority>0.2</priority><changefreq>yearly</changefreq></url>
    <url><loc>{{ route('contact') }}</loc><priority>0.2</priority><changefreq>yearly</changefreq></url>
</urlset>
