<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Motor extends Model
{
    public const CATEGORIES = [
        'naked' => 'Naked',
        'sport' => 'Sportmotor',
        'tourer' => 'Toermotor',
        'adventure' => 'Adventure',
        'cruiser' => 'Cruiser',
        'retro' => 'Retro',
    ];

    /**
     * unverified: toegevoegd zonder gestructureerde bronvermelding (oude seed/manual data).
     * ai_researched: door een AI-agent opgezocht met bronvermelding, nooit door een mens nagekeken.
     * verified: een mens heeft de specificaties tegen een echte bron gecontroleerd.
     * flagged: gemeld via "klopt dit niet?" of door de dataquality-check, wacht op review.
     */
    public const VERIFICATION_STATUSES = ['unverified', 'ai_researched', 'verified', 'flagged'];

    protected $fillable = [
        'brand',
        'model',
        'year',
        'power_hp',
        'torque_nm',
        'weight_kg',
        'engine_type',
        'category',
        'displacement_cc',
        'top_speed_kmh',
        'zero_to_hundred_s',
        'drag_coefficient',
        'frontal_area_m2',
        'photo_url',
        'photo_credit',
        'photo_source_url',
        'seat_height_mm',
        'seat_height_source_url',
        'source',
        'verification_status',
        'data_checked_at',
        'reviewer',
        'confidence',
        'data_flags',
        'api_fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'power_hp' => 'integer',
            'torque_nm' => 'integer',
            'weight_kg' => 'integer',
            'displacement_cc' => 'integer',
            'top_speed_kmh' => 'integer',
            'zero_to_hundred_s' => 'float',
            'drag_coefficient' => 'float',
            'frontal_area_m2' => 'float',
            'seat_height_mm' => 'integer',
            'data_checked_at' => 'datetime',
            'api_fetched_at' => 'datetime',
        ];
    }

    public function garageEntries(): HasMany
    {
        return $this->hasMany(GarageMotor::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MotorReport::class);
    }

    public function label(): string
    {
        return "{$this->brand} {$this->model} {$this->year}";
    }

    public function slug(): string
    {
        return Str::slug("{$this->brand} {$this->model} {$this->year}");
    }

    public function powerToWeight(): float
    {
        return $this->weight_kg > 0 ? $this->power_hp / $this->weight_kg : 0.0;
    }

    /**
     * EU A2 rijbewijs: vermogen maximaal 35kW en vermogen/gewicht maximaal 0,20 kW/kg.
     * Alleen gebaseerd op de ongedrosseerde specificaties in de database; sommige modellen
     * hebben daarnaast een gedrosseerde fabrieksversie die hier niet in meegenomen wordt.
     */
    public function isA2Eligible(): bool
    {
        $powerKw = $this->power_hp * 0.7457;

        if ($powerKw > 35) {
            return false;
        }

        return $this->weight_kg > 0 && ($powerKw / $this->weight_kg) <= 0.20;
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category ?? 'Onbekend';
    }
}
