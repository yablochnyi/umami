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
        'is_active' => 'boolean',
        'gopos_payload' => 'array',
        'gopos_synced_at' => 'datetime',
    ];

    public array $translatable = ['name', 'intro_text', 'seo_text'];

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }
}
