<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Services\MotorLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MotorController extends Controller
{
    public function search(Request $request, MotorLookupService $motors): JsonResponse
    {
        $query = (string) $request->query('q', '');

        return response()->json([
            'motors' => $motors->search($query)->map(fn ($motor) => $this->serialize($motor))->values(),
        ]);
    }

    public function lookup(Request $request, MotorLookupService $motors): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:160'],
        ]);

        try {
            $motor = $motors->findOrFetch($data['query']);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 404);
        }

        return response()->json(['motor' => $this->serialize($motor)]);
    }

    public function storeManual(Request $request): JsonResponse
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:80'],
            'model' => ['required', 'string', 'max:120'],
            'year' => ['required', 'integer', 'between:1950,'.((int) date('Y') + 1)],
            'power_hp' => ['required', 'integer', 'between:1,600'],
            'torque_nm' => ['required', 'integer', 'between:1,600'],
            'weight_kg' => ['required', 'integer', 'between:50,500'],
            'engine_type' => ['required', 'string', 'max:40'],
            'displacement_cc' => ['required', 'integer', 'between:49,3000'],
            'top_speed_kmh' => ['nullable', 'integer', 'between:50,400'],
            'zero_to_hundred_s' => ['nullable', 'numeric', 'between:1,15'],
        ]);

        $motor = Motor::query()->updateOrCreate(
            [
                'brand' => $data['brand'],
                'model' => $data['model'],
                'year' => $data['year'],
            ],
            $data + [
                'drag_coefficient' => 0.55,
                'frontal_area_m2' => 0.6,
                'source' => 'manual',
                'api_fetched_at' => now(),
            ],
        );

        return response()->json(['motor' => $this->serialize($motor)]);
    }

    private function serialize($motor): array
    {
        return [
            'id' => $motor->id,
            'label' => $motor->label(),
            'brand' => $motor->brand,
            'model' => $motor->model,
            'year' => $motor->year,
            'power_hp' => $motor->power_hp,
            'torque_nm' => $motor->torque_nm,
            'weight_kg' => $motor->weight_kg,
            'engine_type' => $motor->engine_type,
            'displacement_cc' => $motor->displacement_cc,
            'top_speed_kmh' => $motor->top_speed_kmh,
            'zero_to_hundred_s' => $motor->zero_to_hundred_s,
            'photo_url' => $motor->photo_url ? asset(ltrim($motor->photo_url, '/')) : null,
            'photo_credit' => $motor->photo_credit,
            'photo_source_url' => $motor->photo_source_url,
        ];
    }
}
