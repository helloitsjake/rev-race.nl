<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'weight_kg',
        'height_cm',
        'age',
        'riding_style',
        'riding_experience_years',
        'license_category',
        'is_premium',
        'premium_until',
        'mollie_customer_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function garageMotors(): HasMany
    {
        return $this->hasMany(GarageMotor::class);
    }

    public function isPremium(): bool
    {
        return (bool) $this->is_premium
            && ($this->premium_until === null || $this->premium_until->isFuture());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'premium_until' => 'datetime',
            'is_premium' => 'boolean',
        ];
    }
}
