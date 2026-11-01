<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'item_code',
        'product_name',
        'batch_number',
        'expiry_date',
        'quantity_vials',
        'doses_per_vial',
        'container_type',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_vials' => 'integer',
        'doses_per_vial' => 'integer',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
