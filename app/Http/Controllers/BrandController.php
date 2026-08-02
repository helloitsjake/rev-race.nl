<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        $brands = Motor::query()
            ->select('brand')
            ->selectRaw('COUNT(*) as motor_count')
            ->groupBy('brand')
            ->orderBy('brand')
            ->get()
            ->map(fn ($row) => [
                'brand' => $row->brand,
                'slug' => Str::slug($row->brand),
                'count' => $row->motor_count,
            ]);

        return view('brands', ['brands' => $brands]);
    }

    public function show(string $merk): View
    {
        $motors = Motor::query()->get();
        $brandMotors = $motors->filter(fn (Motor $motor) => Str::slug($motor->brand) === $merk)->values();

        abort_if($brandMotors->isEmpty(), 404);

        $brandName = $brandMotors->first()->brand;

        $comparisons = $this->brandComparisons($motors, $brandMotors);

        return view('brand-show', [
            'brand' => $brandName,
            'slug' => $merk,
            'motors' => $brandMotors->sortBy('model')->values(),
            'comparisons' => $comparisons,
        ]);
    }

    /**
     * Vergelijkingen binnen dezelfde categorie waar minstens één van de twee motoren dit merk is,
     * zelfde beperking als ComparisonController::pairs() (alleen binnen categorie, geen kruis
     * tussen bijv. cruiser en supersport).
     *
     * @param  Collection<int, Motor>  $allMotors
     * @param  Collection<int, Motor>  $brandMotors
     * @return Collection<int, array{motorA: Motor, motorB: Motor, slug: string}>
     */
    private function brandComparisons(Collection $allMotors, Collection $brandMotors): Collection
    {
        $comparisons = collect();

        foreach ($brandMotors as $motorA) {
            if ($motorA->category === null) {
                continue;
            }

            $others = $allMotors->filter(fn (Motor $motor) => $motor->category === $motorA->category
                && $motor->isNot($motorA));

            foreach ($others as $motorB) {
                $comparisons->push([
                    'motorA' => $motorA,
                    'motorB' => $motorB,
                    'slug' => "{$motorA->slug()}-vs-{$motorB->slug()}",
                ]);
            }
        }

        return $comparisons
            ->unique(fn ($row) => collect([$row['motorA']->id, $row['motorB']->id])->sort()->implode('-'))
            ->take(12)
            ->values();
    }
}
