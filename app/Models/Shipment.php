<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_location_id',
        'destination_location_id',
        'cargo_type',
        'target_temp_min',
        'target_temp_max',
        'total_weight_kg',
        'total_volume_cbm',
        'expected_departure',
        'expected_arrival',
        'actual_departure',
        'actual_arrival',
        'current_status',
        'is_delayed',
        'delay_duration_hours',
        'delay_severity',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'target_temp_min' => 'float',
        'target_temp_max' => 'float',
        'total_weight_kg' => 'float',
        'total_volume_cbm' => 'float',
        'expected_departure' => 'datetime',
        'expected_arrival' => 'datetime',
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
        'is_delayed' => 'boolean',
        'delay_duration_hours' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'origin_location_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'destination_location_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(ShipmentException::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    // Scopes for easy querying (clean code)
    public function scopeForCustomer($query, $userId)
    {
        return $query->where('customer_id', $userId);
    }

    public function scopeDelayed($query)
    {
        return $query->where('is_delayed', true);
    }

    public function scopeInTransit($query)
    {
        return $query->where('current_status', 'IN_TRANSIT');
    }

    public function scopeDelivered($query)
    {
        return $query->where('current_status', 'DELIVERED');
    }
}
