<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Motor extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'year',
        'power_hp',
        'torque_nm',
        'weight_kg',
        'engine_type',
        'displacement_cc',
        'top_speed_kmh',
        'zero_to_hundred_s',
        'drag_coefficient',
        'frontal_area_m2',
        'photo_url',
        'source',
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
            'api_fetched_at' => 'datetime',
        ];
    }

    public function garageEntries(): HasMany
    {
        return $this->hasMany(GarageMotor::class);
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
}
