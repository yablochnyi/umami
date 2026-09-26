<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'gopos_id',
        'goorder_id',
        'goorder_reference_id',
        'goorder_published',
        'goorder_synced_at',
        'goorder_rules',
        'goorder_payload',
        'schedule_enabled',
        'schedule_hours',
        'menu_category_id',
        'gopos_category_id',
        'gopos_tax_id',
        'gopos_joint_id',
        'name',
        'slug',
        'description',
        'marketing_description',
        'seo_title',
        'seo_description',
        'price',
        'image',
        'source_image',
        'is_bestseller',
        'is_active',
        'gopos_payload',
        'gopos_synced_at',
        'sort_order',
    ];

    protected $casts = [
        'goorder_published' => 'boolean',
        'goorder_synced_at' => 'datetime',
        'goorder_rules' => 'array',
        'goorder_payload' => 'array',
        'schedule_enabled' => 'boolean',
        'schedule_hours' => 'array',
        'is_bestseller' => 'boolean',
        'is_active' => 'boolean',
        'gopos_payload' => 'array',
        'gopos_synced_at' => 'datetime',
    ];

    public array $translatable = ['name', 'description', 'marketing_description', 'seo_title', 'seo_description'];

    public function scopeVisible($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('goorder_published')->orWhere('goorder_published', true))
            ->whereHas('category', fn ($q) => $q->visible());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
