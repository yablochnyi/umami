<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'delivery_type',
        'fulfillment_type',
        'scheduled_at',
        'payment_type',
        'wants_invoice',
        'nip',
        'street',
        'building_number',
        'apartment_number',
        'comment',
        'subtotal',
        'delivery_cost',
        'total',
        'free_delivery_from',
        'minimum_delivery_amount',
        'gopos_id',
        'gopos_uid',
        'gopos_number',
        'gopos_payload',
        'gopos_error',
        'gopos_sent_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'wants_invoice' => 'boolean',
        'subtotal' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'total' => 'decimal:2',
        'free_delivery_from' => 'decimal:2',
        'minimum_delivery_amount' => 'decimal:2',
        'gopos_payload' => 'array',
        'gopos_sent_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
