<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Meting: zie MEETPLAN.md. */
class MeasurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_elke_pagina_laadt_gtm_met_consent_standaard_op_geweigerd(): void
    {
        $html = $this->get('/over-ons')->assertOk()->getContent();

        $this->assertStringContainsString("gtag('consent', 'default'", $html);
        $this->assertStringContainsString("ad_storage: 'denied'", $html);
        $this->assertStringContainsString('GTM-NFH7Z6V5', $html);
        $this->assertStringContainsString('analytics.ahrefs.com/analytics.js', $html);
        $this->assertStringContainsString('page_type: "about"', $html);
        // De consent-standaard moet vóór GTM staan, anders vuurt GTM zonder consentstatus.
        $this->assertLessThan(strpos($html, 'GTM-NFH7Z6V5'), strpos($html, "gtag('consent', 'default'"));
    }

    public function test_contactformulier_meldt_een_lead_aan_de_datalayer(): void
    {
        Mail::fake();

        $this->from('/contact')->post('/contact', [
            'name' => 'Test', 'email' => 'test@example.com', 'message' => 'Hoi',
        ])->assertRedirect('/contact');

        $this->get('/contact')
            ->assertSee('"event":"generate_lead"', false)
            ->assertSee('"lead_type":"contact"', false);

        // Eenmalig: na een refresh mag de lead niet opnieuw tellen.
        $this->get('/contact')->assertDontSee('"event":"generate_lead"', false);
    }

    public function test_embed_laadt_geen_gtm(): void
    {
        $this->get('/embed')->assertDontSee('GTM-NFH7Z6V5', false);
    }
}
