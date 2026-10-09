<?php

namespace Tests\Feature;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_trackdays4all_staat_live_met_circuits_en_aanbod(): void
    {
        $this->get('/partners')->assertOk()->assertSee('Trackdays4all');

        $this->get('/partners/trackdays4all')
            ->assertOk()
            ->assertSee('TT Circuit Assen')
            ->assertSee('Trackday 4 Starters')
            ->assertSee('utm_source=rev-race.nl', false)
            ->assertSee('rel="sponsored noopener"', false);
    }

    public function test_een_partner_zonder_extra_inhoud_krijgt_de_korte_pagina(): void
    {
        Partner::create([
            'name' => 'Testdealer', 'slug' => 'testdealer', 'category' => 'Dealer',
            'description' => 'Een dealer.', 'status' => 'verified',
        ]);

        $this->get('/partners/testdealer')->assertOk()->assertSee('Over Testdealer')->assertDontSee('Waar je rijdt');
    }

    public function test_een_niet_geverifieerde_partner_is_niet_zichtbaar(): void
    {
        Partner::create([
            'name' => 'Concept', 'slug' => 'concept', 'category' => 'Dealer',
            'description' => 'Nog niet klaar.', 'status' => 'draft',
        ]);

        $this->get('/partners/concept')->assertNotFound();
    }

    public function test_uitgaande_link_krijgt_utm_tags(): void
    {
        $partner = new Partner(['slug' => 'x', 'website_url' => 'https://voorbeeld.nl/?a=1']);

        $this->assertSame(
            'https://voorbeeld.nl/?a=1&utm_source=rev-race.nl&utm_medium=partner&utm_campaign=x&utm_content=partnerpagina',
            $partner->outboundUrl()
        );
    }
}
