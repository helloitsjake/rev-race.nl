<?php

namespace App\Http\Controllers;

use App\Models\Motor;
use App\Models\SimulationResult;
use App\Services\SimulationLimitService;
use App\Services\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function index(Request $request, SimulationLimitService $limits): View
    {
        return view('simulation', [
            'motors' => Motor::query()->orderBy('brand')->orderBy('model')->get(),
            'limit' => $limits->status($request->user(), $request->ip()),
        ]);
    }

    public function limit(Request $request, SimulationLimitService $limits): JsonResponse
    {
        return response()->json($limits->status($request->user(), $request->ip()));
    }

    public function run(Request $request, SimulationService $simulations, SimulationLimitService $limits): JsonResponse
    {
        $data = $request->validate([
            'motor_a_id' => ['required', 'integer', 'exists:motors,id', 'different:motor_b_id'],
            'motor_b_id' => ['required', 'integer', 'exists:motors,id'],
            'road_type' => ['required', Rule::in(['straight', 'twisty'])],
            'road_condition' => ['required', Rule::in(['dry', 'wet', 'rain'])],
            'distance_m' => ['required', 'integer', Rule::in([100, 250, 500, 1000, 2000])],
            'rider_a_kg' => ['nullable', 'integer', 'between:0,180'],
            'rider_b_kg' => ['nullable', 'integer', 'between:0,180'],
        ]);

        $status = $limits->status($request->user(), $request->ip());

        if ($status['blocked']) {
            return response()->json([
                'message' => 'Daglimiet bereikt.',
                'limit' => $status,
            ], 429);
        }

        $motorA = Motor::query()->findOrFail($data['motor_a_id']);
        $motorB = Motor::query()->findOrFail($data['motor_b_id']);
        $result = $simulations->race($motorA, $motorB, $data);

        $limits->record($request->user(), $request->ip());
        $newStatus = $limits->status($request->user(), $request->ip());

        $share = SimulationResult::query()->create([
            'share_code' => $this->shareCode(),
            'user_id' => $request->user()?->id,
            'motor_a_id' => $motorA->id,
            'motor_b_id' => $motorB->id,
            'road_type' => $data['road_type'],
            'road_condition' => $data['road_condition'],
            'distance_m' => $data['distance_m'],
            'rider_a_kg' => $data['rider_a_kg'] ?? null,
            'rider_b_kg' => $data['rider_b_kg'] ?? null,
            'time_a_s' => $result['time_a_s'],
            'time_b_s' => $result['time_b_s'],
            'winner' => $result['winner'],
            'samples' => $result['samples'],
        ]);

        return response()->json([
            'result' => $result + [
                'motor_a' => $motorA->label(),
                'motor_b' => $motorB->label(),
                'share_code' => $share->share_code,
                'share_url' => route('share.show', $share->share_code),
            ],
            'limit' => $newStatus,
        ]);
    }

    public function showShared(string $code): View
    {
        $result = SimulationResult::query()
            ->with(['motorA', 'motorB'])
            ->where('share_code', $code)
            ->firstOrFail();

        return view('shared', ['result' => $result]);
    }

    private function shareCode(): string
    {
        do {
            $code = Str::lower(Str::random(7));
        } while (SimulationResult::query()->where('share_code', $code)->exists());

        return $code;
    }
}
