<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    /**
     * draft: net aangemaakt, nog niet beoordeeld.
     * pending_verification: aanmelding binnen, wacht op controle van bedrijfsgegevens.
     * verified: gecontroleerd, mag publiek zichtbaar zijn.
     * rejected: afgekeurd (bv. onjuiste gegevens, geen echt bedrijf).
     * archived: was ooit verified, nu (tijdelijk) niet meer publiek getoond.
     */
    public const STATUSES = ['draft', 'pending_verification', 'verified', 'rejected', 'archived'];

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'website_url',
        'contact_email',
        'contact_phone',
        'logo_url',
        'address_street',
        'address_postcode',
        'address_city',
        'founded_year',
        'about_text',
        'why_choose_text',
        'usps',
        'opening_hours',
        'status',
        'verified_at',
        'internal_notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'usps' => 'array',
        ];
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', 'verified');
    }

    public function fullAddress(): ?string
    {
        if (! $this->address_street || ! $this->address_city) {
            return null;
        }

        return trim("{$this->address_street}, {$this->address_postcode} {$this->address_city}");
    }

    public function mapsUrl(): ?string
    {
        $address = $this->fullAddress();

        return $address ? 'https://www.google.com/maps/dir/?api=1&destination='.urlencode($address) : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
