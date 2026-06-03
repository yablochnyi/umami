<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class SiteText extends Model
{
    use HasTranslations;

    protected $fillable = [
        'group',
        'key',
        'label',
        'value',
        'type',
        'sort_order',
    ];

    public array $translatable = ['value'];
}
