<?php

namespace Tests\Feature;

use App\Models\Motor;
use App\Support\Alternatives;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** A2 per segment en alternatieven (24-11-2026). Draait op de echte motordata uit de migraties. */
class SeoLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a2_segmentpagina_toont_alleen_a2_modellen_met_faq(): void
    {
        $html = $this->get('/a2-motoren/sport')->assertOk()
            ->assertSee('A2 sportmotor: alle', false)
            ->assertSee('"@type":"FAQPage"', false)
            ->getContent();

        $tooStrong = Motor::query()->where('category', 'sport')->get()->first(fn (Motor $m) => ! $m->isA2Eligible());
        $this->assertStringNotContainsString('>' . $tooStrong->brand . ' ' . $tooStrong->model . '<', $html);
    }

    public function test_segment_zonder_genoeg_a2_modellen_geeft_404(): void
    {
        $this->get('/a2-motoren/tourer')->assertNotFound();
        $this->get('/a2-motoren/bestaat-niet')->assertNotFound();
    }

    public function test_a2_overzicht_linkt_naar_de_segmentpaginas(): void
    {
        $this->get('/a2-motoren')->assertOk()
            ->assertSee(route('a2-motoren.segment', 'naked'), false)
            ->assertSee('Hoeveel cc mag je rijden met een A2-rijbewijs?');
    }

    public function test_alternatievenpagina_voor_een_gekozen_model(): void
    {
        $slug = config('alternatieven.models')[0];
        $motor = Motor::query()->get()->first(fn (Motor $m) => $m->slug() === $slug);
        $brand = \Illuminate\Support\Str::slug($motor->brand);

        $this->get("/merken/{$brand}/{$slug}/alternatieven")->assertOk()
            ->assertSee('Alternatieven voor de ' . $motor->shortLabel(), false);

        // De modelpagina linkt ernaar.
        $this->get("/merken/{$brand}/{$slug}")->assertSee("/merken/{$brand}/{$slug}/alternatieven", false);
    }

    public function test_alternatieven_zitten_in_hetzelfde_segment_zonder_dubbele_families(): void
    {
        $motors = Motor::query()->get();

        $found = 0;
        foreach (config('alternatieven.models') as $slug) {
            // De testdatabase (alleen migraties) mist een paar modellen die op productie wel staan.
            if (! $motor = $motors->first(fn (Motor $m) => $m->slug() === $slug)) {
                continue;
            }
            $found++;
            $alts = Alternatives::for($motor, $motors);

            $this->assertGreaterThanOrEqual(Alternatives::MIN, $alts->count(), $slug);
            foreach ($alts as $i => $row) {
                $this->assertSame($motor->category, $row['motor']->category, $slug);
                $this->assertFalse(Alternatives::sameFamily($motor, $row['motor']), $slug);
                if ($motor->isA2Eligible()) {
                    $this->assertTrue($row['motor']->isA2Eligible(), $slug);
                }
                foreach ($alts->slice($i + 1) as $other) {
                    $this->assertFalse(Alternatives::sameFamily($row['motor'], $other['motor']), $slug);
                }
            }
        }
        $this->assertGreaterThanOrEqual(75, $found);
    }

    public function test_model_zonder_alternatievenpagina_geeft_404(): void
    {
        $motor = Motor::query()->get()->first(fn (Motor $m) => ! Alternatives::isListed($m));

        $this->get('/merken/' . \Illuminate\Support\Str::slug($motor->brand) . '/' . $motor->slug() . '/alternatieven')->assertNotFound();
    }

    public function test_modelpagina_heeft_de_cijfers_in_de_title(): void
    {
        $motor = Motor::query()->first();

        $this->get('/merken/' . \Illuminate\Support\Str::slug($motor->brand) . '/' . $motor->slug())
            ->assertSee('<title>' . e($motor->label()) . ': gewicht ' . $motor->weight_kg . ' kg, ' . $motor->power_hp . ' pk', false);
    }

    public function test_sitemap_is_een_index_met_deelsitemaps(): void
    {
        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<sitemapindex', false)
            ->assertSee(route('sitemaps.pages'), false)
            ->assertSee(route('sitemaps.models'), false)
            ->assertSee(route('sitemaps.comparisons', 1), false);

        $this->get('/sitemaps/paginas.xml')->assertOk()
            ->assertSee(route('a2-motoren.segment', 'retro'), false)
            ->assertSee('/alternatieven</loc>', false);

        $this->get('/sitemaps/modellen.xml')->assertOk()->assertSee('/merken/', false);
        $this->get('/sitemaps/vergelijkingen-1.xml')->assertOk()->assertSee('-vs-', false);
        $this->get('/sitemaps/vergelijkingen-999.xml')->assertNotFound();
    }

    public function test_vergelijkingssitemaps_bevatten_precies_alle_paren(): void
    {
        // Verwacht: elk paar binnen hetzelfde segment precies één keer (zoals ComparisonController::pairs()).
        $expected = \App\Models\Motor::query()->whereNotNull('category')->get()->groupBy('category')
            ->sum(fn ($group) => intdiv($group->count() * ($group->count() - 1), 2));
        $found = [];
        for ($n = 1; ; $n++) {
            $response = $this->get("/sitemaps/vergelijkingen-{$n}.xml");
            if ($response->status() === 404) {
                break;
            }
            preg_match_all('#/vergelijk/([^<]+)</loc>#', $response->getContent(), $m);
            $found = array_merge($found, $m[1]);
        }

        $this->assertCount($expected, $found);
        $this->assertCount($expected, array_unique($found));
    }
}
