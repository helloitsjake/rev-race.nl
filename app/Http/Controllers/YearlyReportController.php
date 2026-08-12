<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * "Staat van de Nederlandse motorrijder": een jaarlijks, citeerbaar databestand op basis van
 * echte simulatiedata (geen enquête, geen verzonnen cijfers). Elke sectie toont zichzelf pas
 * als er genoeg samples zijn, zelfde "eerlijke lege staat"-principe als /meest-gezocht.
 */
class YearlyReportController extends Controller
{
    private const MIN_TOTAL_SIMULATIONS = 20;

    private const MIN_BUCKET_SIMULATIONS = 5;

    public function show(): View
    {
        $totalSimulations = DB::table('simulation_results')->count();
        $hasEnoughData = $totalSimulations >= self::MIN_TOTAL_SIMULATIONS;

        return view('staat-van-de-nederlandse-motorrijder', [
            'year' => now()->year,
            'totalSimulations' => $totalSimulations,
            'hasEnoughData' => $hasEnoughData,
            'topModels' => $hasEnoughData ? $this->topModels() : collect(),
            'topBrands' => $hasEnoughData ? $this->topBrands() : collect(),
            'conditionSplit' => $hasEnoughData ? $this->conditionSplit($totalSimulations) : collect(),
            'a2Share' => $hasEnoughData ? $this->a2Share() : null,
            'ridingStyleSplit' => $hasEnoughData ? $this->ridingStyleSplit() : collect(),
            'ageGroupBrands' => $hasEnoughData ? $this->ageGroupTopBrands() : collect(),
        ]);
    }

    private function combinedMotorIds(): \Closure
    {
        return function ($query) {
            $query->from('simulation_results')->select('motor_a_id as motor_id')
                ->unionAll(
                    DB::table('simulation_results')->select('motor_b_id as motor_id')
                );
        };
    }

    /**
     * @return Collection<int, array{motor: Motor, uses: int}>
     */
    private function topModels(int $limit = 10): Collection
    {
        $counts = DB::table('simulation_results')
            ->select('motor_id', DB::raw('COUNT(*) as uses'))
            ->fromSub($this->combinedMotorIds(), 'combined')
            ->groupBy('motor_id')
            ->orderByDesc('uses')
            ->limit($limit)
            ->pluck('uses', 'motor_id');

        if ($counts->isEmpty()) {
            return collect();
        }

        $motors = Motor::query()->whereIn('id', $counts->keys())->get()->keyBy('id');

        return $counts
            ->map(fn ($uses, $motorId) => isset($motors[$motorId]) ? ['motor' => $motors[$motorId], 'uses' => $uses] : null)
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array{brand: string, uses: int}>
     */
    private function topBrands(int $limit = 8): Collection
    {
        $rows = DB::table('simulation_results')
            ->fromSub($this->combinedMotorIds(), 'combined')
            ->join('motors', 'motors.id', '=', 'combined.motor_id')
            ->select('motors.brand', DB::raw('COUNT(*) as uses'))
            ->groupBy('motors.brand')
            ->orderByDesc('uses')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => ['brand' => $row->brand, 'uses' => $row->uses]);
    }

    /**
     * @return Collection<int, array{label: string, percentage: int}>
     */
    private function conditionSplit(int $total): Collection
    {
        $labels = ['dry' => 'Droog', 'wet' => 'Vochtig', 'rain' => 'Nat'];

        $counts = DB::table('simulation_results')
            ->select('road_condition', DB::raw('COUNT(*) as total'))
            ->groupBy('road_condition')
            ->pluck('total', 'road_condition');

        return collect($labels)
            ->map(fn ($label, $key) => [
                'label' => $label,
                'percentage' => $total > 0 ? round((($counts[$key] ?? 0) / $total) * 100) : 0,
            ])
            ->values();
    }

    /**
     * @return array{percentage: int, total_sides: int}|null
     */
    private function a2Share(): ?array
    {
        $rows = DB::table('simulation_results')->select('motor_a_id', 'motor_b_id')->get();

        if ($rows->isEmpty()) {
            return null;
        }

        $motorIds = $rows->flatMap(fn ($row) => [$row->motor_a_id, $row->motor_b_id])->unique();
        $eligibility = Motor::query()->whereIn('id', $motorIds)->get()
            ->mapWithKeys(fn (Motor $motor) => [$motor->id => $motor->isA2Eligible()]);

        $a2Count = 0;
        $total = 0;

        foreach ($rows as $row) {
            foreach ([$row->motor_a_id, $row->motor_b_id] as $motorId) {
                $total++;
                if ($eligibility[$motorId] ?? false) {
                    $a2Count++;
                }
            }
        }

        return [
            'percentage' => $total > 0 ? (int) round(($a2Count / $total) * 100) : 0,
            'total_sides' => $total,
        ];
    }

    /**
     * @return Collection<int, array{style: string, total: int}>
     */
    private function ridingStyleSplit(): Collection
    {
        $labels = ['recreatief' => 'Recreatief', 'sportief' => 'Sportief', 'track' => 'Track'];

        $rows = DB::table('simulation_results')
            ->join('users', 'users.id', '=', 'simulation_results.user_id')
            ->select('users.riding_style', DB::raw('COUNT(*) as total'))
            ->whereNotNull('users.riding_style')
            ->groupBy('users.riding_style')
            ->orderByDesc('total')
            ->get();

        return $rows->map(fn ($row) => ['style' => $labels[$row->riding_style] ?? $row->riding_style, 'total' => $row->total]);
    }

    /**
     * Populairste merk per leeftijdsgroep, alleen op basis van simulaties door ingelogde
     * gebruikers met een bekende geboortedatum. Een groep verschijnt alleen bij genoeg samples,
     * geen ranking op basis van 1 of 2 simulaties.
     *
     * @return Collection<int, array{label: string, top_brand: string, count: int, total: int}>
     */
    private function ageGroupTopBrands(): Collection
    {
        $rows = DB::table('simulation_results')
            ->join('users', 'users.id', '=', 'simulation_results.user_id')
            ->whereNotNull('users.birthdate')
            ->select('simulation_results.motor_a_id', 'simulation_results.motor_b_id', 'users.birthdate')
            ->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $motorBrands = Motor::query()->pluck('brand', 'id');
        $buckets = ['18-24' => [], '25-34' => [], '35-44' => [], '45+' => []];

        foreach ($rows as $row) {
            $age = Carbon::parse($row->birthdate)->age;
            $bucket = match (true) {
                $age < 25 => '18-24',
                $age < 35 => '25-34',
                $age < 45 => '35-44',
                default => '45+',
            };

            foreach ([$row->motor_a_id, $row->motor_b_id] as $motorId) {
                $brand = $motorBrands[$motorId] ?? null;
                if ($brand !== null) {
                    $buckets[$bucket][$brand] = ($buckets[$bucket][$brand] ?? 0) + 1;
                }
            }
        }

        return collect($buckets)
            ->map(function (array $brandCounts, string $label) {
                $total = array_sum($brandCounts);
                if ($total < self::MIN_BUCKET_SIMULATIONS) {
                    return null;
                }

                arsort($brandCounts);
                $topBrand = array_key_first($brandCounts);

                return [
                    'label' => $label,
                    'top_brand' => $topBrand,
                    'count' => $brandCounts[$topBrand],
                    'total' => $total,
                ];
            })
            ->filter()
            ->values();
    }
}
