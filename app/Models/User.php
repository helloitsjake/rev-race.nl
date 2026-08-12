<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const REFERRAL_SOURCES = [
        'zoekmachine' => 'Zoekmachine (Google, Bing)',
        'ai_assistent' => 'AI-assistent (ChatGPT, Perplexity, Copilot)',
        'social_media' => 'Social media',
        'motorforum' => 'Motorforum of community',
        'vriend_bekende' => 'Via een vriend of bekende',
        'dealer_partner' => 'Via een dealer of partner',
        'anders' => 'Anders',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'weight_kg',
        'height_cm',
        'birthdate',
        'riding_style',
        'referral_source',
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

    public function ensureGarageToken(): string
    {
        if (! $this->garage_token) {
            $this->garage_token = (string) Str::ulid();
            $this->save();
        }

        return $this->garage_token;
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
            'birthdate' => 'date',
            'premium_until' => 'datetime',
            'is_premium' => 'boolean',
        ];
    }
}
