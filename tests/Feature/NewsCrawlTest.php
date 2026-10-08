<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Services\NewsCrawlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewsCrawlTest extends TestCase
{
    use RefreshDatabase;

    private const ENGLISH_BODY = 'Can-Am Ryker is a three-wheeler with two wheels in front, a handlebar instead of a steering wheel, and an automatic transmission that handles shifting. Since its 2018 debut, it has been the entry point for people who have never operated a motorcycle, and the 2027 model adds new colors to the lineup for the season.';

    private const DUTCH_BODY = 'De nieuwe Ducati Multistrada V4 S Grand Tour is gebouwd voor de lange afstand. Met een grotere tank, koffers van het merk en een verwarmd zadel is het een motor voor wie de hele dag in het zadel wil zitten. De prijs is nog niet bekend voor Nederland.';

    private const FEED = <<<'XML'
<?xml version="1.0"?>
<rss><channel>
<item><title>2027 Test Bike First Look</title><link>https://example.com/test-bike</link>
<category>Motorcycle Previews</category><description>The 2027 Test Bike is new and fast.</description></item>
</channel></rss>
XML;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.anthropic.key' => 'test-key',
            'services.anthropic.model' => 'claude-sonnet-4-6',
            'ai.daily_budget_usd' => 2.20,
        ]);

        Mail::fake();
    }

    private function fakeFeeds(array $anthropicResponse, int $status = 200): void
    {
        Http::fake([
            'ultimatemotorcycling.com/*' => Http::response(self::FEED),
            'www.mcnews.com.au/*' => Http::response('<rss><channel></channel></rss>'),
            'api.anthropic.com/*' => Http::response($anthropicResponse, $status),
        ]);
    }

    private function dutchAiResponse(): array
    {
        return [
            'usage' => ['input_tokens' => 600, 'output_tokens' => 300],
            'content' => [['text' => json_encode([
                'title' => 'Nederlandse titel',
                'excerpt' => 'Korte Nederlandse samenvatting.',
                'body' => self::DUTCH_BODY,
            ])]],
        ];
    }

    private function englishArticle(): Article
    {
        return Article::create([
            'title' => '2027 Can-Am Ryker First Look: 11 Fast Facts',
            'slug' => '2027-can-am-ryker-first-look-11-fast-facts',
            'category' => Article::NEWS_CATEGORY,
            'excerpt' => 'Can-Am Ryker is a three-wheeler...',
            'body' => self::ENGLISH_BODY."\n\nBron: https://example.com/ryker",
            'source_name' => 'Ultimate Motorcycling',
            'source_url' => 'https://example.com/ryker',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);
    }

    public function test_bij_een_api_fout_wordt_er_niets_gepubliceerd_en_wordt_het_gemeld(): void
    {
        $this->fakeFeeds(['error' => ['message' => 'API key is invalid.']], 401);

        app(NewsCrawlService::class)->crawl();

        $this->assertSame(0, Article::count());
        $this->assertTrue(Cache::has('news-crawl-failure:'.now()->toDateString()));
    }

    public function test_een_onbruikbaar_ai_antwoord_wordt_niet_gepubliceerd(): void
    {
        $this->fakeFeeds(['usage' => ['input_tokens' => 1, 'output_tokens' => 1], 'content' => [['text' => 'geen json']]]);

        app(NewsCrawlService::class)->crawl();

        $this->assertSame(0, Article::count());
    }

    public function test_bij_een_opgestookt_budget_wordt_er_niets_gepubliceerd(): void
    {
        config(['ai.daily_budget_usd' => 0]);
        $this->fakeFeeds($this->dutchAiResponse());

        app(NewsCrawlService::class)->crawl();

        $this->assertSame(0, Article::count());
    }

    public function test_een_geslaagde_herschrijving_wordt_gepubliceerd(): void
    {
        $this->fakeFeeds($this->dutchAiResponse());

        app(NewsCrawlService::class)->crawl();

        $article = Article::sole();
        $this->assertSame('Nederlandse titel', $article->title);
        $this->assertStringEndsWith('Bron: https://example.com/test-bike', $article->body);
    }

    public function test_engelse_artikelen_worden_herschreven_met_behoud_van_slug_en_datum(): void
    {
        $article = $this->englishArticle();
        $publishedAt = $article->published_at;
        $this->fakeFeeds($this->dutchAiResponse());

        app(NewsCrawlService::class)->rewriteUntranslated();

        $article->refresh();
        $this->assertSame('Nederlandse titel', $article->title);
        $this->assertSame('2027-can-am-ryker-first-look-11-fast-facts', $article->slug);
        $this->assertTrue($publishedAt->equalTo($article->published_at));
        $this->assertSame(1, substr_count($article->body, 'Bron: '));
        $this->assertFalse(app(NewsCrawlService::class)->looksEnglish($article->body));
    }

    public function test_engelse_artikelen_blijven_ongewijzigd_als_herschrijven_faalt(): void
    {
        $article = $this->englishArticle();
        $this->fakeFeeds(['error' => ['message' => 'API key is invalid.']], 401);

        app(NewsCrawlService::class)->rewriteUntranslated();

        $this->assertSame('2027 Can-Am Ryker First Look: 11 Fast Facts', $article->refresh()->title);
    }

    public function test_taalcheck_onderscheidt_engels_van_nederlands(): void
    {
        $service = app(NewsCrawlService::class);

        $this->assertTrue($service->looksEnglish(self::ENGLISH_BODY));
        $this->assertFalse($service->looksEnglish(self::DUTCH_BODY));
    }
}
