<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use Illuminate\View\View;

class ToplijstController extends Controller
{
    /**
     * @return array<string, array{title: string, description: string, field: string, unit: string, direction: string}>
     */
    public static function lists(): array
    {
        return [
            'beste-pk-kg-verhouding' => [
                'title' => 'Beste pk per kg verhouding',
                'description' => 'De motoren met de beste vermogen ten opzichte van hun gewicht.',
                'field' => null,
                'unit' => 'pk/kg',
                'direction' => 'desc',
                'value' => fn (Motor $motor) => $motor->powerToWeight(),
                'format' => fn ($value) => number_format($value, 2),
            ],
            'snelste-0-100-sprint' => [
                'title' => 'Snelste 0 naar 100 sprint',
                'description' => 'De motoren die het snelst van stilstand naar 100 km/h accelereren.',
                'unit' => 's',
                'direction' => 'asc',
                'value' => fn (Motor $motor) => $motor->zero_to_hundred_s,
                'format' => fn ($value) => number_format($value, 1).'s',
            ],
            'hoogste-topsnelheid' => [
                'title' => 'Hoogste topsnelheid',
                'description' => 'De motoren met de hoogste opgegeven topsnelheid.',
                'unit' => 'km/h',
                'direction' => 'desc',
                'value' => fn (Motor $motor) => $motor->top_speed_kmh,
                'format' => fn ($value) => number_format($value, 0).' km/h',
            ],
        ];
    }

    public function show(string $slug): View
    {
        $lists = self::lists();
        abort_unless(isset($lists[$slug]), 404);

        $config = $lists[$slug];

        $motors = Motor::query()->get()
            ->map(fn (Motor $motor) => ['motor' => $motor, 'value' => ($config['value'])($motor)])
            ->filter(fn ($row) => $row['value'] !== null)
            ->sortBy('value', SORT_REGULAR, $config['direction'] === 'desc')
            ->values();

        return view('toplijst', [
            'slug' => $slug,
            'config' => $config,
            'rows' => $motors,
        ]);
    }
}
