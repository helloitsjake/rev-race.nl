<?php

namespace App\Services;

use App\Models\Motor;

/**
 * Signaleert onwaarschijnlijke motordata voor menselijke controle. Verandert of verwijdert
 * nooit zelf iets — geeft alleen redenen terug, zodat een reviewer (of de "klopt dit niet?"-
 * meldingen) kan beoordelen of het een echte fout is.
 */
class MotorDataAuditor
{
    /**
     * Ruime, plausibele bereiken voor een productiemotorfiets. Bewust breed (dit moet zeldzame
     * echte motoren niet ten onrechte markeren), bedoeld om duidelijke uitschieters te vangen,
     * geen precisie-check.
     */
    private const RANGES = [
        'power_hp' => [3, 350],
        'weight_kg' => [60, 500],
        'displacement_cc' => [49, 2500],
        'torque_nm' => [3, 300],
        'top_speed_kmh' => [40, 350],
    ];

    /**
     * Woorden die wijzen op een placeholder/testrecord in plaats van een echt merk of model,
     * niet op een onbekend-maar-legitiem merk (dat zou een deny-list van "echte merken" vergen
     * die we niet hebben en niet moeten verzinnen).
     */
    private const PLACEHOLDER_TERMS = ['zelfbouw', 'test', 'demo', 'onbekend', 'unknown', 'tbd', 'placeholder', 'n/a', 'nvt'];

    /**
     * @return array<int, string> lege array = geen bevindingen
     */
    public function flagsFor(Motor $motor): array
    {
        $flags = [];

        foreach (self::PLACEHOLDER_TERMS as $term) {
            if (str_contains(mb_strtolower((string) $motor->brand), $term)) {
                $flags[] = "merk lijkt een placeholder (\"{$motor->brand}\"), geen echt motormerk";
            }
            if (str_contains(mb_strtolower((string) $motor->model), $term)) {
                $flags[] = "model lijkt een placeholder (\"{$motor->model}\")";
            }
        }

        foreach (self::RANGES as $field => [$min, $max]) {
            $value = $motor->{$field};
            if ($value === null) {
                continue;
            }
            if ($value < $min || $value > $max) {
                $flags[] = "{$field}={$value} valt buiten het verwachte bereik ({$min}-{$max})";
            }
        }

        if (! $motor->category) {
            $flags[] = 'geen categorie toegekend';
        }

        if ($motor->weight_kg > 0 && $motor->power_hp > 0) {
            $ratio = $motor->power_hp / $motor->weight_kg;
            if ($ratio > 1.2) {
                $flags[] = "pk/kg-verhouding ({$ratio}) is extreem hoog, controleer vermogen en gewicht";
            }
        }

        return $flags;
    }

    /**
     * @return array<int, array{motor: Motor, flags: array<int, string>}>
     */
    public function auditAll(): array
    {
        return Motor::query()
            ->get()
            ->map(fn (Motor $motor) => ['motor' => $motor, 'flags' => $this->flagsFor($motor)])
            ->filter(fn (array $row) => ! empty($row['flags']))
            ->values()
            ->all();
    }
}
