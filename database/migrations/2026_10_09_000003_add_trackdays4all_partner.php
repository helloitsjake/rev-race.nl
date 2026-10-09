<?php

use App\Models\Partner;
use Illuminate\Database\Migrations\Migration;

/**
 * De eerste echte partner (9 okt 2026). Alle feiten komen van trackdays4all.nl zoals die site er
 * op 9 okt 2026 uitzag. Prijzen staan er bewust niet in: die wisselen met vroegboekacties, de
 * knoppen sturen door naar de actuele kalender.
 */
return new class extends Migration
{
    public function up(): void
    {
        Partner::query()->updateOrCreate(['slug' => 'trackdays4all'], [
            'name' => 'Trackdays4all',
            'category' => 'Circuitdagen',
            'description' => 'Circuitdagen sinds 1999 op TT Circuit Assen, Circuit des Ecuyers en Val de Vienne. Vrij rijden in je eigen niveaugroep, met begeleiding als je die wilt.',
            'website_url' => 'https://trackdays4all.nl/',
            'contact_email' => null,
            'contact_phone' => '+31 (0)85 - 87 70 427',
            'logo_url' => 'images/partners/trackdays4all/logo.svg',
            'hero_image' => 'images/partners/trackdays4all/val-de-vienne.jpg',
            'founded_year' => 1999,
            'opening_hours' => 'Ma t/m vr, 09:00 - 17:00',
            'about_text' => 'Trackdays4all begon in 1999 als hobbyproject van Jeroen Versteeg, die met een groep medecoureurs een plek zocht om te trainen voor het wegraceseizoen. Die eerste circuitdag op Val de Vienne staat 28 jaar later nog steeds op de kalender, en inmiddels reden er meer dan 15.000 deelnemers uit heel Europa mee. Sinds 2020 is het bedrijf van Kevin Valk. Hij reed in het WK SuperStock 1000, won daar een race op Assen en was hoofdinstructeur bij Trackdays4all voordat hij de organisatie overnam.',
            'why_choose_text' => 'Je rijdt vrij. Er rijdt geen verplichte instructeur voor je uit: je kiest zelf je tempo en je lijn, in een groep die is ingedeeld op snelheid en ervaring. Na de eerste rondetijden wordt die indeling nog bijgesteld. Wil je wel begeleiding, dan boek je persoonlijke videobegeleiding bij een van de tien instructeurs, die allemaal in een kampioenschap hebben gereden.',
            'usps' => [
                'Tijdwaarneming en transponder bij elke training inbegrepen',
                'Gratis veringadvies van HK Suspension (Öhlins en Wilbers), behalve bij Trackday 4 Starters',
                'Reparatieservice van HMB Paddock Service op de paddock',
                'Geaccrediteerd door KNMV, IDC en MON',
            ],
            'facts' => [
                ['value' => '1999', 'label' => 'Eerste circuitdag, op Val de Vienne'],
                ['value' => '15.000+', 'label' => 'Deelnemers uit heel Europa'],
                ['value' => '10', 'label' => 'Instructeurs met kampioenschapservaring'],
                ['value' => '3', 'label' => 'Vaste circuits op de kalender van 2027'],
            ],
            'venues' => [
                ['name' => 'TT Circuit Assen', 'place' => 'Assen, Nederland', 'length' => '4.555 m', 'corners' => '18', 'sound' => '101 dB', 'note' => 'Eendaagse circuitdagen en Trackday 4 Starters', 'url' => 'https://trackdays4all.nl/tt-circuit-assen/'],
                ['name' => 'Circuit des Ecuyers', 'place' => 'Château-Thierry, Frankrijk', 'length' => '3.500 m', 'corners' => '17', 'sound' => '95 dB', 'note' => 'Pinksterweekend, technisch en met hoogteverschil', 'url' => 'https://trackdays4all.nl/ecuyers/'],
                ['name' => 'Circuit du Val de Vienne', 'place' => 'Le Vigeant, Frankrijk', 'length' => '3.768 m', 'corners' => '14', 'sound' => 'Geen limiet', 'note' => 'Drie dagen voorjaarstraining', 'url' => 'https://trackdays4all.nl/val-de-vienne/'],
            ],
            'offers' => [
                ['tag' => 'Eerste keer', 'title' => 'Trackday 4 Starters', 'text' => 'Je eerste dag op TT Circuit Assen, met theorie, een circuitbriefing en instructie in kleine groepen. Maximaal 100 deelnemers en alleen standaard uitlaten.', 'points' => ['A, A1 of A2-rijbewijs is genoeg', 'Geen racekuipen nodig'], 'url' => 'https://trackdays4all.nl/trackday4starters/'],
                ['tag' => 'Meest gekozen', 'title' => 'Eendaagse circuitdag', 'text' => 'De klassieker op Assen: vijf sessies vrij rijden in een van vijf niveaugroepen, met de hele dag tijdwaarneming.', 'points' => ['Samen 1 uur en 33 minuten rijtijd', 'Indeling op ervaring en rondetijd'], 'url' => 'https://trackdays4all.nl/tt-circuit-assen/'],
                ['tag' => 'Meerdaags', 'title' => 'Ecuyers en Val de Vienne', 'text' => 'Twee of drie dagen in Frankrijk, voor wie rondetijden wil zoeken en seriewerk wil doen.', 'points' => ['Geen geluidslimiet op Val de Vienne', 'Transport van je motor mogelijk'], 'url' => 'https://trackdays4all.nl/val-de-vienne/'],
            ],
            'status' => 'verified',
            'verified_at' => now(),
            'internal_notes' => 'Eerste partner, live gezet op 9 okt 2026.',
            'sort_order' => 1,
        ]);
    }

    public function down(): void
    {
        Partner::query()->where('slug', 'trackdays4all')->delete();
    }
};
