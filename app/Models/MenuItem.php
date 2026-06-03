<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class MenuItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'menu_category_id',
        'name',
        'description',
        'price',
        'image',
        'source_image',
        'is_bestseller',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_bestseller' => 'boolean',
        'is_active' => 'boolean',
    ];

    public array $translatable = ['name', 'description'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }
}
