<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SubmitIndexNowCommand extends Command
{
    protected $signature = 'seo:indexnow {--dry-run : Alleen tonen hoeveel URLs gevonden worden, niets versturen}';
    protected $description = "Verstuurt alle URL's uit sitemap.xml naar IndexNow (Bing, Yandex, Seznam e.a.), zodat nieuwe of gewijzigde pagina's niet op een organische hercrawl moeten wachten.";

    /**
     * Vast IndexNow-key, ook gebruikt in public/{key}.txt ter verificatie van domeineigenaarschap
     * (IndexNow-protocol vereist dat bestand op de root van het domein). Geen geheim, alleen
     * bewijs dat wij deze URL's mogen aanmelden.
     */
    private const KEY = '4961e2805aa775eb29e85f416423ce9d';

    public function handle(): int
    {
        $sitemapUrl = rtrim(config('app.url'), '/').'/sitemap.xml';

        $response = Http::timeout(30)->get($sitemapUrl);

        if (! $response->successful()) {
            $this->error("Kon {$sitemapUrl} niet ophalen (status {$response->status()}).");

            return self::FAILURE;
        }

        $xml = simplexml_load_string($response->body());

        if ($xml === false) {
            $this->error('Sitemap kon niet als XML worden gelezen.');

            return self::FAILURE;
        }

        // <urlset> declareert een default namespace, dus $xml->url werkt niet zonder expliciete
        // namespace-registratie: SimpleXML matcht dan alles weg behalve wat toevallig zonder
        // namespace staat (in de praktijk: 0 of 1 resultaat).
        $xml->registerXPathNamespace('s', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urls = collect($xml->xpath('//s:url/s:loc') ?: [])->map(fn ($node) => (string) $node)->filter()->values();

        if ($urls->isEmpty()) {
            $this->warn('Geen URL\'s gevonden in de sitemap.');

            return self::SUCCESS;
        }

        $this->info("{$urls->count()} URL's gevonden in {$sitemapUrl}.");

        if ($this->option('dry-run')) {
            $this->comment('--dry-run: niets verstuurd naar IndexNow.');

            return self::SUCCESS;
        }

        $host = (string) parse_url($sitemapUrl, PHP_URL_HOST);
        $keyLocation = 'https://'.$host.'/'.self::KEY.'.txt';

        // IndexNow accepteert maximaal 10.000 URL's per aanvraag.
        foreach ($urls->chunk(10000) as $chunk) {
            $result = Http::timeout(30)->post('https://api.indexnow.org/indexnow', [
                'host' => $host,
                'key' => self::KEY,
                'keyLocation' => $keyLocation,
                'urlList' => $chunk->values()->all(),
            ]);

            if ($result->successful()) {
                $this->info("Batch van {$chunk->count()} URL's geaccepteerd (status {$result->status()}).");
            } else {
                $this->error("Batch van {$chunk->count()} URL's afgewezen (status {$result->status()}): {$result->body()}");
            }
        }

        return self::SUCCESS;
    }
}
