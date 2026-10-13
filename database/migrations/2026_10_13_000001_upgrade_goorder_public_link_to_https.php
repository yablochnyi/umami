<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->where('key', 'order_url')
            ->whereIn('value', ['http://umamisushifood.goorder.pl', 'http://umamisushifood.goorder.pl/'])
            ->update(['value' => 'https://umamisushifood.goorder.pl/']);
    }

    public function down(): void
    {
        // Keep the verified HTTPS URL when rolling back; do not downgrade it to HTTP.
    }
};
