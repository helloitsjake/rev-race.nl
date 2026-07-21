<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class NewsCrawlService
{
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
            'url' => 'https://mcnews.com.au/feed/',
            'category_pattern' => '/^\d{4} motorcycles$/i',
        ],
    ];

    public function crawl(bool $dryRun = false): void
    {
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

                Article::create([
                    'title' => $articleData['title'],
                    'slug' => $slug,
                    'category' => 'Nieuwe releases',
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

    protected function generateArticle(string $title, string $description, string $link): array
    {
        $system = "Je bent een gespecialiseerde redacteur voor RevRace, een platform voor motorliefhebbers.
Je krijgt een Engelstalig nieuwsbericht over een nieuw motormodel en je moet dit herschrijven tot een
boeiend, feitelijk correct artikel van 200-300 woorden in het Nederlands.
Houd de toon professioneel, enthousiast en passend bij motorrijders.
Geef een pakkende titel, een korte samenvatting (excerpt) en de body in Markdown-formaat.
Geef je antwoord uitsluitend in JSON-formaat met de volgende keys: title, excerpt, body.";

        $response = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1000,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => "Titel: {$title}\n\nBeschrijving: {$description}\n\nBron: {$link}"],
            ],
        ]);

        $text = (string) $response->json('content.0.text');
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/', '', trim($text));
        $data = json_decode($text, true);

        return [
            'title' => $data['title'] ?? $title,
            'excerpt' => $data['excerpt'] ?? Str::limit($description, 150),
            'body' => ($data['body'] ?? $description) . "\n\nBron: " . $link,
        ];
    }
}
