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
        'city',
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
        'submission_key',
        'tracking_token',
        'locale',
        'goorder_id',
        'goorder_token',
        'goorder_checkout',
        'goorder_quote',
        'goorder_status',
        'goorder_error',
        'goorder_submit_started_at',
        'goorder_synced_at',
        'expected_ready_at',
    ];

    protected $hidden = ['tracking_token', 'submission_key', 'goorder_token', 'goorder_checkout'];

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
        'goorder_token' => 'encrypted',
        'goorder_checkout' => 'encrypted:array',
        'goorder_quote' => 'array',
        'goorder_submit_started_at' => 'immutable_datetime',
        'goorder_synced_at' => 'immutable_datetime',
        'expected_ready_at' => 'immutable_datetime',
    ];

    public function trackingUrl(): string
    {
        return route('orders.track', ['token' => $this->tracking_token]);
    }

    public function trackingFinished(): bool
    {
        return in_array($this->status, ['completed', 'rejected', 'canceled', 'goorder_failed'], true);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
