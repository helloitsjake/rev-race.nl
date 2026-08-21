<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Motor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LlmsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_llms_txt_volgt_de_structuur_van_de_specificatie(): void
    {
        $response = $this->get('/llms.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');

        $body = $response->getContent();

        // llmstxt.org: H1 met de naam, daarna een blockquote met de samenvatting.
        $this->assertStringStartsWith('# RevRace', $body);
        $this->assertStringContainsString("\n> RevRace is een Nederlands motorplatform", $body);

        foreach (['## Kernpagina', '## Segmenten', '## Toplijsten', '## Optional'] as $sectie) {
            $this->assertStringContainsString($sectie, $body);
        }

        // Elk segment uit Motor::CATEGORIES hoort met eigen omschrijving in de index te staan.
        foreach (array_keys(Motor::CATEGORIES) as $key) {
            $this->assertStringContainsString('/segment/'.$key.')', $body);
        }
    }

    public function test_alleen_eigen_kennisartikelen_staan_in_de_index(): void
    {
        Article::create([
            'title' => 'Motorrijbewijs halen',
            'slug' => 'motorrijbewijs-halen',
            'category' => 'Beginnend motorrijder',
            'excerpt' => 'Wat het kost en hoe het werkt.',
            'body' => 'Tekst.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        Article::create([
            'title' => '2027 Yamaha YZ450FX first look',
            'slug' => '2027-yamaha-yz450fx-first-look',
            'category' => Article::NEWS_CATEGORY,
            'excerpt' => 'Herschreven uit een externe bron.',
            'body' => 'Tekst.',
            'source_name' => 'Externe bron',
            'source_url' => 'https://example.com/yz450fx',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        // Eigen redactie die wél een bron citeert: hoort er gewoon in te staan.
        Article::create([
            'title' => 'Van A2 naar onbeperkt',
            'slug' => 'a2-naar-onbeperkt-rijbewijs-doorstromen',
            'category' => 'Beginnend motorrijder',
            'excerpt' => 'Hoe doorstromen werkt.',
            'body' => 'Tekst.',
            'source_name' => 'Rijksoverheid.nl',
            'source_url' => 'https://www.rijksoverheid.nl/motorrijbewijs',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $body = $this->get('/llms.txt')->assertOk()->getContent();

        $this->assertStringContainsString('/kennis/motorrijbewijs-halen)', $body);
        $this->assertStringContainsString('/kennis/a2-naar-onbeperkt-rijbewijs-doorstromen)', $body);
        $this->assertStringNotContainsString('2027-yamaha-yz450fx-first-look', $body);
    }

    public function test_aantallen_komen_uit_de_database_en_lege_secties_vallen_weg(): void
    {
        $body = $this->get('/llms.txt')->assertOk()->getContent();

        $motoren = Motor::query()->count();
        $merken = Motor::query()->distinct()->count('brand');

        $this->assertStringContainsString($motoren.' motoren van '.$merken.' merken', $body);

        // Zonder eigen kennisartikelen hoort er geen lege sectie in het bestand te staan.
        $this->assertStringNotContainsString('## Kennisartikelen', $body);
        $this->assertStringContainsString('## Optional', $body);
    }
}
