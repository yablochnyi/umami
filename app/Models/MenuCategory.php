<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class MenuCategory extends Model
{
    use HasTranslations;

    protected $fillable = [
        'gopos_id',
        'goorder_id',
        'goorder_reference_id',
        'goorder_import_enabled',
        'goorder_published',
        'goorder_synced_at',
        'schedule_enabled',
        'schedule_hours',
        'name',
        'intro_text',
        'seo_text',
        'slug',
        'sort_order',
        'is_active',
        'gopos_payload',
        'gopos_synced_at',
    ];

    protected $casts = [
        'goorder_import_enabled' => 'boolean',
        'goorder_published' => 'boolean',
        'goorder_synced_at' => 'datetime',
        'schedule_enabled' => 'boolean',
        'schedule_hours' => 'array',
        'is_active' => 'boolean',
        'gopos_payload' => 'array',
        'gopos_synced_at' => 'datetime',
    ];

    public array $translatable = ['name', 'intro_text', 'seo_text'];

    public function scopeVisible($query)
    {
        return $query->where('is_active', true)->where('goorder_import_enabled', true)
            ->where(fn ($q) => $q->whereNull('goorder_published')->orWhere('goorder_published', true));
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
