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

    public function showModel(string $merk, string $model): View
    {
        $motors = Motor::query()->get();
        $motor = $motors->first(fn (Motor $candidate) => Str::slug($candidate->brand) === $merk && $candidate->slug() === $model);

        abort_if(! $motor, 404);

        return view('brand-model-show', [
            'brandSlug' => $merk,
            'motor' => $motor,
            'comparisons' => $this->modelComparisons($motors, $motor),
        ]);
    }

    /**
     * Vergelijkingen voor één specifiek model: zelfde categorie, merk van $motor eerst,
     * zelfde volgorde-logica als ComparisonController::relatedComparisons().
     *
     * @param  Collection<int, Motor>  $motors
     * @return Collection<int, array{motor: Motor, slug: string}>
     */
    private function modelComparisons(Collection $motors, Motor $motor): Collection
    {
        return $motors
            ->filter(fn (Motor $other) => $other->category !== null
                && $other->category === $motor->category
                && $other->isNot($motor))
            ->sort(fn (Motor $a, Motor $b) => [$a->brand === $motor->brand ? 0 : 1, $a->brand, $a->model]
                <=> [$b->brand === $motor->brand ? 0 : 1, $b->brand, $b->model])
            ->take(6)
            ->map(fn (Motor $other) => [
                'motor' => $other,
                'slug' => "{$motor->slug()}-vs-{$other->slug()}",
            ])
            ->values();
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
