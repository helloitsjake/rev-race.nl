<?php

namespace Database\Seeders;

use App\Models\Motor;
use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ($this->motors() as $motor) {
            Motor::query()->updateOrCreate(
                [
                    'brand' => $motor['brand'],
                    'model' => $motor['model'],
                    'year' => $motor['year'],
                ],
                $motor,
            );
        }

        foreach ($this->partners() as $partner) {
            Partner::query()->updateOrCreate(['slug' => $partner['slug']], $partner);
        }
    }

    /**
     * Motorendata staat in database/data/motors.json (niet meer inline) sinds de database
     * uitgebreid werd naar 250+ modellen; een los, doorzoekbaar JSON bestand is dan praktischer
     * om te onderhouden en te reviewen dan een groeiende PHP array.
     *
     * @return array<int, array<string, mixed>>
     */
    private function motors(): array
    {
        return json_decode(file_get_contents(database_path('data/motors.json')), true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function partners(): array
    {
        return [
            [
                'name' => 'MotoPlus NL',
                'slug' => 'motoplus-nl',
                'category' => 'Dealer & Onderhoud',
                'description' => 'Dealer en onderhoudspartner voor sportieve straat- en trackmotoren.',
                'website_url' => null,
                'contact_email' => 'partners@rev-race.nl',
                'contact_phone' => '030 123 4567',
                'address_street' => 'Kanaalweg 45',
                'address_postcode' => '3526 KL',
                'address_city' => 'Utrecht',
                'founded_year' => 2011,
                'about_text' => 'MotoPlus NL is een onafhankelijke dealer en onderhoudswerkplaats, gespecialiseerd in sportieve straat- en trackmotoren. Het team bestaat uit gediplomeerde monteurs met racing achtergrond, die naast standaard onderhoud ook trackprep, bandenwissels en dynotests verzorgen.',
                'why_choose_text' => 'Waar veel dealers vooral verkopen, draait het bij MotoPlus om de motor zelf. Elke beurt wordt uitgevoerd door een monteur die zelf op het circuit rijdt, dus advies over afstelling of onderhoud komt uit ervaring, niet uit een boekje. RevRace gebruikers krijgen een gratis diagnosecheck bij hun eerste bezoek.',
                'usps' => [
                    'Gediplomeerde monteurs met racing ervaring',
                    'Trackprep en dynotest onder één dak',
                    'Gratis diagnosecheck voor RevRace gebruikers',
                    'Onderdelen vaak dezelfde week leverbaar',
                ],
                'opening_hours' => 'Ma t/m vr 08.30 tot 17.30, za 09.00 tot 13.00',
                'sort_order' => 1,
            ],
            [
                'name' => 'Vroom Verzekert',
                'slug' => 'vroom-verzekert',
                'category' => 'Verzekering',
                'description' => 'Motorverzekering op maat voor sport-, naked- en toermotoren.',
                'website_url' => null,
                'contact_email' => 'partners@rev-race.nl',
                'contact_phone' => '020 987 6543',
                'address_street' => 'Overtoom 158',
                'address_postcode' => '1054 HL',
                'address_city' => 'Amsterdam',
                'founded_year' => 2016,
                'about_text' => 'Vroom Verzekert is een onafhankelijk assurantiekantoor dat zich volledig richt op motorverzekeringen. Van dagelijkse toermotor tot circuit gereden supersport, de polissen zijn afgestemd op hoe je daadwerkelijk rijdt in plaats van een standaard tarief voor iedereen.',
                'why_choose_text' => 'Motorverzekeringen bij een algemene verzekeraar zitten vaak vol kleine lettertjes over trackdays of modificaties. Vroom Verzekert kent die uitzonderingen juist goed en bouwt de polis daaromheen, inclusief een trackday module voor wie regelmatig het circuit opzoekt.',
                'usps' => [
                    'Polissen die trackdays niet uitsluiten',
                    'Persoonlijk contact, geen callcenter',
                    'Vergelijking tussen meerdere verzekeraars inbegrepen',
                    'Schademelding via WhatsApp mogelijk',
                ],
                'opening_hours' => 'Ma t/m vr 09.00 tot 17.00',
                'sort_order' => 2,
            ],
            [
                'name' => 'TT Circuit Events',
                'slug' => 'tt-circuit-events',
                'category' => 'Trackdays & Events',
                'description' => 'Trackdays, rijtrainingen en vrije sessies voor verschillende niveaus.',
                'website_url' => null,
                'contact_email' => 'partners@rev-race.nl',
                'contact_phone' => '0592 34 56 78',
                'address_street' => 'Industrieweg 12',
                'address_postcode' => '9405 KB',
                'address_city' => 'Assen',
                'founded_year' => 2009,
                'about_text' => 'TT Circuit Events organiseert trackdays en rijtrainingen op en rond het TT Circuit Assen, van laagdrempelige introductiedagen tot begeleide sessies voor gevorderde rijders. Elke dag wordt begeleid door instructeurs met een licentie, met aparte niveaugroepen zodat beginners niet naast doorgewinterde racers hoeven te rijden.',
                'why_choose_text' => 'Een trackday boeken kan overweldigend zijn als je nog nooit op een circuit hebt gestaan. TT Circuit Events deelt rijders daarom altijd in op niveau en geeft voorafgaand een korte briefing, zodat de focus op plezier en veiligheid ligt in plaats van op tijden jagen.',
                'usps' => [
                    'Niveaugroepen van beginner tot gevorderd',
                    'Gelicenseerde instructeurs aanwezig',
                    'Eigen materiaal huren mogelijk',
                    'Foto en video van je sessie achteraf beschikbaar',
                ],
                'opening_hours' => 'Op trackday events, zie kalender',
                'sort_order' => 3,
            ],
        ];
    }
}
