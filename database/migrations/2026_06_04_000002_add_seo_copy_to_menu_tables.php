<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_categories', function (Blueprint $table) {
            $table->json('intro_text')->nullable()->after('name');
            $table->json('seo_text')->nullable()->after('intro_text');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->json('marketing_description')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('marketing_description');
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropColumn(['intro_text', 'seo_text']);
        });
    }
};
