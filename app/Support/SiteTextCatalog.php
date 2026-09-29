<?php

namespace App\Support;

use App\Models\SiteText;

final class SiteTextCatalog
{
    public static function label(SiteText $text): string
    {
        return __('texts.blocks')[$text->key] ?? __('texts.other_block');
    }
}
