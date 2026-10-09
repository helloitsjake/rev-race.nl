<?php

namespace App\Services;

use App\Models\AiUsageLog;
use App\Models\Article;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsCrawlService
{
    public function __construct(
        private readonly AiSpendGuard $guard,
        private readonly OpenAiClient $ai,
    ) {}

    /**
     * category_pattern selecteert alleen items die daadwerkelijk over een nieuw model gaan
     * (op basis van de categorie-tags die deze bronnen zelf aan zulke artikelen hangen),
     * zodat racewedstrijden, gear-reviews en industrienieuws niet als "Nieuwe releases" landen.
     */
    protected array $sources = [
        'Ultimate Motorcycling' => [
            'url' => 'https://ultimatemotorcycling.com/feed/',
            'category_pattern' => '/motorcycle previews/i',
        ],
        'MCNews.com.au' => [
            'url' => 'https://www.mcnews.com.au/feed/',
            'category_pattern' => '/^\d{4} motorcycles$/i',
        ],
    ];

    public function crawl(bool $dryRun = false): void
    {
        if (!$dryRun) {
            $this->rewriteUntranslated();
        }

        foreach ($this->sources as $name => $config) {
            $this->processSource($name, $config, $dryRun);
        }
    }

    protected function processSource(string $sourceName, array $config, bool $dryRun): void
    {
        try {
            $response = Http::get($config['url']);
            $xml = simplexml_load_string($response->body());

            if (!$xml || !isset($xml->channel->item)) {
                echo "Kon feed niet lezen voor {$sourceName} ({$config['url']})\n";
                return;
            }

            foreach ($xml->channel->item as $item) {
                $categories = [];
                foreach ($item->category as $category) {
                    $categories[] = (string) $category;
                }

                $isRelevant = (bool) array_filter(
                    $categories,
                    fn (string $category) => preg_match($config['category_pattern'], $category)
                );

                if (!$isRelevant) {
                    continue;
                }

                $title = trim((string) $item->title);

                // Bronnen taggen soms niet-motorvoertuigen (bv. een pick-up truck) onterecht
                // als "Motorcycle Previews". Extra check omdat dit direct live gepubliceerd wordt.
                if (preg_match('/\b(truck|pick-?up|car|auto|suv)\b/i', $title)) {
                    continue;
                }
                $link = trim((string) $item->link);
                $description = $this->cleanDescription((string) $item->description);
                $slug = Str::slug($title);

                if ($dryRun) {
                    echo "Gevonden: [{$sourceName}] {$title} ({$link})\n";
                    continue;
                }

                if (Article::where('slug', $slug)->exists()) {
                    continue;
                }

                $articleData = $this->generateArticle($title, $description, $link);

                // Mislukt het herschrijven, dan publiceren we niets. Het item staat de volgende
                // run nog in de feed en wordt dan opnieuw geprobeerd. Vroeger ging hier de
                // Engelse brontekst live, en dat viel wekenlang niemand op.
                if ($articleData === null) {
                    echo "Overgeslagen, herschrijven mislukt: {$title}\n";
                    continue;
                }

                Article::create([
                    'title' => $articleData['title'],
                    'slug' => $slug,
                    'category' => Article::NEWS_CATEGORY,
                    'excerpt' => $articleData['excerpt'],
                    'body' => $articleData['body'],
                    'source_name' => $sourceName,
                    'source_url' => $link,
                    'is_published' => true,
                    'published_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            echo "Fout bij verwerken van {$sourceName}: " . $e->getMessage() . "\n";
        }
    }

    protected function cleanDescription(string $description): string
    {
        $description = preg_replace('/The post .*? appeared first on .*?\.?$/s', '', $description);

        return trim(strip_tags($description));
    }

    protected function generateArticle(string $title, string $description, string $link): ?array
    {
        $system = "Je bent een gespecialiseerde redacteur voor RevRace, een platform voor motorliefhebbers.
Je krijgt een Engelstalig nieuwsbericht over een nieuw motormodel en je moet dit herschrijven tot een
boeiend, feitelijk correct artikel in het Nederlands van 200-300 woorden, of korter als de bron weinig
informatie bevat. Gebruik alleen feiten uit de aangeleverde tekst en verzin geen specificaties, prijzen of data.
Houd de toon professioneel, enthousiast en passend bij motorrijders.
Geef een pakkende titel, een korte samenvatting (excerpt) en de body in Markdown-formaat.
Geef je antwoord uitsluitend in JSON-formaat met de volgende keys: title, excerpt, body.";

        // De crawler valt onder hetzelfde dagbudget als de bezoekers-lookup. Dat is bewust:
        // is het budget opgestookt, dan is dat juist het moment om geen extra calls meer te
        // doen. Er wordt dan niets gepubliceerd; de volgende run probeert het opnieuw.
        $blocked = $this->guard->blockedReason(AiSpendGuard::PURPOSE_NEWS_ARTICLE);

        if ($blocked !== null) {
            $this->guard->recordBlocked(AiSpendGuard::PURPOSE_NEWS_ARTICLE, $blocked, null, null, $title);

            return null;
        }

        $reply = $this->ai->json($system, "Titel: {$title}\n\nBeschrijving: {$description}\n\nBron: {$link}", maxTokens: 1500);

        $this->guard->recordCall(
            AiSpendGuard::PURPOSE_NEWS_ARTICLE,
            $reply['ok'] ? AiUsageLog::OUTCOME_SUCCESS : AiUsageLog::OUTCOME_ERROR,
            $reply['model'],
            $reply['usage'],
            null,
            null,
            $title,
        );

        if (! $reply['ok']) {
            $this->reportFailure(sprintf('OpenAI gaf HTTP %d: %s', $reply['status'], $reply['error']));

            return null;
        }

        $text = (string) $reply['text'];
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($text));
        $data = json_decode($text, true);

        if (!is_array($data) || blank($data['title'] ?? null) || blank($data['body'] ?? null)) {
            $this->reportFailure('AI-antwoord was geen bruikbare JSON met title en body voor "'.$title.'".');

            return null;
        }

        return [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'] ?? Str::limit(strip_tags($data['body']), 150),
            'body' => $data['body']."\n\nBron: ".$link,
        ];
    }

    /**
     * Herschrijft nieuwsartikelen die nog in het Engels online staan. Die zijn ontstaan in
     * september 2026, toen de oude fallback bij een mislukte API-call de brontekst
     * onbewerkt publiceerde. Slug en publicatiedatum blijven gelijk, zodat al
     * geïndexeerde URL's blijven werken.
     */
    public function rewriteUntranslated(): int
    {
        $rewritten = 0;

        $articles = Article::query()
            ->where('category', Article::NEWS_CATEGORY)
            ->whereNotNull('source_url')
            ->get()
            ->filter(fn (Article $article) => $this->looksEnglish($article->body));

        foreach ($articles as $article) {
            $description = trim(preg_replace('/\s*Bron: \S+\s*$/', '', $article->body));
            $articleData = $this->generateArticle($article->title, $description, $article->source_url);

            // Faalt er één, dan faalt de rest vrijwel zeker om dezelfde reden (key, budget).
            if ($articleData === null) {
                echo "Herschrijven gestopt bij: {$article->title}\n";
                break;
            }

            $article->update($articleData);
            $rewritten++;
            echo "Herschreven naar NL: {$articleData['title']}\n";
        }

        return $rewritten;
    }

    /**
     * Grove taalcheck op veelvoorkomende functiewoorden. Goed genoeg om een Engelse
     * brontekst te onderscheiden van een Nederlandse herschrijving. De drempel is laag
     * omdat de MCNews-feed soms maar één zin aanlevert.
     */
    public function looksEnglish(string $text): bool
    {
        $words = str_word_count(strtolower($text), 1);
        $english = count(array_intersect($words, ['the', 'and', 'with', 'for', 'its', 'of', 'is', 'to']));
        $dutch = count(array_intersect($words, ['de', 'het', 'een', 'en', 'met', 'voor', 'van', 'is']));

        return $english >= 2 && $english > $dutch * 2;
    }

    /**
     * Een mislukte herschrijving is nu zichtbaar: in de log en met maximaal één mail per dag.
     */
    protected function reportFailure(string $reason): void
    {
        Log::error('Nieuwscrawler: '.$reason);

        if (!Cache::add('news-crawl-failure:'.now()->toDateString(), true, now()->endOfDay())) {
            return;
        }

        try {
            Mail::raw(
                "De nieuwscrawler van RevRace kon een artikel niet herschrijven, dus er is niets gepubliceerd.\n\n"
                ."Reden: {$reason}\n\nDe volgende run probeert het opnieuw. Check de OPENAI_API_KEY en het tegoed als dit blijft terugkomen.",
                fn ($message) => $message->to(config('ai.alert_email'))->subject('RevRace: nieuwscrawler kan niet herschrijven'),
            );
        } catch (\Throwable $exception) {
            Log::error('Foutmelding nieuwscrawler kon niet gemaild worden: '.$exception->getMessage());
        }
    }
}
