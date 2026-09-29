<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AnalyticsOrder extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['ordered_at' => 'immutable_datetime', 'payments' => 'array', 'synced_at' => 'immutable_datetime'];
    }

    public function items(): HasMany
    {
        return $this->hasMany(AnalyticsItem::class);
    }
}
