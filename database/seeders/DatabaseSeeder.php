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
     * @return array<int, array<string, mixed>>
     */
    private function motors(): array
    {
        return [
            ['brand' => 'BMW', 'model' => 'S1000XR', 'year' => 2017, 'power_hp' => 165, 'torque_nm' => 112, 'weight_kg' => 228, 'engine_type' => 'Inline-4', 'category' => 'tourer', 'displacement_cc' => 999, 'top_speed_kmh' => 274, 'zero_to_hundred_s' => 3.1, 'drag_coefficient' => 0.38, 'frontal_area_m2' => 0.650],
            ['brand' => 'KTM', 'model' => '1290 Super Duke R', 'year' => 2021, 'power_hp' => 177, 'torque_nm' => 144, 'weight_kg' => 200, 'engine_type' => 'V-twin', 'category' => 'naked', 'displacement_cc' => 1301, 'top_speed_kmh' => 289, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.52, 'frontal_area_m2' => 0.550],
            ['brand' => 'Honda', 'model' => 'CBR1000RR-R', 'year' => 2021, 'power_hp' => 217, 'torque_nm' => 113, 'weight_kg' => 201, 'engine_type' => 'Inline-4', 'category' => 'sport', 'displacement_cc' => 999, 'top_speed_kmh' => 299, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.36, 'frontal_area_m2' => 0.600],
            ['brand' => 'Kawasaki', 'model' => 'ZX-10R', 'year' => 2021, 'power_hp' => 203, 'torque_nm' => 114, 'weight_kg' => 207, 'engine_type' => 'Inline-4', 'category' => 'sport', 'displacement_cc' => 998, 'top_speed_kmh' => 299, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.37, 'frontal_area_m2' => 0.610],
            ['brand' => 'Ducati', 'model' => 'Panigale V4', 'year' => 2022, 'power_hp' => 214, 'torque_nm' => 124, 'weight_kg' => 198, 'engine_type' => 'V4', 'category' => 'sport', 'displacement_cc' => 1103, 'top_speed_kmh' => 305, 'zero_to_hundred_s' => 2.8, 'drag_coefficient' => 0.35, 'frontal_area_m2' => 0.580],
            ['brand' => 'Yamaha', 'model' => 'MT-10', 'year' => 2022, 'power_hp' => 166, 'torque_nm' => 112, 'weight_kg' => 210, 'engine_type' => 'Inline-4', 'category' => 'naked', 'displacement_cc' => 998, 'top_speed_kmh' => 260, 'zero_to_hundred_s' => 3.0, 'drag_coefficient' => 0.50, 'frontal_area_m2' => 0.570],
            ['brand' => 'Suzuki', 'model' => 'GSX-S1000', 'year' => 2021, 'power_hp' => 150, 'torque_nm' => 106, 'weight_kg' => 214, 'engine_type' => 'Inline-4', 'category' => 'naked', 'displacement_cc' => 999, 'top_speed_kmh' => 250, 'zero_to_hundred_s' => 3.2, 'drag_coefficient' => 0.48, 'frontal_area_m2' => 0.580],
            ['brand' => 'Triumph', 'model' => 'Speed Triple 1200 RS', 'year' => 2021, 'power_hp' => 180, 'torque_nm' => 125, 'weight_kg' => 198, 'engine_type' => 'Inline-3', 'category' => 'naked', 'displacement_cc' => 1160, 'top_speed_kmh' => 270, 'zero_to_hundred_s' => 3.0, 'drag_coefficient' => 0.51, 'frontal_area_m2' => 0.560],
            ['brand' => 'BMW', 'model' => 'M1000RR', 'year' => 2022, 'power_hp' => 212, 'torque_nm' => 113, 'weight_kg' => 192, 'engine_type' => 'Inline-4', 'category' => 'sport', 'displacement_cc' => 999, 'top_speed_kmh' => 306, 'zero_to_hundred_s' => 2.8, 'drag_coefficient' => 0.34, 'frontal_area_m2' => 0.590],
            ['brand' => 'Honda', 'model' => 'CB1300', 'year' => 2019, 'power_hp' => 114, 'torque_nm' => 116, 'weight_kg' => 259, 'engine_type' => 'Inline-4', 'category' => 'retro', 'displacement_cc' => 1284, 'top_speed_kmh' => 210, 'zero_to_hundred_s' => 3.8, 'drag_coefficient' => 0.55, 'frontal_area_m2' => 0.680],

            // A2-geschikte instapmotoren (max 35kW en max 0.20 kW/kg), toegevoegd zodat de
            // "welke motor past bij mij"-wizard ook voor beginners met een A2 rijbewijs matches oplevert.
            ['brand' => 'Honda', 'model' => 'CB500F', 'year' => 2022, 'power_hp' => 46, 'torque_nm' => 43, 'weight_kg' => 192, 'engine_type' => 'Parallel-twin', 'category' => 'naked', 'displacement_cc' => 471, 'top_speed_kmh' => 170, 'zero_to_hundred_s' => 6.5, 'drag_coefficient' => 0.58, 'frontal_area_m2' => 0.550],
            ['brand' => 'Honda', 'model' => 'CBR500R', 'year' => 2022, 'power_hp' => 46, 'torque_nm' => 43, 'weight_kg' => 195, 'engine_type' => 'Parallel-twin', 'category' => 'sport', 'displacement_cc' => 471, 'top_speed_kmh' => 170, 'zero_to_hundred_s' => 6.3, 'drag_coefficient' => 0.42, 'frontal_area_m2' => 0.520],
            ['brand' => 'Yamaha', 'model' => 'MT-03', 'year' => 2021, 'power_hp' => 42, 'torque_nm' => 29, 'weight_kg' => 168, 'engine_type' => 'Parallel-twin', 'category' => 'naked', 'displacement_cc' => 321, 'top_speed_kmh' => 150, 'zero_to_hundred_s' => 7.5, 'drag_coefficient' => 0.58, 'frontal_area_m2' => 0.520],
            ['brand' => 'Kawasaki', 'model' => 'Z400', 'year' => 2021, 'power_hp' => 45, 'torque_nm' => 37, 'weight_kg' => 179, 'engine_type' => 'Parallel-twin', 'category' => 'naked', 'displacement_cc' => 399, 'top_speed_kmh' => 160, 'zero_to_hundred_s' => 7.0, 'drag_coefficient' => 0.57, 'frontal_area_m2' => 0.530],
        ];
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
