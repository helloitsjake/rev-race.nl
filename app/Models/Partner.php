<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
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
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'usps' => 'array',
        ];
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
