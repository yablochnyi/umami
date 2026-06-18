<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'gopos_id',
        'name',
        'email',
        'phone',
        'nip',
        'city',
        'street',
        'building_number',
        'apartment_number',
        'gopos_payload',
        'gopos_synced_at',
    ];

    protected $casts = [
        'gopos_payload' => 'array',
        'gopos_synced_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
