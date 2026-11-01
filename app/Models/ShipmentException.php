<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentException extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'tracking_event_id',
        'exception_type',
        'severity',
        'status',
        'detected_at',
        'resolved_at',
        'root_cause',
        'resolution_notes',
        'resolved_by',
    ];

    protected $casts = [
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function trackingEvent(): BelongsTo
    {
        return $this->belongsTo(TrackingEvent::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'OPEN');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'INVESTIGATING');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'RESOLVED');
    }
}
