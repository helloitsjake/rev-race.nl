<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WizardController extends Controller
{
    /**
     * @return array<string, string>
     */
    public static function experienceLevels(): array
    {
        return [
            'beginner' => 'Net rijbewijs / A2',
            'ervaren' => 'Vol rijbewijs / ervaren',
        ];
    }

    public function index(Request $request): View
    {
        $rijstijl = $request->query('rijstijl');
        $ervaring = $request->query('ervaring');

        $rijstijl = array_key_exists((string) $rijstijl, Motor::CATEGORIES) ? $rijstijl : null;
        $ervaring = array_key_exists((string) $ervaring, self::experienceLevels()) ? $ervaring : null;

        $matches = null;
        $fallback = null;

        if ($rijstijl && $ervaring) {
            $matches = $this->match($rijstijl, $ervaring);

            if ($matches->isEmpty() && $ervaring === 'beginner') {
                $fallback = $this->match(null, 'beginner');
            }
        }

        return view('wizard', [
            'categories' => Motor::CATEGORIES,
            'experienceLevels' => self::experienceLevels(),
            'selectedRijstijl' => $rijstijl,
            'selectedErvaring' => $ervaring,
            'matches' => $matches,
            'fallback' => $fallback,
        ]);
    }

    private function match(?string $rijstijl, string $ervaring): Collection
    {
        $query = Motor::query()->whereNotNull('category');

        if ($rijstijl) {
            $query->where('category', $rijstijl);
        }

        return $query->get()
            ->when($ervaring === 'beginner', fn ($motors) => $motors->filter(fn (Motor $motor) => $motor->isA2Eligible()))
            ->sortBy([['brand', 'asc'], ['model', 'asc']])
            ->values();
    }
}
