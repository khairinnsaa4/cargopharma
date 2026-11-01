<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackingEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'location_id',
        'event_type',
        'status_snapshot',
        'event_time',
        'recorded_lat',
        'recorded_lng',
        'ambient_temperature',
        'container_temperature',
        'description',
        'created_by',
    ];

    protected $casts = [
        'event_time' => 'datetime',
        'recorded_lat' => 'float',
        'recorded_lng' => 'float',
        'ambient_temperature' => 'float',
        'container_temperature' => 'float',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }
}
