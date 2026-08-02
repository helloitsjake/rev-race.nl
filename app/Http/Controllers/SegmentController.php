<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\View\View;

class SegmentController extends Controller
{
    /**
     * Korte, feitelijke duiding per segment. Geen marketingtaal, sluit aan bij hoe de wizard
     * dezelfde categorieën al aan rijstijl/terrein koppelt.
     */
    public const DESCRIPTIONS = [
        'naked' => 'Een kale motor zonder kuip: rechtop zitten, direct sturen, veelzijdig voor zowel dagelijks gebruik als een stevige bocht.',
        'sport' => 'Gebouwd voor snelheid en leunhoek, met een sportieve, voorovergebogen zithouding.',
        'tourer' => 'Comfort en actieradius staan voorop: rustige zithouding, gemaakt voor lange afstanden op de snelweg.',
        'adventure' => 'De crossover tussen asfalt en onverhard terrein, herkenbaar aan het hoge zicht en de robuuste bouw.',
        'cruiser' => 'Lage zit, relaxte houding en veel nadruk op karakter en gevoel in plaats van pure topsnelheid.',
        'retro' => 'Klassieke vormgeving met moderne techniek eronder, vaak gekozen om de uitstraling, niet om de specificaties.',
    ];

    public function index(): View
    {
        $counts = Motor::query()
            ->whereNotNull('category')
            ->selectRaw('category, COUNT(*) as motor_count')
            ->groupBy('category')
            ->pluck('motor_count', 'category');

        $segments = collect(Motor::CATEGORIES)->map(fn ($label, $key) => [
            'key' => $key,
            'label' => $label,
            'description' => self::DESCRIPTIONS[$key] ?? null,
            'count' => $counts[$key] ?? 0,
        ])->values();

        return view('segments', ['segments' => $segments]);
    }

    public function show(string $categorie): View
    {
        abort_unless(array_key_exists($categorie, Motor::CATEGORIES), 404);

        $motors = Motor::query()
            ->where('category', $categorie)
            ->get()
            ->sortByDesc(fn (Motor $motor) => $motor->powerToWeight())
            ->values();

        abort_if($motors->isEmpty(), 404);

        $comparisons = $motors
            ->take(12)
            ->map(function (Motor $motor, int $i) use ($motors) {
                $partner = $motors->get($i + 1) ?? $motors->first();

                return $partner && ! $partner->is($motor)
                    ? ['motorA' => $motor, 'motorB' => $partner, 'slug' => "{$motor->slug()}-vs-{$partner->slug()}"]
                    : null;
            })
            ->filter()
            ->take(6)
            ->values();

        return view('segment-show', [
            'categorie' => $categorie,
            'label' => Motor::CATEGORIES[$categorie],
            'description' => self::DESCRIPTIONS[$categorie] ?? null,
            'motors' => $motors,
            'comparisons' => $comparisons,
        ]);
    }
}
