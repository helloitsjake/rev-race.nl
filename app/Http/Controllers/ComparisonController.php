<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Services\SimulationService;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function show(string $slug, SimulationService $simulations): View
    {
        [$slugA, $slugB] = $this->splitSlug($slug);

        $motors = Motor::query()->get();
        $motorA = $motors->first(fn (Motor $motor) => $motor->slug() === $slugA);
        $motorB = $motors->first(fn (Motor $motor) => $motor->slug() === $slugB);

        abort_if(! $motorA || ! $motorB || $motorA->is($motorB), 404);

        $conditions = ['dry' => 'Droog', 'wet' => 'Vochtig', 'rain' => 'Nat'];
        $results = [];

        foreach ($conditions as $key => $label) {
            $results[$key] = [
                'label' => $label,
                'result' => $simulations->race($motorA, $motorB, [
                    'road_type' => 'straight',
                    'road_condition' => $key,
                    'distance_m' => 500,
                ]),
            ];
        }

        return view('compare', [
            'motorA' => $motorA,
            'motorB' => $motorB,
            'results' => $results,
        ]);
    }

    /**
     * @return array<int, string>
     */
    public static function pairs(): Collection
    {
        $motors = Motor::query()->orderBy('brand')->orderBy('model')->get();
        $pairs = collect();

        foreach ($motors as $i => $motorA) {
            foreach ($motors->slice($i + 1) as $motorB) {
                $pairs->push("{$motorA->slug()}-vs-{$motorB->slug()}");
            }
        }

        return $pairs;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitSlug(string $slug): array
    {
        $parts = explode('-vs-', $slug, 2);

        return [$parts[0] ?? '', $parts[1] ?? ''];
    }
}
