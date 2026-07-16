<?php

namespace App\Services;

use App\Models\Motor;

class SimulationService
{
    /**
     * @return array<string, mixed>
     */
    public function race(Motor $motorA, Motor $motorB, array $options): array
    {
        $condition = $this->condition($options['road_condition'] ?? 'dry');
        $distance = (int) ($options['distance_m'] ?? 500);
        $roadType = $options['road_type'] ?? 'straight';
        $riderA = (int) ($options['rider_a_kg'] ?? 0);
        $riderB = (int) ($options['rider_b_kg'] ?? $riderA);

        $a = $this->simulate($motorA, $distance, $condition, $riderA, $roadType);
        $b = $this->simulate($motorB, $distance, $condition, $riderB, $roadType);

        return [
            'time_a_s' => round($a['time'], 3),
            'time_b_s' => round($b['time'], 3),
            'winner' => $a['time'] <= $b['time'] ? 'A' : 'B',
            'delta_s' => round(abs($a['time'] - $b['time']), 3),
            'samples' => [
                'a' => $a['samples'],
                'b' => $b['samples'],
            ],
        ];
    }

    /**
     * Remafstand vanaf een gekozen snelheid. Remvertraging is vooral wegconditie
     * afhankelijk, met een lichte correctie voor gewicht (meer massa, iets meer
     * remweg bij gelijke bandengrip).
     *
     * @return array{distance_m: float, decel_ms2: float}
     */
    public function brakingDistance(Motor $motor, float $speedKmh, string $roadCondition): array
    {
        $condition = $this->condition($roadCondition);
        $speedMs = $speedKmh / 3.6;
        $weightFactor = (200 / max($motor->weight_kg, 1)) ** 0.15;
        $decel = 9.5 * $condition['mu_b'] * $weightFactor;
        $distance = ($speedMs ** 2) / (2 * $decel);

        return [
            'distance_m' => round($distance, 1),
            'decel_ms2' => round($decel, 2),
        ];
    }

    /**
     * @return array<string, float>
     */
    private function condition(string $condition): array
    {
        return match ($condition) {
            'wet' => ['mu_t' => 0.70, 'mu_b' => 0.72, 'mu_c' => 0.65],
            'rain' => ['mu_t' => 0.45, 'mu_b' => 0.50, 'mu_c' => 0.40],
            default => ['mu_t' => 1.00, 'mu_b' => 1.00, 'mu_c' => 1.00],
        };
    }

    /**
     * @param  array<string, float>  $condition
     * @return array{time: float, samples: array<int, array{x: float, v: int}>}
     */
    private function simulate(Motor $motor, int $distance, array $condition, int $extraMass, string $roadType): array
    {
        if ($roadType === 'twisty') {
            return $this->twisty($motor, $distance, $condition, $extraMass);
        }

        return $this->straight($motor, $distance, $condition, $extraMass);
    }

    /**
     * @param  array<string, float>  $condition
     * @return array{time: float, samples: array<int, array{x: float, v: int}>}
     */
    private function straight(Motor $motor, int $distance, array $condition, int $extraMass): array
    {
        $dt = 0.005;
        $rho = 1.2;
        $rollingResistance = 0.015;
        $gravity = 9.81;
        $shiftSpeed = [0, 0, 18, 30, 44, 58, 75];
        $gearRatio = [0, 12.5, 8.8, 6.8, 5.5, 4.6, 4.0];
        $totalMass = $motor->weight_kg + $extraMass;
        $maxForce = $condition['mu_t'] * $totalMass * $gravity;

        $velocity = 0.0;
        $position = 0.0;
        $time = 0.0;
        $gear = 1;
        $samples = [];
        $lastSample = -1.0;

        while ($position < $distance && $time < 180) {
            if ($gear < 6 && $velocity > $shiftSpeed[$gear + 1]) {
                $gear++;
            }

            $driveForce = min(
                ($motor->torque_nm * $this->torqueFactor($motor, $velocity) * $gearRatio[$gear]) / 0.305,
                ($motor->power_hp * 745.7) / max($velocity, 1),
                $maxForce,
            );

            $drag = 0.5 * $rho * $motor->drag_coefficient * $motor->frontal_area_m2 * $velocity * $velocity;
            $rolling = $rollingResistance * $totalMass * $gravity;
            $acceleration = ($driveForce - $drag - $rolling) / $totalMass;

            $velocity = max(0, $velocity + ($acceleration * $dt));
            $position += $velocity * $dt;
            $time += $dt;

            if ($lastSample < 0 || $position - $lastSample > $distance / 80) {
                $lastSample = $position;
                $samples[] = ['x' => round(min($position, $distance), 2), 'v' => (int) round($velocity * 3.6)];
            }
        }

        return ['time' => $time, 'samples' => $samples];
    }

    /**
     * @param  array<string, float>  $condition
     * @return array{time: float, samples: array<int, array{x: float, v: int}>}
     */
    private function twisty(Motor $motor, int $distance, array $condition, int $extraMass): array
    {
        $dt = 0.005;
        $rho = 1.2;
        $rollingResistance = 0.015;
        $gravity = 9.81;
        $shiftSpeed = [0, 0, 18, 30, 44, 58, 75];
        $gearRatio = [0, 12.5, 8.8, 6.8, 5.5, 4.6, 4.0];
        $totalMass = $motor->weight_kg + $extraMass;
        $brakingDecel = 12.5 * $condition['mu_b'];
        $maxForce = $condition['mu_t'] * $totalMass * $gravity;
        $cornerSkillFactor = $motor->weight_kg > 220 ? 1.07 : ($motor->weight_kg < 205 ? 1.01 : 1.04);

        $pattern = [
            ['type' => 'straight', 'distance' => 120],
            ['type' => 'corner', 'distance' => 70, 'radius' => 60],
            ['type' => 'straight', 'distance' => 90],
            ['type' => 'corner', 'distance' => 80, 'radius' => 45],
            ['type' => 'straight', 'distance' => 150],
            ['type' => 'corner', 'distance' => 60, 'radius' => 55],
            ['type' => 'straight', 'distance' => 100],
            ['type' => 'corner', 'distance' => 75, 'radius' => 70],
            ['type' => 'straight', 'distance' => 80],
            ['type' => 'corner', 'distance' => 65, 'radius' => 40],
        ];

        $segments = [];
        $covered = 0;
        $index = 0;
        while ($covered < $distance) {
            $segment = $pattern[$index % count($pattern)];
            $segment['distance'] = min($segment['distance'], $distance - $covered);
            $segments[] = $segment;
            $covered += $segment['distance'];
            $index++;
        }

        $steps = max(1, (int) ceil($distance / 2));
        $dx = $distance / $steps;
        $velocityLimit = array_fill(0, $steps, 300.0);
        $position = 0.0;

        foreach ($segments as $segment) {
            if ($segment['type'] === 'corner') {
                $limit = sqrt($condition['mu_c'] * $gravity * $segment['radius']) * $cornerSkillFactor;
                $start = (int) floor($position / $dx);
                $end = min($steps - 1, (int) floor(($position + $segment['distance']) / $dx));

                for ($i = $start; $i <= $end; $i++) {
                    $velocityLimit[$i] = min($velocityLimit[$i], $limit);
                }
            }

            $position += $segment['distance'];
        }

        for ($i = $steps - 2; $i >= 0; $i--) {
            $nextLimit = sqrt($velocityLimit[$i + 1] * $velocityLimit[$i + 1] + 2 * $brakingDecel * $dx);
            $velocityLimit[$i] = min($velocityLimit[$i], $nextLimit);
        }

        $velocity = 0.5;
        $position = 0.0;
        $time = 0.0;
        $gear = 1;
        $samples = [];
        $lastSample = -1.0;

        for ($i = 0; $i < $steps && $position < $distance; $i++) {
            $target = $velocityLimit[$i];
            $innerSteps = max(5, (int) round($dx / max($velocity, 0.5) / $dt));
            $stepDt = $dx / max($velocity, 0.5) / $innerSteps;

            for ($j = 0; $j < $innerSteps && $position < $distance; $j++) {
                if ($gear < 6 && $velocity > $shiftSpeed[$gear + 1]) {
                    $gear++;
                }

                $driveForce = $velocity < $target ? min(
                    ($motor->torque_nm * $this->torqueFactor($motor, $velocity) * $gearRatio[$gear]) / 0.305,
                    ($motor->power_hp * 745.7) / max($velocity, 1),
                    $maxForce,
                ) : 0;

                $brakingForce = $velocity > $target ? $totalMass * $brakingDecel : 0;
                $drag = 0.5 * $rho * $motor->drag_coefficient * $motor->frontal_area_m2 * $velocity * $velocity;
                $rolling = $rollingResistance * $totalMass * $gravity;
                $acceleration = ($driveForce - $brakingForce - $drag - $rolling) / $totalMass;

                $velocity = max(0.5, $velocity + ($acceleration * $stepDt));
                $position += $velocity * $stepDt;
                $time += $stepDt;
            }

            if ($lastSample < 0 || $position - $lastSample > $distance / 80) {
                $lastSample = $position;
                $samples[] = ['x' => round(min($position, $distance), 2), 'v' => (int) round($velocity * 3.6)];
            }
        }

        return ['time' => $time, 'samples' => $samples];
    }

    private function torqueFactor(Motor $motor, float $velocity): float
    {
        $engine = strtolower($motor->engine_type);

        if (str_contains($engine, 'twin') || str_contains($engine, 'v')) {
            return $velocity < 8 ? 0.85 + ($velocity / 8 * 0.15) : 1.0;
        }

        return $velocity < 15 ? 0.68 + ($velocity / 15 * 0.32) : 1.0;
    }
}
