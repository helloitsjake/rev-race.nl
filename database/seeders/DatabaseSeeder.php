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
            ['brand' => 'BMW', 'model' => 'S1000XR', 'year' => 2017, 'power_hp' => 165, 'torque_nm' => 112, 'weight_kg' => 228, 'engine_type' => 'Inline-4', 'displacement_cc' => 999, 'top_speed_kmh' => 274, 'zero_to_hundred_s' => 3.1, 'drag_coefficient' => 0.38, 'frontal_area_m2' => 0.650],
            ['brand' => 'KTM', 'model' => '1290 Super Duke R', 'year' => 2021, 'power_hp' => 177, 'torque_nm' => 144, 'weight_kg' => 200, 'engine_type' => 'V-twin', 'displacement_cc' => 1301, 'top_speed_kmh' => 289, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.52, 'frontal_area_m2' => 0.550],
            ['brand' => 'Honda', 'model' => 'CBR1000RR-R', 'year' => 2021, 'power_hp' => 217, 'torque_nm' => 113, 'weight_kg' => 201, 'engine_type' => 'Inline-4', 'displacement_cc' => 999, 'top_speed_kmh' => 299, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.36, 'frontal_area_m2' => 0.600],
            ['brand' => 'Kawasaki', 'model' => 'ZX-10R', 'year' => 2021, 'power_hp' => 203, 'torque_nm' => 114, 'weight_kg' => 207, 'engine_type' => 'Inline-4', 'displacement_cc' => 998, 'top_speed_kmh' => 299, 'zero_to_hundred_s' => 2.9, 'drag_coefficient' => 0.37, 'frontal_area_m2' => 0.610],
            ['brand' => 'Ducati', 'model' => 'Panigale V4', 'year' => 2022, 'power_hp' => 214, 'torque_nm' => 124, 'weight_kg' => 198, 'engine_type' => 'V4', 'displacement_cc' => 1103, 'top_speed_kmh' => 305, 'zero_to_hundred_s' => 2.8, 'drag_coefficient' => 0.35, 'frontal_area_m2' => 0.580],
            ['brand' => 'Yamaha', 'model' => 'MT-10', 'year' => 2022, 'power_hp' => 166, 'torque_nm' => 112, 'weight_kg' => 210, 'engine_type' => 'Inline-4', 'displacement_cc' => 998, 'top_speed_kmh' => 260, 'zero_to_hundred_s' => 3.0, 'drag_coefficient' => 0.50, 'frontal_area_m2' => 0.570],
            ['brand' => 'Suzuki', 'model' => 'GSX-S1000', 'year' => 2021, 'power_hp' => 150, 'torque_nm' => 106, 'weight_kg' => 214, 'engine_type' => 'Inline-4', 'displacement_cc' => 999, 'top_speed_kmh' => 250, 'zero_to_hundred_s' => 3.2, 'drag_coefficient' => 0.48, 'frontal_area_m2' => 0.580],
            ['brand' => 'Triumph', 'model' => 'Speed Triple 1200 RS', 'year' => 2021, 'power_hp' => 180, 'torque_nm' => 125, 'weight_kg' => 198, 'engine_type' => 'Inline-3', 'displacement_cc' => 1160, 'top_speed_kmh' => 270, 'zero_to_hundred_s' => 3.0, 'drag_coefficient' => 0.51, 'frontal_area_m2' => 0.560],
            ['brand' => 'BMW', 'model' => 'M1000RR', 'year' => 2022, 'power_hp' => 212, 'torque_nm' => 113, 'weight_kg' => 192, 'engine_type' => 'Inline-4', 'displacement_cc' => 999, 'top_speed_kmh' => 306, 'zero_to_hundred_s' => 2.8, 'drag_coefficient' => 0.34, 'frontal_area_m2' => 0.590],
            ['brand' => 'Honda', 'model' => 'CB1300', 'year' => 2019, 'power_hp' => 114, 'torque_nm' => 116, 'weight_kg' => 259, 'engine_type' => 'Inline-4', 'displacement_cc' => 1284, 'top_speed_kmh' => 210, 'zero_to_hundred_s' => 3.8, 'drag_coefficient' => 0.55, 'frontal_area_m2' => 0.680],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function partners(): array
    {
        return [
            ['name' => 'MotoPlus NL', 'slug' => 'motoplus-nl', 'category' => 'Dealer & Onderhoud', 'description' => 'Dealer en onderhoudspartner voor sportieve straat- en trackmotoren.', 'website_url' => null, 'contact_email' => 'partners@revrace.nl', 'sort_order' => 1],
            ['name' => 'Vroom Verzekert', 'slug' => 'vroom-verzekert', 'category' => 'Verzekering', 'description' => 'Motorverzekering op maat voor sport-, naked- en toermotoren.', 'website_url' => null, 'contact_email' => 'partners@revrace.nl', 'sort_order' => 2],
            ['name' => 'TT Circuit Events', 'slug' => 'tt-circuit-events', 'category' => 'Trackdays & Events', 'description' => 'Trackdays, rijtrainingen en vrije sessies voor verschillende niveaus.', 'website_url' => null, 'contact_email' => 'partners@revrace.nl', 'sort_order' => 3],
        ];
    }
}
