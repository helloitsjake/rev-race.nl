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
            'road_type' => ['required', Rule::in(['straight', 'twisty', 'topspeed', 'braking'])],
            'road_condition' => ['required', Rule::in(['dry', 'wet', 'rain'])],
            'distance_m' => ['required_if:road_type,straight,twisty', 'nullable', 'integer', Rule::in([100, 250, 500, 1000, 2000])],
            'speed_kmh' => ['required_if:road_type,braking', 'nullable', 'integer', Rule::in([50, 100, 130, 160])],
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

        $result = match ($data['road_type']) {
            'topspeed' => $this->runTopSpeed($motorA, $motorB),
            'braking' => $this->runBraking($motorA, $motorB, $data, $simulations),
            default => $simulations->race($motorA, $motorB, $data) + ['mode' => 'race'],
        };

        if ($result === null) {
            return response()->json([
                'message' => 'Voor deze motor is geen opgegeven topsnelheid bekend.',
            ], 422);
        }

        $limits->record($request->user(), $request->ip());
        $newStatus = $limits->status($request->user(), $request->ip());

        $result['motor_a'] = $motorA->label();
        $result['motor_b'] = $motorB->label();

        if ($result['mode'] === 'race') {
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

            $result['share_code'] = $share->share_code;
            $result['share_url'] = route('share.show', $share->share_code);
        }

        return response()->json([
            'result' => $result,
            'limit' => $newStatus,
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function runTopSpeed(Motor $motorA, Motor $motorB): ?array
    {
        if (! $motorA->top_speed_kmh || ! $motorB->top_speed_kmh) {
            return null;
        }

        return [
            'mode' => 'topspeed',
            'winner' => $motorA->top_speed_kmh >= $motorB->top_speed_kmh ? 'A' : 'B',
            'top_speed_kmh_a' => $motorA->top_speed_kmh,
            'top_speed_kmh_b' => $motorB->top_speed_kmh,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function runBraking(Motor $motorA, Motor $motorB, array $data, SimulationService $simulations): array
    {
        $a = $simulations->brakingDistance($motorA, (float) $data['speed_kmh'], $data['road_condition']);
        $b = $simulations->brakingDistance($motorB, (float) $data['speed_kmh'], $data['road_condition']);

        return [
            'mode' => 'braking',
            'winner' => $a['distance_m'] <= $b['distance_m'] ? 'A' : 'B',
            'braking_distance_m_a' => $a['distance_m'],
            'braking_distance_m_b' => $b['distance_m'],
            'speed_kmh' => $data['speed_kmh'],
        ];
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
