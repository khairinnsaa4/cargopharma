<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'latitude',
        'longitude',
        'location_type',
        'has_cold_storage',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'has_cold_storage' => 'boolean',
    ];

    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_location_id');
    }

    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_location_id');
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class);
    }
}
