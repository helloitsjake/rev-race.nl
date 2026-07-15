<?php

namespace App\Http\Controllers;

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
        ];
    }
}
